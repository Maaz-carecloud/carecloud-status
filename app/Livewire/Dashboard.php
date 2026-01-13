<?php

namespace App\Livewire;

use App\Enums\ComponentStatus;
use App\Models\Component;
use App\Models\ComponentStatusLog;
use App\Models\Incident;
use App\Models\Subscriber;
use App\Services\MetricsService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component as LivewireComponent;

#[Title('Dashboard')]
class Dashboard extends LivewireComponent
{
    #[Computed]
    public function systemStatus()
    {
        $components = Component::all();
        
        if ($components->isEmpty()) {
            return [
                'status' => 'operational',
                'label' => 'All Systems Operational',
                'color' => 'green',
                'icon' => 'check-circle',
            ];
        }

        $hasCritical = $components->contains(fn($c) => $c->status === ComponentStatus::MAJOR_OUTAGE);
        $hasDegraded = $components->contains(fn($c) => in_array($c->status, [
            ComponentStatus::PARTIAL_OUTAGE,
            ComponentStatus::DEGRADED_PERFORMANCE,
            ComponentStatus::UNDER_MAINTENANCE
        ]));

        if ($hasCritical) {
            return [
                'status' => 'critical',
                'label' => 'Systems Experiencing Issues',
                'color' => 'red',
                'icon' => 'exclamation-circle',
            ];
        }

        if ($hasDegraded) {
            return [
                'status' => 'degraded',
                'label' => 'Some Systems Degraded',
                'color' => 'yellow',
                'icon' => 'exclamation-triangle',
            ];
        }

        return [
            'status' => 'operational',
            'label' => 'All Systems Operational',
            'color' => 'green',
            'icon' => 'check-circle',
        ];
    }

    #[Computed]
    public function activeIncidents()
    {
        $incidents = Incident::whereNull('resolved_at')
            ->with('components')
            ->latest()
            ->get();

        $bySeverity = [
            'critical' => $incidents->where('impact', 'critical')->count(),
            'major' => $incidents->where('impact', 'major')->count(),
            'minor' => $incidents->where('impact', 'minor')->count(),
        ];

        return [
            'total' => $incidents->count(),
            'by_severity' => $bySeverity,
            'incidents' => $incidents->take(5),
        ];
    }

    #[Computed]
    public function recentActivity()
    {
        $recentChanges = ComponentStatusLog::with('component')
            ->where('created_at', '>=', now()->subHours(24))
            ->count();

        $recentIncidents = Incident::where('created_at', '>=', now()->subHours(24))
            ->count();

        return [
            'status_changes' => $recentChanges,
            'new_incidents' => $recentIncidents,
            'total_events' => $recentChanges + $recentIncidents,
        ];
    }

    #[Computed]
    public function weeklyUptime()
    {
        $metricsService = app(MetricsService::class);
        $components = Component::all();
        
        if ($components->isEmpty()) {
            return 100.0;
        }

        $totalUptime = 0;
        foreach ($components as $component) {
            $uptime = $metricsService->getComponentUptime($component->id, 7);
            $totalUptime += $uptime['uptime_percentage'];
        }

        return round($totalUptime / $components->count(), 2);
    }

    #[Computed]
    public function subscriberCount()
    {
        return Subscriber::whereNotNull('verified_at')->count();
    }

    #[Computed]
    public function componentCount()
    {
        return Component::count();
    }

    #[Computed]
    public function recentIncidents()
    {
        return Incident::with('components')
            ->latest()
            ->take(5)
            ->get();
    }

    #[Computed]
    public function recentStatusChanges()
    {
        return ComponentStatusLog::with('component')
            ->latest()
            ->take(10)
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
