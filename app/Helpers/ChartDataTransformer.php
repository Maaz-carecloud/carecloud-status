<?php

namespace App\Helpers;

use App\Enums\ComponentStatus;
use Carbon\Carbon;

/**
 * ChartDataTransformer
 * 
 * Transforms service data into chart-ready formats for various charting libraries.
 * Currently supports ApexCharts format.
 */
class ChartDataTransformer
{
    /**
     * Transform 90-day timeline data to ApexCharts heatmap format.
     * 
     * Creates a heatmap showing component status over time.
     * Each cell represents a day, colored by status.
     * 
     * @param array $timelineData Raw timeline data from ComponentStatusService
     * @return array ApexCharts-ready configuration
     */
    public static function toApexChartsHeatmap(array $timelineData): array
    {
        $series = [];
        
        // Group by week for better visualization
        $weeklyData = [];
        foreach ($timelineData as $day) {
            $date = Carbon::parse($day['date']);
            $weekNumber = $date->format('W');
            $weekYear = $date->format('Y');
            $weekKey = "{$weekYear}-W{$weekNumber}";
            
            if (!isset($weeklyData[$weekKey])) {
                $weeklyData[$weekKey] = [
                    'name' => $date->startOfWeek()->format('M d') . ' - ' . $date->endOfWeek()->format('M d'),
                    'data' => [],
                ];
            }
            
            // Map status to numeric value for heatmap (higher = better)
            $value = match ($day['status']) {
                'operational' => 4,
                'under_maintenance' => 3,
                'degraded_performance' => 2,
                'partial_outage' => 1,
                'major_outage' => 0,
                default => 0,
            };
            
            $weeklyData[$weekKey]['data'][] = [
                'x' => $date->format('D'),
                'y' => $value,
                'status' => $day['status_label'],
                'date' => $day['date'],
                'color' => $day['color'],
            ];
        }
        
        return [
            'series' => array_values($weeklyData),
            'options' => [
                'chart' => [
                    'type' => 'heatmap',
                    'height' => 350,
                    'toolbar' => [
                        'show' => false,
                    ],
                ],
                'plotOptions' => [
                    'heatmap' => [
                        'radius' => 2,
                        'enableShades' => false,
                        'colorScale' => [
                            'ranges' => [
                                [
                                    'from' => 0,
                                    'to' => 0,
                                    'color' => '#EF4444',
                                    'name' => 'Major Outage',
                                ],
                                [
                                    'from' => 1,
                                    'to' => 1,
                                    'color' => '#F97316',
                                    'name' => 'Partial Outage',
                                ],
                                [
                                    'from' => 2,
                                    'to' => 2,
                                    'color' => '#FCD34D',
                                    'name' => 'Degraded',
                                ],
                                [
                                    'from' => 3,
                                    'to' => 3,
                                    'color' => '#3B82F6',
                                    'name' => 'Maintenance',
                                ],
                                [
                                    'from' => 4,
                                    'to' => 4,
                                    'color' => '#10B981',
                                    'name' => 'Operational',
                                ],
                            ],
                        ],
                    ],
                ],
                'dataLabels' => [
                    'enabled' => false,
                ],
                'tooltip' => [
                    'custom' => 'function({ seriesIndex, dataPointIndex, w }) {
                        const data = w.config.series[seriesIndex].data[dataPointIndex];
                        return `<div class="p-2">
                            <div class="font-semibold">${data.date}</div>
                            <div class="text-sm">${data.status}</div>
                        </div>`;
                    }',
                ],
            ],
        ];
    }

    /**
     * Transform 90-day timeline to ApexCharts area chart format.
     * 
     * Shows status changes over time as an area chart.
     * 
     * @param array $timelineData Raw timeline data
     * @return array ApexCharts-ready configuration
     */
    public static function toApexChartsAreaChart(array $timelineData): array
    {
        $dates = [];
        $values = [];
        $colors = [];
        
        foreach ($timelineData as $day) {
            $dates[] = Carbon::parse($day['date'])->format('M d');
            
            // Map status to numeric value (higher = better)
            $value = match ($day['status']) {
                'operational' => 100,
                'under_maintenance' => 75,
                'degraded_performance' => 50,
                'partial_outage' => 25,
                'major_outage' => 0,
                default => 0,
            };
            
            $values[] = $value;
            $colors[] = $day['color'];
        }
        
        return [
            'series' => [
                [
                    'name' => 'Component Status',
                    'data' => $values,
                ],
            ],
            'options' => [
                'chart' => [
                    'type' => 'area',
                    'height' => 350,
                    'toolbar' => [
                        'show' => false,
                    ],
                    'sparkline' => [
                        'enabled' => false,
                    ],
                ],
                'dataLabels' => [
                    'enabled' => false,
                ],
                'stroke' => [
                    'curve' => 'smooth',
                    'width' => 2,
                ],
                'fill' => [
                    'type' => 'gradient',
                    'gradient' => [
                        'shadeIntensity' => 1,
                        'opacityFrom' => 0.7,
                        'opacityTo' => 0.3,
                    ],
                ],
                'xaxis' => [
                    'categories' => $dates,
                    'labels' => [
                        'rotate' => -45,
                        'rotateAlways' => true,
                    ],
                ],
                'yaxis' => [
                    'min' => 0,
                    'max' => 100,
                    'labels' => [
                        'formatter' => 'function(val) { return val + "%" }',
                    ],
                ],
                'tooltip' => [
                    'y' => [
                        'formatter' => 'function(val) { 
                            if (val === 100) return "Operational";
                            if (val === 75) return "Maintenance";
                            if (val === 50) return "Degraded";
                            if (val === 25) return "Partial Outage";
                            return "Major Outage";
                        }',
                    ],
                ],
                'colors' => ['#10B981'],
            ],
        ];
    }

    /**
     * Transform aggregated status data to ApexCharts stacked bar chart.
     * 
     * Shows component status distribution over time.
     * 
     * @param array $aggregatedData Data from getAggregatedStatusData()
     * @return array ApexCharts-ready configuration
     */
    public static function toApexChartsStackedBar(array $aggregatedData): array
    {
        $categories = [];
        $operational = [];
        $degraded = [];
        $partialOutage = [];
        $majorOutage = [];
        $maintenance = [];
        
        foreach ($aggregatedData as $day) {
            $categories[] = Carbon::parse($day['date'])->format('M d');
            $operational[] = $day['operational'];
            $degraded[] = $day['degraded'];
            $partialOutage[] = $day['partial_outage'];
            $majorOutage[] = $day['major_outage'];
            $maintenance[] = $day['maintenance'];
        }
        
        return [
            'series' => [
                [
                    'name' => 'Operational',
                    'data' => $operational,
                ],
                [
                    'name' => 'Maintenance',
                    'data' => $maintenance,
                ],
                [
                    'name' => 'Degraded',
                    'data' => $degraded,
                ],
                [
                    'name' => 'Partial Outage',
                    'data' => $partialOutage,
                ],
                [
                    'name' => 'Major Outage',
                    'data' => $majorOutage,
                ],
            ],
            'options' => [
                'chart' => [
                    'type' => 'bar',
                    'height' => 350,
                    'stacked' => true,
                    'toolbar' => [
                        'show' => false,
                    ],
                ],
                'plotOptions' => [
                    'bar' => [
                        'horizontal' => false,
                        'columnWidth' => '90%',
                    ],
                ],
                'xaxis' => [
                    'categories' => $categories,
                    'labels' => [
                        'rotate' => -45,
                        'rotateAlways' => true,
                    ],
                ],
                'yaxis' => [
                    'title' => [
                        'text' => 'Number of Components',
                    ],
                ],
                'legend' => [
                    'position' => 'top',
                    'horizontalAlign' => 'right',
                ],
                'colors' => ['#10B981', '#3B82F6', '#FCD34D', '#F97316', '#EF4444'],
                'fill' => [
                    'opacity' => 1,
                ],
            ],
        ];
    }

    /**
     * Transform aggregated data to ApexCharts line chart for uptime percentage.
     * 
     * Shows overall system uptime over time.
     * 
     * @param array $aggregatedData Data from getAggregatedStatusData()
     * @return array ApexCharts-ready configuration
     */
    public static function toApexChartsUptimeLine(array $aggregatedData): array
    {
        $dates = [];
        $uptimeValues = [];
        
        foreach ($aggregatedData as $day) {
            $dates[] = Carbon::parse($day['date'])->format('M d');
            $uptimeValues[] = round($day['uptime_percentage'], 2);
        }
        
        return [
            'series' => [
                [
                    'name' => 'Uptime',
                    'data' => $uptimeValues,
                ],
            ],
            'options' => [
                'chart' => [
                    'type' => 'line',
                    'height' => 350,
                    'toolbar' => [
                        'show' => false,
                    ],
                ],
                'dataLabels' => [
                    'enabled' => false,
                ],
                'stroke' => [
                    'curve' => 'smooth',
                    'width' => 3,
                ],
                'xaxis' => [
                    'categories' => $dates,
                    'labels' => [
                        'rotate' => -45,
                        'rotateAlways' => true,
                    ],
                ],
                'yaxis' => [
                    'min' => 0,
                    'max' => 100,
                    'labels' => [
                        'formatter' => 'function(val) { return val.toFixed(1) + "%" }',
                    ],
                ],
                'tooltip' => [
                    'y' => [
                        'formatter' => 'function(val) { return val.toFixed(2) + "%" }',
                    ],
                ],
                'colors' => ['#10B981'],
                'markers' => [
                    'size' => 4,
                    'colors' => ['#10B981'],
                    'strokeColors' => '#fff',
                    'strokeWidth' => 2,
                ],
            ],
        ];
    }
}
