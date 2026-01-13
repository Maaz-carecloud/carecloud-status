<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold">Subscribers</h1>
            <p class="text-gray-600 mt-1">Manage notification subscribers and their preferences</p>
        </div>
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
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search subscribers..."
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            {{-- Channel Filter --}}
            <div class="min-w-[180px]">
                <select wire:model.live="channelFilter"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="all">All Channels</option>
                    @foreach($channels as $channel)
                    <option value="{{ $channel->value }}">{{ $channel->label() }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Status Filter --}}
            <div class="min-w-[180px]">
                <select wire:model.live="statusFilter"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="all">All Status</option>
                    <option value="verified">Verified</option>
                    <option value="unverified">Unverified</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Subscribers Table --}}
    <div class="bg-white border rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left">
                            <button wire:click="sortBy('email')"
                                class="flex items-center gap-1 font-semibold text-gray-700 hover:text-gray-900">
                                Contact
                                @if($sortField === 'email')
                                <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700">Channels</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700">Components</th>
                        <th class="px-6 py-3 text-left">
                            <button wire:click="sortBy('verified_at')"
                                class="flex items-center gap-1 font-semibold text-gray-700 hover:text-gray-900">
                                Status
                                @if($sortField === 'verified_at')
                                <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left">
                            <button wire:click="sortBy('created_at')"
                                class="flex items-center gap-1 font-semibold text-gray-700 hover:text-gray-900">
                                Subscribed
                                @if($sortField === 'created_at')
                                <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-right font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($subscribers as $subscriber)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="space-y-1">
                                @if($subscriber->email)
                                <div class="flex items-center gap-2 text-sm">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    <span class="font-medium">{{ $subscriber->email }}</span>
                                </div>
                                @endif
                                @if($subscriber->phone)
                                <div class="flex items-center gap-2 text-sm text-gray-600">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                    <span>{{ $subscriber->phone }}</span>
                                </div>
                                @endif
                                @if($subscriber->teams_webhook_url)
                                <div class="flex items-center gap-2 text-sm text-gray-600">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                                    </svg>
                                    <span class="text-xs truncate" title="{{ $subscriber->teams_webhook_url }}">
                                        {{ Str::limit($subscriber->teams_webhook_url, 30) }}
                                    </span>
                                </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                @foreach($this->getChannels($subscriber) as $channel)
                                <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-800">
                                    {{ $channel }}
                                </span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-gray-600">
                                {{ $subscriber->components_count }} {{ Str::plural('component',
                                $subscriber->components_count) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col gap-1">
                                @if($subscriber->isVerified())
                                <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-800 w-fit">
                                    Verified
                                </span>
                                @else
                                <span class="px-2 py-1 text-xs rounded bg-yellow-100 text-yellow-800 w-fit">
                                    Unverified
                                </span>
                                @endif

                                @if($subscriber->is_active)
                                <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-800 w-fit">
                                    Active
                                </span>
                                @else
                                <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-800 w-fit">
                                    Inactive
                                </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-600">
                                {{ $subscriber->created_at->timezone('America/New_York')->format('M j, Y') }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $subscriber->created_at->timezone('America/New_York')->diffForHumans() }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                @can('update', $subscriber)
                                {{-- Toggle Active Status --}}
                                <button wire:click="toggleActive({{ $subscriber->id }})"
                                    title="{{ $subscriber->is_active ? 'Disable' : 'Enable' }}"
                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $subscriber->is_active ? 'bg-green-600' : 'bg-gray-300' }}">
                                    <span
                                        class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $subscriber->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                </button>

                                {{-- Resend Verification --}}
                                @if(!$subscriber->isVerified())
                                <button wire:click="resendVerification({{ $subscriber->id }})"
                                    class="px-3 py-1 text-xs bg-yellow-100 text-yellow-800 rounded hover:bg-yellow-200 font-semibold">
                                    Resend
                                </button>
                                <button wire:click="verifyManually({{ $subscriber->id }})"
                                    wire:confirm="Manually verify this subscriber?"
                                    class="px-3 py-1 text-xs bg-green-100 text-green-800 rounded hover:bg-green-200 font-semibold">
                                    Verify
                                </button>
                                @endif
                                @endcan

                                @can('delete', $subscriber)
                                <button wire:click="confirmDelete({{ $subscriber->id }})"
                                    class="px-3 py-1 text-xs text-red-600 hover:bg-red-50 rounded font-semibold">
                                    Delete
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            No subscribers found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($subscribers->hasPages())
        <div class="px-6 py-4 border-t">
            {{ $subscribers->links() }}
        </div>
        @endif
    </div>

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal && $subscriberToDelete)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <h3 class="text-xl font-bold mb-4">Confirm Deletion</h3>
            <p class="text-gray-600 mb-6">
                Are you sure you want to delete this subscriber?
                This action cannot be undone and will remove all their component subscriptions.
            </p>
            <div class="flex gap-3 justify-end">
                <button wire:click="cancelDelete" class="px-4 py-2 border rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button wire:click="deleteSubscriber" wire:loading.attr="disabled"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="deleteSubscriber">Delete Subscriber</span>
                    <span wire:loading wire:target="deleteSubscriber">Deleting...</span>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>