<?php

namespace App\Enums;

use Illuminate\Support\Collection;

enum TaskStatus: string
{
    case STARTED = 'started';
    case IN_PROGRESS = 'in_progress';
    case DONE = 'done';

    public function label(): string
    {
        return match ($this) {
            self::STARTED => 'Started',
            self::IN_PROGRESS => 'In Progress',
            self::DONE => 'Done',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::STARTED => 'border-yellow-500',
            self::IN_PROGRESS => 'border-blue-500',
            self::DONE => 'border-green-500',
        };
    }

    public static function ordered(): array
    {
        return [
            self::STARTED,
            self::IN_PROGRESS,
            self::DONE,
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
