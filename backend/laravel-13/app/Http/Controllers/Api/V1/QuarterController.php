<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Year;
use App\Models\Quarter;
use App\Http\Requests\Api\V1\QuarterRequest;
use App\Http\Resources\Api\V1\QuarterResource;

class QuarterController extends Controller
{
    public function index(Request $request)
    {
        $query = Quarter::with('year');

        // Filter by year if provided
        if ($request->has('year_id')) {
            $query->where('year_id', $request->year_id);
        }

        // Filter by year value (2026) instead of ID
        if ($request->has('year')) {
            $year = Year::where('year', $request->year)->firstOrFail();
            $query->where('year_id', $year->id);
        }

        // Order by year Desc, then by quarter number
        $quarters = $query->orderBy('year_id', 'desc')
            ->orderBy('label', 'asc')
            ->get();

        return QuarterResource::collection($quarters);
    }

    public function store(QuarterRequest $request)
    {
        $quarter = Quarter::create($request->validated());

        return (new QuarterResource($quarter->load('year')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Quarter $quarter)
    {
        return new QuarterResource($quarter->load('year'));
    }

    public function update(QuarterRequest $request, Quarter $quarter)
    {
        $quarter->update($request->validated());

        return new QuarterResource($quarter->load('year'));
    }

    public function destroy(Quarter $quarter)
    {
        $quarter->delete();

        return response()->json(null, 204);
    }

    /**
     * Get quarters for a specific year (convenience method).
     * 
     * GET /api/v1/years/{year}/quarters
     */
    public function getByYear(Year $year)
    {
        $quarters = $year->quarters()->orderBy('label')->get();
        
        return QuarterResource::collection($quarters);
    }
}
