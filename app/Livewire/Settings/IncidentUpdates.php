<?php

namespace App\Livewire\Settings;

use App\Enums\IncidentStatus;
use App\Models\Incident;
use App\Models\IncidentUpdate;
use App\Services\IncidentService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Title('Incident Updates')]
class IncidentUpdates extends Component
{
    use AuthorizesRequests;

    public int $incidentId;
    public Incident $incident;

    #[Validate('required|string|min:10')]
    public string $message = '';

    #[Validate('required|in:investigating,identified,monitoring,resolved')]
    public string $status = '';

    public bool $showForm = false;

    public function mount(int $incidentId): void
    {
        $this->incidentId = $incidentId;
        $this->incident = Incident::with(['updates.user', 'components'])->findOrFail($incidentId);
        
        // Set current status as default
        $this->status = $this->incident->status->value;
        
        $this->authorize('view', $this->incident);
    }

    public function render()
    {
        // Refresh incident with updates
        $this->incident->load(['updates' => function ($query) {
            $query->with('user')->latest();
        }]);

        return view('livewire.settings.incident-updates', [
            'updates' => $this->incident->updates,
            'statuses' => IncidentStatus::cases(),
        ]);
    }

    public function toggleForm(): void
    {
        $this->authorize('update', $this->incident);
        
        $this->showForm = !$this->showForm;
        
        if (!$this->showForm) {
            $this->resetForm();
        }
    }

    public function addUpdate(IncidentService $service): void
    {
        $this->authorize('update', $this->incident);

        $this->validate();

        try {
            $service->addIncidentUpdate(
                $this->incident,
                [
                    'status' => $this->status,
                    'message' => $this->message,
                ],
                auth()->user()
            );

            session()->flash('success', 'Update posted successfully.');
            
            $this->dispatch('incident-updated');
            
            $this->resetForm();
            $this->showForm = false;
            
            // Refresh the incident
            $this->incident->refresh();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to post update: ' . $e->getMessage());
        }
    }

    public function cancelUpdate(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    protected function resetForm(): void
    {
        $this->message = '';
        $this->status = $this->incident->fresh()->status->value;
        $this->resetValidation();
    }

    public function deleteUpdate(int $updateId): void
    {
        $update = IncidentUpdate::findOrFail($updateId);
        
        $this->authorize('update', $this->incident);

        // Prevent deletion of the initial update
        if ($update->incident_id !== $this->incident->id) {
            session()->flash('error', 'Invalid update.');
            return;
        }

        // Don't allow deleting if it's the only update
        if ($this->incident->updates()->count() <= 1) {
            session()->flash('error', 'Cannot delete the only update for this incident.');
            return;
        }

        try {
            $update->delete();
            
            session()->flash('success', 'Update deleted successfully.');
            
            $this->incident->refresh();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete update: ' . $e->getMessage());
        }
    }

    #[On('incident-updated')]
    public function refreshIncident(): void
    {
        $this->incident->refresh();
    }
}
