<?php

namespace App\Enums;

use Illuminate\Support\Collection;

enum TaskStatus: int
{
    case NOT_STARTED = 0;
    case IN_PROGRESS = 1;
    case COMPLETE = 2;

    public function label(): string
    {
        return match ($this) {
            self::NOT_STARTED => 'Not Started',
            self::IN_PROGRESS => 'In Progress',
            self::COMPLETE => 'Complelte',
        };
    }

    public function labels(): array
    {
        $results = [];

        foreach (self::cases() as $case) {
            $results[$case->value] = $case->label();
        }
        
        return $results;
    }

    public static function ordered(): array
    {
        return [
            self::NOT_STARTED,
            self::IN_PROGRESS,
            self::COMPLETE,
        ];
    }

    public static function userGroupedStatuses(): Collection
    {
        $custom_order = array_map(fn($status) => $status->value, self::ordered());

        return auth()->user()->tasks()->get()->groupBy('status')
            ->map(function ($group, $status) {
                return (object) [
                    'status' => self::from($status),
                    'count' => $group->count(),
                ];
            })
            ->sortBy(fn($item) => array_search($item->status->value, $custom_order))
            ->values();
    }
}
