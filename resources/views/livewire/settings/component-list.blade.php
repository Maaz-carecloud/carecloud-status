<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold">Components</h1>
            <p class="text-gray-600 mt-1">Manage system components and their status</p>
        </div>

        @can('create', App\Models\Component::class)
        <a href="{{ route('components.create') }}"
            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 font-semibold">
            + Add Component
        </a>
        @endcan
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg p-4 mb-6">
        {{ session('success') }}
    </div>
    @endif

    {{-- Filters --}}
    <div class="bg-white border rounded-lg p-4 mb-6">
        <div class="flex flex-wrap gap-4">
            {{-- Search --}}
            <div class="flex-1 min-w-[200px]">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search components..."
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            {{-- Status Filter --}}
            <div class="min-w-[200px]">
                <select wire:model.live="statusFilter"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="all">All Statuses</option>
                    @foreach($statuses as $status)
                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Components Table --}}
    <div class="bg-white border rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left">
                            <button wire:click="sortBy('order')"
                                class="flex items-center gap-1 font-semibold text-gray-700 hover:text-gray-900">
                                Order
                                @if($sortField === 'order')
                                <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left">
                            <button wire:click="sortBy('name')"
                                class="flex items-center gap-1 font-semibold text-gray-700 hover:text-gray-900">
                                Name
                                @if($sortField === 'name')
                                <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left">
                            <button wire:click="sortBy('status')"
                                class="flex items-center gap-1 font-semibold text-gray-700 hover:text-gray-900">
                                Status
                                @if($sortField === 'status')
                                <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700">Active Incidents</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700">Enabled</th>
                        <th class="px-6 py-3 text-right font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($components as $component)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-sm text-gray-600">{{ $component->order }}</span>
                                @can('update', $component)
                                <div class="flex flex-col">
                                    <button wire:click="moveUp({{ $component->id }})"
                                        class="text-gray-400 hover:text-gray-600" title="Move up">
                                        ▲
                                    </button>
                                    <button wire:click="moveDown({{ $component->id }})"
                                        class="text-gray-400 hover:text-gray-600" title="Move down">
                                        ▼
                                    </button>
                                </div>
                                @endcan
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div>
                                <div class="font-semibold">{{ $component->name }}</div>
                                @if($component->description)
                                <div class="text-sm text-gray-600">{{ Str::limit($component->description, 60) }}</div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 text-sm rounded"
                                style="background-color: {{ $component->status->color() }}20; color: {{ $component->status->color() }};">
                                {{ $component->status->label() }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($component->incidents->count() > 0)
                            <span class="text-sm font-semibold text-orange-600">
                                {{ $component->incidents->count() }} active
                            </span>
                            @else
                            <span class="text-sm text-gray-400">None</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @can('update', $component)
                            <button wire:click="toggleEnabled({{ $component->id }})"
                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $component->is_enabled ? 'bg-green-600' : 'bg-gray-300' }}">
                                <span
                                    class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $component->is_enabled ? 'translate-x-6' : 'translate-x-1' }}"></span>
                            </button>
                            @else
                            <span
                                class="px-2 py-1 text-xs rounded {{ $component->is_enabled ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $component->is_enabled ? 'Enabled' : 'Disabled' }}
                            </span>
                            @endcan
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                @can('update', $component)
                                <a href="{{ route('components.edit', $component) }}"
                                    class="text-blue-600 hover:text-blue-800 font-semibold text-sm">
                                    Edit
                                </a>
                                @endcan

                                @can('delete', $component)
                                <button wire:click="confirmDelete({{ $component->id }})"
                                    class="text-red-600 hover:text-red-800 font-semibold text-sm">
                                    Delete
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            No components found.
                            @can('create', App\Models\Component::class)
                            <a href="{{ route('components.create') }}"
                                class="text-blue-600 hover:text-blue-800 underline">
                                Create your first component
                            </a>
                            @endcan
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($components->hasPages())
        <div class="px-6 py-4 border-t">
            {{ $components->links() }}
        </div>
        @endif
    </div>

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal && $componentToDelete)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <h3 class="text-xl font-bold mb-4">Confirm Deletion</h3>
            <p class="text-gray-600 mb-6">
                Are you sure you want to delete <strong>{{ $componentToDelete->name }}</strong>?
                This action cannot be undone and will remove all associated status logs.
            </p>
            <div class="flex gap-3 justify-end">
                <button wire:click="cancelDelete" class="px-4 py-2 border rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button wire:click="deleteComponent" wire:loading.attr="disabled"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="deleteComponent">Delete Component</span>
                    <span wire:loading wire:target="deleteComponent">Deleting...</span>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>