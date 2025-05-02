<?php

namespace App\Enums;

enum UserStatus: int
{
    case INACTIVE = 0;
    case ACTIVE = 1;
    case BANNED = 2;

    public function label(): string
    {
        return match($this) {
            self::INACTIVE => 'Inactive',
            self::ACTIVE => 'Active',
            self::BANNED => 'Banned',
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
