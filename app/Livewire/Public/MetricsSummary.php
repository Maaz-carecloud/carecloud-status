<?php

namespace App\Livewire\Public;

use App\Models\Component;
use App\Services\MetricsService;
use Livewire\Attributes\Computed;
use Livewire\Component as LivewireComponent;

/**
 * Public-facing metrics summary component.
 * 
 * Displays read-only metrics for public status page:
 * - Overall system uptime
 * - Incident count summary
 * - Simple status timeline
 * 
 * SEO-friendly with server-side rendering.
 */
class MetricsSummary extends LivewireComponent
{
    public int $days = 90;

    #[Computed]
    public function overallUptime()
    {
        $metricsService = app(MetricsService::class);
        $components = Component::all();
        
        if ($components->isEmpty()) {
            return [
                'uptime_percentage' => 100.0,
                'status_label' => 'All Systems Operational',
                'status_color' => 'green',
            ];
        }

        $totalUptime = 0;
        $componentCount = $components->count();

        foreach ($components as $component) {
            $uptime = $metricsService->getComponentUptime($component->id, $this->days);
            $totalUptime += $uptime['uptime_percentage'];
        }

        $averageUptime = $componentCount > 0 ? $totalUptime / $componentCount : 100.0;

        // Determine status label and color
        $statusLabel = 'All Systems Operational';
        $statusColor = 'green';
        
        if ($averageUptime < 99.0) {
            $statusLabel = 'Experiencing Issues';
            $statusColor = 'red';
        } elseif ($averageUptime < 99.9) {
            $statusLabel = 'Degraded Performance';
            $statusColor = 'yellow';
        }

        return [
            'uptime_percentage' => round($averageUptime, 3),
            'status_label' => $statusLabel,
            'status_color' => $statusColor,
            'period_days' => $this->days,
        ];
    }

    #[Computed]
    public function incidentSummary()
    {
        $metricsService = app(MetricsService::class);
        $distribution = $metricsService->getIncidentCountsByImpact($this->days);
        
        return [
            'total_count' => $distribution['total_count'],
            'critical_count' => $distribution['by_impact']['critical']['count'] ?? 0,
            'major_count' => $distribution['by_impact']['major']['count'] ?? 0,
            'minor_count' => $distribution['by_impact']['minor']['count'] ?? 0,
            'period_days' => $this->days,
        ];
    }

    #[Computed]
    public function statusTimeline()
    {
        $metricsService = app(MetricsService::class);
        $components = Component::all();
        
        if ($components->isEmpty()) {
            return [];
        }

        // Get timeline for first component as representative sample
        $firstComponent = $components->first();
        return $metricsService->getDailyStatusTimeline($firstComponent->id, $this->days);
    }

    #[Computed]
    public function uptimeHistory()
    {
        $timeline = $this->statusTimeline;
        
        if (empty($timeline)) {
            return [
                'average_uptime' => 100.0,
                'best_day' => null,
                'worst_day' => null,
            ];
        }

        $uptimeValues = array_column($timeline, 'uptime_percentage');
        $averageUptime = array_sum($uptimeValues) / count($uptimeValues);
        
        // Find best and worst days
        $maxUptime = max($uptimeValues);
        $minUptime = min($uptimeValues);
        
        $bestDay = null;
        $worstDay = null;
        
        foreach ($timeline as $day) {
            if ($day['uptime_percentage'] == $maxUptime && !$bestDay) {
                $bestDay = $day;
            }
            if ($day['uptime_percentage'] == $minUptime && !$worstDay) {
                $worstDay = $day;
            }
        }

        return [
            'average_uptime' => round($averageUptime, 2),
            'best_day' => $bestDay,
            'worst_day' => $worstDay,
        ];
    }

    public function render()
    {
        return view('livewire.public.metrics-summary');
    }
}
