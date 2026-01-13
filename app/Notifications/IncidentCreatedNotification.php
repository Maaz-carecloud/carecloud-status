<?php

namespace App\Notifications;

use App\Models\Incident;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IncidentCreatedNotification extends Notification
{

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Incident $incident
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
        $componentNames = $this->incident->components->pluck('name')->join(', ');
        
        return (new MailMessage)
            ->subject("[{$this->incident->impact->label()}] New Incident: {$this->incident->name}")
            ->greeting("New Incident Reported")
            ->line("**{$this->incident->name}**")
            ->line("Impact: {$this->incident->impact->label()}")
            ->line("Status: {$this->incident->status->label()}")
            ->when($componentNames, function ($mail) use ($componentNames) {
                return $mail->line("Affected Components: {$componentNames}");
            })
            ->when($this->incident->message, function ($mail) {
                return $mail->line($this->incident->message);
            })
            ->action('View Status Page', url('/'))
            ->line('We will keep you updated as we investigate this issue.');
    }

    /**
     * Get the SMS representation of the notification (stub).
     */
    public function toSms(object $notifiable): string
    {
        // SMS stub - implement with provider like Twilio or SNS
        return "[{$this->incident->impact->label()}] {$this->incident->name}. Status: {$this->incident->status->label()}. View: " . url('/');
    }

    /**
     * Get the Teams representation of the notification.
     */
    public function toTeams(object $notifiable): array
    {
        $componentNames = $this->incident->components->pluck('name')->join(', ');
        
        return [
            '@type' => 'MessageCard',
            '@context' => 'https://schema.org/extensions',
            'summary' => "New Incident: {$this->incident->name}",
            'themeColor' => $this->incident->impact->color(),
            'title' => "🚨 New Incident Reported",
            'sections' => [
                [
                    'activityTitle' => $this->incident->name,
                    'activitySubtitle' => "Impact: {$this->incident->impact->label()}",
                    'facts' => array_filter([
                        ['name' => 'Status', 'value' => $this->incident->status->label()],
                        $componentNames ? ['name' => 'Affected Components', 'value' => $componentNames] : null,
                        $this->incident->message ? ['name' => 'Details', 'value' => $this->incident->message] : null,
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
            'incident_id' => $this->incident->id,
            'incident_name' => $this->incident->name,
            'status' => $this->incident->status->value,
            'impact' => $this->incident->impact->value,
        ];
    }
}
