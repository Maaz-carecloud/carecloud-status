<div class="space-y-6">
    {{-- Hero Status Cards --}}
    <div class="grid gap-6 md:grid-cols-3">
        {{-- System Status Card --}}
        <div
            class="rounded-lg border shadow-sm p-6 {{ $this->systemStatus['color'] === 'green' ? 'border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-900/20' : ($this->systemStatus['color'] === 'yellow' ? 'border-yellow-200 bg-yellow-50 dark:border-yellow-800 dark:bg-yellow-900/20' : 'border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-900/20') }}">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <h3
                        class="text-sm font-medium {{ $this->systemStatus['color'] === 'green' ? 'text-green-600 dark:text-green-400' : ($this->systemStatus['color'] === 'yellow' ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400') }}">
                        Current System Status
                    </h3>
                    <p
                        class="mt-2 text-2xl font-bold {{ $this->systemStatus['color'] === 'green' ? 'text-green-900 dark:text-green-100' : ($this->systemStatus['color'] === 'yellow' ? 'text-yellow-900 dark:text-yellow-100' : 'text-red-900 dark:text-red-100') }}">
                        {{ $this->systemStatus['label'] }}
                    </p>
                    <p
                        class="mt-1 text-sm {{ $this->systemStatus['color'] === 'green' ? 'text-green-700 dark:text-green-300' : ($this->systemStatus['color'] === 'yellow' ? 'text-yellow-700 dark:text-yellow-300' : 'text-red-700 dark:text-red-300') }}">
                        Monitoring {{ $this->componentCount }} components
                    </p>
                </div>
                <svg class="h-10 w-10 {{ $this->systemStatus['color'] === 'green' ? 'text-green-500' : ($this->systemStatus['color'] === 'yellow' ? 'text-yellow-500' : 'text-red-500') }}"
                    fill="currentColor" viewBox="0 0 20 20">
                    @if($this->systemStatus['icon'] === 'check-circle')
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                    @elseif($this->systemStatus['icon'] === 'exclamation-triangle')
                    <path fill-rule="evenodd"
                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                        clip-rule="evenodd" />
                    @else
                    <path fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                        clip-rule="evenodd" />
                    @endif
                </svg>
            </div>
        </div>

        {{-- Active Incidents Card --}}
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Active Incidents</h3>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                        {{ $this->activeIncidents['total'] }}
                    </p>
                    <div class="mt-3 space-y-1">
                        @if($this->activeIncidents['by_severity']['critical'] > 0)
                        <div class="flex items-center gap-2 text-sm">
                            <span class="inline-block h-2 w-2 rounded-full bg-red-500"></span>
                            <span class="text-gray-700 dark:text-gray-300">{{
                                $this->activeIncidents['by_severity']['critical'] }} Critical</span>
                        </div>
                        @endif
                        @if($this->activeIncidents['by_severity']['major'] > 0)
                        <div class="flex items-center gap-2 text-sm">
                            <span class="inline-block h-2 w-2 rounded-full bg-orange-500"></span>
                            <span class="text-gray-700 dark:text-gray-300">{{
                                $this->activeIncidents['by_severity']['major'] }} Major</span>
                        </div>
                        @endif
                        @if($this->activeIncidents['by_severity']['minor'] > 0)
                        <div class="flex items-center gap-2 text-sm">
                            <span class="inline-block h-2 w-2 rounded-full bg-yellow-500"></span>
                            <span class="text-gray-700 dark:text-gray-300">{{
                                $this->activeIncidents['by_severity']['minor'] }} Minor</span>
                        </div>
                        @endif
                        @if($this->activeIncidents['total'] === 0)
                        <p class="text-sm text-gray-500 dark:text-gray-400">No active incidents</p>
                        @endif
                    </div>
                </div>
                <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
        </div>

        {{-- Recent Activity Card --}}
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Last 24 Hours</h3>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                        {{ $this->recentActivity['total_events'] }}
                    </p>
                    <div class="mt-3 space-y-1 text-sm text-gray-600 dark:text-gray-400">
                        <p>{{ $this->recentActivity['status_changes'] }} status changes</p>
                        <p>{{ $this->recentActivity['new_incidents'] }} new incidents</p>
                    </div>
                </div>
                <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="grid gap-4 md:grid-cols-3">
        @if(auth()->user()->role->canManageIncidents())
        <a href="{{ route('incidents.create') }}" wire:navigate
            class="flex items-center gap-3 rounded-lg border border-gray-200 bg-white p-4 shadow-sm transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
                <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="flex-1">
                <h4 class="font-semibold text-gray-900 dark:text-white">Create Incident</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">Report a new issue</p>
            </div>
        </a>
        @endif

        @if(auth()->user()->role->canManageComponents())
        <a href="{{ route('components.index') }}" wire:navigate
            class="flex items-center gap-3 rounded-lg border border-gray-200 bg-white p-4 shadow-sm transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30">
                <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="flex-1">
                <h4 class="font-semibold text-gray-900 dark:text-white">Manage Components</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">Update component status</p>
            </div>
        </a>
        @endif

        <a href="{{ route('analytics.index') }}" wire:navigate
            class="flex items-center gap-3 rounded-lg border border-gray-200 bg-white p-4 shadow-sm transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900/30">
                <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
            <div class="flex-1">
                <h4 class="font-semibold text-gray-900 dark:text-white">View Analytics</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">Component metrics & trends</p>
            </div>
        </a>
    </div>

    {{-- Key Metrics --}}
    <div class="grid gap-6 md:grid-cols-4">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">7-Day Uptime</h3>
            <div class="mt-2 flex items-baseline">
                <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($this->weeklyUptime, 2) }}%
                </p>
            </div>
            <div class="mt-3 h-2 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                <div class="h-full rounded-full {{ $this->weeklyUptime >= 99.9 ? 'bg-green-500' : ($this->weeklyUptime >= 99.0 ? 'bg-yellow-500' : 'bg-red-500') }}"
                    style="width: {{ $this->weeklyUptime }}%"></div>
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Active Subscribers</h3>
            <div class="mt-2 flex items-baseline">
                <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($this->subscriberCount) }}
                </p>
            </div>
            <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">Verified email addresses</p>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Components</h3>
            <div class="mt-2 flex items-baseline">
                <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $this->componentCount }}</p>
            </div>
            <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">Monitored services</p>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Status Today</h3>
            <div class="mt-2 flex items-baseline">
                <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $this->recentActivity['status_changes']
                    }}</p>
            </div>
            <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">Changes in 24h</p>
        </div>
    </div>

    {{-- Activity Feeds --}}
    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Recent Incidents --}}
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Incidents</h3>
                @if(auth()->user()->role->canManageIncidents())
                <a href="{{ route('incidents.index') }}" wire:navigate
                    class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">
                    View all →
                </a>
                @endif
            </div>

            @if($this->recentIncidents->isEmpty())
            <p class="py-8 text-center text-gray-500 dark:text-gray-400">No recent incidents</p>
            @else
            <div class="space-y-3">
                @foreach($this->recentIncidents as $incident)
                <div class="rounded-lg border border-gray-100 p-3 dark:border-gray-700">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span
                                    class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $incident->impact->value === 'critical' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' : ($incident->impact->value === 'major' ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400') }}">
                                    {{ $incident->impact->label() }}
                                </span>
                                @if($incident->resolved_at)
                                <span
                                    class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                    Resolved
                                </span>
                                @else
                                <span
                                    class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                                    Active
                                </span>
                                @endif
                            </div>
                            <p class="font-medium text-gray-900 dark:text-white truncate">{{ $incident->name }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $incident->components->pluck('name')->join(', ') ?: 'All components' }} • {{
                                $incident->created_at->timezone('America/New_York')->diffForHumans() }}
                            </p>
                        </div>
                        @if(auth()->user()->role->canManageIncidents())
                        <a href="{{ route('incidents.edit', $incident->id) }}" wire:navigate
                            class="flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Recent Status Changes --}}
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Status Changes</h3>
                <a href="{{ route('analytics.index') }}" wire:navigate
                    class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">
                    View analytics →
                </a>
            </div>

            @if($this->recentStatusChanges->isEmpty())
            <p class="py-8 text-center text-gray-500 dark:text-gray-400">No recent status changes</p>
            @else
            <div class="space-y-2">
                @foreach($this->recentStatusChanges as $log)
                <div class="flex items-center gap-3 py-2">
                    <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full"
                        style="background-color: {{ $log->new_status->color() }}20;">
                        <div class="h-2 w-2 rounded-full" style="background-color: {{ $log->new_status->color() }};">
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                            {{ $log->component->name }}
                        </p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">
                            {{ $log->old_status->label() }} → {{ $log->new_status->label() }}
                        </p>
                    </div>
                    <span class="flex-shrink-0 text-xs text-gray-500 dark:text-gray-400">
                        {{ $log->created_at->diffForHumans(null, true, true) }}
                    </span>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>