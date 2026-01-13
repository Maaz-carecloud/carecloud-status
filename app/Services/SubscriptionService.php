<?php

namespace App\Services;

use App\Models\Component;
use App\Models\Incident;
use App\Models\Subscriber;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * SubscriptionService
 * 
 * Manages subscriber registration, verification, preferences, and unsubscription.
 * Handles email verification flow and component subscription management.
 * 
 * Workflow:
 * 1. User subscribes with email and component preferences
 * 2. Generate verification token and send email
 * 3. User clicks verification link
 * 4. Activate subscription
 * 5. Send notifications for subscribed components
 * 6. Allow preference updates and unsubscription
 */
class SubscriptionService
{
    public function __construct(
        protected NotificationService $notificationService
    ) {
    }
    /**
     * Create a new subscriber with component subscriptions.
     * 
     * Workflow:
     * - Validate email is unique
     * - Create subscriber record
     * - Generate verification token
     * - Attach selected components
     * - Send verification email
     * - Return subscriber
     * 
     * @param string $email The subscriber's email
     * @param array $componentIds Array of component IDs to subscribe to
     * @param string|null $phone Optional phone number for SMS notifications
     * @param string|null $teamsWebhookUrl Optional Teams webhook URL
     * @return Subscriber
     */
    public function subscribe(string $email, array $componentIds = [], ?string $phone = null, ?string $teamsWebhookUrl = null): Subscriber
    {
        return DB::transaction(function () use ($email, $componentIds, $phone, $teamsWebhookUrl) {
            // Check if email already exists
            $existing = Subscriber::where('email', $email)->first();
            
            if ($existing) {
                // If exists but inactive, reactivate
                if (!$existing->is_active) {
                    $existing->update([
                        'is_active' => true,
                        'verification_token' => $this->generateVerificationToken(),
                        'phone' => $phone,
                        'teams_webhook_url' => $teamsWebhookUrl,
                    ]);
                    
                    // Update component subscriptions
                    if (!empty($componentIds)) {
                        $existing->components()->sync($componentIds);
                    }
                    
                    // Resend verification if not verified
                    if (!$existing->isVerified()) {
                        $this->notificationService->sendVerificationEmail($existing);
                    }
                    
                    return $existing;
                }
                
                throw new \InvalidArgumentException('Email is already subscribed.');
            }

            // Create new subscriber
            $subscriber = Subscriber::create([
                'email' => $email,
                'phone' => $phone,
                'teams_webhook_url' => $teamsWebhookUrl,
                'verification_token' => $this->generateVerificationToken(),
                'is_active' => true,
            ]);

            // Attach selected components
            if (!empty($componentIds)) {
                $subscriber->components()->attach($componentIds);
            }

            // Send verification email
            $this->notificationService->sendVerificationEmail($subscriber);

            return $subscriber->fresh(['components']);
        });
    }

    /**
     * Verify a subscriber's email address.
     * 
     * Workflow:
     * - Find subscriber by token
     * - Validate token hasn't expired
     * - Mark subscriber as verified
     * - Clear verification token
     * - Send welcome email
     * 
     * @param string $token The verification token
     * @return Subscriber|null
     */
    public function verifySubscriber(string $token): ?Subscriber
    {
        return DB::transaction(function () use ($token) {
            $subscriber = Subscriber::where('verification_token', $token)
                ->whereNull('verified_at')
                ->first();

            if (!$subscriber) {
                return null;
            }

            // Mark as verified
            $subscriber->update([
                'verified_at' => now(),
                'verification_token' => null,
            ]);

            // Send welcome email
            $this->notificationService->sendWelcomeEmail($subscriber);

            return $subscriber;
        });
    }

    /**
     * Resend verification email to subscriber.
     * 
     * Used when user didn't receive or lost original verification email.
     * 
     * @param string $email The subscriber's email
     * @return bool True if email was sent
     */
    public function resendVerification(string $email): bool
    {
        $subscriber = Subscriber::where('email', $email)
            ->whereNull('verified_at')
            ->where('is_active', true)
            ->first();

        if (!$subscriber) {
            return false;
        }

        // Generate new token if expired or missing
        if (!$subscriber->verification_token) {
            $subscriber->update([
                'verification_token' => $this->generateVerificationToken(),
            ]);
        }

        // Resend verification email
        return $this->notificationService->sendVerificationEmail($subscriber->fresh());
    }

    /**
     * Update subscriber's component preferences.
     * 
     * Workflow:
     * - Validate subscriber is verified
     * - Sync component subscriptions
     * - Send confirmation email
     * 
     * @param Subscriber $subscriber The subscriber
     * @param array $componentIds Array of component IDs to subscribe to
     * @return void
     */
    public function updatePreferences(Subscriber $subscriber, array $componentIds): void
    {
        DB::transaction(function () use ($subscriber, $componentIds) {
            // Sync component subscriptions
            $subscriber->components()->sync($componentIds);

            // Send confirmation email
            $this->notificationService->sendPreferenceUpdateConfirmation($subscriber->fresh());
        });
    }

    /**
     * Enable a subscriber.
     * 
     * @param Subscriber $subscriber The subscriber to enable
     * @return void
     */
    public function enableSubscriber(Subscriber $subscriber): void
    {
        $subscriber->update(['is_active' => true]);
    }

    /**
     * Disable a subscriber.
     * 
     * @param Subscriber $subscriber The subscriber to disable
     * @return void
     */
    public function disableSubscriber(Subscriber $subscriber): void
    {
        $subscriber->update(['is_active' => false]);
    }

    /**
     * Attach components to a subscriber.
     * 
     * @param Subscriber $subscriber The subscriber
     * @param array $componentIds Array of component IDs
     * @return void
     */
    public function attachComponents(Subscriber $subscriber, array $componentIds): void
    {
        $subscriber->components()->syncWithoutDetaching($componentIds);
    }

    /**
     * Detach components from a subscriber.
     * 
     * @param Subscriber $subscriber The subscriber
     * @param array $componentIds Array of component IDs
     * @return void
     */
    public function detachComponents(Subscriber $subscriber, array $componentIds): void
    {
        $subscriber->components()->detach($componentIds);
    }

    /**
     * Unsubscribe a subscriber.
     * 
     * Workflow:
     * - Mark subscriber as inactive
     * - Detach all component subscriptions
     * - Send unsubscribe confirmation email
     * - Keep record for compliance/resubscription
     * 
     * @param string $email The subscriber's email
     * @return bool True if unsubscribed successfully
     */
    public function unsubscribe(string $email): bool
    {
        return DB::transaction(function () use ($email) {
            $subscriber = Subscriber::where('email', $email)->first();

            if (!$subscriber) {
                return false;
            }

            // Mark as inactive
            $subscriber->update(['is_active' => false]);

            // Detach all components
            $subscriber->components()->detach();

            // Send confirmation
            $this->notificationService->sendUnsubscribeConfirmation($email);

            return true;
        });
    }

    /**
     * Unsubscribe using token (one-click unsubscribe).
     * 
     * Used for email footer unsubscribe links.
     * 
     * @param string $token The unsubscribe token
     * @return bool True if unsubscribed successfully
     */
    public function unsubscribeByToken(string $token): bool
    {
        return DB::transaction(function () use ($token) {
            // Use email as token for simplicity (could be enhanced with dedicated unsubscribe tokens)
            $subscriber = Subscriber::where('email', $token)->first();

            if (!$subscriber) {
                return false;
            }

            // Mark as inactive
            $subscriber->update(['is_active' => false]);

            // Detach all components
            $subscriber->components()->detach();

            return true;
        });
    }

    /**
     * Get subscribers for specific components.
     * 
     * Returns only verified and active subscribers.
     * Used for targeted notifications.
     * 
     * @param array $componentIds Array of component IDs
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSubscribersForComponents(array $componentIds)
    {
        return Subscriber::verified()
            ->active()
            ->whereHas('components', function ($query) use ($componentIds) {
                $query->whereIn('components.id', $componentIds);
            })
            ->with('components')
            ->get()
            ->unique('id');
    }

    /**
     * Get subscribers for a given incident.
     * 
     * Returns subscribers subscribed to any of the incident's affected components.
     * 
     * @param Incident $incident The incident
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSubscribersForIncident(Incident $incident)
    {
        $componentIds = $incident->components->pluck('id')->toArray();
        
        if (empty($componentIds)) {
            return collect();
        }
        
        return $this->getSubscribersForComponents($componentIds);
    }

    /**
     * Get all active subscribers.
     * 
     * Used for system-wide announcements.
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllActiveSubscribers()
    {
        return Subscriber::verified()
            ->active()
            ->with('components')
            ->get();
    }

    /**
     * Check if an email is already subscribed.
     * 
     * @param string $email The email to check
     * @return bool
     */
    public function isSubscribed(string $email): bool
    {
        return Subscriber::where('email', $email)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Generate a unique verification token.
     * 
     * @return string
     */
    protected function generateVerificationToken(): string
    {
        return Str::random(64);
    }

    /**
     * Get subscription statistics.
     * 
     * Returns counts of total, verified, and active subscribers.
     * Used for admin dashboard.
     * 
     * @return array
     */
    public function getSubscriptionStats(): array
    {
        $total = Subscriber::count();
        $verified = Subscriber::verified()->count();
        $active = Subscriber::active()->count();
        $unverified = Subscriber::whereNull('verified_at')->count();

        return [
            'total' => $total,
            'verified' => $verified,
            'active' => $active,
            'unverified' => $unverified,
        ];
    }

    /**
     * Clean up unverified subscribers older than specified days.
     * 
     * Removes subscribers who never verified their email.
     * Run as scheduled task.
     * 
     * @param int $days Number of days (default 30)
     * @return int Number of subscribers deleted
     */
    public function cleanupUnverifiedSubscribers(int $days = 30): int
    {
        $cutoffDate = Carbon::now()->subDays($days);

        return Subscriber::whereNull('verified_at')
            ->where('created_at', '<', $cutoffDate)
            ->delete();
    }
}
