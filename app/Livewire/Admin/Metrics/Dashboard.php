<?php

namespace App\Livewire\Admin\Metrics;

use App\Models\Component;
use App\Services\MetricsService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component as LivewireComponent;

#[Title('Metrics Dashboard')]
class Dashboard extends LivewireComponent
{
    public int $days = 90;
    public ?int $selectedComponentId = null;

    public function mount(): void
    {
        // Default to first component for timeline graph
        $firstComponent = Component::first();
        $this->selectedComponentId = $firstComponent?->id;
    }

    #[Computed]
    public function components()
    {
        return Component::orderBy('name')->get();
    }

    #[Computed]
    public function globalUptime()
    {
        $metricsService = app(MetricsService::class);
        
        // Calculate average uptime across all components
        $components = $this->components;
        
        if ($components->isEmpty()) {
            return [
                'uptime_percentage' => 100.0,
                'period_start' => now()->timezone('America/New_York')->subDays($this->days)->format('M d, Y'),
                'period_end' => now()->timezone('America/New_York')->format('M d, Y'),
                'components_count' => 0,
            ];
        }

        $totalUptime = 0;
        $componentCount = $components->count();

        foreach ($components as $component) {
            $uptime = $metricsService->getComponentUptime($component->id, $this->days);
            $totalUptime += $uptime['uptime_percentage'];
        }

        $averageUptime = $componentCount > 0 ? $totalUptime / $componentCount : 100.0;

        return [
            'uptime_percentage' => round($averageUptime, 2),
            'period_start' => now()->timezone('America/New_York')->subDays($this->days)->format('M d, Y'),
            'period_end' => now()->timezone('America/New_York')->format('M d, Y'),
            'components_count' => $componentCount,
        ];
    }

    #[Computed]
    public function incidentDistribution()
    {
        $metricsService = app(MetricsService::class);
        return $metricsService->getIncidentCountsByImpact($this->days);
    }

    #[Computed]
    public function mttr()
    {
        $metricsService = app(MetricsService::class);
        return $metricsService->getMTTR($this->days);
    }

    #[Computed]
    public function statusTimeline()
    {
        if (!$this->selectedComponentId) {
            return [];
        }

        $metricsService = app(MetricsService::class);
        return $metricsService->getDailyStatusTimeline($this->selectedComponentId, $this->days);
    }

    public function selectComponent(int $componentId): void
    {
        $this->selectedComponentId = $componentId;
        unset($this->statusTimeline); // Clear computed cache
    }

    public function setDays(int $days): void
    {
        $this->days = $days;
        
        // Clear all computed property caches
        unset($this->globalUptime);
        unset($this->incidentDistribution);
        unset($this->mttr);
        unset($this->statusTimeline);
    }

    public function render()
    {
        return view('livewire.admin.metrics.dashboard');
    }
}
