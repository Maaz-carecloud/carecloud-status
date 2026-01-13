<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Appearance')" :subheading=" __('Update the appearance settings for your site')">
        <form wire:submit="save" class="space-y-6">
            {{-- Theme Selection --}}
            <div>
                <flux:heading size="lg">Theme</flux:heading>
                <flux:subheading>Choose your preferred theme</flux:subheading>

                <div class="mt-4">
                    <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
                        <flux:radio value="light" icon="sun">{{ __('Light') }}</flux:radio>
                        <flux:radio value="dark" icon="moon">{{ __('Dark') }}</flux:radio>
                        <flux:radio value="system" icon="computer-desktop">{{ __('System') }}</flux:radio>
                    </flux:radio.group>
                </div>
            </div>

            {{-- Site Name --}}
            <div>
                <flux:heading size="lg">Site Name</flux:heading>
                <flux:subheading>The name displayed across your status page</flux:subheading>

                <div class="mt-4">
                    <flux:input wire:model="siteName" placeholder="{{ config('app.name') }}" />
                </div>
            </div>

            {{-- Theme Color --}}
            <div>
                <flux:heading size="lg">Theme Color</flux:heading>
                <flux:subheading>Primary color used for buttons and accents throughout the site</flux:subheading>

                <div class="mt-4 flex items-center gap-4">
                    <input type="color" wire:model.live="themeColor"
                        class="h-12 w-24 rounded-lg border border-gray-300 cursor-pointer" />
                    <flux:input wire:model="themeColor" placeholder="#3B82F6" class="flex-1 max-w-xs" />
                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="$set('themeColor', '#3B82F6')"
                            class="px-3 py-2 text-sm border rounded-lg hover:bg-gray-50">Blue</button>
                        <button type="button" wire:click="$set('themeColor', '#10B981')"
                            class="px-3 py-2 text-sm border rounded-lg hover:bg-gray-50">Green</button>
                        <button type="button" wire:click="$set('themeColor', '#8B5CF6')"
                            class="px-3 py-2 text-sm border rounded-lg hover:bg-gray-50">Purple</button>
                        <button type="button" wire:click="$set('themeColor', '#EF4444')"
                            class="px-3 py-2 text-sm border rounded-lg hover:bg-gray-50">Red</button>
                    </div>
                </div>
                @error('themeColor') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
            </div>

            {{-- Admin Logo --}}
            <div>
                <flux:heading size="lg">Admin Dashboard Logo</flux:heading>
                <flux:subheading>Logo displayed in the admin dashboard sidebar</flux:subheading>

                <div class="mt-4 space-y-4">
                    @if($currentAdminLogo)
                    <div class="flex items-start gap-4">
                        <div class="border rounded-lg p-4 bg-white">
                            <img src="{{ Storage::url($currentAdminLogo) }}" alt="Admin Logo"
                                class="h-16 object-contain">
                        </div>
                        <flux:button wire:click="removeAdminLogo" variant="danger" size="sm"
                            wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="removeAdminLogo">Remove</span>
                            <span wire:loading wire:target="removeAdminLogo">Removing...</span>
                        </flux:button>
                    </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Upload New Logo</label>
                        <input type="file" wire:model="adminLogo" accept="image/*"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                        @error('adminLogo') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
                    </div>

                    @if($adminLogo)
                    <div class="border rounded-lg p-4 bg-white">
                        <p class="text-sm text-gray-600 mb-2">Preview:</p>
                        <img src="{{ $adminLogo->temporaryUrl() }}" alt="Preview" class="h-16 object-contain">
                    </div>
                    @endif
                </div>
            </div>

            {{-- Public Logo --}}
            <div>
                <flux:heading size="lg">Public Status Page Logo</flux:heading>
                <flux:subheading>Logo displayed on your public status page header</flux:subheading>

                <div class="mt-4 space-y-4">
                    @if($currentPublicLogo)
                    <div class="flex items-start gap-4">
                        <div class="border rounded-lg p-4 bg-white">
                            <img src="{{ Storage::url($currentPublicLogo) }}" alt="Public Logo"
                                class="h-16 object-contain">
                        </div>
                        <flux:button wire:click="removePublicLogo" variant="danger" size="sm"
                            wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="removePublicLogo">Remove</span>
                            <span wire:loading wire:target="removePublicLogo">Removing...</span>
                        </flux:button>
                    </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Upload New Logo</label>
                        <input type="file" wire:model="publicLogo" accept="image/*"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                        @error('publicLogo') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
                    </div>

                    @if($publicLogo)
                    <div class="border rounded-lg p-4 bg-white">
                        <p class="text-sm text-gray-600 mb-2">Preview:</p>
                        <img src="{{ $publicLogo->temporaryUrl() }}" alt="Preview" class="h-16 object-contain">
                    </div>
                    @endif
                </div>
            </div>

            {{-- Favicon --}}
            <div>
                <flux:heading size="lg">Favicon</flux:heading>
                <flux:subheading>Icon displayed in browser tabs (ICO, PNG, or SVG, max 1MB)</flux:subheading>

                <div class="mt-4 space-y-4">
                    @if($currentFavicon)
                    <div class="flex items-start gap-4">
                        <div class="border rounded-lg p-4 bg-white">
                            <img src="{{ Storage::url($currentFavicon) }}" alt="Favicon" class="h-8 w-8 object-contain">
                        </div>
                        <flux:button wire:click="removeFavicon" variant="danger" size="sm" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="removeFavicon">Remove</span>
                            <span wire:loading wire:target="removeFavicon">Removing...</span>
                        </flux:button>
                    </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Upload New Favicon</label>
                        <input type="file" wire:model="favicon" accept=".ico,.png,.svg"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                        @error('favicon') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
                        <p class="text-xs text-gray-500 mt-1">Recommended: 32x32 or 64x64 pixels</p>
                    </div>

                    @if($favicon)
                    <div class="border rounded-lg p-4 bg-white">
                        <p class="text-sm text-gray-600 mb-2">Preview:</p>
                        <img src="{{ $favicon->temporaryUrl() }}" alt="Preview" class="h-8 w-8 object-contain">
                    </div>
                    @endif
                </div>
            </div>

            {{-- Loading Indicator --}}
            <div wire:loading wire:target="adminLogo,publicLogo,favicon" class="text-sm text-gray-600">
                Uploading...
            </div>

            {{-- Save Button --}}
            <div class="flex justify-end">
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                    <span wire:loading.remove>Save Changes</span>
                    <span wire:loading>Saving...</span>
                </flux:button>
            </div>
        </form>
    </x-settings.layout>
</section>