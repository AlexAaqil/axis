<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Objective;
use App\Models\Quarter;
use App\Http\Requests\Api\V1\ObjectiveRequest;
use App\Http\Resources\Api\V1\ObjectiveResource;
use App\Enums\Status;

class ObjectiveController extends Controller
{
    /**
     * Display a listing of objectives.
     * 
     * Supports filtering by:
     * - quarter_id
     * - status
     * - priority
     * - completed
     * 
     * GET /api/v1/objectives
     * GET /api/v1/objectives?quarter_id=1
     * GET /api/v1/objectives?quarter_id=1&status=1
     * GET /api/v1/objectives?quarter_id=1&priority=2
     */
    public function index(Request $request): JsonResponse
    {
        $query = Objective::with(['quarter.year']);

        // Filter by quarter ID
        if ($request->has('quarter_id')) {
            $query->where('quarter_id', $request->quarter_id);
        }

        // Filter by quarter year (e.g., year=2025)
        if ($request->has('year')) {
            $query->whereHas('quarter.year', function ($q) use ($request) {
                $q->where('year', $request->year);
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter completed/active objectives
        if ($request->has('completed')) {
            if ($request->boolean('completed')) {
                $query->where('status', Status::DONE->value);
            } else {
                $query->whereNotIn('status', [Status::DONE->value, Status::ARCHIVED->value]);
            }
        }

        // Search by label
        if ($request->has('search')) {
            $query->where('label', 'like', '%' . $request->search . '%');
        }

        // Sorting
        $sortField = $request->input('sort_by', 'sort_order');
        $sortDirection = $request->input('sort_direction', 'asc');
        
        $allowedSortFields = ['sort_order', 'priority', 'status', 'due_date', 'created_at', 'label'];
        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortDirection === 'desc' ? 'desc' : 'asc');
        }

        // Add secondary sort for consistency
        if ($sortField !== 'sort_order') {
            $query->orderBy('sort_order', 'asc');
        }

        // Pagination or all
        if ($request->has('per_page')) {
            $perPage = min($request->input('per_page', 15), 100); // Max 100 per page
            $objectives = $query->paginate($perPage);
            return ObjectiveResource::collection($objectives)->response();
        }

        $objectives = $query->get();
        return ObjectiveResource::collection($objectives)->response();
    }

    /**
     * Store a newly created objective.
     * 
     * POST /api/v1/objectives
     */
    public function store(ObjectiveRequest $request): JsonResponse
    {
        $data = $request->validated();
    
        // Auto-set completed_at only if status is DONE and not provided
        if (isset($data['status']) && $data['status'] == Status::DONE->value && empty($data['completed_at'])) {
            $data['completed_at'] = now()->toDateString();
        }
        
        $objective = Objective::create($data);

        return (new ObjectiveResource($objective->load(['quarter.year'])))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified objective.
     * 
     * GET /api/v1/objectives/{objective}
     */
    public function show(Objective $objective): JsonResponse
    {
        return (new ObjectiveResource($objective->load(['quarter.year'])))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Update the specified objective.
     * 
     * PUT/PATCH /api/v1/objectives/{objective}
     */
    public function update(ObjectiveRequest $request, Objective $objective): JsonResponse
    {
        $data = $request->validated();
        
        // Auto-set completed_at when status changes to DONE
        if (isset($data['status']) && $data['status'] == Status::DONE->value && empty($data['completed_at'])) {
            $data['completed_at'] = now()->toDateString();
        }
        
        // Clear completed_at if status is not DONE
        if (isset($data['status']) && $data['status'] != Status::DONE->value) {
            $data['completed_at'] = null;
        }
        
        $objective->update($data);

        return (new ObjectiveResource($objective->fresh()->load(['quarter.year'])))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Remove the specified objective.
     * 
     * DELETE /api/v1/objectives/{objective}
     */
    public function destroy(Objective $objective): JsonResponse
    {
        // Prevent deletion of completed objectives
        if ($objective->status->value === Status::DONE->value) {
            return response()->json([
                'message' => 'Cannot delete a completed objective. Archive it first.',
                'status' => 'error'
            ], 409);
        }

        $objective->delete();
        return response()->json(null, 204);
    }

    /**
     * Bulk update sort orders for reordering.
     * 
     * POST /api/v1/objectives/reorder
     * 
     * Request body:
     * {
     *     "objectives": [
     *         {"id": 1, "sort_order": 0},
     *         {"id": 2, "sort_order": 1},
     *         {"id": 3, "sort_order": 2}
     *     ]
     * }
     */
    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'objectives' => 'required|array',
            'objectives.*.id' => 'required|exists:objectives,id',
            'objectives.*.sort_order' => 'required|integer|min:0|max:9999',
        ]);

        foreach ($request->objectives as $item) {
            Objective::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json([
            'message' => 'Objectives reordered successfully',
            'status' => 'success'
        ], 200);
    }

    /**
     * Bulk update status for multiple objectives.
     * 
     * POST /api/v1/objectives/bulk-status
     * 
     * Request body:
     * {
     *     "objective_ids": [1, 2, 3],
     *     "status": 1
     * }
     */
    public function bulkStatusUpdate(Request $request): JsonResponse
    {
        $request->validate([
            'objective_ids' => 'required|array',
            'objective_ids.*' => 'exists:objectives,id',
            'status' => 'required|integer|in:' . implode(',', Status::values()),
        ]);

        $count = Objective::whereIn('id', $request->objective_ids)
            ->update(['status' => $request->status]);

        // If status is DONE, also set completed_at
        if ($request->status === Status::DONE->value) {
            Objective::whereIn('id', $request->objective_ids)
                ->update(['completed_at' => now()]);
        }

        return response()->json([
            'message' => "{$count} objectives updated successfully",
            'status' => 'success'
        ], 200);
    }

    /**
     * Get objectives grouped by status (for dashboard/kanban view).
     * 
     * GET /api/v1/objectives/kanban?quarter_id=1
     */
    public function kanban(Request $request): JsonResponse
    {
        $request->validate([
            'quarter_id' => 'required|exists:quarters,id',
        ]);

        $objectives = Objective::with(['quarter.year'])
            ->where('quarter_id', $request->quarter_id)
            ->orderBy('sort_order', 'asc')
            ->get();

        $grouped = [
            'todo' => ObjectiveResource::collection($objectives->where('status', Status::TODO->value)),
            'doing' => ObjectiveResource::collection($objectives->where('status', Status::DOING->value)),
            'done' => ObjectiveResource::collection($objectives->where('status', Status::DONE->value)),
            'archived' => ObjectiveResource::collection($objectives->where('status', Status::ARCHIVED->value)),
        ];

        return response()->json([
            'data' => $grouped,
            'status' => 'success'
        ], 200);
    }
}