<?php

namespace App\Livewire\Public;

use App\Models\Component;
use App\Models\Incident;
use App\Services\IncidentService;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component as LivewireComponent;

#[Title('Status Page')]
#[Layout('components.layouts.public')]
class StatusPage extends LivewireComponent
{
    public function render()
    {
        // Cache status page data for 60 seconds
        $components = Cache::remember('status_page_components', 60, function () {
            return Component::enabled()
                ->ordered()
                ->with(['incidents' => function ($query) {
                    $query->active();
                }])
                ->get();
        });

        $activeIncidents = Cache::remember('status_page_active_incidents', 60, function () {
            return Incident::active()
                ->with(['components', 'updates' => function ($query) {
                    $query->latest()->limit(3);
                }])
                ->recent()
                ->get()
                ->sortByDesc(fn($incident) => $incident->impact->sortOrder());
        });

        $scheduledMaintenance = Cache::remember('status_page_scheduled_maintenance', 60, function () {
            return Incident::scheduled()
                ->where('scheduled_at', '>', now())
                ->with(['components'])
                ->orderBy('scheduled_at')
                ->get();
        });

        // Get last resolved incident
        $lastResolvedIncident = Cache::remember('status_page_last_resolved_incident', 60, function () {
            return Incident::where('status', 'resolved')
                ->with(['components', 'updates' => function ($query) {
                    $query->latest();
                }])
                ->orderBy('resolved_at', 'desc')
                ->first();
        });

        // Get past incidents (last 30 days, resolved)
        $pastIncidents = Cache::remember('status_page_past_incidents', 60, function () {
            return Incident::where('status', 'resolved')
                ->where('created_at', '>=', now()->subDays(30))
                ->with(['components'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->groupBy(function ($incident) {
                    // Group by resolved_at if available, otherwise use created_at
                    $date = $incident->resolved_at ?? $incident->created_at;
                    return $date->format('Y-m-d');
                });
        });

        // Calculate overall system status
        $overallStatus = $this->calculateOverallStatus($components);

        return view('livewire.public.status-page', [
            'components' => $components,
            'activeIncidents' => $activeIncidents,
            'scheduledMaintenance' => $scheduledMaintenance,
            'lastResolvedIncident' => $lastResolvedIncident,
            'pastIncidents' => $pastIncidents,
            'overallStatus' => $overallStatus,
        ]);
    }

    /**
     * Calculate overall system status based on component statuses.
     */
    protected function calculateOverallStatus($components): array
    {
        $statusCounts = $components->countBy('status');

        if ($statusCounts->get('major_outage', 0) > 0) {
            return ['status' => 'major_outage', 'label' => 'Major Outage', 'color' => 'red'];
        }

        if ($statusCounts->get('partial_outage', 0) > 0) {
            return ['status' => 'partial_outage', 'label' => 'Partial Outage', 'color' => 'orange'];
        }

        if ($statusCounts->get('degraded_performance', 0) > 0) {
            return ['status' => 'degraded_performance', 'label' => 'Degraded Performance', 'color' => '#D97706'];
        }

        if ($statusCounts->get('under_maintenance', 0) > 0) {
            return ['status' => 'under_maintenance', 'label' => 'Under Maintenance', 'color' => 'blue'];
        }

        return ['status' => 'operational', 'label' => 'All Systems Operational', 'color' => 'green'];
    }

    /**
     * Refresh the status page data.
     */
    public function refresh(): void
    {
        // Clear cached data
        Cache::forget('status_page_components');
        Cache::forget('status_page_active_incidents');
        Cache::forget('status_page_scheduled_maintenance');
        Cache::forget('status_page_last_resolved_incident');
        Cache::forget('status_page_past_incidents');
    }
}
