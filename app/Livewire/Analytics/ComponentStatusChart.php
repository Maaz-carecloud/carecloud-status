<?php

namespace App\Livewire\Analytics;

use App\Helpers\ChartDataTransformer;
use App\Models\Component;
use App\Services\ComponentStatusService;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Title;
use Livewire\Component as LivewireComponent;

#[Title('Component Status Analytics')]
class ComponentStatusChart extends LivewireComponent
{
    public ?int $componentId = null;
    public int $days = 90;
    public string $chartType = 'area'; // area, heatmap, stacked, uptime

    public function mount(?int $componentId = null, int $days = 90): void
    {
        $this->componentId = $componentId;
        $this->days = $days;
    }

    public function render(ComponentStatusService $service)
    {
        $chartData = $this->getChartData($service);
        
        return view('livewire.analytics.component-status-chart', [
            'chartData' => $chartData,
            'components' => Component::enabled()->ordered()->select('id', 'name', 'status')->get(),
        ]);
    }

    public function updatedComponentId(): void
    {
        // Clear cache when component changes
        $this->clearCache();
    }

    public function updatedDays(): void
    {
        // Clear cache when days change
        $this->clearCache();
    }

    protected function getChartData(ComponentStatusService $service): array
    {
        $cacheKey = "status_chart_{$this->componentId}_{$this->days}";
        
        return Cache::remember($cacheKey, now()->addHours(1), function () use ($service) {
            if ($this->componentId) {
                // Single component timeline
                $component = Component::select('id', 'name', 'status')->findOrFail($this->componentId);
                $timelineData = $service->get90DayStatusTimeline($component, $this->days);
                
                return [
                    'timeline' => $timelineData,
                    'area' => ChartDataTransformer::toApexChartsAreaChart($timelineData),
                    'heatmap' => ChartDataTransformer::toApexChartsHeatmap($timelineData),
                ];
            } else {
                // Aggregated data for all components
                $aggregatedData = $service->getAggregatedStatusData($this->days);
                
                return [
                    'aggregated' => $aggregatedData,
                    'stacked' => ChartDataTransformer::toApexChartsStackedBar($aggregatedData),
                    'uptime' => ChartDataTransformer::toApexChartsUptimeLine($aggregatedData),
                ];
            }
        });
    }

    protected function clearCache(): void
    {
        $cacheKey = "status_chart_{$this->componentId}_{$this->days}";
        Cache::forget($cacheKey);
    }

    public function setDays(int $days): void
    {
        $this->days = $days;
    }

    public function setChartType(string $type): void
    {
        $this->chartType = $type;
    }

    public function exportData(): array
    {
        $service = app(ComponentStatusService::class);
        
        if ($this->componentId) {
            $component = Component::findOrFail($this->componentId);
            return $service->get90DayStatusTimeline($component, $this->days);
        }
        
        return $service->getAggregatedStatusData($this->days);
    }
}
