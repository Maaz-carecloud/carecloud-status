<?php

namespace App\Enums;

enum IncidentStatus: string
{
    case INVESTIGATING = 'investigating';
    case IDENTIFIED = 'identified';
    case MONITORING = 'monitoring';
    case RESOLVED = 'resolved';

    public function label(): string
    {
        return match ($this) {
            self::INVESTIGATING => 'Investigating',
            self::IDENTIFIED => 'Identified',
            self::MONITORING => 'Monitoring',
            self::RESOLVED => 'Resolved',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::INVESTIGATING => 'red',
            self::IDENTIFIED => 'orange',
            self::MONITORING => '#D97706',
            self::RESOLVED => 'green',
        };
    }
}
