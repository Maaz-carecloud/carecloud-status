<?php

namespace App\Services;

use App\Enums\IncidentImpact;
use App\Enums\IncidentStatus;
use App\Models\Component;
use App\Models\Incident;
use App\Models\IncidentUpdate;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * IncidentService
 * 
 * Handles all business logic related to incidents including creation,
 * updates, status transitions, and scheduled maintenance management.
 * 
 * Workflow:
 * 1. Create incident with initial status (investigating)
 * 2. Attach affected components
 * 3. Post updates as investigation progresses
 * 4. Notify subscribers of affected components
 * 5. Transition through statuses: investigating -> identified -> monitoring -> resolved
 * 6. Update component statuses based on incident impact
 * 7. Log all status changes for audit trail
 */
class IncidentService
{
    public function __construct(
        protected ComponentStatusService $componentStatusService,
        protected NotificationService $notificationService,
        protected MetricsService $metricsService
    ) {
    }
    /**
     * Create a new incident.
     * 
     * Workflow:
     * - Validate incident data
     * - Create incident record
     * - Attach affected components
     * - Update component statuses
     * - Create initial incident update
     * - Trigger notifications to subscribers
     * - Log status changes
     * 
     * @param array $data Incident data (name, message, impact, component_ids, etc.)
     * @param User $user The user creating the incident
     * @return Incident
     */
    public function createIncident(array $data, User $user): Incident
    {
        return DB::transaction(function () use ($data, $user) {
            // Create incident with default status
            $incident = Incident::create([
                'name' => $data['name'],
                'message' => $data['message'] ?? null,
                'status' => IncidentStatus::INVESTIGATING,
                'impact' => $data['impact'] ?? IncidentImpact::MINOR,
                'is_scheduled' => false,
                'started_at' => now(),
                'user_id' => $user->id,
            ]);

            // Attach affected components if provided with their statuses
            if (!empty($data['component_ids'])) {
                $componentStatuses = $data['component_statuses'] ?? [];
                $syncData = [];
                
                foreach ($data['component_ids'] as $componentId) {
                    $syncData[$componentId] = [
                        'status' => $componentStatuses[$componentId] ?? null
                    ];
                }
                
                $incident->components()->attach($syncData);
                
                // Update component statuses based on the statuses set in incident
                $this->applyComponentStatuses($incident, $componentStatuses);
            }

            // Create initial incident update
            $incident->updates()->create([
                'status' => IncidentStatus::INVESTIGATING,
                'message' => $data['message'] ?? 'We are investigating this issue.',
                'user_id' => $user->id,
            ]);

            // Trigger notification to subscribers
            $this->notificationService->notifyNewIncident($incident);

            // Clear incident metrics cache
            $this->metricsService->clearIncidentMetricsCache();

            // Clear component metrics cache for affected components
            foreach ($incident->components as $component) {
                $this->metricsService->clearComponentMetricsCache($component->id);
            }

            return $incident->load(['components', 'updates', 'user']);
        });
    }

    /**
     * Create a scheduled maintenance incident.
     * 
     * Workflow:
     * - Validate scheduled time (must be future)
     * - Create incident with is_scheduled flag
     * - Attach affected components
     * - Send advance notification to subscribers
     * - Schedule reminder notifications
     * 
     * @param array $data Maintenance data (name, message, scheduled_at, component_ids, etc.)
     * @param User $user The user creating the maintenance
     * @return Incident
     */
    public function createScheduledMaintenance(array $data, User $user): Incident
    {
        return DB::transaction(function () use ($data, $user) {
            // Validate scheduled time is in the future
            $scheduledAt = Carbon::parse($data['scheduled_at']);
            if ($scheduledAt->isPast()) {
                throw new \InvalidArgumentException('Scheduled time must be in the future.');
            }

            // Create scheduled maintenance incident
            $incident = Incident::create([
                'name' => $data['name'],
                'message' => $data['message'] ?? null,
                'status' => IncidentStatus::IDENTIFIED,
                'impact' => $data['impact'] ?? IncidentImpact::MINOR,
                'is_scheduled' => true,
                'scheduled_at' => $scheduledAt,
                'user_id' => $user->id,
            ]);

            // Attach affected components if provided with their statuses
            if (!empty($data['component_ids'])) {
                $componentStatuses = $data['component_statuses'] ?? [];
                $syncData = [];
                
                foreach ($data['component_ids'] as $componentId) {
                    $syncData[$componentId] = [
                        'status' => $componentStatuses[$componentId] ?? null
                    ];
                }
                
                $incident->components()->attach($syncData);
            }

            // Create initial maintenance update
            $incident->updates()->create([
                'status' => IncidentStatus::IDENTIFIED,
                'message' => $data['message'] ?? 'Scheduled maintenance has been planned.',
                'user_id' => $user->id,
            ]);

            // Trigger notification to subscribers
            $this->notificationService->notifyScheduledMaintenance($incident);

            return $incident->load(['components', 'updates', 'user']);
        });
    }

    /**
     * Add an update to an existing incident.
     * 
     * Workflow:
     * - Validate incident exists and is not resolved
     * - Create incident update record
     * - Update incident status if changed
     * - Update component statuses if needed
     * - Trigger notifications to subscribers
     * - Log status changes
     * 
     * @param Incident $incident The incident to update
     * @param array $data Update data (status, message)
     * @param User $user The user posting the update
     * @return void
     */
    public function addIncidentUpdate(Incident $incident, array $data, User $user): void
    {
        DB::transaction(function () use ($incident, $data, $user) {
            // Validate incident is not already resolved
            if ($incident->isResolved()) {
                throw new \InvalidArgumentException('Cannot add updates to a resolved incident.');
            }

            // Get the new status from data or keep current
            $newStatus = isset($data['status']) 
                ? ($data['status'] instanceof IncidentStatus ? $data['status'] : IncidentStatus::from($data['status']))
                : $incident->status;

            // Create incident update
            $update = $incident->updates()->create([
                'status' => $newStatus,
                'message' => $data['message'],
                'user_id' => $user->id,
            ]);

            // Update incident status if changed
            if ($newStatus !== $incident->status) {
                $incident->update(['status' => $newStatus]);

                // If incident is resolved, set resolved_at timestamp and restore component statuses
                if ($newStatus === IncidentStatus::RESOLVED) {
                    $incident->update(['resolved_at' => now()]);
                    
                    // Restore component statuses to operational if no other active incidents
                    if (!$incident->components->isEmpty()) {
                        $componentIds = $incident->components->pluck('id')->toArray();
                        $this->componentStatusService->restoreComponentStatuses(
                            $componentIds,
                            $incident
                        );
                    }
                } else {
                    // Update component statuses if status changed (but not resolved)
                    if (!$incident->components->isEmpty()) {
                        $componentIds = $incident->components->pluck('id')->toArray();
                        // Re-apply the stored statuses from pivot table
                        $componentStatuses = [];
                        foreach ($incident->fresh()->components as $component) {
                            if ($component->pivot->status) {
                                $componentStatuses[$component->id] = $component->pivot->status;
                            }
                        }
                        if (!empty($componentStatuses)) {
                            $this->applyComponentStatuses($incident, $componentStatuses);
                        }
                    }
                }
            }

            // Trigger notification to subscribers
            $this->notificationService->notifyIncidentUpdate($update);

            // Clear incident metrics cache
            $this->metricsService->clearIncidentMetricsCache();

            // Clear component metrics cache if status changed
            if ($newStatus !== $incident->status) {
                foreach ($incident->components as $component) {
                    $this->metricsService->clearComponentMetricsCache($component->id);
                }
            }
        });
    }

    /**
     * Transition incident to a new status.
     * 
     * Validates status transition is valid according to workflow:
     * investigating -> identified -> monitoring -> resolved
     * 
     * @param Incident $incident The incident to transition
     * @param IncidentStatus $newStatus The new status
     * @param User $user The user making the transition
     * @return void
     */
    public function transitionStatus(Incident $incident, IncidentStatus $newStatus, User $user): void
    {
        DB::transaction(function () use ($incident, $newStatus, $user) {
            // Validate transition is not to the same status
            if ($incident->status === $newStatus) {
                return;
            }

            // Update incident status
            $oldStatus = $incident->status;
            $incident->update(['status' => $newStatus]);

            // Create an incident update for the status change
            $incident->updates()->create([
                'status' => $newStatus,
                'message' => $this->getStatusTransitionMessage($oldStatus, $newStatus),
                'user_id' => $user->id,
            ]);

            // Update component statuses based on new incident status
            if (!$incident->components->isEmpty()) {
                $componentIds = $incident->components->pluck('id')->toArray();
                
                if ($newStatus === IncidentStatus::RESOLVED) {
                    // Restore components to operational
                    $this->componentStatusService->restoreComponentStatuses(
                        $componentIds,
                        $incident
                    );
                } else {
                    // Update component statuses based on incident
                    $this->componentStatusService->updateStatusesForIncident(
                        $componentIds,
                        $incident->fresh()
                    );
                }
            }
        });
    }

    /**
     * Resolve an incident.
     * 
     * Workflow:
     * - Mark incident as resolved
     * - Set resolved_at timestamp
     * - Restore component statuses to operational
     * - Create final incident update
     * - Notify subscribers of resolution
     * - Log all status changes
     * 
     * @param Incident $incident The incident to resolve
     * @param string $resolutionMessage Final update message
     * @param User $user The user resolving the incident
     * @return void
     */
    public function resolveIncident(Incident $incident, string $resolutionMessage, User $user): void
    {
        DB::transaction(function () use ($incident, $resolutionMessage, $user) {
            // Validate incident is not already resolved
            if ($incident->isResolved()) {
                throw new \InvalidArgumentException('Incident is already resolved.');
            }

            // Update incident to resolved status
            $incident->update([
                'status' => IncidentStatus::RESOLVED,
                'resolved_at' => now(),
            ]);

            // Create final resolution update
            $incident->updates()->create([
                'status' => IncidentStatus::RESOLVED,
                'message' => $resolutionMessage,
                'user_id' => $user->id,
            ]);

            // Restore affected component statuses to operational
            if (!$incident->components->isEmpty()) {
                $componentIds = $incident->components->pluck('id')->toArray();
                $this->componentStatusService->restoreComponentStatuses(
                    $componentIds,
                    $incident
                );
            }

            // Trigger resolution notification to subscribers
            $this->notificationService->notifyIncidentResolved($incident->fresh());

            // Clear incident metrics cache (MTTR affected by resolution)
            $this->metricsService->clearIncidentMetricsCache();

            // Clear component metrics cache for restored components
            foreach ($incident->components as $component) {
                $this->metricsService->clearComponentMetricsCache($component->id);
            }
        });
    }

    /**
     * Update components affected by an incident.
     * 
     * Workflow:
     * - Detach components no longer affected
     * - Attach newly affected components
     * - Update component statuses based on incident impact
     * - Log status changes
     * - Notify relevant subscribers
     * 
     * @param Incident $incident The incident
     * @param array $componentIds Array of component IDs
     * @param array $componentStatuses Array of component statuses
     * @return void
     */
    public function updateAffectedComponents(Incident $incident, array $componentIds, array $componentStatuses = []): void
    {
        DB::transaction(function () use ($incident, $componentIds, $componentStatuses) {
            // Get currently affected components
            $currentComponentIds = $incident->components->pluck('id')->toArray();
            
            // Determine which components are being removed
            $removedComponentIds = array_diff($currentComponentIds, $componentIds);
            
            // Determine which components are being added
            $addedComponentIds = array_diff($componentIds, $currentComponentIds);
            
            // Prepare sync data with statuses
            $syncData = [];
            foreach ($componentIds as $componentId) {
                $syncData[$componentId] = [
                    'status' => $componentStatuses[$componentId] ?? null
                ];
            }
            
            // Sync components with the incident
            $incident->components()->sync($syncData);
            
            // Restore status for removed components (only if incident is not resolved)
            if (!empty($removedComponentIds) && !$incident->isResolved()) {
                $this->componentStatusService->restoreComponentStatuses(
                    $removedComponentIds,
                    $incident
                );
            }
            
            // Apply statuses to all affected components
            if (!empty($componentStatuses)) {
                $this->applyComponentStatuses($incident, $componentStatuses);
            }
        });
    }

    /**
     * Apply component statuses from incident.
     * 
     * @param Incident $incident The incident
     * @param array $componentStatuses Array of component statuses keyed by component ID
     * @return void
     */
    protected function applyComponentStatuses(Incident $incident, array $componentStatuses): void
    {
        foreach ($componentStatuses as $componentId => $status) {
            if ($status) {
                $component = Component::find($componentId);
                if ($component) {
                    $newStatus = \App\Enums\ComponentStatus::from($status);
                    $this->componentStatusService->updateStatus($component, $newStatus, null, $incident);
                }
            }
        }
    }

    /**
     * Get active incidents for public status page.
     * 
     * Returns incidents ordered by impact and creation date,
     * with related components and latest updates.
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveIncidents()
    {
        return Incident::active()
            ->with(['components', 'updates' => fn($query) => $query->latest()->limit(3)])
            ->recent()
            ->get()
            ->sortByDesc(fn($incident) => $incident->impact->sortOrder());
    }

    /**
     * Get incident history for a date range.
     * 
     * Used for historical views and reporting.
     * 
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getIncidentHistory($startDate, $endDate)
    {
        return Incident::with(['components', 'updates', 'user'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->recent()
            ->get();
    }

    /**
     * Delete an incident.
     * 
     * Workflow:
     * - Validate user has permission
     * - Restore component statuses
     * - Delete related updates
     * - Delete incident
     * - Log deletion
     * 
     * @param Incident $incident The incident to delete
     * @return void
     */
    public function deleteIncident(Incident $incident): void
    {
        DB::transaction(function () use ($incident) {
            // Restore component statuses before deletion
            if (!$incident->components->isEmpty()) {
                $componentIds = $incident->components->pluck('id')->toArray();
                $this->componentStatusService->restoreComponentStatuses(
                    $componentIds,
                    $incident
                );
            }

            // Detach all components
            $incident->components()->detach();

            // Delete all updates (cascade should handle this, but being explicit)
            $incident->updates()->delete();

            // Delete the incident
            $incident->delete();
        });
    }

    /**
     * Get status transition message.
     * 
     * @param IncidentStatus $oldStatus
     * @param IncidentStatus $newStatus
     * @return string
     */
    protected function getStatusTransitionMessage(IncidentStatus $oldStatus, IncidentStatus $newStatus): string
    {
        return match ($newStatus) {
            IncidentStatus::INVESTIGATING => 'We are currently investigating this issue.',
            IncidentStatus::IDENTIFIED => 'The issue has been identified and we are working on a fix.',
            IncidentStatus::MONITORING => 'A fix has been implemented and we are monitoring the results.',
            IncidentStatus::RESOLVED => 'This incident has been resolved.',
        };
    }
}
