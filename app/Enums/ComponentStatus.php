<?php

namespace App\Enums;

enum ComponentStatus: string
{
    case OPERATIONAL = 'operational';
    case DEGRADED_PERFORMANCE = 'degraded_performance';
    case PARTIAL_OUTAGE = 'partial_outage';
    case MAJOR_OUTAGE = 'major_outage';
    case UNDER_MAINTENANCE = 'under_maintenance';

    public function label(): string
    {
        return match ($this) {
            self::OPERATIONAL => 'Operational',
            self::DEGRADED_PERFORMANCE => 'Degraded Performance',
            self::PARTIAL_OUTAGE => 'Partial Outage',
            self::MAJOR_OUTAGE => 'Major Outage',
            self::UNDER_MAINTENANCE => 'Under Maintenance',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::OPERATIONAL => 'green',
            self::DEGRADED_PERFORMANCE => '#D97706',
            self::PARTIAL_OUTAGE => 'orange',
            self::MAJOR_OUTAGE => 'red',
            self::UNDER_MAINTENANCE => 'blue',
        };
    }

    /**
     * Get the uptime weight for this status.
     * 
     * Returns the percentage (0.0 to 1.0) this status contributes to uptime.
     * Used for weighted uptime calculations in metrics.
     * 
     * @return float Weight from 0.0 (no credit) to 1.0 (full credit)
     */
    public function uptimeWeight(): float
    {
        $weights = config('metrics.uptime_weights', [
            'operational' => 1.0,
            'degraded_performance' => 0.75,
            'partial_outage' => 0.25,
            'major_outage' => 0.0,
            'under_maintenance' => 1.0,
        ]);

        return $weights[$this->value] ?? 0.0;
    }
}
