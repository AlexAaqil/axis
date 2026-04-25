<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class ObjectiveResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'label' => $this->label,
            'description' => $this->description,
            'color' => $this->color,
            'icon' => $this->icon,
            'sort_order' => $this->sort_order,
            'priority' => [
                'value' => $this->priority instanceof \App\Enums\Priority 
                    ? $this->priority->value 
                    : $this->priority,
                'label' => $this->when(
                    $this->priority instanceof \App\Enums\Priority,
                    fn() => $this->priority->label(),
                    fn() => \App\Enums\Priority::tryFrom($this->priority)?->label() ?? 'Unknown'
                ),
            ],
            'status' => [
                'value' => $this->status instanceof \App\Enums\Status 
                    ? $this->status->value 
                    : $this->status,
                'label' => $this->when(
                    $this->status instanceof \App\Enums\Status,
                    fn() => $this->status->label(),
                    fn() => \App\Enums\Status::tryFrom($this->status)?->label() ?? 'Unknown'
                ),
            ],
            'start_date' => $this->when(
                $this->start_date instanceof Carbon,
                fn() => $this->start_date->toDateString(),
                fn() => $this->start_date
            ),
            'due_date' => $this->when(
                $this->due_date instanceof Carbon,
                fn() => $this->due_date->toDateString(),
                fn() => $this->due_date
            ),
            'completed_at' => $this->when(
                $this->completed_at instanceof Carbon,
                fn() => $this->completed_at->toDateString(),
                fn() => $this->completed_at
            ),
            'is_overdue' => $this->isOverdue(),
            'quarter' => [
                'id' => $this->quarter->id,
                'label' => $this->quarter->label,
                'year' => $this->quarter->year->year,
            ],
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}