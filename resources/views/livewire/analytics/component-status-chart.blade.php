<div>
    <div class="mb-6">
        <h1 class="text-3xl font-bold mb-2">Component Status Analytics</h1>
        <p class="text-gray-600">Visual analysis of component status over time</p>
    </div>

    {{-- Controls --}}
    <div class="bg-white border rounded-lg p-4 mb-6">
        <div class="flex flex-wrap gap-4">
            {{-- Component Selector --}}
            <div class="flex-1 min-w-[250px]">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Component</label>
                <select wire:model.live="componentId"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:opacity-50"
                    wire:loading.attr="disabled">
                    <option value="">All Components (Aggregated)</option>
                    @foreach($components as $component)
                    <option value="{{ $component->id }}">{{ $component->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Time Period Selector --}}
            <div class="min-w-[150px]">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Time Period</label>
                <select wire:model.live="days"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:opacity-50"
                    wire:loading.attr="disabled">
                    <option value="7">Last 7 Days</option>
                    <option value="30">Last 30 Days</option>
                    <option value="60">Last 60 Days</option>
                    <option value="90">Last 90 Days</option>
                </select>
            </div>

            {{-- Chart Type Selector (for single component) --}}
            @if($componentId)
            <div class="min-w-[150px]">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Chart Type</label>
                <select wire:model.change="chartType"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="area">Area Chart</option>
                    <option value="heatmap">Heatmap</option>
                </select>
            </div>
            @else
            <div class="min-w-[150px]">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Chart Type</label>
                <select wire:model.change="chartType"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="stacked">Stacked Bar</option>
                    <option value="uptime">Uptime Line</option>
                </select>
            </div>
            @endif
        </div>
    </div>

    {{-- Chart Container --}}
    <div class="bg-white border rounded-lg p-6 mb-6 relative" style="height: 400px;"
        wire:key="chart-{{ $componentId }}-{{ $days }}">
        {{-- Loading Overlay --}}
        <div wire:loading wire:target="componentId,days,chartType"
            class="absolute inset-0 bg-white/80 flex items-center justify-center z-10 rounded-lg">
            <div class="text-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-3"></div>
                <p class="text-gray-600 font-medium">Loading chart data...</p>
            </div>
        </div>

        <canvas id="status-chart"></canvas>
    </div>

    {{-- Statistics Summary --}}
    @if($componentId && isset($chartData['timeline']))
    <div class="bg-white border rounded-lg p-6">
        <h3 class="text-lg font-semibold mb-4">Statistics Summary</h3>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            @php
            $statusCounts = [
            'operational' => 0,
            'degraded_performance' => 0,
            'partial_outage' => 0,
            'major_outage' => 0,
            'under_maintenance' => 0,
            ];

            foreach ($chartData['timeline'] as $day) {
            $statusCounts[$day['status']]++;
            }

            $totalDays = count($chartData['timeline']);
            @endphp

            <div class="text-center p-4 border rounded-lg bg-green-50">
                <div class="text-3xl font-bold text-green-700">{{ $statusCounts['operational'] }}</div>
                <div class="text-sm text-gray-600 mt-1">Operational Days</div>
                <div class="text-xs text-gray-500 mt-1">
                    {{ $totalDays > 0 ? round(($statusCounts['operational'] / $totalDays) * 100, 1) : 0 }}%
                </div>
            </div>

            <div class="text-center p-4 border rounded-lg bg-yellow-50">
                <div class="text-3xl font-bold text-yellow-700">{{ $statusCounts['degraded_performance'] }}</div>
                <div class="text-sm text-gray-600 mt-1">Degraded Days</div>
                <div class="text-xs text-gray-500 mt-1">
                    {{ $totalDays > 0 ? round(($statusCounts['degraded_performance'] / $totalDays) * 100, 1) : 0 }}%
                </div>
            </div>

            <div class="text-center p-4 border rounded-lg bg-orange-50">
                <div class="text-3xl font-bold text-orange-700">{{ $statusCounts['partial_outage'] }}</div>
                <div class="text-sm text-gray-600 mt-1">Partial Outage Days</div>
                <div class="text-xs text-gray-500 mt-1">
                    {{ $totalDays > 0 ? round(($statusCounts['partial_outage'] / $totalDays) * 100, 1) : 0 }}%
                </div>
            </div>

            <div class="text-center p-4 border rounded-lg bg-red-50">
                <div class="text-3xl font-bold text-red-700">{{ $statusCounts['major_outage'] }}</div>
                <div class="text-sm text-gray-600 mt-1">Major Outage Days</div>
                <div class="text-xs text-gray-500 mt-1">
                    {{ $totalDays > 0 ? round(($statusCounts['major_outage'] / $totalDays) * 100, 1) : 0 }}%
                </div>
            </div>

            <div class="text-center p-4 border rounded-lg bg-blue-50">
                <div class="text-3xl font-bold text-blue-700">{{ $statusCounts['under_maintenance'] }}</div>
                <div class="text-sm text-gray-600 mt-1">Maintenance Days</div>
                <div class="text-xs text-gray-500 mt-1">
                    {{ $totalDays > 0 ? round(($statusCounts['under_maintenance'] / $totalDays) * 100, 1) : 0 }}%
                </div>
            </div>
        </div>
    </div>
    @elseif(!$componentId && isset($chartData['aggregated']))
    <div class="bg-white border rounded-lg p-6">
        <h3 class="text-lg font-semibold mb-4">Overall Statistics</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            @php
            $totalDays = count($chartData['aggregated']);
            $avgUptime = $totalDays > 0
            ? round(collect($chartData['aggregated'])->avg('uptime_percentage'), 2)
            : 0;
            $minUptime = $totalDays > 0
            ? round(collect($chartData['aggregated'])->min('uptime_percentage'), 2)
            : 0;
            $maxUptime = $totalDays > 0
            ? round(collect($chartData['aggregated'])->max('uptime_percentage'), 2)
            : 0;
            @endphp

            <div class="text-center p-4 border rounded-lg bg-green-50">
                <div class="text-3xl font-bold text-green-700">{{ $avgUptime }}%</div>
                <div class="text-sm text-gray-600 mt-1">Average Uptime</div>
                <div class="text-xs text-gray-500 mt-1">Over {{ $days }} days</div>
            </div>

            <div class="text-center p-4 border rounded-lg bg-yellow-50">
                <div class="text-3xl font-bold text-yellow-700">{{ $minUptime }}%</div>
                <div class="text-sm text-gray-600 mt-1">Minimum Uptime</div>
                <div class="text-xs text-gray-500 mt-1">Worst day</div>
            </div>

            <div class="text-center p-4 border rounded-lg bg-blue-50">
                <div class="text-3xl font-bold text-blue-700">{{ $maxUptime }}%</div>
                <div class="text-sm text-gray-600 mt-1">Maximum Uptime</div>
                <div class="text-xs text-gray-500 mt-1">Best day</div>
            </div>
        </div>
    </div>
    @endif

    {{-- Chart.js Script --}}
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.js"></script>
    <script>
        let chart = null;

        function renderChart() {
            const chartData = @json($chartData);
            const chartType = @json($chartType);
            const componentId = @json($componentId);
                
                // Destroy existing chart
                if (chart) {
                    chart.destroy();
                }

                const ctx = document.getElementById('status-chart');
                if (!ctx) return;

                if (componentId && chartData.timeline) {
                    // Single component timeline
                    const timeline = chartData.timeline;
                    
                    if (chartType === 'area') {
                        // Area chart showing status over time
                        const statusValues = {
                            'operational': 100,
                            'under_maintenance': 75,
                            'degraded_performance': 50,
                            'partial_outage': 25,
                            'major_outage': 0
                        };
                        
                        const statusColors = {
                            'operational': 'rgb(34, 197, 94)',
                            'under_maintenance': 'rgb(59, 130, 246)',
                            'degraded_performance': 'rgb(234, 179, 8)',
                            'partial_outage': 'rgb(249, 115, 22)',
                            'major_outage': 'rgb(239, 68, 68)'
                        };

                        chart = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: timeline.map(d => d.date),
                                datasets: [{
                                    label: 'Status',
                                    data: timeline.map(d => statusValues[d.status]),
                                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                    borderColor: 'rgb(59, 130, 246)',
                                    borderWidth: 2,
                                    fill: true,
                                    tension: 0.4,
                                    pointBackgroundColor: timeline.map(d => statusColors[d.status]),
                                    pointBorderColor: timeline.map(d => statusColors[d.status]),
                                    pointRadius: 4,
                                    pointHoverRadius: 6
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                const status = timeline[context.dataIndex].status;
                                                return status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    y: {
                                        min: 0,
                                        max: 100,
                                        ticks: {
                                            stepSize: 25,
                                            callback: function(value) {
                                                const labels = {
                                                    0: 'Major Outage',
                                                    25: 'Partial Outage',
                                                    50: 'Degraded',
                                                    75: 'Maintenance',
                                                    100: 'Operational'
                                                };
                                                return labels[value] || '';
                                            }
                                        }
                                    },
                                    x: {
                                        ticks: {
                                            maxRotation: 45,
                                            minRotation: 45
                                        }
                                    }
                                }
                            }
                        });
                    } else {
                        // Heatmap-style visualization using bar chart
                        const statusColors = {
                            'operational': 'rgb(34, 197, 94)',
                            'under_maintenance': 'rgb(59, 130, 246)',
                            'degraded_performance': 'rgb(234, 179, 8)',
                            'partial_outage': 'rgb(249, 115, 22)',
                            'major_outage': 'rgb(239, 68, 68)'
                        };

                        chart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: timeline.map(d => d.date),
                                datasets: [{
                                    label: 'Status',
                                    data: timeline.map(() => 1),
                                    backgroundColor: timeline.map(d => statusColors[d.status]),
                                    borderWidth: 0
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                const status = timeline[context.dataIndex].status;
                                                return status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    y: {
                                        display: false
                                    },
                                    x: {
                                        ticks: {
                                            maxRotation: 90,
                                            minRotation: 90,
                                            font: {
                                                size: 9
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }
                } else if (!componentId && chartData.aggregated) {
                    // Aggregated data for all components
                    const aggregated = chartData.aggregated;
                    
                    if (chartType === 'stacked') {
                        // Stacked bar chart showing status distribution
                        chart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: aggregated.map(d => d.date),
                                datasets: [
                                    {
                                        label: 'Operational',
                                        data: aggregated.map(d => d.operational_percentage || 0),
                                        backgroundColor: 'rgba(34, 197, 94, 0.8)',
                                        borderColor: 'rgb(34, 197, 94)',
                                        borderWidth: 1
                                    },
                                    {
                                        label: 'Maintenance',
                                        data: aggregated.map(d => d.maintenance_percentage || 0),
                                        backgroundColor: 'rgba(59, 130, 246, 0.8)',
                                        borderColor: 'rgb(59, 130, 246)',
                                        borderWidth: 1
                                    },
                                    {
                                        label: 'Degraded',
                                        data: aggregated.map(d => d.degraded_percentage || 0),
                                        backgroundColor: 'rgba(234, 179, 8, 0.8)',
                                        borderColor: 'rgb(234, 179, 8)',
                                        borderWidth: 1
                                    },
                                    {
                                        label: 'Outage',
                                        data: aggregated.map(d => (d.partial_outage_percentage || 0) + (d.major_outage_percentage || 0)),
                                        backgroundColor: 'rgba(239, 68, 68, 0.8)',
                                        borderColor: 'rgb(239, 68, 68)',
                                        borderWidth: 1
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: true,
                                        position: 'top'
                                    },
                                    tooltip: {
                                        mode: 'index',
                                        intersect: false,
                                        callbacks: {
                                            label: function(context) {
                                                return context.dataset.label + ': ' + context.parsed.y.toFixed(1) + '%';
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    x: {
                                        stacked: true,
                                        ticks: {
                                            maxRotation: 45,
                                            minRotation: 45
                                        }
                                    },
                                    y: {
                                        stacked: true,
                                        min: 0,
                                        max: 100,
                                        ticks: {
                                            callback: function(value) {
                                                return value + '%';
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    } else {
                        // Uptime line chart
                        chart = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: aggregated.map(d => d.date),
                                datasets: [{
                                    label: 'Uptime Percentage',
                                    data: aggregated.map(d => d.uptime_percentage || 0),
                                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                                    borderColor: 'rgb(34, 197, 94)',
                                    borderWidth: 2,
                                    fill: true,
                                    tension: 0.4,
                                    pointRadius: 3,
                                    pointHoverRadius: 5
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                return 'Uptime: ' + context.parsed.y.toFixed(2) + '%';
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    y: {
                                        min: 0,
                                        max: 100,
                                        ticks: {
                                            callback: function(value) {
                                                return value + '%';
                                            }
                                        }
                                    },
                                    x: {
                                        ticks: {
                                            maxRotation: 45,
                                            minRotation: 45
                                        }
                                    }
                                }
                            }
                        });
                    }
                }
        }

        // Initial render on page load
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Chart !== 'undefined') {
                setTimeout(renderChart, 100);
            }
        });
        
        // Re-render on Livewire navigation
        document.addEventListener('livewire:navigated', function() {
            if (typeof Chart !== 'undefined') {
                setTimeout(renderChart, 100);
            }
        });
        
        // Re-render on Livewire updates
        document.addEventListener('livewire:init', () => {
            Livewire.hook('morph.updated', () => {
                setTimeout(renderChart, 100);
            });
        });
    </script>
    @endpush
</div>