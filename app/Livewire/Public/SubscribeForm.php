<?php

namespace App\Livewire\Public;

use App\Models\Component as ComponentModel;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Title('Subscribe to Updates')]
#[Layout('components.layouts.public')]
class SubscribeForm extends Component
{
    public $activeTab = 'email'; // email, sms, teams

    #[Validate('required|email')]
    public $email = '';

    #[Validate('nullable|string')]
    public $phone = '';

    #[Validate('nullable|url')]
    public $teamsWebhookUrl = '';

    #[Validate('array')]
    public $selectedComponents = [];

    public $showSuccess = false;
    public $showError = false;
    public $errorMessage = '';

    public function mount()
    {
        // Automatically select all components by default
        $this->selectedComponents = ComponentModel::enabled()
            ->pluck('id')
            ->toArray();
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        // Cache components list for 5 minutes
        $components = Cache::remember('subscribe_form_components', 300, function () {
            return ComponentModel::enabled()
                ->ordered()
                ->get(['id', 'name', 'description', 'status']);
        });

        return view('livewire.public.subscribe-form', [
            'components' => $components,
        ]);
    }

    /**
     * Subscribe the user to notifications.
     */
    public function subscribe(SubscriptionService $subscriptionService): void
    {
        $this->validate();

        // Reset messages
        $this->showSuccess = false;
        $this->showError = false;
        $this->errorMessage = '';

        try {
            // Subscribe with selected options
            $subscriber = $subscriptionService->subscribe(
                $this->email,
                $this->selectedComponents,
                $this->phone ?: null,
                $this->teamsWebhookUrl ?: null
            );

            // Show success message
            $this->showSuccess = true;

            // Clear form (except component selection)
            $this->email = '';
            $this->phone = '';
            $this->teamsWebhookUrl = '';

            // Dispatch browser event for analytics/tracking
            $this->dispatch('subscription-created', ['email' => $subscriber->email]);

        } catch (\InvalidArgumentException $e) {
            // Handle duplicate subscription
            $this->showError = true;
            $this->errorMessage = $e->getMessage();
        } catch (\Exception $e) {
            // Handle unexpected errors
            $this->showError = true;
            $this->errorMessage = 'An error occurred while processing your subscription. Please try again.';
            
            // Log the error for debugging
            Log::error('Subscription error', [
                'email' => $this->email,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Dismiss success message.
     */
    public function dismissSuccess(): void
    {
        $this->showSuccess = false;
    }

    /**
     * Dismiss error message.
     */
    public function dismissError(): void
    {
        $this->showError = false;
        $this->errorMessage = '';
    }
}
