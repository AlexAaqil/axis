<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuarterResource extends JsonResource
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
            'label' => $this->label,
            'start_date' => $this->start_date->toDateString(),  // YYYY-MM-DD format
            'end_date' => $this->end_date->toDateString(),
            'date_range' => $this->date_range,  // Accessor from model
            'year' => [
                'id' => $this->year->id,
                'year' => $this->year->year,
            ]
        ];
    }
}
