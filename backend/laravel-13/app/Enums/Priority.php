<?php

namespace App\Enums;

enum Priority: int
{
    case LOW = 0;
    case MEDIUM = 1;
    case HIGH = 2;
    case URGENT = 3;

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match($this) {
            self::LOW => 'Low',
            self::MEDIUM => 'Medium',
            self::HIGH => 'High',
            self::URGENT => 'Urgent',
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
     * Get all labels for select dropdowns.
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn($case) => [
            $case->value => $case->label()
        ])->toArray();
    }
}