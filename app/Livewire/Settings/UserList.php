<?php

namespace App\Livewire\Settings;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Users')]
class UserList extends Component
{
    use WithPagination;
    use AuthorizesRequests;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $roleFilter = 'all';

    public string $sortField = 'name';
    public string $sortDirection = 'asc';

    public ?int $userToDelete = null;
    public bool $showDeleteModal = false;

    public ?int $editingUserId = null;
    public bool $showEditModal = false;
    public bool $showCreateModal = false;

    #[Validate('required|string|max:255')]
    public string $editName = '';

    #[Validate('required|email|max:255')]
    public string $editEmail = '';

    #[Validate('required|in:super_admin,admin,editor')]
    public string $editRole = 'editor';

    #[Validate('nullable|string|min:8')]
    public string $editPassword = '';

    public function mount(): void
    {
        // Only super_admins can manage users
        $this->authorize('viewAny', User::class);
    }

    public function render()
    {
        $query = User::query()
            ->withCount(['incidents', 'incidentUpdates']);

        // Search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%");
            });
        }

        // Role filter
        if ($this->roleFilter !== 'all') {
            $query->where('role', $this->roleFilter);
        }

        // Sorting
        $query->orderBy($this->sortField, $this->sortDirection);

        $users = $query->paginate(20);

        return view('livewire.settings.user-list', [
            'users' => $users,
            'roles' => UserRole::cases(),
        ]);
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRoleFilter(): void
    {
        $this->resetPage();
    }

    public function updateRole(int $userId, string $role): void
    {
        $user = User::findOrFail($userId);
        
        $this->authorize('update', $user);

        // Prevent users from changing their own role
        if ($user->id === auth()->id()) {
            session()->flash('error', 'You cannot change your own role.');
            return;
        }

        try {
            $user->update(['role' => UserRole::from($role)]);
            
            session()->flash('success', 'User role updated successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update user role: ' . $e->getMessage());
        }
    }

    public function createUser(): void
    {
        $this->authorize('create', User::class);
        
        $this->resetEditForm();
        $this->editingUserId = null;
        $this->showCreateModal = true;
    }

    public function saveNewUser(): void
    {
        $this->authorize('create', User::class);

        // Validate with unique email rule
        $this->validate([
            'editName' => 'required|string|max:255',
            'editEmail' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'editRole' => 'required|in:super_admin,admin,editor',
            'editPassword' => 'required|string|min:8',
        ]);

        try {
            User::create([
                'name' => $this->editName,
                'email' => $this->editEmail,
                'role' => UserRole::from($this->editRole),
                'password' => Hash::make($this->editPassword),
                'email_verified_at' => now(), // Auto-verify admin-created users
            ]);
            
            session()->flash('success', 'User created successfully.');
            
            $this->cancelCreate();
            $this->dispatch('user-created');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to create user: ' . $e->getMessage());
        }
    }

    public function cancelCreate(): void
    {
        $this->showCreateModal = false;
        $this->resetEditForm();
    }

    public function editUser(int $userId): void
    {
        $user = User::findOrFail($userId);
        
        $this->authorize('update', $user);

        $this->editingUserId = $userId;
        $this->editName = $user->name;
        $this->editEmail = $user->email;
        $this->editRole = $user->role->value;
        $this->editPassword = '';
        $this->showEditModal = true;
    }

    public function saveUser(): void
    {
        if (!$this->editingUserId) {
            return;
        }

        $user = User::findOrFail($this->editingUserId);
        
        $this->authorize('update', $user);

        // Validate with unique email rule
        $this->validate([
            'editEmail' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        // Prevent users from changing their own role
        if ($user->id === auth()->id() && $this->editRole !== $user->role->value) {
            session()->flash('error', 'You cannot change your own role.');
            return;
        }

        try {
            $data = [
                'name' => $this->editName,
                'email' => $this->editEmail,
                'role' => UserRole::from($this->editRole),
            ];

            // Update password if provided
            if (!empty($this->editPassword)) {
                $data['password'] = Hash::make($this->editPassword);
            }

            $user->update($data);
            
            session()->flash('success', 'User updated successfully.');
            
            $this->cancelEdit();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update user: ' . $e->getMessage());
        }
    }

    public function cancelEdit(): void
    {
        $this->editingUserId = null;
        $this->showEditModal = false;
        $this->resetEditForm();
    }

    protected function resetEditForm(): void
    {
        $this->editName = '';
        $this->editEmail = '';
        $this->editRole = 'editor';
        $this->editPassword = '';
        $this->resetValidation();
    }

    public function confirmDelete(int $userId): void
    {
        $user = User::findOrFail($userId);
        
        // Prevent users from deleting themselves
        if ($user->id === auth()->id()) {
            session()->flash('error', 'You cannot delete your own account.');
            return;
        }

        $this->userToDelete = $userId;
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->userToDelete = null;
        $this->showDeleteModal = false;
    }

    public function deleteUser(): void
    {
        if (!$this->userToDelete) {
            return;
        }

        $user = User::findOrFail($this->userToDelete);
        
        $this->authorize('delete', $user);

        // Prevent users from deleting themselves
        if ($user->id === auth()->id()) {
            session()->flash('error', 'You cannot delete your own account.');
            $this->cancelDelete();
            return;
        }

        try {
            $user->delete();
            
            session()->flash('success', 'User deleted successfully.');
            
            $this->cancelDelete();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete user: ' . $e->getMessage());
            $this->cancelDelete();
        }
    }

    #[On('user-created')]
    public function refreshList(): void
    {
        // Livewire will automatically re-render
    }
}
