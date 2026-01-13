<?php

namespace App\Livewire\Settings;

use App\Enums\ComponentStatus;
use App\Models\Component;
use App\Services\ComponentStatusService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component as LivewireComponent;

#[Title('Component Form')]
class ComponentForm extends LivewireComponent
{
    use AuthorizesRequests;

    public ?Component $component = null;

    #[Validate('required|string|max:255')]
    public $name = '';

    #[Validate('nullable|string|max:1000')]
    public $description = '';

    #[Validate('required')]
    public $status = '';

    #[Validate('required|integer|min:0')]
    public $order = 0;

    #[Validate('boolean')]
    public $is_enabled = true;

    public $isEditMode = false;

    public function mount(?int $componentId = null)
    {
        if ($componentId) {
            $this->component = Component::findOrFail($componentId);
            $this->authorize('update', $this->component);
            $this->isEditMode = true;
            $this->loadComponent();
        } else {
            $this->authorize('create', Component::class);
            $this->status = ComponentStatus::OPERATIONAL->value;
            $this->order = $this->getNextOrder();
        }
    }

    /**
     * Load component data into form.
     */
    protected function loadComponent(): void
    {
        $this->name = $this->component->name;
        $this->description = $this->component->description ?? '';
        $this->status = $this->component->status->value;
        $this->order = $this->component->order;
        $this->is_enabled = $this->component->is_enabled;
    }

    /**
     * Get the next order number.
     */
    protected function getNextOrder(): int
    {
        return Component::max('order') + 1 ?? 0;
    }

    public function render()
    {
        return view('livewire.settings.component-form', [
            'statuses' => ComponentStatus::cases(),
        ]);
    }

    /**
     * Save component (create or update).
     */
    public function save(ComponentStatusService $statusService): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'description' => $this->description ?: null,
            'status' => ComponentStatus::from($this->status),
            'order' => $this->order,
            'is_enabled' => $this->is_enabled,
        ];

        if ($this->isEditMode) {
            $this->authorize('update', $this->component);
            
            $oldStatus = $this->component->status;
            $newStatus = ComponentStatus::from($this->status);
            
            // Update basic component data (not status)
            $this->component->update([
                'name' => $data['name'],
                'description' => $data['description'],
                'order' => $data['order'],
                'is_enabled' => $data['is_enabled'],
            ]);
            
            // Use ComponentStatusService to update status (creates log entry)
            if ($oldStatus !== $newStatus) {
                $statusService->updateStatus(
                    $this->component,
                    $newStatus,
                    auth()->user()
                );
            }
            
            $this->dispatch('component-updated');
            session()->flash('success', 'Component updated successfully.');
        } else {
            $this->authorize('create', Component::class);
            Component::create($data);
            $this->dispatch('component-created');
            session()->flash('success', 'Component created successfully.');
        }

        $this->redirectRoute('components.index');
    }

    /**
     * Cancel and return to list.
     */
    public function cancel(): void
    {
        $this->redirectRoute('components.index');
    }

    /**
     * Delete component (only in edit mode).
     */
    public function delete(): void
    {
        if (!$this->isEditMode || !$this->component) {
            return;
        }

        $this->authorize('delete', $this->component);

        // Detach all relationships
        $this->component->incidents()->detach();
        $this->component->subscribers()->detach();
        $this->component->statusLogs()->delete();

        // Delete component
        $this->component->delete();

        $this->dispatch('component-deleted');
        session()->flash('success', 'Component deleted successfully.');
        $this->redirectRoute('components.index');
    }
}
