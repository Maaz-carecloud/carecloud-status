<?php

namespace App\Notifications;

use App\Models\Subscriber;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriberVerificationNotification extends Notification
{
    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Subscriber $subscriber
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $verificationUrl = route('subscription.verify', [
            'token' => $this->subscriber->verification_token
        ]);

        $componentNames = $this->subscriber->components->pluck('name')->join(', ');

        return (new MailMessage)
            ->subject('Verify Your Subscription - ' . config('app.name'))
            ->greeting('Welcome!')
            ->line('Thank you for subscribing to status updates from ' . config('app.name') . '.')
            ->line('You have subscribed to receive notifications from CareCloud Status.')
            ->line('Please click the button below to verify your email address and activate your subscription.')
            ->action('Verify Email Address', $verificationUrl)
            ->line('This verification link will expire in 24 hours.')
            ->line('If you did not subscribe, no further action is required.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'subscriber_id' => $this->subscriber->id,
            'email' => $this->subscriber->email,
        ];
    }
}
