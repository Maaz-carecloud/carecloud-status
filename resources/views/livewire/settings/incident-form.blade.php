<div>
    <div class="mb-6">
        <div class="flex items-center gap-4 mb-2">
            <a href="{{ route('incidents.index') }}" class="text-gray-600 hover:text-gray-900">
                ← Back to Incidents
            </a>
        </div>
        <h1 class="text-3xl font-bold">
            {{ $incidentId ? 'Edit Incident' : 'New Incident' }}
        </h1>
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Form --}}
        <div class="lg:col-span-2">
            <div class="bg-white border rounded-lg p-6">
                <form wire:submit="save">
                    {{-- Incident Type Toggle --}}
                    <div class="mb-6">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" wire:model.live="isScheduled" class="sr-only peer">
                            <div
                                class="relative w-11 h-6 bg-gray-300 peer-focus:ring-2 peer-focus:ring-blue-500 rounded-full peer peer-checked:bg-blue-600 transition-colors">
                                <div
                                    class="absolute top-0.5 left-0.5 bg-white w-5 h-5 rounded-full transition-transform peer-checked:translate-x-5">
                                </div>
                            </div>
                            <span class="ml-3 font-semibold text-gray-700">
                                {{ $isScheduled ? 'Scheduled Maintenance' : 'Unscheduled Incident' }}
                            </span>
                        </label>
                        <p class="text-sm text-gray-600 mt-2">
                            {{ $isScheduled
                            ? 'This is planned maintenance scheduled for a specific time'
                            : 'This is an unscheduled incident that is currently affecting services' }}
                        </p>
                    </div>

                    {{-- Title --}}
                    <div class="mb-6">
                        <label for="name" class="block font-semibold text-gray-700 mb-2">
                            Incident Title <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="name" wire:model="name"
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
                            placeholder="e.g., Database Connection Issues">
                        @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Message --}}
                    <div class="mb-6">
                        <label for="message" class="block font-semibold text-gray-700 mb-2">
                            Message <span class="text-red-500">*</span>
                        </label>
                        <textarea id="message" wire:model="message" rows="4"
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('message') border-red-500 @enderror"
                            placeholder="Describe the incident and its impact..."></textarea>
                        @error('message')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        {{-- Status --}}
                        <div>
                            <label for="status" class="block font-semibold text-gray-700 mb-2">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select id="status" wire:model="status"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('status') border-red-500 @enderror">
                                @foreach($incidentStatuses as $statusOption)
                                <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
                                @endforeach
                            </select>
                            @error('status')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Impact --}}
                        <div>
                            <label for="impact" class="block font-semibold text-gray-700 mb-2">
                                Impact Level <span class="text-red-500">*</span>
                            </label>
                            <select id="impact" wire:model="impact"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('impact') border-red-500 @enderror">
                                @foreach($impacts as $impactOption)
                                <option value="{{ $impactOption->value }}">
                                    {{ ucfirst($impactOption->value) }} Impact
                                </option>
                                @endforeach
                            </select>
                            @error('impact')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Scheduled Date/Time --}}
                    @if($isScheduled)
                    <div class="mb-6">
                        <label for="scheduledAt" class="block font-semibold text-gray-700 mb-2">
                            Scheduled Date & Time <span class="text-red-500">*</span>
                        </label>
                        <input type="datetime-local" id="scheduledAt" wire:model="scheduledAt"
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('scheduledAt') border-red-500 @enderror">
                        @error('scheduledAt')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-sm text-gray-600 mt-1">When will this maintenance begin?</p>
                    </div>
                    @endif

                    {{-- Affected Components --}}
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-2">
                            <label class="block font-semibold text-gray-700">
                                Affected Components <span class="text-red-500">*</span>
                            </label>
                            <button type="button" wire:click="toggleAllComponents"
                                class="text-sm text-blue-600 hover:text-blue-800">
                                {{ count($affectedComponents) === $components->count() ? 'Deselect All' : 'Select All'
                                }}
                            </button>
                        </div>

                        <div
                            class="border rounded-lg p-4 max-h-96 overflow-y-auto @error('affectedComponents') border-red-500 @enderror">
                            @forelse($components as $component)
                            <div class="flex items-center py-2 hover:bg-gray-50 px-2 rounded gap-3">
                                <label class="flex items-center flex-1 cursor-pointer">
                                    <input type="checkbox" wire:model.live="affectedComponents"
                                        value="{{ $component->id }}"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-2 focus:ring-blue-500">
                                    <span class="ml-3 text-gray-700">{{ $component->name }}</span>
                                    <span class="ml-auto px-2 py-1 text-xs rounded"
                                        style="background-color: {{ $component->status->color() }}20; color: {{ $component->status->color() }};">
                                        Current: {{ $component->status->label() }}
                                    </span>
                                </label>

                                @if(in_array($component->id, $affectedComponents))
                                <div class="flex items-center gap-2">
                                    <span class="text-sm text-gray-600">→</span>
                                    <select wire:model="componentStatuses.{{ $component->id }}"
                                        class="px-3 py-1 text-sm rounded border focus:ring-2 focus:ring-blue-500 min-w-[180px]">
                                        @foreach($statuses as $statusOption)
                                        <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif
                            </div>
                            @empty
                            <p class="text-gray-500 text-sm">No components available. Please create components first.
                            </p>
                            @endforelse
                        </div>
                        @error('affectedComponents')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-sm text-gray-600 mt-1">
                            Select components and set their status during this incident.
                            The arrow (→) shows the status change that will be applied.
                        </p>
                    </div>

                    {{-- Form Actions --}}
                    <div class="flex items-center justify-between pt-6 border-t">
                        <div>
                            @if($incidentId)
                            @can('delete', $incident)
                            <button type="button" wire:click="delete"
                                wire:confirm="Are you sure you want to delete this incident? This action cannot be undone."
                                wire:loading.attr="disabled"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-semibold disabled:opacity-50 disabled:cursor-not-allowed">
                                <span wire:loading.remove wire:target="delete">Delete Incident</span>
                                <span wire:loading wire:target="delete">Deleting...</span>
                            </button>
                            @endcan
                            @endif
                        </div>

                        <div class="flex gap-3">
                            <button type="button" wire:click="cancel"
                                class="px-6 py-2 border rounded-lg hover:bg-gray-50 font-semibold">
                                Cancel
                            </button>
                            <button type="submit" wire:loading.attr="disabled"
                                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold disabled:opacity-50 disabled:cursor-not-allowed">
                                <span wire:loading.remove>{{ $incidentId ? 'Update Incident' : 'Create Incident'
                                    }}</span>
                                <span wire:loading>{{ $incidentId ? 'Updating...' : 'Creating...' }}</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Status Guide --}}
            <div class="bg-white border rounded-lg p-6">
                <h3 class="font-semibold text-gray-700 mb-4">Status Workflow</h3>
                <div class="space-y-3 text-sm">
                    @foreach($statuses as $statusOption)
                    <div class="flex items-center gap-3">
                        <span class="px-3 py-1 rounded text-xs font-semibold min-w-[110px] text-center"
                            style="background-color: {{ $statusOption->color() }}20; color: {{ $statusOption->color() }};">
                            {{ $statusOption->label() }}
                        </span>
                        <span class="text-gray-600">
                            @if($statusOption->value === 'investigating')
                            Initial incident status
                            @elseif($statusOption->value === 'identified')
                            Root cause found
                            @elseif($statusOption->value === 'monitoring')
                            Fix deployed, monitoring
                            @elseif($statusOption->value === 'resolved')
                            Issue fully resolved
                            @endif
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Impact Guide --}}
            <div class="bg-white border rounded-lg p-6">
                <h3 class="font-semibold text-gray-700 mb-4">Impact Levels</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex items-center gap-3">
                        <span
                            class="px-3 py-1 rounded text-xs font-semibold bg-yellow-100 text-yellow-800 min-w-[110px] text-center">
                            MINOR
                        </span>
                        <span class="text-gray-600">Minor issues, limited impact</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span
                            class="px-3 py-1 rounded text-xs font-semibold bg-orange-100 text-orange-800 min-w-[110px] text-center">
                            MAJOR
                        </span>
                        <span class="text-gray-600">Significant degradation</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span
                            class="px-3 py-1 rounded text-xs font-semibold bg-red-100 text-red-800 min-w-[110px] text-center">
                            CRITICAL
                        </span>
                        <span class="text-gray-600">Complete service outage</span>
                    </div>
                </div>
            </div>

            {{-- Updates Section (Edit mode only) --}}
            @if($incidentId)
            <div class="bg-white border rounded-lg p-6">
                <h3 class="font-semibold text-gray-700 mb-4">Incident Updates</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Post updates to keep users informed about the progress of this incident.
                </p>
                <livewire:settings.incident-updates :incidentId="$incidentId" :key="'updates-' . $incidentId" />
            </div>
            @endif
        </div>
    </div>
</div>