<?php

namespace App\Enums;

enum IncidentImpact: string
{
    case MINOR = 'minor';
    case MAJOR = 'major';
    case CRITICAL = 'critical';

    public function label(): string
    {
        return match ($this) {
            self::MINOR => 'Minor',
            self::MAJOR => 'Major',
            self::CRITICAL => 'Critical',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::MINOR => '#D97706',
            self::MAJOR => 'orange',
            self::CRITICAL => 'red',
        };
    }

    public function sortOrder(): int
    {
        return match ($this) {
            self::MINOR => 1,
            self::MAJOR => 2,
            self::CRITICAL => 3,
        };
    }
}
