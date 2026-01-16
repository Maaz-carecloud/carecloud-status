<div class="space-y-6">
    {{-- Page Header --}}
    <div>
        <h1 class="text-3xl font-bold mb-2">Incident History</h1>
        <p class="text-gray-600">Review past incidents and system events</p>
    </div>

    {{-- Statistics Summary --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white border rounded-lg p-4">
            <p class="text-sm text-gray-600">Total Incidents</p>
            <p class="text-2xl font-bold">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white border rounded-lg p-4">
            <p class="text-sm text-gray-600">Resolved</p>
            <p class="text-2xl font-bold text-green-600">{{ $stats['resolved'] }}</p>
        </div>
        <div class="bg-white border rounded-lg p-4">
            <p class="text-sm text-gray-600">Active</p>
            <p class="text-2xl font-bold text-orange-600">{{ $stats['active'] }}</p>
        </div>
        <div class="bg-white border rounded-lg p-4">
            <p class="text-sm text-gray-600">Avg Resolution</p>
            <p class="text-2xl font-bold">
                @if($stats['avg_resolution_time'])
                {{ floor($stats['avg_resolution_time'] / 60) }}h {{ $stats['avg_resolution_time'] % 60 }}m
                @else
                N/A
                @endif
            </p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white border rounded-lg p-4">
        <div class="flex flex-wrap gap-4 items-center">
            {{-- Time Period Filter --}}
            <div>
                <label class="text-sm font-semibold text-gray-700 block mb-2">Time Period</label>
                <div class="flex gap-2">
                    <button wire:click="setDays(7)"
                        class="px-3 py-1 text-sm rounded {{ $days === 7 ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        7 Days
                    </button>
                    <button wire:click="setDays(30)"
                        class="px-3 py-1 text-sm rounded {{ $days === 30 ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        30 Days
                    </button>
                    <button wire:click="setDays(90)"
                        class="px-3 py-1 text-sm rounded {{ $days === 90 ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        90 Days
                    </button>
                </div>
            </div>

            {{-- Status Filter --}}
            <div>
                <label class="text-sm font-semibold text-gray-700 block mb-2">Status</label>
                <div class="flex gap-2">
                    <button wire:click="setFilter('all')"
                        class="px-3 py-1 text-sm rounded {{ $filter === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        All
                    </button>
                    <button wire:click="setFilter('ongoing')"
                        class="px-3 py-1 text-sm rounded {{ $filter === 'ongoing' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        Ongoing
                    </button>
                    <button wire:click="setFilter('resolved')"
                        class="px-3 py-1 text-sm rounded {{ $filter === 'resolved' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        Resolved
                    </button>
                </div>
            </div>

            {{-- Reset Filters --}}
            <div class="ml-auto">
                <button wire:click="resetFilters" class="px-3 py-1 text-sm text-gray-600 hover:text-gray-800 underline">
                    Reset Filters
                </button>
            </div>
        </div>
    </div>

    {{-- Incidents List --}}
    @if($incidents->count() > 0)
    <div class="space-y-4">
        @foreach($incidents as $incident)
        <article class="bg-white border rounded-lg p-6 shadow-sm">
            <header class="flex items-start justify-between mb-4">
                <div class="flex-1">
                    <h2 class="text-xl font-semibold mb-2">{{ $incident->name }}</h2>
                    <div class="flex flex-wrap gap-2">
                        <span class="px-2 py-1 text-xs font-semibold rounded"
                            style="background-color: {{ $incident->status->color() }}20; color: {{ $incident->status->color() }};">
                            {{ $incident->status->label() }}
                        </span>
                        <span class="px-2 py-1 text-xs font-semibold rounded"
                            style="background-color: {{ $incident->impact->color() }}20; color: {{ $incident->impact->color() }};">
                            {{ $incident->impact->label() }} Impact
                        </span>
                        @if($incident->is_scheduled)
                        <span class="px-2 py-1 text-xs font-semibold rounded bg-blue-100 text-blue-800">
                            Scheduled Maintenance
                        </span>
                        @endif
                    </div>
                </div>
                <time class="text-sm text-gray-500 whitespace-nowrap ml-4">
                    {{ $incident->created_at->timezone('America/New_York')->format('M d, Y') }}
                </time>
            </header>

            {{-- Affected Components --}}
            @if($incident->components->count() > 0)
            <div class="mb-3">
                <p class="text-sm text-gray-600">
                    <strong>Affected Components:</strong>
                    {{ $incident->components->pluck('name')->join(', ') }}
                </p>
            </div>
            @endif

            {{-- Duration --}}
            @if($incident->started_at)
            <p class="text-sm text-gray-600 mb-3">
                <strong>Duration:</strong>
                @if($incident->resolved_at)
                {{
                $incident->started_at->timezone('America/New_York')->diffForHumans($incident->resolved_at->timezone('America/New_York'),
                true) }}
                @else
                {{ $incident->started_at->timezone('America/New_York')->diffForHumans() }} (ongoing)
                @endif
            </p>
            @endif

            {{-- Latest Update --}}
            @if($incident->updates->first())
            <div class="border-t pt-3 mt-3">
                <p class="text-sm text-gray-600 mb-1">
                    <strong>Latest Update:</strong>
                </p>
                <p class="text-sm">{!! nl2br(e($incident->updates->first()->message)) !!}</p>
            </div>
            @endif
        </article>
        @endforeach
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $incidents->links() }}
    </div>
    @else
    <div class="bg-white border rounded-lg p-8 text-center">
        <p class="text-gray-600">No incidents found for the selected time period.</p>
    </div>
    @endif
</div>