<div class="space-y-6">
    {{-- Header with Time Range Selector --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Metrics Dashboard</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ $this->globalUptime['period_start'] }} - {{ $this->globalUptime['period_end'] }}
            </p>
        </div>

        <div class="flex gap-2">
            <button wire:click="setDays(7)"
                class="rounded-lg px-4 py-2 text-sm font-medium transition {{ $days === 7 ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                7 Days
            </button>
            <button wire:click="setDays(30)"
                class="rounded-lg px-4 py-2 text-sm font-medium transition {{ $days === 30 ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                30 Days
            </button>
            <button wire:click="setDays(90)"
                class="rounded-lg px-4 py-2 text-sm font-medium transition {{ $days === 90 ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                90 Days
            </button>
        </div>
    </div>

    {{-- Metrics Cards --}}
    <div class="grid gap-6 md:grid-cols-3">
        {{-- Global Uptime Card --}}
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Global Uptime</h3>
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="mt-4">
                <div class="text-3xl font-bold text-gray-900 dark:text-white">
                    {{ number_format($this->globalUptime['uptime_percentage'], 2) }}%
                </div>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Across {{ $this->globalUptime['components_count'] }} components
                </p>
            </div>
            <div class="mt-4">
                <div class="h-2 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                    <div class="h-full rounded-full {{ $this->globalUptime['uptime_percentage'] >= 99.9 ? 'bg-green-500' : ($this->globalUptime['uptime_percentage'] >= 99.0 ? 'bg-yellow-500' : 'bg-red-500') }}"
                        style="width: {{ $this->globalUptime['uptime_percentage'] }}%"></div>
                </div>
            </div>
        </div>

        {{-- Incident Distribution Card --}}
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Incidents</h3>
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div class="mt-4">
                <div class="text-3xl font-bold text-gray-900 dark:text-white">
                    {{ $this->incidentDistribution['total_count'] }}
                </div>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Total incidents</p>
            </div>
            <div class="mt-4 space-y-2">
                @foreach($this->incidentDistribution['by_impact'] as $impact => $data)
                <div class="flex items-center justify-between text-sm">
                    <span class="capitalize text-gray-600 dark:text-gray-400">
                        <span
                            class="inline-block h-2 w-2 rounded-full {{ $impact === 'critical' ? 'bg-red-500' : ($impact === 'major' ? 'bg-orange-500' : 'bg-yellow-500') }}"></span>
                        {{ ucfirst($impact) }}
                    </span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $data['count'] }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- MTTR Card --}}
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Mean Time To Resolution</h3>
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="mt-4">
                <div class="text-3xl font-bold text-gray-900 dark:text-white">
                    {{ $this->mttr['overall_formatted'] ?? 'N/A' }}
                </div>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Average resolution time
                </p>
            </div>
            <div class="mt-4 space-y-2">
                @foreach($this->mttr['by_impact'] as $impact => $data)
                <div class="flex items-center justify-between text-sm">
                    <span class="capitalize text-gray-600 dark:text-gray-400">{{ ucfirst($impact) }}</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $data['formatted'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- 90-Day Status Timeline Graph --}}
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Status Timeline ({{ $days }} Days)</h3>

            {{-- Component Selector --}}
            <select wire:model.live="selectedComponentId"
                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                @foreach($this->components as $component)
                <option value="{{ $component->id }}">{{ $component->name }}</option>
                @endforeach
            </select>
        </div>

        @if(empty($this->statusTimeline))
        <div class="flex h-64 items-center justify-center text-gray-500 dark:text-gray-400">
            No data available
        </div>
        @else
        {{-- Chart Container --}}
        <div class="relative h-64">
            <canvas id="statusChart" wire:ignore></canvas>
        </div>

        {{-- Legend --}}
        <div class="mt-4 flex flex-wrap gap-4 text-sm">
            <div class="flex items-center gap-2">
                <span class="inline-block h-3 w-3 rounded bg-green-500"></span>
                <span class="text-gray-600 dark:text-gray-400">Operational</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-block h-3 w-3 rounded bg-yellow-500"></span>
                <span class="text-gray-600 dark:text-gray-400">Degraded</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-block h-3 w-3 rounded bg-red-500"></span>
                <span class="text-gray-600 dark:text-gray-400">Outage</span>
            </div>
        </div>

        {{-- Chart.js Initialization --}}
        @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.js"></script>
        @endpush
        <script>
            let metricsChart = null;
            
            function initMetricsChart() {
                const ctx = document.getElementById('statusChart');
                if (!ctx) return;
                
                const data = @json($this->statusTimeline);
                
                if (metricsChart) {
                    metricsChart.destroy();
                }
                
                metricsChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.map(d => d.date),
                        datasets: [
                            {
                                label: 'Operational',
                                data: data.map(d => (d.operational_minutes / 1440 * 100).toFixed(1)),
                                backgroundColor: 'rgba(34, 197, 94, 0.8)',
                                borderColor: 'rgb(34, 197, 94)',
                                borderWidth: 1
                            },
                            {
                                label: 'Degraded',
                                data: data.map(d => (d.degraded_minutes / 1440 * 100).toFixed(1)),
                                backgroundColor: 'rgba(234, 179, 8, 0.8)',
                                borderColor: 'rgb(234, 179, 8)',
                                borderWidth: 1
                            },
                            {
                                label: 'Outage',
                                data: data.map(d => (d.outage_minutes / 1440 * 100).toFixed(1)),
                                backgroundColor: 'rgba(239, 68, 68, 0.8)',
                                borderColor: 'rgb(239, 68, 68)',
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                stacked: true,
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                stacked: true,
                                beginAtZero: true,
                                max: 100,
                                ticks: {
                                    callback: function(value) {
                                        return value + '%';
                                    }
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ' + context.parsed.y + '%';
                                    }
                                }
                            }
                        }
                    }
                });
            }
            
            // Initialize on DOMContentLoaded
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof Chart !== 'undefined') {
                    initMetricsChart();
                }
            });
            
            // Also listen for Livewire navigation
            document.addEventListener('livewire:navigated', function() {
                if (typeof Chart !== 'undefined') {
                    setTimeout(initMetricsChart, 100);
                }
            });
            
            // Re-initialize on Livewire updates
            document.addEventListener('livewire:init', () => {
                Livewire.on('livewire:update', function() {
                    setTimeout(initMetricsChart, 100);
                });
            });
        </script>
        @endif
    </div>

    {{-- Data Table: Recent Status Changes --}}
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Status Distribution Summary</h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead
                    class="border-b border-gray-200 text-xs uppercase text-gray-600 dark:border-gray-700 dark:text-gray-400">
                    <tr>
                        <th class="pb-3">Date</th>
                        <th class="pb-3 text-right">Operational</th>
                        <th class="pb-3 text-right">Degraded</th>
                        <th class="pb-3 text-right">Outage</th>
                        <th class="pb-3 text-right">Uptime %</th>
                        <th class="pb-3 text-right">Changes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse(array_slice(array_reverse($this->statusTimeline), 0, 10) as $day)
                    <tr class="text-gray-700 dark:text-gray-300">
                        <td class="py-3">{{ \Carbon\Carbon::parse($day['date'])->timezone('America/New_York')->format('M
                            d, Y') }}</td>
                        <td class="py-3 text-right">{{ number_format($day['operational_minutes'] / 60, 1) }}h</td>
                        <td class="py-3 text-right">{{ number_format($day['degraded_minutes'] / 60, 1) }}h</td>
                        <td class="py-3 text-right">{{ number_format($day['outage_minutes'] / 60, 1) }}h</td>
                        <td
                            class="py-3 text-right font-medium {{ $day['uptime_percentage'] >= 99.9 ? 'text-green-600 dark:text-green-400' : ($day['uptime_percentage'] >= 99.0 ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400') }}">
                            {{ number_format($day['uptime_percentage'], 2) }}%
                        </td>
                        <td class="py-3 text-right">{{ $day['status_changes'] }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-gray-500 dark:text-gray-400">
                            No status data available
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>