<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TeamsChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification): void
    {
        // Get the webhook URL from the notifiable entity
        $webhookUrl = $notifiable->teams_webhook_url ?? config('services.teams.webhook_url');

        if (!$webhookUrl) {
            Log::warning('Teams webhook URL not configured for notification', [
                'notification' => get_class($notification),
                'notifiable' => get_class($notifiable),
            ]);
            return;
        }

        // Get the Teams message from the notification
        if (!method_exists($notification, 'toTeams')) {
            return;
        }

        $message = $notification->toTeams($notifiable);

        try {
            // Send POST request to Teams webhook
            $response = Http::post($webhookUrl, $message);

            if ($response && !$response->successful()) {
                Log::error('Failed to send Teams notification', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'notification' => get_class($notification),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Exception sending Teams notification', [
                'message' => $e->getMessage(),
                'notification' => get_class($notification),
            ]);
        }
    }
}
