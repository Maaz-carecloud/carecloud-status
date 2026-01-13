<div class="space-y-8">
    {{-- Overall Status Banner --}}
    <div
        class="overflow-hidden rounded-lg border {{ $this->overallUptime['status_color'] === 'green' ? 'border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-900/20' : ($this->overallUptime['status_color'] === 'yellow' ? 'border-yellow-200 bg-yellow-50 dark:border-yellow-800 dark:bg-yellow-900/20' : 'border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-900/20') }} shadow-sm">
        <div class="px-6 py-8 text-center">
            <div
                class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full {{ $this->overallUptime['status_color'] === 'green' ? 'bg-green-100 dark:bg-green-800' : ($this->overallUptime['status_color'] === 'yellow' ? 'bg-yellow-100 dark:bg-yellow-800' : 'bg-red-100 dark:bg-red-800') }}">
                <svg class="h-8 w-8 {{ $this->overallUptime['status_color'] === 'green' ? 'text-green-600 dark:text-green-400' : ($this->overallUptime['status_color'] === 'yellow' ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400') }}"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    @if($this->overallUptime['status_color'] === 'green')
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    @else
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    @endif
                </svg>
            </div>

            <h2
                class="text-2xl font-bold {{ $this->overallUptime['status_color'] === 'green' ? 'text-green-900 dark:text-green-100' : ($this->overallUptime['status_color'] === 'yellow' ? 'text-yellow-900 dark:text-yellow-100' : 'text-red-900 dark:text-red-100') }}">
                {{ $this->overallUptime['status_label'] }}
            </h2>

            <p
                class="mt-2 text-lg {{ $this->overallUptime['status_color'] === 'green' ? 'text-green-700 dark:text-green-300' : ($this->overallUptime['status_color'] === 'yellow' ? 'text-yellow-700 dark:text-yellow-300' : 'text-red-700 dark:text-red-300') }}">
                {{ number_format($this->overallUptime['uptime_percentage'], 3) }}% uptime over the last {{
                $this->overallUptime['period_days'] }} days
            </p>
        </div>
    </div>

    {{-- Metrics Grid --}}
    <div class="grid gap-6 md:grid-cols-3">
        {{-- Uptime Card --}}
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <h3 class="text-sm font-medium uppercase tracking-wide text-gray-600 dark:text-gray-400">
                System Uptime
            </h3>
            <div class="mt-4">
                <div class="text-4xl font-bold text-gray-900 dark:text-white">
                    {{ number_format($this->overallUptime['uptime_percentage'], 2) }}<span
                        class="text-2xl text-gray-500">%</span>
                </div>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Last {{ $this->days }} days
                </p>
            </div>

            {{-- Visual uptime bar --}}
            <div class="mt-4">
                <div class="h-2 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                    <div class="h-full transition-all duration-500 {{ $this->overallUptime['uptime_percentage'] >= 99.9 ? 'bg-green-500' : ($this->overallUptime['uptime_percentage'] >= 99.0 ? 'bg-yellow-500' : 'bg-red-500') }}"
                        style="width: {{ $this->overallUptime['uptime_percentage'] }}%"></div>
                </div>
            </div>
        </div>

        {{-- Incidents Card --}}
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <h3 class="text-sm font-medium uppercase tracking-wide text-gray-600 dark:text-gray-400">
                Total Incidents
            </h3>
            <div class="mt-4">
                <div class="text-4xl font-bold text-gray-900 dark:text-white">
                    {{ $this->incidentSummary['total_count'] }}
                </div>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Last {{ $this->incidentSummary['period_days'] }} days
                </p>
            </div>

            {{-- Incident breakdown --}}
            <div class="mt-4 space-y-2">
                <div class="flex items-center justify-between text-sm">
                    <span class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                        <span class="inline-block h-2 w-2 rounded-full bg-red-500"></span>
                        Critical
                    </span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $this->incidentSummary['critical_count']
                        }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                        <span class="inline-block h-2 w-2 rounded-full bg-orange-500"></span>
                        Major
                    </span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $this->incidentSummary['major_count']
                        }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                        <span class="inline-block h-2 w-2 rounded-full bg-yellow-500"></span>
                        Minor
                    </span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $this->incidentSummary['minor_count']
                        }}</span>
                </div>
            </div>
        </div>

        {{-- History Stats Card --}}
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <h3 class="text-sm font-medium uppercase tracking-wide text-gray-600 dark:text-gray-400">
                Period Average
            </h3>
            <div class="mt-4">
                <div class="text-4xl font-bold text-gray-900 dark:text-white">
                    {{ number_format($this->uptimeHistory['average_uptime'], 1) }}<span
                        class="text-2xl text-gray-500">%</span>
                </div>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Daily average uptime
                </p>
            </div>

            {{-- Best/Worst day stats --}}
            @if($this->uptimeHistory['best_day'] && $this->uptimeHistory['worst_day'])
            <div class="mt-4 space-y-2 text-sm">
                <div class="flex items-center justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Best day</span>
                    <span class="font-medium text-green-600 dark:text-green-400">
                        {{ number_format($this->uptimeHistory['best_day']['uptime_percentage'], 1) }}%
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Worst day</span>
                    <span class="font-medium text-orange-600 dark:text-orange-400">
                        {{ number_format($this->uptimeHistory['worst_day']['uptime_percentage'], 1) }}%
                    </span>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Simple Status Timeline --}}
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
            {{ $this->days }}-Day Status History
        </h3>

        @if(empty($this->statusTimeline))
        <div class="flex h-32 items-center justify-center text-gray-500 dark:text-gray-400">
            No status data available
        </div>
        @else
        {{-- Simple visual timeline (bar chart) --}}
        <div class="space-y-1">
            @foreach(array_reverse(array_slice($this->statusTimeline, -30)) as $day)
            <div class="group flex items-center gap-3">
                <div class="w-20 text-xs text-gray-600 dark:text-gray-400">
                    {{ \Carbon\Carbon::parse($day['date'])->timezone('America/New_York')->format('M d') }}
                </div>

                {{-- Status bar --}}
                <div class="relative flex-1">
                    <div class="flex h-6 overflow-hidden rounded">
                        @if($day['operational_minutes'] > 0)
                        <div class="bg-green-500 transition-all group-hover:bg-green-600"
                            style="width: {{ ($day['operational_minutes'] / 1440 * 100) }}%"
                            title="Operational: {{ number_format($day['operational_minutes'] / 60, 1) }}h"></div>
                        @endif
                        @if($day['degraded_minutes'] > 0)
                        <div class="bg-yellow-500 transition-all group-hover:bg-yellow-600"
                            style="width: {{ ($day['degraded_minutes'] / 1440 * 100) }}%"
                            title="Degraded: {{ number_format($day['degraded_minutes'] / 60, 1) }}h"></div>
                        @endif
                        @if($day['outage_minutes'] > 0)
                        <div class="bg-red-500 transition-all group-hover:bg-red-600"
                            style="width: {{ ($day['outage_minutes'] / 1440 * 100) }}%"
                            title="Outage: {{ number_format($day['outage_minutes'] / 60, 1) }}h"></div>
                        @endif
                        @if($day['maintenance_minutes'] > 0)
                        <div class="bg-blue-500 transition-all group-hover:bg-blue-600"
                            style="width: {{ ($day['maintenance_minutes'] / 1440 * 100) }}%"
                            title="Maintenance: {{ number_format($day['maintenance_minutes'] / 60, 1) }}h"></div>
                        @endif
                    </div>
                </div>

                {{-- Uptime percentage --}}
                <div
                    class="w-16 text-right text-xs font-medium {{ $day['uptime_percentage'] >= 99.9 ? 'text-green-600 dark:text-green-400' : ($day['uptime_percentage'] >= 99.0 ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400') }}">
                    {{ number_format($day['uptime_percentage'], 1) }}%
                </div>
            </div>
            @endforeach
        </div>

        {{-- Legend --}}
        <div class="mt-6 flex flex-wrap justify-center gap-4 text-sm">
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
            <div class="flex items-center gap-2">
                <span class="inline-block h-3 w-3 rounded bg-blue-500"></span>
                <span class="text-gray-600 dark:text-gray-400">Maintenance</span>
            </div>
        </div>

        {{-- SEO-friendly text summary --}}
        <div class="mt-6 border-t border-gray-200 pt-6 text-sm text-gray-600 dark:border-gray-700 dark:text-gray-400">
            <p>
                Our system has maintained an average uptime of
                <strong class="text-gray-900 dark:text-white">{{ number_format($this->uptimeHistory['average_uptime'],
                    2) }}%</strong>
                over the past {{ $this->days }} days. We've experienced
                <strong class="text-gray-900 dark:text-white">{{ $this->incidentSummary['total_count'] }}
                    incidents</strong>
                during this period, including {{ $this->incidentSummary['critical_count'] }} critical,
                {{ $this->incidentSummary['major_count'] }} major, and
                {{ $this->incidentSummary['minor_count'] }} minor incidents.
            </p>
        </div>
        @endif
    </div>
</div>