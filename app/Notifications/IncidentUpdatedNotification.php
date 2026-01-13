<?php

namespace App\Notifications;

use App\Models\IncidentUpdate;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IncidentUpdatedNotification extends Notification
{

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public IncidentUpdate $update
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
        $channels = [];

        // Add email channel by default (always send emails)
        $channels[] = 'mail';

        // Add SMS channel if subscriber has phone number and channel exists
        if (!empty($notifiable->phone) && class_exists('App\Notifications\Channels\SmsChannel')) {
            $channels[] = 'sms';
        }

        // Add Teams channel if subscriber has Teams webhook and channel exists
        if (!empty($notifiable->teams_webhook_url) && class_exists('App\Notifications\Channels\TeamsChannel')) {
            $channels[] = 'teams';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $incident = $this->update->incident;
        $componentNames = $incident->components->pluck('name')->join(', ');
        
        return (new MailMessage)
            ->subject("[Update] {$incident->name} - {$this->update->status->label()}")
            ->greeting("Incident Update")
            ->line("**{$incident->name}**")
            ->line("New Status: {$this->update->status->label()}")
            ->when($componentNames, function ($mail) use ($componentNames) {
                return $mail->line("Affected Components: {$componentNames}");
            })
            ->line($this->update->message)
            ->action('View Status Page', url('/'))
            ->line("Posted by: {$this->update->user->name}")
            ->line("Time: {$this->update->created_at->timezone('America/New_York')->format('M d, Y H:i T')}");
    }

    /**
     * Get the SMS representation of the notification (stub).
     */
    public function toSms(object $notifiable): string
    {
        $incident = $this->update->incident;
        
        // SMS stub - implement with provider like Twilio or SNS
        return "[Update] {$incident->name} - {$this->update->status->label()}: {$this->update->message}";
    }

    /**
     * Get the Teams representation of the notification.
     */
    public function toTeams(object $notifiable): array
    {
        $incident = $this->update->incident;
        $componentNames = $incident->components->pluck('name')->join(', ');
        
        return [
            '@type' => 'MessageCard',
            '@context' => 'https://schema.org/extensions',
            'summary' => "Incident Update: {$incident->name}",
            'themeColor' => $this->update->status->color(),
            'title' => "📢 Incident Update",
            'sections' => [
                [
                    'activityTitle' => $incident->name,
                    'activitySubtitle' => "Status: {$this->update->status->label()}",
                    'facts' => array_filter([
                        ['name' => 'Status', 'value' => $this->update->status->label()],
                        $componentNames ? ['name' => 'Affected Components', 'value' => $componentNames] : null,
                        ['name' => 'Update', 'value' => $this->update->message],
                        ['name' => 'Posted By', 'value' => $this->update->user->name],
                        ['name' => 'Time', 'value' => $this->update->created_at->timezone('America/New_York')->format('M d, Y H:i T')],
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
            'incident_id' => $this->update->incident_id,
            'update_id' => $this->update->id,
            'status' => $this->update->status->value,
            'message' => $this->update->message,
        ];
    }
}
