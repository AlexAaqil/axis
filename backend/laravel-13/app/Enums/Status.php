<?php

namespace App\Enums;

enum Status: int
{
    case TODO = 0;
    case DOING = 1;
    case DONE = 2;
    case ARCHIVED = 3;

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match($this) {
            self::TODO => 'To Do',
            self::DOING => 'In Progress',
            self::DONE => 'Done',
            self::ARCHIVED => 'Archived',
        };
    }

    /**
     * Get all values for validation.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Check if status is a terminal state (cannot be changed).
     */
    public function isTerminal(): bool
    {
        return in_array($this, [self::DONE, self::ARCHIVED]);
    }

    /**
     * Get allowed next statuses from current status.
     */
    public function allowedTransitions(): array
    {
        return match($this) {
            self::TODO => [self::DOING->value, self::ARCHIVED->value],
            self::DOING => [self::DONE->value, self::ARCHIVED->value],
            self::DONE => [self::ARCHIVED->value],
            self::ARCHIVED => [],  // Cannot change from archived
        };
    }
}