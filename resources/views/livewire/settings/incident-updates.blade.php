<div>
    {{-- Flash Messages --}}
    @if (session()->has('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg p-3 mb-4 text-sm">
        {{ session('success') }}
    </div>
    @endif

    @if (session()->has('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-3 mb-4 text-sm">
        {{ session('error') }}
    </div>
    @endif

    {{-- Add Update Button --}}
    @can('update', $incident)
    @if(!$showForm)
    <button wire:click="toggleForm"
        class="w-full mb-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold">
        + Post Update
    </button>
    @endif
    @endcan

    {{-- Add Update Form --}}
    @if($showForm)
    <div class="bg-gray-50 border rounded-lg p-4 mb-4">
        <h4 class="font-semibold text-gray-700 mb-3">Post New Update</h4>

        <form wire:submit="addUpdate">
            {{-- Message --}}
            <div class="mb-4">
                <label for="update-message" class="block text-sm font-semibold text-gray-700 mb-2">
                    Update Message <span class="text-red-500">*</span>
                </label>
                <textarea id="update-message" wire:model="message" rows="3"
                    class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm @error('message') border-red-500 @enderror"
                    placeholder="Describe the current status and any progress made..."></textarea>
                @error('message')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Status --}}
            <div class="mb-4">
                <label for="update-status" class="block text-sm font-semibold text-gray-700 mb-2">
                    New Status <span class="text-red-500">*</span>
                </label>
                <select id="update-status" wire:model="status"
                    class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm @error('status') border-red-500 @enderror">
                    @foreach($statuses as $statusOption)
                    <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
                    @endforeach
                </select>
                @error('status')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-600 mt-1">This will update the incident's current status</p>
            </div>

            {{-- Actions --}}
            <div class="flex gap-2">
                <button type="submit" wire:loading.attr="disabled"
                    class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 font-semibold disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove>Post Update</span>
                    <span wire:loading>Posting...</span>
                </button>
                <button type="button" wire:click="cancelUpdate"
                    class="px-4 py-2 border rounded-lg hover:bg-gray-50 text-sm font-semibold">
                    Cancel
                </button>
            </div>
        </form>
    </div>
    @endif

    {{-- Updates Timeline --}}
    <div class="space-y-4">
        <h4 class="font-semibold text-gray-700">Update History</h4>

        @forelse($updates as $update)
        <div class="border rounded-lg p-4 bg-white">
            {{-- Update Header --}}
            <div class="flex items-start justify-between mb-2">
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 text-xs font-semibold rounded"
                        style="background-color: {{ $update->status->color() }}20; color: {{ $update->status->color() }};">
                        {{ $update->status->label() }}
                    </span>
                    <span class="text-sm text-gray-600">
                        {{ $update->created_at->timezone('America/New_York')->format('M j, Y g:i A') }}
                    </span>
                </div>

                @can('update', $incident)
                @if($updates->count() > 1)
                <button wire:click="deleteUpdate({{ $update->id }})"
                    wire:confirm="Are you sure you want to delete this update?" wire:loading.attr="disabled"
                    class="text-red-600 hover:text-red-800 text-xs font-semibold disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="deleteUpdate({{ $update->id }})">Delete</span>
                    <span wire:loading wire:target="deleteUpdate({{ $update->id }})">Deleting...</span>
                </button>
                @endif
                @endcan
            </div>

            {{-- Update Message --}}
            <p class="text-gray-700 text-sm mb-2">{{ $update->message }}</p>

            {{-- Update Footer --}}
            <div class="flex items-center gap-2 text-xs text-gray-500">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span>Posted by {{ $update->user->name }}</span>
                <span>•</span>
                <span>{{ $update->created_at->timezone('America/New_York')->diffForHumans() }}</span>
            </div>
        </div>
        @empty
        <div class="border rounded-lg p-6 text-center text-gray-500 text-sm">
            No updates posted yet.
        </div>
        @endforelse
    </div>

    {{-- Current Incident Status --}}
    <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <div class="flex items-center gap-2 text-sm">
            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-blue-800">
                <strong>Current Status:</strong> {{ $incident->status->label() }}
                @if($incident->isResolved() && $incident->resolved_at)
                (Resolved {{ $incident->resolved_at->timezone('America/New_York')->diffForHumans() }})
                @endif
            </span>
        </div>
    </div>
</div>