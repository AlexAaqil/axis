<?php

namespace App\Enums;

enum TaskPriority: int
{
	case LOW = 0;
	case MEDIUM = 1;
	case HIGH = 2;

	public function label(): string
	{
		return match ($this) {
			self::LOW => 'Low',
			self::MEDIUM => 'Medium',
			self::HIGH => 'High',
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
}