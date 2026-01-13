<?php

namespace App\Services;

use App\Enums\ComponentStatus;
use App\Enums\IncidentImpact;
use App\Models\Component;
use App\Models\ComponentStatusLog;
use App\Models\Incident;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * ComponentStatusService
 * 
 * Manages component status changes, status logging, and status history.
 * Ensures status changes are tracked and logged for audit purposes.
 * 
 * Workflow:
 * 1. Validate status change is valid
 * 2. Update component status
 * 3. Create status log entry
 * 4. Notify subscribers of status change
 * 5. Handle automatic status restoration after incident resolution
 */
class ComponentStatusService
{
    public function __construct(
        protected NotificationService $notificationService,
        protected MetricsService $metricsService
    ) {
    }
    /**
     * Update a component's status.
     * 
     * Workflow:
     * - Validate new status
     * - Store old status
     * - Update component
     * - Create status log entry
     * - Notify subscribers
     * - Return updated component
     * 
     * @param Component $component The component to update
     * @param ComponentStatus $newStatus The new status
     * @param User|null $user The user making the change (null for automated changes)
     * @param Incident|null $incident Optional incident causing the change
     * @return Component
     */
    public function updateStatus(
        Component $component,
        ComponentStatus $newStatus,
        ?User $user = null,
        ?Incident $incident = null
    ): Component {
        return DB::transaction(function () use ($component, $newStatus, $user, $incident) {
            // Store old status before update
            $oldStatus = $component->status;

            // Skip if status hasn't changed
            if ($oldStatus === $newStatus) {
                return $component;
            }

            // Update component status
            $component->update(['status' => $newStatus]);

            // Create status log entry
            ComponentStatusLog::create([
                'component_id' => $component->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'user_id' => $user?->id,
                'incident_id' => $incident?->id,
            ]);

            // Notify subscribers of status change (only for non-incident changes)
            if (!$incident && $component->subscribers()->count() > 0) {
                $this->notificationService->notifyComponentStatusChange(
                    $component,
                    $oldStatus->value,
                    $newStatus->value
                );
            }

            // Clear component metrics cache
            $this->metricsService->clearComponentMetricsCache($component->id);

            return $component->fresh();
        });
    }

    /**
     * Batch update component statuses based on incident impact.
     * 
     * Maps incident impact to appropriate component status:
     * - Minor impact -> degraded_performance
     * - Major impact -> partial_outage
     * - Critical impact -> major_outage
     * 
     * @param array $componentIds Array of component IDs
     * @param Incident $incident The incident affecting the components
     * @return void
     */
    public function updateStatusesForIncident(array $componentIds, Incident $incident): void
    {
        DB::transaction(function () use ($componentIds, $incident) {
            $components = Component::whereIn('id', $componentIds)->get();
            
            // Map incident impact to component status
            $targetStatus = $this->mapIncidentImpactToStatus($incident);

            foreach ($components as $component) {
                $this->updateStatus($component, $targetStatus, null, $incident);
            }
        });
    }

    /**
     * Restore components to operational status.
     * 
     * Used when an incident is resolved.
     * Checks if component has other active incidents before restoring.
     * 
     * @param array $componentIds Array of component IDs
     * @param Incident $resolvedIncident The incident being resolved
     * @return void
     */
    public function restoreComponentStatuses(array $componentIds, Incident $resolvedIncident): void
    {
        DB::transaction(function () use ($componentIds, $resolvedIncident) {
            $components = Component::whereIn('id', $componentIds)->get();

            foreach ($components as $component) {
                // Check if component has other active incidents
                if (!$this->hasActiveIncidents($component, $resolvedIncident->id)) {
                    // Safe to restore to operational
                    $this->updateStatus($component, ComponentStatus::OPERATIONAL, null, $resolvedIncident);
                }
            }
        });
    }

    /**
     * Get component status history.
     * 
     * Returns all status changes for a component with related data
     * (user, incident, timestamps).
     * 
     * @param Component $component The component
     * @param int $limit Number of records to return
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getStatusHistory(Component $component, int $limit = 50)
    {
        return $component->statusLogs()
            ->with(['user', 'incident'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get current status summary for all components.
     * 
     * Returns grouped count of components by status.
     * Used for dashboard statistics.
     * 
     * @return array
     */
    public function getStatusSummary(): array
    {
        $summary = Component::enabled()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        // Ensure all statuses are represented
        return [
            'operational' => $summary['operational'] ?? 0,
            'degraded_performance' => $summary['degraded_performance'] ?? 0,
            'partial_outage' => $summary['partial_outage'] ?? 0,
            'major_outage' => $summary['major_outage'] ?? 0,
            'under_maintenance' => $summary['under_maintenance'] ?? 0,
        ];
    }

    /**
     * Calculate uptime percentage for a component.
     * 
     * Calculates based on status logs within specified time period.
     * Operational time / Total time * 100
     * 
     * @param Component $component The component
     * @param int $days Number of days to calculate (default 30)
     * @return float Uptime percentage
     */
    public function calculateUptime(Component $component, int $days = 30): float
    {
        $startDate = Carbon::now()->subDays($days);
        $endDate = Carbon::now();
        
        // Get all status changes within the period
        $statusLogs = $component->statusLogs()
            ->where('created_at', '>=', $startDate)
            ->orderBy('created_at')
            ->get();

        // If no status changes, use current status
        if ($statusLogs->isEmpty()) {
            return $component->status === ComponentStatus::OPERATIONAL ? 100.0 : 0.0;
        }

        $totalMinutes = $startDate->diffInMinutes($endDate);
        $operationalMinutes = 0;
        $currentStatus = $statusLogs->first()->old_status;
        $currentTime = $startDate;

        foreach ($statusLogs as $log) {
            $logTime = Carbon::parse($log->created_at);
            
            // Add time in current status
            if ($currentStatus === ComponentStatus::OPERATIONAL) {
                $operationalMinutes += $currentTime->diffInMinutes($logTime);
            }
            
            $currentStatus = $log->new_status;
            $currentTime = $logTime;
        }

        // Add remaining time in final status
        if ($currentStatus === ComponentStatus::OPERATIONAL) {
            $operationalMinutes += $currentTime->diffInMinutes($endDate);
        }

        return ($operationalMinutes / $totalMinutes) * 100;
    }

    /**
     * Get components by status.
     * 
     * @param ComponentStatus $status The status to filter by
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getComponentsByStatus(ComponentStatus $status)
    {
        return Component::enabled()
            ->where('status', $status)
            ->ordered()
            ->get();
    }

    /**
     * Check if a component has active incidents.
     * 
     * Used to determine if status can be restored to operational.
     * 
     * @param Component $component The component
     * @param int|null $excludeIncidentId Optional incident ID to exclude from check
     * @return bool
     */
    public function hasActiveIncidents(Component $component, ?int $excludeIncidentId = null): bool
    {
        $query = $component->incidents()->active();
        
        if ($excludeIncidentId) {
            $query->where('incidents.id', '!=', $excludeIncidentId);
        }
        
        return $query->exists();
    }

    /**
     * Set component to maintenance mode.
     * 
     * Special workflow for scheduled maintenance.
     * 
     * @param Component $component The component
     * @param Incident $maintenance The maintenance incident
     * @param User $user The user setting maintenance
     * @return void
     */
    public function setMaintenanceMode(Component $component, Incident $maintenance, User $user): void
    {
        $this->updateStatus($component, ComponentStatus::UNDER_MAINTENANCE, $user, $maintenance);
    }

    /**
     * Log current status snapshot for all components.
     * 
     * Used for daily status logging/reporting.
     * Creates a status log entry with same old and new status.
     * 
     * @return int Number of components logged
     */
    public function logDailyStatusSnapshot(): int
    {
        $components = Component::enabled()->get();
        $count = 0;

        foreach ($components as $component) {
            ComponentStatusLog::create([
                'component_id' => $component->id,
                'old_status' => $component->status,
                'new_status' => $component->status,
                'user_id' => null,
                'incident_id' => null,
            ]);
            $count++;
        }

        return $count;
    }

    /**
     * Map incident impact to component status.
     * 
     * @param Incident $incident
     * @return ComponentStatus
     */
    protected function mapIncidentImpactToStatus(Incident $incident): ComponentStatus
    {
        // Scheduled maintenance uses maintenance status
        if ($incident->is_scheduled) {
            return ComponentStatus::UNDER_MAINTENANCE;
        }

        // Map impact to status
        return match ($incident->impact) {
            IncidentImpact::MINOR => ComponentStatus::DEGRADED_PERFORMANCE,
            IncidentImpact::MAJOR => ComponentStatus::PARTIAL_OUTAGE,
            IncidentImpact::CRITICAL => ComponentStatus::MAJOR_OUTAGE,
        };
    }

    /**
     * Get 90-day status timeline data for a component.
     * 
     * Returns daily aggregated status data for charting purposes.
     * Each day shows the predominant status (status with most duration).
     * 
     * @param Component $component The component to analyze
     * @param int $days Number of days to analyze (default 90)
     * @return array Array of daily status data
     */
    public function get90DayStatusTimeline(Component $component, int $days = 90): array
    {
        $endDate = Carbon::now()->endOfDay();
        $startDate = Carbon::now()->subDays($days)->startOfDay();

        // Get all status logs for this component in the period with optimized query
        $statusLogs = ComponentStatusLog::where('component_id', $component->id)
            ->where('created_at', '>=', $startDate)
            ->orderBy('created_at')
            ->select('id', 'component_id', 'old_status', 'new_status', 'created_at', 'incident_id')
            ->get();

        $timeline = [];
        
        // Process each day
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dayStart = $date->copy()->startOfDay();
            $dayEnd = $date->copy()->endOfDay();

            // Find logs that occurred during this day
            $dayLogs = $statusLogs->filter(function ($log) use ($dayStart, $dayEnd) {
                $logTime = Carbon::parse($log->created_at);
                return $logTime->between($dayStart, $dayEnd);
            });

            // Determine the status at the start of the day
            $previousLog = $statusLogs->filter(function ($log) use ($dayStart) {
                return Carbon::parse($log->created_at)->lt($dayStart);
            })->last();

            $startStatus = $previousLog ? $previousLog->new_status : $component->status;

            // Calculate time spent in each status during this day
            $statusDurations = [];
            $currentStatus = $startStatus;
            $currentTime = $dayStart;

            foreach ($dayLogs as $log) {
                $logTime = Carbon::parse($log->created_at);
                $duration = $currentTime->diffInMinutes($logTime);
                
                if (!isset($statusDurations[$currentStatus->value])) {
                    $statusDurations[$currentStatus->value] = 0;
                }
                $statusDurations[$currentStatus->value] += $duration;
                
                $currentStatus = $log->new_status;
                $currentTime = $logTime;
            }

            // Add remaining time in final status
            $remainingDuration = $currentTime->diffInMinutes($dayEnd);
            if (!isset($statusDurations[$currentStatus->value])) {
                $statusDurations[$currentStatus->value] = 0;
            }
            $statusDurations[$currentStatus->value] += $remainingDuration;

            // Determine predominant status (longest duration)
            $predominantStatus = $currentStatus->value;
            $maxDuration = 0;
            foreach ($statusDurations as $status => $duration) {
                if ($duration > $maxDuration) {
                    $maxDuration = $duration;
                    $predominantStatus = $status;
                }
            }

            $statusEnum = ComponentStatus::from($predominantStatus);
            
            $timeline[] = [
                'date' => $date->format('Y-m-d'),
                'status' => $predominantStatus,
                'status_label' => $statusEnum->label(),
                'color' => $statusEnum->color(),
                'durations' => $statusDurations,
                'incident_count' => $dayLogs->whereNotNull('incident_id')->unique('incident_id')->count(),
            ];
        }

        return $timeline;
    }

    /**
     * Get aggregated status data for all components over a period.
     * 
     * Returns summary data for dashboard charts.
     * 
     * @param int $days Number of days to analyze
     * @return array Aggregated status data
     */
    public function getAggregatedStatusData(int $days = 90): array
    {
        $endDate = Carbon::now()->endOfDay();
        $startDate = Carbon::now()->subDays($days)->startOfDay();

        // Get all enabled components
        $components = Component::enabled()->select('id', 'status')->get();
        $componentIds = $components->pluck('id')->toArray();
        
        // Fetch ALL status logs for ALL components in ONE query
        $allStatusLogs = ComponentStatusLog::whereIn('component_id', $componentIds)
            ->where('created_at', '>=', $startDate)
            ->orderBy('component_id')
            ->orderBy('created_at')
            ->select('id', 'component_id', 'old_status', 'new_status', 'created_at', 'incident_id')
            ->get()
            ->groupBy('component_id');

        // Build timelines for all components
        $componentTimelines = [];
        foreach ($components as $component) {
            $componentLogs = $allStatusLogs->get($component->id, collect());
            $componentTimelines[$component->id] = $this->buildTimelineFromLogs(
                $component,
                $componentLogs,
                $startDate,
                $endDate
            );
        }

        // Aggregate by date
        $aggregatedData = [];
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dateKey = $date->format('Y-m-d');
            
            $statusCounts = [
                'operational' => 0,
                'degraded_performance' => 0,
                'partial_outage' => 0,
                'major_outage' => 0,
                'under_maintenance' => 0,
            ];

            foreach ($componentTimelines as $timeline) {
                $dayData = collect($timeline)->firstWhere('date', $dateKey);
                if ($dayData) {
                    $statusCounts[$dayData['status']]++;
                }
            }

            $aggregatedData[] = [
                'date' => $dateKey,
                'total_components' => $components->count(),
                'operational' => $statusCounts['operational'],
                'degraded' => $statusCounts['degraded_performance'],
                'partial_outage' => $statusCounts['partial_outage'],
                'major_outage' => $statusCounts['major_outage'],
                'maintenance' => $statusCounts['under_maintenance'],
                'uptime_percentage' => $components->count() > 0 
                    ? round(($statusCounts['operational'] / $components->count()) * 100, 2)
                    : 100,
            ];
        }

        return $aggregatedData;
    }

    /**
     * Build timeline from pre-fetched logs (helper for bulk operations)
     */
    protected function buildTimelineFromLogs(
        Component $component,
        $statusLogs,
        Carbon $startDate,
        Carbon $endDate
    ): array {
        $timeline = [];
        
        // Process each day
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dayStart = $date->copy()->startOfDay();
            $dayEnd = $date->copy()->endOfDay();

            // Find logs that occurred during this day
            $dayLogs = $statusLogs->filter(function ($log) use ($dayStart, $dayEnd) {
                $logTime = Carbon::parse($log->created_at);
                return $logTime->between($dayStart, $dayEnd);
            });

            // Determine the status at the start of the day
            $previousLog = $statusLogs->filter(function ($log) use ($dayStart) {
                return Carbon::parse($log->created_at)->lt($dayStart);
            })->last();

            $startStatus = $previousLog ? $previousLog->new_status : $component->status;

            // Calculate time spent in each status during this day
            $statusDurations = [];
            $currentStatus = $startStatus;
            $currentTime = $dayStart;

            foreach ($dayLogs as $log) {
                $logTime = Carbon::parse($log->created_at);
                $duration = $currentTime->diffInMinutes($logTime);
                
                if (!isset($statusDurations[$currentStatus->value])) {
                    $statusDurations[$currentStatus->value] = 0;
                }
                $statusDurations[$currentStatus->value] += $duration;
                
                $currentStatus = $log->new_status;
                $currentTime = $logTime;
            }

            // Add remaining time in final status
            $remainingDuration = $currentTime->diffInMinutes($dayEnd);
            if (!isset($statusDurations[$currentStatus->value])) {
                $statusDurations[$currentStatus->value] = 0;
            }
            $statusDurations[$currentStatus->value] += $remainingDuration;

            // Determine predominant status (longest duration)
            $predominantStatus = $currentStatus->value;
            $maxDuration = 0;
            foreach ($statusDurations as $status => $duration) {
                if ($duration > $maxDuration) {
                    $maxDuration = $duration;
                    $predominantStatus = $status;
                }
            }

            $statusEnum = ComponentStatus::from($predominantStatus);
            
            $timeline[] = [
                'date' => $date->format('Y-m-d'),
                'status' => $predominantStatus,
                'status_label' => $statusEnum->label(),
                'color' => $statusEnum->color(),
                'durations' => $statusDurations,
                'incident_count' => $dayLogs->whereNotNull('incident_id')->unique('incident_id')->count(),
            ];
        }

        return $timeline;
    }
}
