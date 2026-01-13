<div>
    <div class="mb-6">
        <div class="flex items-center gap-4 mb-2">
            <a href="{{ route('components.index') }}" class="text-gray-600 hover:text-gray-900">
                ← Back to Components
            </a>
        </div>
        <h1 class="text-3xl font-bold">
            {{ $isEditMode ? 'Edit Component' : 'New Component' }}
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

    {{-- Form --}}
    <div class="bg-white border rounded-lg p-6">
        <form wire:submit="save">
            {{-- Name --}}
            <div class="mb-6">
                <label for="name" class="block font-semibold text-gray-700 mb-2">
                    Component Name <span class="text-red-500">*</span>
                </label>
                <input type="text" id="name" wire:model="name"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
                    placeholder="e.g., API Server, Database, Payment Gateway">
                @error('name')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Description --}}
            <div class="mb-6">
                <label for="description" class="block font-semibold text-gray-700 mb-2">
                    Description
                </label>
                <textarea id="description" wire:model="description" rows="3"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror"
                    placeholder="Optional description of what this component does..."></textarea>
                @error('description')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Status & Order --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                {{-- Status --}}
                <div>
                    <label for="status" class="block font-semibold text-gray-700 mb-2">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select id="status" wire:model="status"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('status') border-red-500 @enderror">
                        <option value="">Select status...</option>
                        @foreach($statuses as $statusOption)
                        <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
                        @endforeach
                    </select>
                    @error('status')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Order --}}
                <div>
                    <label for="order" class="block font-semibold text-gray-700 mb-2">
                        Display Order <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="order" wire:model="order" min="0"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('order') border-red-500 @enderror">
                    @error('order')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-sm text-gray-600 mt-1">Lower numbers appear first</p>
                </div>
            </div>

            {{-- Visibility Toggle --}}
            <div class="mb-6">
                <label class="block font-semibold text-gray-700 mb-2">
                    Visibility
                </label>
                <div class="flex items-center">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model.live="is_enabled" class="sr-only peer">
                        <div
                            class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600">
                        </div>
                        <span class="ms-3 text-sm font-medium text-gray-900">
                            {{ $is_enabled ? 'Enabled' : 'Disabled' }}
                        </span>
                    </label>
                </div>
                <p class="text-sm text-gray-600 mt-2">When enabled, this component will be visible on the public status
                    page</p>
            </div>

            {{-- Group ID (Future Feature) --}}
            @if(false)
            <div class="mb-6">
                <label for="group_id" class="block font-semibold text-gray-700 mb-2">
                    Component Group
                </label>
                <select id="group_id" wire:model="groupId"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">No Group</option>
                    {{-- Future: List component groups --}}
                </select>
                <p class="text-sm text-gray-600 mt-1">Optional: Organize components into groups</p>
            </div>
            @endif

            {{-- Form Actions --}}
            <div class="pt-6 mt-6 border-t border-gray-200">
                <div class="flex items-center justify-end gap-3">
                    @if($isEditMode)
                    @can('delete', $component)
                    <button type="button" wire:click="delete"
                        wire:confirm="Are you sure you want to delete this component? This action cannot be undone."
                        wire:loading.attr="disabled"
                        class="mr-auto px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-semibold disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="delete">Delete Component</span>
                        <span wire:loading wire:target="delete">Deleting...</span>
                    </button>
                    @endcan
                    @endif

                    <a href="{{ route('components.index') }}"
                        class="px-6 py-2.5 border border-gray-300 bg-white text-gray-700 rounded-lg hover:bg-gray-50 font-semibold">
                        Cancel
                    </a>
                    <button type="submit" wire:loading.attr="disabled"
                        class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove>{{ $isEditMode ? 'Update Component' : 'Create Component' }}</span>
                        <span wire:loading>{{ $isEditMode ? 'Updating...' : 'Creating...' }}</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Component Preview --}}
    @if($name)
    <div class="mt-6 bg-white border rounded-lg p-6">
        <h3 class="font-semibold text-gray-700 mb-4">Preview</h3>
        <div class="border rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <div class="font-semibold text-lg">{{ $name }}</div>
                    @if($description)
                    <div class="text-gray-600 text-sm mt-1">{{ $description }}</div>
                    @endif
                </div>
                @if($status)
                @php
                $statusEnum = \App\Enums\ComponentStatus::from($status);
                @endphp
                <span class="px-3 py-1 text-sm font-semibold rounded"
                    style="background-color: {{ $statusEnum->color() }}20; color: {{ $statusEnum->color() }};">
                    {{ $statusEnum->label() }}
                </span>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>