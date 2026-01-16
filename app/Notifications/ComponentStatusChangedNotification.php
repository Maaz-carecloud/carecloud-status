<?php

namespace App\Notifications;

use App\Models\Component;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ComponentStatusChangedNotification extends Notification
{
    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Component $component,
        public string $oldStatus,
        public string $newStatus
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
        $channels = ['mail'];

        // Add SMS channel if subscriber has phone number and channel exists
        if (isset($notifiable->phone) && class_exists('App\Notifications\Channels\SmsChannel')) {
            $channels[] = 'sms';
            \Log::info('Adding SMS channel to ComponentStatusChangedNotification', [
                'phone' => $notifiable->phone,
                'subscriber_id' => $notifiable->id ?? null,
            ]);
        } else {
            \Log::info('SMS channel NOT added', [
                'has_phone' => isset($notifiable->phone),
                'phone' => $notifiable->phone ?? null,
                'class_exists' => class_exists('App\Notifications\Channels\SmsChannel'),
            ]);
        }

        // Add Teams channel if subscriber has Teams webhook and channel exists
        if (isset($notifiable->teams_webhook_url) && class_exists('App\Notifications\Channels\TeamsChannel')) {
            $channels[] = 'teams';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $statusEmoji = match($this->newStatus) {
            'operational' => '✅',
            'degraded_performance' => '⚠️',
            'partial_outage' => '🔶',
            'major_outage' => '🔴',
            'under_maintenance' => '🔧',
            default => 'ℹ️',
        };

        return (new MailMessage)
            ->subject("{$statusEmoji} Component Status Changed: {$this->component->name}")
            ->greeting('Component Status Update')
            ->line("**{$this->component->name}**")
            ->line("Status has changed from **{$this->oldStatus}** to **{$this->newStatus}**.")
            ->when($this->component->description, function ($mail) {
                return $mail->line('<div style="text-align: justify;">' . nl2br($this->component->description) . '</div>');
            })
            ->action('View Status Page', url('/'))
            ->line('We will keep you updated if the status changes again.');
    }

    /**
     * Get the SMS representation of the notification.
     */
    public function toSms(object $notifiable): string
    {
        return "[Status Change] {$this->component->name}: {$this->oldStatus} → {$this->newStatus}.";
    }

    /**
     * Get the Teams representation of the notification.
     */
    public function toTeams(object $notifiable): array
    {
        $statusEmoji = match($this->newStatus) {
            'operational' => '✅',
            'degraded_performance' => '⚠️',
            'partial_outage' => '🔶',
            'major_outage' => '🔴',
            'under_maintenance' => '🔧',
            default => 'ℹ️',
        };

        $themeColor = match($this->newStatus) {
            'operational' => '28a745', // Green
            'degraded_performance' => 'ffc107', // Yellow
            'partial_outage' => 'fd7e14', // Orange
            'major_outage' => 'dc3545', // Red
            'under_maintenance' => '6c757d', // Gray
            default => '17a2b8', // Blue
        };

        return [
            '@type' => 'MessageCard',
            '@context' => 'https://schema.org/extensions',
            'summary' => "Component Status Changed: {$this->component->name}",
            'themeColor' => $themeColor,
            'title' => "{$statusEmoji} Component Status Update",
            'sections' => [
                [
                    'activityTitle' => $this->component->name,
                    'activitySubtitle' => "Status Changed",
                    'facts' => array_filter([
                        ['name' => 'Previous Status', 'value' => $this->oldStatus],
                        ['name' => 'Current Status', 'value' => $this->newStatus],
                        $this->component->description ? ['name' => 'Description', 'value' => $this->component->description] : null,
                    ]),
                ]
            ],
            'potentialAction' => [
                [
                    '@type' => 'OpenUri',
                    'name' => 'View Status Page',
                    'targets' => [
                        ['os' => 'default', 'uri' => url('/')]
                    ]
                ]
            ]
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'component_id' => $this->component->id,
            'component_name' => $this->component->name,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
        ];
    }
}
