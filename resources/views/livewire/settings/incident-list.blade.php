<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold">Incidents</h1>
            <p class="text-gray-600 mt-1">Manage system incidents and scheduled maintenance</p>
        </div>

        @can('create', App\Models\Incident::class)
        <a href="{{ route('incidents.create') }}"
            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 font-semibold">
            + Create Incident
        </a>
        @endcan
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg p-4 mb-6">
        {{ session('success') }}
    </div>
    @endif

    @if (session()->has('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4 mb-6">
        {{ session('error') }}
    </div>
    @endif

    {{-- Filters --}}
    <div class="bg-white border rounded-lg p-4 mb-6">
        <div class="flex flex-wrap gap-4">
            {{-- Search --}}
            <div class="flex-1 min-w-[200px]">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search incidents..."
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            {{-- Status Filter --}}
            <div class="min-w-[180px]">
                <select wire:model.live="statusFilter"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="all">All Statuses</option>
                    @foreach($statuses as $status)
                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Impact Filter --}}
            <div class="min-w-[180px]">
                <select wire:model.live="impactFilter"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="all">All Impacts</option>
                    @foreach($impacts as $impact)
                    <option value="{{ $impact->value }}">{{ ucfirst($impact->value) }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Type Filter --}}
            <div class="min-w-[180px]">
                <select wire:model.live="typeFilter"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="all">All Types</option>
                    <option value="unscheduled">Incidents</option>
                    <option value="scheduled">Scheduled Maintenance</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Incidents List --}}
    <div class="space-y-4">
        @forelse($incidents as $incident)
        <div class="bg-white border rounded-lg p-6 hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    {{-- Header --}}
                    <div class="flex items-start gap-3 mb-3">
                        {{-- Impact Badge --}}
                        <span class="px-3 py-1 text-sm font-semibold rounded uppercase
                                {{ $incident->impact->value === 'critical' ? 'bg-red-100 text-red-800' : '' }}
                                {{ $incident->impact->value === 'major' ? 'bg-orange-100 text-orange-800' : '' }}
                                {{ $incident->impact->value === 'minor' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                            {{ $incident->impact->value }}
                        </span>

                        {{-- Status Badge --}}
                        <span class="px-3 py-1 text-sm font-semibold rounded"
                            style="background-color: {{ $incident->status->color() }}20; color: {{ $incident->status->color() }};">
                            {{ $incident->status->label() }}
                        </span>

                        {{-- Scheduled Badge --}}
                        @if($incident->is_scheduled)
                        <span class="px-3 py-1 text-sm font-semibold rounded bg-blue-100 text-blue-800">
                            Scheduled Maintenance
                        </span>
                        @endif
                    </div>

                    {{-- Title --}}
                    <h3 class="text-xl font-bold mb-2">{{ $incident->name }}</h3>

                    {{-- Message Preview --}}
                    <p class="text-gray-600 mb-3">{{ Str::limit($incident->message, 150) }}</p>

                    {{-- Meta Information --}}
                    <div class="flex flex-wrap gap-4 text-sm text-gray-600">
                        <div class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ $incident->created_at->timezone('America/New_York')->diffForHumans() }}</span>
                        </div>

                        <div class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span>{{ $incident->user->name }}</span>
                        </div>

                        <div class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                            </svg>
                            <span>{{ $incident->updates->count() }} {{ Str::plural('update',
                                $incident->updates->count()) }}</span>
                        </div>

                        @if($incident->components_count > 0)
                        <div class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                            </svg>
                            <span>{{ $incident->components_count }} affected {{ Str::plural('component',
                                $incident->components_count) }}</span>
                        </div>
                        @endif

                        @if($incident->resolved_at)
                        <div class="flex items-center gap-1 text-green-600 font-semibold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Resolved {{ $incident->resolved_at->timezone('America/New_York')->diffForHumans()
                                }}</span>
                        </div>
                        @endif
                    </div>

                    {{-- Affected Components --}}
                    @if($incident->components->isNotEmpty())
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach($incident->components as $component)
                        <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-700">
                            {{ $component->name }}
                        </span>
                        @endforeach
                    </div>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="flex flex-col gap-2 ml-4">
                    @can('update', $incident)
                    <a href="{{ route('incidents.edit', $incident) }}"
                        class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 text-center font-semibold">
                        Edit
                    </a>

                    @if(!$incident->isResolved())
                    <button wire:click="resolveIncident({{ $incident->id }})"
                        wire:confirm="Mark this incident as resolved?"
                        class="px-4 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700 font-semibold">
                        Resolve
                    </button>
                    @endif
                    @endcan

                    @can('delete', $incident)
                    <button wire:click="confirmDelete({{ $incident->id }})"
                        class="px-4 py-2 border border-red-600 text-red-600 text-sm rounded hover:bg-red-50 font-semibold">
                        Delete
                    </button>
                    @endcan
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white border rounded-lg p-8 text-center text-gray-500">
            No incidents found.
            @can('create', App\Models\Incident::class)
            <a href="{{ route('incidents.create') }}" class="text-blue-600 hover:text-blue-800 underline">
                Create your first incident
            </a>
            @endcan
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($incidents->hasPages())
    <div class="mt-6">
        {{ $incidents->links() }}
    </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal && $incidentToDelete)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <h3 class="text-xl font-bold mb-4">Confirm Deletion</h3>
            <p class="text-gray-600 mb-6">
                Are you sure you want to delete this incident?
                This action cannot be undone and will remove all associated updates and notifications.
            </p>
            <div class="flex gap-3 justify-end">
                <button wire:click="cancelDelete" class="px-4 py-2 border rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button wire:click="deleteIncident" wire:loading.attr="disabled"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="deleteIncident">Delete Incident</span>
                    <span wire:loading wire:target="deleteIncident">Deleting...</span>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>