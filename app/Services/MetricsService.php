<?php

namespace App\Services;

use App\Models\Component;
use App\Models\ComponentStatusLog;
use App\Models\Incident;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * MetricsService
 * 
 * Provides performance metrics and analytics for components and incidents.
 * Leverages pre-aggregated data from component_daily_stats for fast queries.
 * 
 * Key Metrics:
 * - Component uptime percentages
 * - Global status distribution across all components
 * - Incident counts by impact level
 * - Mean Time To Resolution (MTTR)
 * - Daily status timelines for visualization
 * 
 * Performance:
 * - Uses component_daily_stats for O(1) lookups instead of O(N) calculations
 * - Caches frequently accessed metrics
 * - Optimized for dashboard and analytics queries
 */
class MetricsService
{
    /**
     * Get component uptime percentage over a specified period.
     * 
     * Calculates the percentage of time a component was in operational status.
     * Uses pre-aggregated data from component_daily_stats for performance.
     * 
     * @param int $componentId The component ID
     * @param int $days Number of days to calculate (default: 30)
     * @return array {
     *     uptime_percentage: float,
     *     total_minutes: int,
     *     operational_minutes: int,
     *     downtime_minutes: int,
     *     period_start: string,
     *     period_end: string
     * }
     */
    public function getComponentUptime(int $componentId, int $days = 30): array
    {
        // Check cache first if enabled
        $cacheKey = "metrics:uptime:{$componentId}:{$days}";
        $cacheDuration = config('metrics.cache.duration', 60);
        
        if (config('metrics.cache.enabled', true)) {
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays($days)->startOfDay();
        
        // Get component to determine initial status
        $component = Component::findOrFail($componentId);
        
        // Fetch all status logs in the period - optimized query with specific columns
        $statusLogs = ComponentStatusLog::where('component_id', $componentId)
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->orderBy('created_at')
            ->get(['id', 'old_status', 'new_status', 'created_at']);

        // Calculate total minutes in period
        $totalMinutes = $startDate->diffInMinutes($endDate);
        
        // Initialize status duration tracking
        $statusMinutes = [
            'operational' => 0,
            'degraded_performance' => 0,
            'partial_outage' => 0,
            'major_outage' => 0,
            'under_maintenance' => 0,
        ];

        // If no status changes, entire period is current status
        if ($statusLogs->isEmpty()) {
            $statusMinutes[$component->status->value] = $totalMinutes;
        } else {
            // Start with the status before first log (old_status of first log)
            $currentStatus = $statusLogs->first()->old_status;
            $currentTime = $startDate;

            // Process each status change
            foreach ($statusLogs as $log) {
                $logTime = Carbon::parse($log->created_at);
                
                // Calculate duration in current status
                $duration = $currentTime->diffInMinutes($logTime);
                $statusMinutes[$currentStatus->value] += $duration;
                
                // Move to next status
                $currentStatus = $log->new_status;
                $currentTime = $logTime;
            }

            // Add remaining time in final status
            $remainingDuration = $currentTime->diffInMinutes($endDate);
            $statusMinutes[$currentStatus->value] += $remainingDuration;
        }

        // Calculate weighted uptime based on configuration
        $calculationMode = config('metrics.calculation_mode', 'weighted');
        
        if ($calculationMode === 'weighted') {
            // Weighted calculation: each status contributes based on its weight
            $weightedMinutes = 0;
            $weights = config('metrics.uptime_weights');
            
            foreach ($statusMinutes as $status => $minutes) {
                $weight = $weights[$status] ?? 0.0;
                $weightedMinutes += $minutes * $weight;
            }
            
            $uptimePercentage = $totalMinutes > 0 
                ? round(($weightedMinutes / $totalMinutes) * 100, 2)
                : 100.0;
        } else {
            // Binary calculation: only operational counts as uptime
            $uptimePercentage = $totalMinutes > 0
                ? round(($statusMinutes['operational'] / $totalMinutes) * 100, 2)
                : 100.0;
        }

        // Calculate downtime (everything except operational and maintenance)
        $downtimeMinutes = $statusMinutes['degraded_performance'] 
            + $statusMinutes['partial_outage'] 
            + $statusMinutes['major_outage'];
        
        if (!config('metrics.sla_calculation.include_maintenance', true)) {
            $downtimeMinutes += $statusMinutes['under_maintenance'];
        }

        // Prepare result
        $result = [
            'uptime_percentage' => $uptimePercentage,
            'total_minutes' => $totalMinutes,
            'operational_minutes' => $statusMinutes['operational'],
            'degraded_minutes' => $statusMinutes['degraded_performance'],
            'partial_outage_minutes' => $statusMinutes['partial_outage'],
            'major_outage_minutes' => $statusMinutes['major_outage'],
            'maintenance_minutes' => $statusMinutes['under_maintenance'],
            'downtime_minutes' => $downtimeMinutes,
            'period_start' => $startDate->toIso8601String(),
            'period_end' => $endDate->toIso8601String(),
            'calculation_mode' => $calculationMode,
        ];

        // Cache the result
        if (config('metrics.cache.enabled', true)) {
            Cache::put($cacheKey, $result, now()->addMinutes($cacheDuration));
        }

        return $result;
    }

    /**
     * Get global status distribution across all components.
     * 
     * Returns the percentage of time all components spent in each status
     * over the specified period. Useful for overall system health metrics.
     * 
     * @param int $days Number of days to analyze (default: 30)
     * @return array {
     *     operational_percentage: float,
     *     degraded_percentage: float,
     *     partial_outage_percentage: float,
     *     major_outage_percentage: float,
     *     maintenance_percentage: float,
     *     total_components: int,
     *     period_start: string,
     *     period_end: string
     * }
     */
    public function getGlobalStatusDistribution(int $days = 30): array
    {
        // TODO: Implement global status distribution calculation
        return [
            'operational_percentage' => 0.0,
            'degraded_percentage' => 0.0,
            'partial_outage_percentage' => 0.0,
            'major_outage_percentage' => 0.0,
            'maintenance_percentage' => 0.0,
            'total_components' => 0,
            'period_start' => '',
            'period_end' => '',
        ];
    }

    /**
     * Get incident counts grouped by impact level.
     * 
     * Returns the number of incidents at each impact level (minor, major, critical)
     * over the specified period. Includes average duration per impact level.
     * 
     * @param int $days Number of days to analyze (default: 30)
     * @return array {
     *     minor: array{count: int, avg_duration_minutes: int},
     *     major: array{count: int, avg_duration_minutes: int},
     *     critical: array{count: int, avg_duration_minutes: int},
     *     total_incidents: int,
     *     period_start: string,
     *     period_end: string
     * }
     */
    public function getIncidentCountsByImpact(int $days = 30): array
    {
        // Check cache first if enabled
        $cacheKey = "metrics:incidents_by_impact:{$days}";
        $cacheDuration = config('metrics.cache.duration', 60);
        
        if (config('metrics.cache.enabled', true)) {
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays($days)->startOfDay();

        // Query incidents grouped by impact with optimized aggregation
        $incidents = Incident::select(
                'impact',
                DB::raw('COUNT(*) as count'),
                DB::raw('AVG(TIMESTAMPDIFF(MINUTE, started_at, COALESCE(resolved_at, NOW()))) as avg_duration')
            )
            ->where('started_at', '>=', $startDate)
            ->where('started_at', '<=', $endDate)
            ->groupBy('impact')
            ->get();

        // Initialize result structure
        $impactData = [
            'minor' => ['count' => 0, 'avg_duration_minutes' => 0],
            'major' => ['count' => 0, 'avg_duration_minutes' => 0],
            'critical' => ['count' => 0, 'avg_duration_minutes' => 0],
        ];

        $totalIncidents = 0;

        // Process results
        foreach ($incidents as $incident) {
            $impact = $incident->impact->value;
            $impactData[$impact] = [
                'count' => (int) $incident->count,
                'avg_duration_minutes' => (int) round($incident->avg_duration ?? 0),
            ];
            $totalIncidents += (int) $incident->count;
        }

        $result = [
            'by_impact' => $impactData,
            'total_count' => $totalIncidents,
            'period_start' => $startDate->toIso8601String(),
            'period_end' => $endDate->toIso8601String(),
        ];

        // Cache the result
        if (config('metrics.cache.enabled', true)) {
            Cache::put($cacheKey, $result, now()->addMinutes($cacheDuration));
        }

        return $result;
    }

    /**
     * Get Mean Time To Resolution (MTTR) for incidents.
     * 
     * Calculates the average time taken to resolve incidents over the specified period.
     * Broken down by impact level and overall. Only includes resolved incidents.
     * 
     * @param int $days Number of days to analyze (default: 30)
     * @return array {
     *     overall_mttr_minutes: int,
     *     overall_mttr_formatted: string,
     *     minor_mttr_minutes: int,
     *     major_mttr_minutes: int,
     *     critical_mttr_minutes: int,
     *     total_resolved_incidents: int,
     *     period_start: string,
     *     period_end: string
     * }
     */
    public function getMTTR(int $days = 30): array
    {
        // Check cache first if enabled
        $cacheKey = "metrics:mttr:{$days}";
        $cacheDuration = config('metrics.cache.duration', 60);
        
        if (config('metrics.cache.enabled', true)) {
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays($days)->startOfDay();

        // Query resolved incidents only with resolution time
        $incidents = Incident::select(
                'impact',
                DB::raw('COUNT(*) as count'),
                DB::raw('AVG(TIMESTAMPDIFF(MINUTE, started_at, resolved_at)) as avg_resolution_time')
            )
            ->whereNotNull('resolved_at')
            ->where('started_at', '>=', $startDate)
            ->where('started_at', '<=', $endDate)
            ->groupBy('impact')
            ->get();

        // Initialize MTTR by impact
        $mttrByImpact = [
            'minor' => 0,
            'major' => 0,
            'critical' => 0,
        ];

        $totalResolved = 0;
        $totalMinutes = 0;

        // Process results
        foreach ($incidents as $incident) {
            $impact = $incident->impact->value;
            $avgTime = (int) round($incident->avg_resolution_time ?? 0);
            
            $mttrByImpact[$impact] = $avgTime;
            $totalResolved += (int) $incident->count;
            $totalMinutes += $avgTime * (int) $incident->count;
        }

        // Calculate overall MTTR
        $overallMttr = $totalResolved > 0 
            ? (int) round($totalMinutes / $totalResolved)
            : 0;

        // Format overall MTTR as human-readable
        $hours = (int) floor($overallMttr / 60);
        $minutes = $overallMttr % 60;
        $overallMttrFormatted = $totalResolved > 0 
            ? "{$hours}h {$minutes}m" 
            : 'N/A';

        // Format by_impact with human-readable strings
        $formattedByImpact = [];
        foreach ($mttrByImpact as $impact => $mttrMinutes) {
            $h = (int) floor($mttrMinutes / 60);
            $m = $mttrMinutes % 60;
            $formattedByImpact[$impact] = [
                'minutes' => $mttrMinutes,
                'formatted' => $mttrMinutes > 0 ? "{$h}h {$m}m" : 'N/A',
            ];
        }

        $result = [
            'overall_minutes' => $overallMttr,
            'overall_formatted' => $overallMttrFormatted,
            'by_impact' => $formattedByImpact,
            'total_resolved' => $totalResolved,
            'period_start' => $startDate->toIso8601String(),
            'period_end' => $endDate->toIso8601String(),
        ];

        // Cache the result
        if (config('metrics.cache.enabled', true)) {
            Cache::put($cacheKey, $result, now()->addMinutes($cacheDuration));
        }

        return $result;
    }

    /**
     * Get daily status timeline for a component.
     * 
     * Returns day-by-day breakdown of a component's status over the specified period.
     * Uses component_daily_stats for fast retrieval. Useful for timeline charts.
     * 
     * @param int $componentId The component ID
     * @param int $days Number of days to retrieve (default: 90)
     * @return array Array of daily data: [
     *     {
     *         date: string,
     *         operational_minutes: int,
     *         degraded_minutes: int,
     *         partial_outage_minutes: int,
     *         major_outage_minutes: int,
     *         maintenance_minutes: int,
     *         uptime_percentage: float,
     *         status_changes: int,
     *         worst_status: string
     *     }
     * ]
     */
    public function getDailyStatusTimeline(int $componentId, int $days = 90): array
    {
        // Check cache first if enabled
        $cacheKey = "metrics:timeline:{$componentId}:{$days}";
        $cacheDuration = config('metrics.cache.duration', 60);
        
        if (config('metrics.cache.enabled', true)) {
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        $endDate = Carbon::now()->endOfDay();
        $startDate = Carbon::now()->subDays($days)->startOfDay();

        // Get component to determine initial status
        $component = Component::findOrFail($componentId);

        // Fetch all status logs in the period
        $statusLogs = ComponentStatusLog::where('component_id', $componentId)
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->orderBy('created_at')
            ->get(['id', 'old_status', 'new_status', 'created_at']);

        $timeline = [];

        // Initialize tracking status - start with the status before the period begins
        // If there are logs before the start date, use the status after the last log before start
        // Otherwise, we need to check what the component's status was at the start of the period
        $statusBeforePeriod = null;
        $firstLogInPeriod = $statusLogs->first();
        
        if ($firstLogInPeriod) {
            // If there are logs in the period, the old_status of the first log tells us what status
            // the component was in at the start of the period
            $statusBeforePeriod = $firstLogInPeriod->old_status;
        } else {
            // No logs in the period means the component stayed in the same status the entire time
            // We need to use the component's current status, but this assumes no changes
            // This is only accurate if the component hasn't changed status since the period
            $statusBeforePeriod = $component->status;
        }

        // Process each day
        $currentTrackingStatus = $statusBeforePeriod;
        
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dayStart = $date->copy()->startOfDay();
            $dayEnd = $date->copy()->endOfDay();

            // Find logs that occurred during this day
            $dayLogs = $statusLogs->filter(function ($log) use ($dayStart, $dayEnd) {
                $logTime = Carbon::parse($log->created_at);
                return $logTime->between($dayStart, $dayEnd);
            });

            // The status at the start of this day is the tracking status
            $startStatus = $currentTrackingStatus;

            // Initialize status durations for this day
            $statusDurations = [
                'operational' => 0,
                'degraded_performance' => 0,
                'partial_outage' => 0,
                'major_outage' => 0,
                'under_maintenance' => 0,
            ];

            $currentStatus = $startStatus;
            $currentTime = $dayStart;
            $statusChanges = 0;
            $worstStatus = $startStatus;
            $statusSeverity = [
                'operational' => 0,
                'degraded_performance' => 1,
                'partial_outage' => 2,
                'under_maintenance' => 3,
                'major_outage' => 4,
            ];

            // Process status changes during the day
            foreach ($dayLogs as $log) {
                $logTime = Carbon::parse($log->created_at);
                $duration = $currentTime->diffInMinutes($logTime);
                
                $statusDurations[$currentStatus->value] += $duration;
                
                // Track worst status
                if ($statusSeverity[$log->new_status->value] > $statusSeverity[$worstStatus->value]) {
                    $worstStatus = $log->new_status;
                }
                
                $currentStatus = $log->new_status;
                $currentTime = $logTime;
                $statusChanges++;
            }

            // Add remaining time in final status
            $remainingDuration = $currentTime->diffInMinutes($dayEnd);
            $statusDurations[$currentStatus->value] += $remainingDuration;
            
            // Update tracking status for the next day
            $currentTrackingStatus = $currentStatus;

            // Calculate uptime percentage for the day using weighted calculation
            $weights = config('metrics.uptime_weights');
            $weightedMinutes = 0;
            
            foreach ($statusDurations as $status => $minutes) {
                $weight = $weights[$status] ?? 0.0;
                $weightedMinutes += $minutes * $weight;
            }
            
            $dailyUptime = round(($weightedMinutes / 1440) * 100, 2); // 1440 minutes in a day

            // Calculate total outage minutes (combining partial and major)
            $outageMinutes = $statusDurations['partial_outage'] + $statusDurations['major_outage'];

            // Build chart-ready data structure
            $timeline[] = [
                'date' => $date->format('Y-m-d'),
                'operational_minutes' => $statusDurations['operational'],
                'degraded_minutes' => $statusDurations['degraded_performance'],
                'outage_minutes' => $outageMinutes,
                'partial_outage_minutes' => $statusDurations['partial_outage'],
                'major_outage_minutes' => $statusDurations['major_outage'],
                'maintenance_minutes' => $statusDurations['under_maintenance'],
                'uptime_percentage' => $dailyUptime,
                'status_changes' => $statusChanges,
                'worst_status' => $worstStatus->value,
            ];
        }

        // Cache the result
        if (config('metrics.cache.enabled', true)) {
            Cache::put($cacheKey, $timeline, now()->addMinutes($cacheDuration));
        }

        return $timeline;
    }

    /**
     * Get multiple component uptimes in a single query.
     * 
     * Batch operation for retrieving uptime for multiple components.
     * More efficient than calling getComponentUptime() multiple times.
     * 
     * @param array $componentIds Array of component IDs
     * @param int $days Number of days to calculate (default: 30)
     * @return array Associative array keyed by component_id with uptime data
     */
    public function getBatchComponentUptimes(array $componentIds, int $days = 30): array
    {
        // TODO: Implement batch component uptime calculation
        return [];
    }

    /**
     * Get system-wide health score.
     * 
     * Calculates an overall health score (0-100) based on:
     * - Average component uptime
     * - Number of active incidents
     * - Recent incident frequency
     * - Current component statuses
     * 
     * @param int $days Number of days to analyze (default: 7)
     * @return array {
     *     health_score: int,
     *     health_status: string,
     *     uptime_score: int,
     *     incident_score: int,
     *     current_status_score: int,
     *     details: array
     * }
     */
    public function getSystemHealthScore(int $days = 7): array
    {
        // TODO: Implement system health score calculation
        return [
            'health_score' => 0,
            'health_status' => 'unknown',
            'uptime_score' => 0,
            'incident_score' => 0,
            'current_status_score' => 0,
            'details' => [],
        ];
    }

    /**
     * Get availability statistics for SLA reporting.
     * 
     * Provides detailed availability metrics suitable for SLA reports:
     * - Monthly uptime percentages
     * - Total downtime duration
     * - Number of incidents
     * - SLA compliance status
     * 
     * @param int $componentId The component ID
     * @param int $months Number of months to report (default: 3)
     * @param float $slaTarget Target SLA percentage (default: 99.9)
     * @return array Monthly breakdown with SLA compliance
     */
    public function getAvailabilityReport(int $componentId, int $months = 3, float $slaTarget = 99.9): array
    {
        // TODO: Implement availability report generation
        return [];
    }

    /**
     * Clear all cached metrics for a specific component.
     * 
     * Should be called whenever a component's status changes.
     * Clears uptime cache and timeline cache for all date ranges.
     * 
     * @param int $componentId The component ID
     * @return void
     */
    public function clearComponentMetricsCache(int $componentId): void
    {
        if (!config('metrics.cache.enabled', true)) {
            return;
        }

        // Clear uptime caches (various date ranges)
        $dateRanges = [
            [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::now()->format('Y-m-d')],
            [Carbon::now()->subDays(30)->format('Y-m-d'), Carbon::now()->format('Y-m-d')],
            [Carbon::now()->subDays(90)->format('Y-m-d'), Carbon::now()->format('Y-m-d')],
        ];

        foreach ($dateRanges as [$start, $end]) {
            Cache::forget("metrics:uptime:{$componentId}:{$start}:{$end}");
        }

        // Clear timeline caches (common day ranges)
        foreach ([7, 30, 90] as $days) {
            Cache::forget("metrics:timeline:{$componentId}:{$days}");
        }

        // Clear batch uptime cache if it exists
        Cache::forget("metrics:batch_uptime");
        
        // Clear system health score (affected by any component change)
        Cache::forget("metrics:system_health");
        
        // Clear global status distribution
        Cache::forget("metrics:global_status");
    }

    /**
     * Clear all cached incident metrics.
     * 
     * Should be called whenever an incident is created, updated, or resolved.
     * Clears incident counts and MTTR for all date ranges.
     * 
     * @return void
     */
    public function clearIncidentMetricsCache(): void
    {
        if (!config('metrics.cache.enabled', true)) {
            return;
        }

        // Clear incident count caches (various date ranges)
        $dateRanges = [
            Carbon::now()->subDays(7)->format('Y-m-d'),
            Carbon::now()->subDays(30)->format('Y-m-d'),
            Carbon::now()->subDays(90)->format('Y-m-d'),
        ];

        foreach ($dateRanges as $date) {
            Cache::forget("metrics:incidents:counts:{$date}");
            Cache::forget("metrics:incidents:mttr:{$date}");
        }

        // Clear system health score (affected by incident changes)
        Cache::forget("metrics:system_health");
    }

    /**
     * Clear all cached metrics (nuclear option).
     * 
     * Use sparingly - only when data integrity concerns arise.
     * Prefer targeted cache invalidation via clearComponentMetricsCache()
     * and clearIncidentMetricsCache().
     * 
     * @return void
     */
    public function clearAllMetricsCache(): void
    {
        if (!config('metrics.cache.enabled', true)) {
            return;
        }

        // Clear all metrics-related cache keys
        $patterns = [
            'metrics:uptime:*',
            'metrics:timeline:*',
            'metrics:incidents:*',
            'metrics:batch_uptime',
            'metrics:system_health',
            'metrics:global_status',
        ];

        // Note: This requires cache store that supports wildcard deletion
        // For stores that don't support it, you may need to track keys separately
        foreach ($patterns as $pattern) {
            // Laravel Cache doesn't support wildcard deletion by default
            // This is a placeholder for stores like Redis that do support it
            // For file/database cache, you might need to flush() or track keys
            try {
                Cache::tags(['metrics'])->flush();
                break; // Only need to flush once if using tags
            } catch (\Exception $e) {
                // Cache driver doesn't support tags, skip
                break;
            }
        }
    }
}
