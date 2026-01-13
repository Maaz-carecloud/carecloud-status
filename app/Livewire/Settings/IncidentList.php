<?php

namespace App\Livewire\Settings;

use App\Enums\IncidentImpact;
use App\Enums\IncidentStatus;
use App\Models\Incident;
use App\Services\IncidentService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Incidents')]
class IncidentList extends Component
{
    use WithPagination;
    use AuthorizesRequests;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $statusFilter = 'all';

    #[Url]
    public string $impactFilter = 'all';

    #[Url]
    public string $typeFilter = 'all'; // all, scheduled, unscheduled

    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    public ?int $incidentToDelete = null;
    public bool $showDeleteModal = false;

    public function mount(): void
    {
        $this->authorize('viewAny', Incident::class);
    }

    public function render()
    {
        $query = Incident::query()
            ->with(['user', 'components', 'updates'])
            ->withCount('components');

        // Search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('message', 'like', "%{$this->search}%");
            });
        }

        // Status filter
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        // Impact filter
        if ($this->impactFilter !== 'all') {
            $query->where('impact', $this->impactFilter);
        }

        // Type filter (scheduled vs unscheduled)
        if ($this->typeFilter === 'scheduled') {
            $query->where('is_scheduled', true);
        } elseif ($this->typeFilter === 'unscheduled') {
            $query->where('is_scheduled', false);
        }

        // Sorting
        $query->orderBy($this->sortField, $this->sortDirection);

        $incidents = $query->paginate(15);

        return view('livewire.settings.incident-list', [
            'incidents' => $incidents,
            'statuses' => IncidentStatus::cases(),
            'impacts' => IncidentImpact::cases(),
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

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingImpactFilter(): void
    {
        $this->resetPage();
    }

    public function updatingTypeFilter(): void
    {
        $this->resetPage();
    }

    public function resolveIncident(int $incidentId, IncidentService $service): void
    {
        $incident = Incident::findOrFail($incidentId);
        
        $this->authorize('update', $incident);

        try {
            $service->resolveIncident(
                $incident, 
                'Incident has been resolved.',
                auth()->user()
            );
            
            session()->flash('success', 'Incident resolved successfully.');
            
            $this->dispatch('incident-updated');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to resolve incident: ' . $e->getMessage());
        }
    }

    public function confirmDelete(int $incidentId): void
    {
        $this->incidentToDelete = $incidentId;
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->incidentToDelete = null;
        $this->showDeleteModal = false;
    }

    public function deleteIncident(IncidentService $service): void
    {
        if (!$this->incidentToDelete) {
            return;
        }

        $incident = Incident::findOrFail($this->incidentToDelete);
        
        $this->authorize('delete', $incident);

        try {
            $service->deleteIncident($incident);
            
            session()->flash('success', 'Incident deleted successfully.');
            
            $this->dispatch('incident-deleted');
            
            $this->cancelDelete();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete incident: ' . $e->getMessage());
            $this->cancelDelete();
        }
    }

    #[On('incident-created')]
    #[On('incident-updated')]
    #[On('incident-deleted')]
    public function refreshList(): void
    {
        // Livewire will automatically re-render
    }
}
