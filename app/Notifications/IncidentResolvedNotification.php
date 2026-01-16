<?php

namespace App\Notifications;

use App\Models\Incident;
use Illuminate\Support\HtmlString;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IncidentResolvedNotification extends Notification
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
        $duration = $this->incident->started_at && $this->incident->resolved_at
            ? $this->incident->started_at->diffForHumans($this->incident->resolved_at, true)
            : 'Unknown';
        
        $latestUpdate = $this->incident->updates()->latest()->first();
        
        return (new MailMessage)
            ->subject("[Resolved] {$this->incident->name}")
            ->greeting("✅ Incident Resolved")
            ->line("**{$this->incident->name}**")
            ->line("This incident has been resolved.")
            ->when($componentNames, function ($mail) use ($componentNames) {
                return $mail->line("Affected Components: {$componentNames}");
            })
            ->line("Duration: {$duration}")
            ->when($latestUpdate, function ($mail) use ($latestUpdate) {
                return $mail->line(new HtmlString('Resolution: <div style="text-align: justify;">' . nl2br($latestUpdate->message) . '</div>'));
            })
            ->action('View Status Page', url('/'))
            ->line('All systems are now operational. Thank you for your patience.');
    }

    /**
     * Get the SMS representation of the notification (stub).
     */
    public function toSms(object $notifiable): string
    {
        // SMS stub - implement with provider like Twilio or SNS
        return "[Resolved] {$this->incident->name}. All systems are now operational.";
    }

    /**
     * Get the Teams representation of the notification.
     */
    public function toTeams(object $notifiable): array
    {
        $componentNames = $this->incident->components->pluck('name')->join(', ');
        $duration = $this->incident->started_at && $this->incident->resolved_at
            ? $this->incident->started_at->diffForHumans($this->incident->resolved_at, true)
            : 'Unknown';
        
        $latestUpdate = $this->incident->updates()->latest()->first();
        
        return [
            '@type' => 'MessageCard',
            '@context' => 'https://schema.org/extensions',
            'summary' => "Incident Resolved: {$this->incident->name}",
            'themeColor' => '28a745', // Green
            'title' => "✅ Incident Resolved",
            'sections' => [
                [
                    'activityTitle' => $this->incident->name,
                    'activitySubtitle' => 'All systems are now operational',
                    'facts' => array_filter([
                        $componentNames ? ['name' => 'Affected Components', 'value' => $componentNames] : null,
                        ['name' => 'Duration', 'value' => $duration],
                        $latestUpdate ? ['name' => 'Resolution', 'value' => $latestUpdate->message] : null,
                        ['name' => 'Resolved At', 'value' => $this->incident->resolved_at->timezone('America/New_York')->format('M d, Y H:i T')],
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
            'resolved_at' => $this->incident->resolved_at?->toIso8601String(),
        ];
    }
}
