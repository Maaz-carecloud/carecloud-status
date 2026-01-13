<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold">User Management</h1>
            <p class="text-gray-600 mt-1">Manage user accounts and role assignments</p>
        </div>
        <button wire:click="createUser"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold">
            + Create User
        </button>
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
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search users..."
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            {{-- Role Filter --}}
            <div class="min-w-[180px]">
                <select wire:model.live="roleFilter"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="all">All Roles</option>
                    @foreach($roles as $role)
                    <option value="{{ $role->value }}">{{ $role->label() }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Users Table --}}
    <div class="bg-white border rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
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
                            <button wire:click="sortBy('email')"
                                class="flex items-center gap-1 font-semibold text-gray-700 hover:text-gray-900">
                                Email
                                @if($sortField === 'email')
                                <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left">
                            <button wire:click="sortBy('role')"
                                class="flex items-center gap-1 font-semibold text-gray-700 hover:text-gray-900">
                                Role
                                @if($sortField === 'role')
                                <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700">Activity</th>
                        <th class="px-6 py-3 text-left">
                            <button wire:click="sortBy('created_at')"
                                class="flex items-center gap-1 font-semibold text-gray-700 hover:text-gray-900">
                                Joined
                                @if($sortField === 'created_at')
                                <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-right font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50 {{ $user->id === auth()->id() ? 'bg-blue-50' : '' }}">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div class="font-semibold">{{ $user->name }}</div>
                                @if($user->id === auth()->id())
                                <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-800">You</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-gray-600">{{ $user->email }}</div>
                        </td>
                        <td class="px-6 py-4">
                            @can('update', $user)
                            @if($user->id === auth()->id())
                            <span
                                class="px-3 py-1 text-sm rounded font-semibold
                                            {{ $user->role->value === 'super_admin' ? 'bg-purple-100 text-purple-800' : '' }}
                                            {{ $user->role->value === 'admin' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $user->role->value === 'editor' ? 'bg-green-100 text-green-800' : '' }}">
                                {{ $user->role->label() }}
                            </span>
                            @else
                            <select wire:change="updateRole({{ $user->id }}, $event.target.value)"
                                class="px-3 py-1 text-sm rounded border focus:ring-2 focus:ring-blue-500 font-semibold
                                                    {{ $user->role->value === 'super_admin' ? 'bg-purple-100 text-purple-800 border-purple-300' : '' }}
                                                    {{ $user->role->value === 'admin' ? 'bg-blue-100 text-blue-800 border-blue-300' : '' }}
                                                    {{ $user->role->value === 'editor' ? 'bg-green-100 text-green-800 border-green-300' : '' }}">
                                @foreach($roles as $role)
                                <option value="{{ $role->value }}" {{ $user->role === $role ? 'selected' : '' }}>
                                    {{ $role->label() }}
                                </option>
                                @endforeach
                            </select>
                            @endif
                            @else
                            <span class="px-3 py-1 text-sm rounded font-semibold
                                        {{ $user->role->value === 'super_admin' ? 'bg-purple-100 text-purple-800' : '' }}
                                        {{ $user->role->value === 'admin' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $user->role->value === 'editor' ? 'bg-green-100 text-green-800' : '' }}">
                                {{ $user->role->label() }}
                            </span>
                            @endcan
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-600">
                                <div>{{ $user->incidents_count }} {{ Str::plural('incident', $user->incidents_count) }}
                                </div>
                                <div>{{ $user->incident_updates_count }} {{ Str::plural('update',
                                    $user->incident_updates_count) }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-600">
                                {{ $user->created_at->timezone('America/New_York')->format('M j, Y') }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $user->created_at->timezone('America/New_York')->diffForHumans() }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                @can('update', $user)
                                <button wire:click="editUser({{ $user->id }})"
                                    class="text-blue-600 hover:text-blue-800 font-semibold text-sm">
                                    Edit
                                </button>
                                @endcan

                                @can('delete', $user)
                                @if($user->id !== auth()->id())
                                <button wire:click="confirmDelete({{ $user->id }})"
                                    class="text-red-600 hover:text-red-800 font-semibold text-sm">
                                    Delete
                                </button>
                                @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            No users found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($users->hasPages())
        <div class="px-6 py-4 border-t">
            {{ $users->links() }}
        </div>
        @endif
    </div>

    {{-- Role Permissions Info --}}
    <div class="mt-6 bg-white border rounded-lg p-6">
        <h3 class="font-semibold text-gray-700 mb-4">Role Permissions</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($roles as $role)
            <div class="border rounded-lg p-4">
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-3 py-1 text-sm rounded font-semibold
                            {{ $role->value === 'super_admin' ? 'bg-purple-100 text-purple-800' : '' }}
                            {{ $role->value === 'admin' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $role->value === 'editor' ? 'bg-green-100 text-green-800' : '' }}">
                        {{ $role->label() }}
                    </span>
                </div>
                <ul class="text-sm text-gray-600 space-y-1">
                    @foreach($role->permissions() as $permission)
                    <li class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-green-600 mt-0.5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>{{ str_replace('_', ' ', ucfirst($permission)) }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Edit User Modal --}}
    @if($showEditModal && $editingUserId)
    <div class="fixed inset-0 flex items-center justify-center z-50" style="background: rgba(0, 0, 0, 0.5);">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <h3 class="text-xl font-bold mb-4">Edit User</h3>

            <form wire:submit="saveUser">
                {{-- Name --}}
                <div class="mb-4">
                    <label for="edit-name" class="block text-sm font-semibold text-gray-700 mb-2">
                        Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="edit-name" wire:model="editName"
                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('editName') border-red-500 @enderror">
                    @error('editName')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div class="mb-4">
                    <label for="edit-email" class="block text-sm font-semibold text-gray-700 mb-2">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email" id="edit-email" wire:model="editEmail"
                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('editEmail') border-red-500 @enderror">
                    @error('editEmail')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Role --}}
                <div class="mb-4">
                    <label for="edit-role" class="block text-sm font-semibold text-gray-700 mb-2">
                        Role <span class="text-red-500">*</span>
                    </label>
                    <select id="edit-role" wire:model="editRole"
                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('editRole') border-red-500 @enderror">
                        @foreach($roles as $role)
                        <option value="{{ $role->value }}">{{ $role->label() }}</option>
                        @endforeach
                    </select>
                    @error('editRole')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="mb-4">
                    <label for="edit-password" class="block text-sm font-semibold text-gray-700 mb-2">
                        New Password (leave blank to keep current)
                    </label>
                    <input type="password" id="edit-password" wire:model="editPassword"
                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('editPassword') border-red-500 @enderror">
                    @error('editPassword')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Actions --}}
                <div class="flex gap-3 justify-end">
                    <button type="button" wire:click="cancelEdit" class="px-4 py-2 border rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" wire:loading.attr="disabled"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove>Save Changes</span>
                        <span wire:loading>Saving...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Create User Modal --}}
    @if($showCreateModal)
    <div class="fixed inset-0 flex items-center justify-center z-50" style="background: rgba(0, 0, 0, 0.5);">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <h3 class="text-xl font-bold mb-4">Create New User</h3>

            <form wire:submit="saveNewUser">
                {{-- Name --}}
                <div class="mb-4">
                    <label for="create-name" class="block text-sm font-semibold text-gray-700 mb-2">
                        Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="create-name" wire:model="editName"
                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('editName') border-red-500 @enderror">
                    @error('editName')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div class="mb-4">
                    <label for="create-email" class="block text-sm font-semibold text-gray-700 mb-2">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email" id="create-email" wire:model="editEmail"
                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('editEmail') border-red-500 @enderror">
                    @error('editEmail')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Role --}}
                <div class="mb-4">
                    <label for="create-role" class="block text-sm font-semibold text-gray-700 mb-2">
                        Role <span class="text-red-500">*</span>
                    </label>
                    <select id="create-role" wire:model="editRole"
                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('editRole') border-red-500 @enderror">
                        @foreach($roles as $role)
                        <option value="{{ $role->value }}">{{ $role->label() }}</option>
                        @endforeach
                    </select>
                    @error('editRole')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="mb-4">
                    <label for="create-password" class="block text-sm font-semibold text-gray-700 mb-2">
                        Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password" id="create-password" wire:model="editPassword"
                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('editPassword') border-red-500 @enderror">
                    @error('editPassword')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Minimum 8 characters. User will be automatically verified.</p>
                </div>

                {{-- Actions --}}
                <div class="flex gap-3 justify-end">
                    <button type="button" wire:click="cancelCreate"
                        class="px-4 py-2 border rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" wire:loading.attr="disabled"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove>Create User</span>
                        <span wire:loading>Creating...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal && $userToDelete)
    <div class="fixed inset-0flex items-center justify-center z-50" style="background: rgba(0, 0, 0, 0.5);">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <h3 class="text-xl font-bold mb-4">Confirm Deletion</h3>
            <p class="text-gray-600 mb-6">
                Are you sure you want to delete this user?
                This action cannot be undone and will remove all their data.
            </p>
            <div class="flex gap-3 justify-end">
                <button wire:click="cancelDelete" class="px-4 py-2 border rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button wire:click="deleteUser" wire:loading.attr="disabled"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="deleteUser">Delete User</span>
                    <span wire:loading wire:target="deleteUser">Deleting...</span>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>