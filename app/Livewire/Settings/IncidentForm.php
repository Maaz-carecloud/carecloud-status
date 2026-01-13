<?php

namespace App\Livewire\Settings;

use App\Enums\IncidentImpact;
use App\Enums\IncidentStatus;
use App\Models\Component;
use App\Models\Incident;
use App\Services\IncidentService;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component as LivewireComponent;

#[Title('Incident Form')]
class IncidentForm extends LivewireComponent
{
    use AuthorizesRequests;

    public ?int $incidentId = null;
    public ?Incident $incident = null;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|string')]
    public string $message = '';

    #[Validate('required|in:investigating,identified,monitoring,resolved')]
    public string $status = 'investigating';

    #[Validate('required|in:minor,major,critical')]
    public string $impact = 'minor';

    #[Validate('boolean')]
    public bool $isScheduled = false;

    #[Validate('nullable|date|after:now')]
    public ?string $scheduledAt = null;

    #[Validate(['required', 'array', 'min:1', 'affectedComponents.*' => 'exists:components,id'])]
    public array $affectedComponents = [];

    // Store status for each component
    public array $componentStatuses = [];

    public function mount(?int $incidentId = null): void
    {
        if ($incidentId) {
            $this->incidentId = $incidentId;
            $this->loadIncident();
            $this->authorize('update', $this->incident);
        } else {
            $this->authorize('create', Incident::class);
        }
    }

    protected function loadIncident(): void
    {
        $this->incident = Incident::with('components')->findOrFail($this->incidentId);
        
        $this->name = $this->incident->name;
        $this->message = $this->incident->message;
        $this->status = $this->incident->status->value;
        $this->impact = $this->incident->impact->value;
        $this->isScheduled = $this->incident->is_scheduled;
        $this->scheduledAt = $this->incident->scheduled_at?->timezone('America/New_York')->format('Y-m-d\TH:i');
        $this->affectedComponents = $this->incident->components->pluck('id')->toArray();
        
        // Load component statuses from pivot table
        foreach ($this->incident->components as $component) {
            $this->componentStatuses[$component->id] = $component->pivot->status ?? $component->status->value;
        }
    }

    public function render()
    {
        $components = Component::enabled()->ordered()->get();
        
        return view('livewire.settings.incident-form', [
            'components' => $components,
            'statuses' => \App\Enums\ComponentStatus::cases(),
            'incidentStatuses' => IncidentStatus::cases(),
            'impacts' => IncidentImpact::cases(),
        ]);
    }

    public function updatedIsScheduled(): void
    {
        if (!$this->isScheduled) {
            $this->scheduledAt = null;
        }
    }

    public function updatedAffectedComponents(): void
    {
        // Initialize status for newly added components
        foreach ($this->affectedComponents as $componentId) {
            if (!isset($this->componentStatuses[$componentId])) {
                $component = Component::find($componentId);
                $this->componentStatuses[$componentId] = $component ? $component->status->value : 'degraded_performance';
            }
        }
        
        // Remove statuses for deselected components
        $this->componentStatuses = array_intersect_key(
            $this->componentStatuses,
            array_flip($this->affectedComponents)
        );
    }

    public function save(IncidentService $service)
    {
        $this->validate();

        // Additional validation for scheduled incidents
        if ($this->isScheduled && !$this->scheduledAt) {
            $this->addError('scheduledAt', 'Scheduled date and time is required for scheduled maintenance.');
            return;
        }

        try {
            if ($this->incidentId) {
                // Update existing incident
                $this->authorize('update', $this->incident);

                $oldStatus = $this->incident->status;
                $newStatus = IncidentStatus::from($this->status);

                $this->incident->update([
                    'name' => $this->name,
                    'message' => $this->message,
                    'status' => $newStatus,
                    'impact' => IncidentImpact::from($this->impact),
                    'is_scheduled' => $this->isScheduled,
                    'scheduled_at' => $this->scheduledAt ? Carbon::parse($this->scheduledAt) : null,
                ]);

                // Update affected components
                $service->updateAffectedComponents($this->incident, $this->affectedComponents, $this->componentStatuses);

                // If status changed, create an incident update and notify subscribers
                if ($oldStatus !== $newStatus) {
                    $service->addIncidentUpdate(
                        $this->incident->fresh(),
                        [
                            'status' => $newStatus,
                            'message' => "Status changed from {$oldStatus->label()} to {$newStatus->label()}.",
                        ],
                        auth()->user()
                    );
                }

                session()->flash('success', 'Incident updated successfully.');
                
                $this->dispatch('incident-updated');
            } else {
                // Create new incident
                $this->authorize('create', Incident::class);

                $data = [
                    'name' => $this->name,
                    'message' => $this->message,
                    'status' => IncidentStatus::from($this->status),
                    'impact' => IncidentImpact::from($this->impact),
                    'component_ids' => $this->affectedComponents,
                    'component_statuses' => $this->componentStatuses,
                ];

                if ($this->isScheduled) {
                    $data['scheduled_at'] = $this->scheduledAt;
                    $incident = $service->createScheduledMaintenance($data, auth()->user());
                } else {
                    $incident = $service->createIncident($data, auth()->user());
                }

                session()->flash('success', 'Incident created successfully.');
                
                $this->dispatch('incident-created');
                
                return redirect()->route('incidents.edit', $incident);
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to save incident: ' . $e->getMessage());
        }
    }

    public function cancel(): void
    {
        redirect()->route('incidents.index');
    }

    public function delete(IncidentService $service): void
    {
        if (!$this->incidentId) {
            return;
        }

        $this->authorize('delete', $this->incident);

        try {
            $service->deleteIncident($this->incident);
            
            session()->flash('success', 'Incident deleted successfully.');
            
            $this->dispatch('incident-deleted');
            
            redirect()->route('incidents.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete incident: ' . $e->getMessage());
        }
    }

    public function toggleAllComponents(): void
    {
        $allComponentIds = Component::enabled()->pluck('id')->toArray();
        
        if (count($this->affectedComponents) === count($allComponentIds)) {
            $this->affectedComponents = [];
        } else {
            $this->affectedComponents = $allComponentIds;
        }
    }
}
