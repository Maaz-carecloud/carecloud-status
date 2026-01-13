<?php

namespace App\Livewire\Settings;

use App\Enums\SubscriberType;
use App\Models\Subscriber;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Subscribers')]
class SubscriberList extends Component
{
    use WithPagination;
    use AuthorizesRequests;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $channelFilter = 'all'; // all, email, sms, teams

    #[Url]
    public string $statusFilter = 'all'; // all, verified, unverified, active, inactive

    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    public ?int $subscriberToDelete = null;
    public bool $showDeleteModal = false;

    public function mount(): void
    {
        $this->authorize('viewAny', Subscriber::class);
    }

    public function render()
    {
        $query = Subscriber::query()->withCount('components');

        // Search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('email', 'like', "%{$this->search}%")
                  ->orWhere('phone', 'like', "%{$this->search}%")
                  ->orWhere('teams_webhook_url', 'like', "%{$this->search}%");
            });
        }

        // Channel filter
        if ($this->channelFilter !== 'all') {
            match ($this->channelFilter) {
                'email' => $query->whereNotNull('email'),
                'sms' => $query->whereNotNull('phone'),
                'teams' => $query->whereNotNull('teams_webhook_url'),
            };
        }

        // Status filter
        match ($this->statusFilter) {
            'verified' => $query->verified(),
            'unverified' => $query->whereNull('verified_at'),
            'active' => $query->active(),
            'inactive' => $query->where('is_active', false),
            default => null,
        };

        // Sorting
        $query->orderBy($this->sortField, $this->sortDirection);

        $subscribers = $query->paginate(20);

        return view('livewire.settings.subscriber-list', [
            'subscribers' => $subscribers,
            'channels' => SubscriberType::cases(),
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

    public function updatingChannelFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function toggleActive(int $subscriberId, SubscriptionService $service): void
    {
        $subscriber = Subscriber::findOrFail($subscriberId);
        
        $this->authorize('update', $subscriber);

        try {
            if ($subscriber->is_active) {
                $service->disableSubscriber($subscriber);
                session()->flash('success', 'Subscriber disabled successfully.');
            } else {
                $service->enableSubscriber($subscriber);
                session()->flash('success', 'Subscriber enabled successfully.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update subscriber: ' . $e->getMessage());
        }
    }

    public function resendVerification(int $subscriberId, SubscriptionService $service): void
    {
        $subscriber = Subscriber::findOrFail($subscriberId);
        
        $this->authorize('update', $subscriber);

        if ($subscriber->isVerified()) {
            session()->flash('error', 'Subscriber is already verified.');
            return;
        }

        try {
            $service->resendVerification($subscriber);
            session()->flash('success', 'Verification email sent successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to resend verification: ' . $e->getMessage());
        }
    }

    public function verifyManually(int $subscriberId, SubscriptionService $service): void
    {
        $subscriber = Subscriber::findOrFail($subscriberId);
        
        $this->authorize('update', $subscriber);

        if ($subscriber->isVerified()) {
            session()->flash('error', 'Subscriber is already verified.');
            return;
        }

        try {
            $service->verifySubscriber($subscriber->verification_token);
            session()->flash('success', 'Subscriber verified manually.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to verify subscriber: ' . $e->getMessage());
        }
    }

    public function confirmDelete(int $subscriberId): void
    {
        $this->subscriberToDelete = $subscriberId;
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->subscriberToDelete = null;
        $this->showDeleteModal = false;
    }

    public function deleteSubscriber(): void
    {
        if (!$this->subscriberToDelete) {
            return;
        }

        $subscriber = Subscriber::findOrFail($this->subscriberToDelete);
        
        $this->authorize('delete', $subscriber);

        try {
            // Detach all components
            $subscriber->components()->detach();
            
            // Delete subscriber
            $subscriber->delete();
            
            session()->flash('success', 'Subscriber deleted successfully.');
            
            $this->cancelDelete();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete subscriber: ' . $e->getMessage());
            $this->cancelDelete();
        }
    }

    #[On('subscriber-created')]
    #[On('subscriber-updated')]
    public function refreshList(): void
    {
        // Livewire will automatically re-render
    }

    public function getChannels(Subscriber $subscriber): array
    {
        $channels = [];
        
        if ($subscriber->email) {
            $channels[] = 'Email';
        }
        
        if ($subscriber->phone) {
            $channels[] = 'SMS';
        }
        
        if ($subscriber->teams_webhook_url) {
            $channels[] = 'Teams';
        }
        
        return $channels;
    }
}
