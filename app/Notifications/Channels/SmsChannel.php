<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsChannel
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
        // Get the phone number from the notifiable entity
        $phone = $notifiable->phone ?? null;

        if (!$phone) {
            Log::warning('Phone number not configured for SMS notification', [
                'notification' => get_class($notification),
                'notifiable' => get_class($notifiable),
            ]);
            return;
        }

        // Normalize phone number - remove all non-numeric characters except leading +
        $normalizedPhone = $this->normalizePhoneNumber($phone);

        if (!$normalizedPhone) {
            Log::warning('Invalid phone number format', [
                'phone' => $phone,
                'notification' => get_class($notification),
            ]);
            return;
        }

        // Get the SMS message from the notification
        if (!method_exists($notification, 'toSms')) {
            Log::warning('Notification does not implement toSms method', [
                'notification' => get_class($notification),
            ]);
            return;
        }

        $message = $notification->toSms($notifiable);

        if (empty($message)) {
            Log::warning('Empty SMS message', [
                'notification' => get_class($notification),
            ]);
            return;
        }

        // Send SMS via CareCloud API
        try {
            $response = Http::post(config('services.carecloud_sms.api_url'), [
                'teamId' => config('services.carecloud_sms.team_id'),
                'teamName' => config('services.carecloud_sms.team_name'),
                'message' => $message,
                'toPhoneNumber' => $normalizedPhone,
                'smsType' => config('services.carecloud_sms.sms_type'),
                'practiceCode' => config('services.carecloud_sms.practice_code'),
                'providerCode' => config('services.carecloud_sms.provider_code'),
                'patientAccount' => config('services.carecloud_sms.patient_account'),
            ]);

            if ($response->successful()) {
                $result = $response->json();
                Log::info('SMS notification sent successfully', [
                    'original_phone' => $phone,
                    'normalized_phone' => $normalizedPhone,
                    'notification' => get_class($notification),
                    'response' => $result,
                ]);
            } else {
                Log::error('Failed to send SMS notification', [
                    'original_phone' => $phone,
                    'normalized_phone' => $normalizedPhone,
                    'notification' => get_class($notification),
                    'status_code' => $response->status(),
                    'response' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Exception while sending SMS notification', [
                'original_phone' => $phone,
                'normalized_phone' => $normalizedPhone,
                'notification' => get_class($notification),
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Normalize phone number to handle various formats.
     * Accepts numbers with or without country code.
     * CareCloud API expects 10-digit US phone numbers without country code.
     *
     * @param string $phone
     * @return string|null
     */
    protected function normalizePhoneNumber(string $phone): ?string
    {
        // Remove all spaces, dashes, parentheses, and dots
        $cleaned = preg_replace('/[\s\-\(\)\.]+/', '', $phone);

        // Remove leading + if present
        $cleaned = ltrim($cleaned, '+');

        // Ensure we have only digits
        if (!preg_match('/^\d+$/', $cleaned)) {
            return null;
        }

        // Handle different formats:
        // - If 10 digits: assume US number without country code (e.g., 7328735133)
        // - If 11 digits starting with 1: Strip the leading 1 (US country code)
        // - Otherwise: return null for invalid formats
        
        if (strlen($cleaned) === 10) {
            // Already in correct format
            return $cleaned;
        } elseif (strlen($cleaned) === 11 && $cleaned[0] === '1') {
            // Remove US country code (1) to get 10-digit number
            return substr($cleaned, 1);
        }

        // Invalid length for US phone numbers
        return null;
    }
}
