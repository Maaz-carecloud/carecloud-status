<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Uptime Calculation Weights
    |--------------------------------------------------------------------------
    |
    | Define how each component status contributes to uptime percentage.
    | Values range from 0.0 (no credit) to 1.0 (full credit).
    |
    | Standard Configuration:
    | - operational: 1.0 (100% - fully functional)
    | - degraded_performance: 0.75 (75% - slower but accessible)
    | - partial_outage: 0.25 (25% - limited functionality)
    | - major_outage: 0.0 (0% - completely down)
    | - under_maintenance: 1.0 (100% - planned, not a failure)
    |
    */
    'uptime_weights' => [
        'operational' => 1.0,
        'degraded_performance' => 0.75,
        'partial_outage' => 0.25,
        'major_outage' => 0.0,
        'under_maintenance' => 1.0,
    ],

    /*
    |--------------------------------------------------------------------------
    | SLA Calculation Settings
    |--------------------------------------------------------------------------
    |
    | Configure how SLA metrics are calculated and reported.
    |
    */
    'sla_calculation' => [
        // Include scheduled maintenance in uptime calculations
        'include_maintenance' => true,

        // Calculate based on business hours only (false = 24/7)
        'business_hours_only' => false,

        // Exclude scheduled maintenance from downtime
        'exclude_scheduled' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Calculation Mode
    |--------------------------------------------------------------------------
    |
    | Choose between 'weighted' or 'binary' uptime calculation.
    |
    | - weighted: Uses status weights (more realistic)
    | - binary: Only operational = uptime, everything else = downtime (stricter)
    |
    */
    'calculation_mode' => 'weighted',

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Configure caching for metrics to improve performance.
    |
    */
    'cache' => [
        // Cache duration in minutes for metrics
        'duration' => 60,

        // Enable/disable caching
        'enabled' => true,
    ],
];
