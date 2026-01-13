<?php

namespace App\Livewire\Settings;

use App\Enums\ComponentStatus;
use App\Models\Component;
use App\Services\ComponentStatusService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component as LivewireComponent;
use Livewire\WithPagination;

#[Title('Component Management')]
class ComponentList extends LivewireComponent
{
    use WithPagination, AuthorizesRequests;

    public $search = '';
    public $statusFilter = 'all';
    public $showDeleteModal = false;
    public $componentToDelete = null;
    public $sortField = 'order';
    public $sortDirection = 'asc';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
    ];

    public function mount()
    {
        // Check if user has permission to manage components
        $this->authorize('viewAny', Component::class);
    }

    public function render()
    {
        $query = Component::query()
            ->with(['incidents' => function ($query) {
                $query->active()->limit(3);
            }]);

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        // Apply status filter
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        // Apply sorting
        $query->orderBy($this->sortField, $this->sortDirection);

        $components = $query->paginate(15);

        return view('livewire.settings.component-list', [
            'components' => $components,
            'statuses' => ComponentStatus::cases(),
        ]);
    }

    /**
     * Sort by field.
     */
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

    /**
     * Move component up in order.
     */
    public function moveUp(int $componentId): void
    {
        $component = Component::findOrFail($componentId);
        
        $this->authorize('update', $component);

        // Find the previous component
        $previousComponent = Component::where('order', '<', $component->order)
            ->orderBy('order', 'desc')
            ->first();

        if ($previousComponent) {
            // Swap orders
            $tempOrder = $component->order;
            $component->update(['order' => $previousComponent->order]);
            $previousComponent->update(['order' => $tempOrder]);

            $this->dispatch('component-updated');
        }
    }

    /**
     * Move component down in order.
     */
    public function moveDown(int $componentId): void
    {
        $component = Component::findOrFail($componentId);
        
        $this->authorize('update', $component);

        // Find the next component
        $nextComponent = Component::where('order', '>', $component->order)
            ->orderBy('order', 'asc')
            ->first();

        if ($nextComponent) {
            // Swap orders
            $tempOrder = $component->order;
            $component->update(['order' => $nextComponent->order]);
            $nextComponent->update(['order' => $tempOrder]);

            $this->dispatch('component-updated');
        }
    }

    /**
     * Toggle component enabled status.
     */
    public function toggleEnabled(int $componentId): void
    {
        $component = Component::findOrFail($componentId);
        
        $this->authorize('update', $component);

        $component->update(['is_enabled' => !$component->is_enabled]);

        $this->dispatch('component-updated');
        session()->flash('success', 'Component ' . ($component->is_enabled ? 'enabled' : 'disabled') . ' successfully.');
    }

    /**
     * Confirm component deletion.
     */
    public function confirmDelete(int $componentId): void
    {
        $component = Component::findOrFail($componentId);
        
        $this->authorize('delete', $component);

        $this->componentToDelete = $component;
        $this->showDeleteModal = true;
    }

    /**
     * Delete component.
     */
    public function deleteComponent(): void
    {
        if (!$this->componentToDelete) {
            return;
        }

        $this->authorize('delete', $this->componentToDelete);

        // Detach all relationships
        $this->componentToDelete->incidents()->detach();
        $this->componentToDelete->subscribers()->detach();
        $this->componentToDelete->statusLogs()->delete();

        // Delete component
        $this->componentToDelete->delete();

        $this->showDeleteModal = false;
        $this->componentToDelete = null;

        $this->dispatch('component-deleted');
        session()->flash('success', 'Component deleted successfully.');
    }

    /**
     * Cancel deletion.
     */
    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->componentToDelete = null;
    }

    /**
     * Update search query.
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Update status filter.
     */
    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    /**
     * Refresh the component list.
     */
    #[On('component-created')]
    #[On('component-updated')]
    #[On('component-deleted')]
    public function refreshList(): void
    {
        // Livewire will automatically re-render
    }
}
