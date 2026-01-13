# SMS Integration Documentation

## Overview

The SMS notification system has been integrated with the CareCloud SMS API to send status updates to subscribers via SMS.

## API Endpoint

```
POST https://ccldsms.carecloud.com/api/AcuTwilio/SendSms
```

## Configuration

### Environment Variables

Add these to your `.env` file (defaults are set in `config/services.php`):

```env
CARECLOUD_SMS_API_URL=https://ccldsms.carecloud.com/api/AcuTwilio/SendSms
CARECLOUD_SMS_TEAM_ID=5BDEFA67-7519-4570-B97A-879AEAAC5A24
CARECLOUD_SMS_TEAM_NAME=CarecloudStatus
CARECLOUD_SMS_TYPE=CarecloudStatus
CARECLOUD_SMS_PRACTICE_CODE=9090998
CARECLOUD_SMS_PROVIDER_CODE=0
CARECLOUD_SMS_PATIENT_ACCOUNT=0
```

### Configuration File

Settings are stored in `config/services.php` under the `carecloud_sms` key.

## How It Works

### 1. Subscriber Model

Subscribers with a `phone` field will automatically receive SMS notifications in addition to email.

### 2. Notification Channels

When a notification is triggered, the system checks if the subscriber has:

-   **Email**: Always sent (via `mail` channel)
-   **Phone**: SMS sent via `sms` channel if phone number exists
-   **Teams Webhook**: Teams message sent via `teams` channel if webhook URL exists

### 3. SMS Channel Implementation

Located in: `app/Notifications/Channels/SmsChannel.php`

The channel:

1. Validates the phone number exists
2. Calls the notification's `toSms()` method to get the message
3. Sends an HTTP POST request to the CareCloud SMS API
4. Logs success/failure for monitoring

### 4. API Request Format

```json
{
    "teamId": "5BDEFA67-7519-4570-B97A-879AEAAC5A24",
    "teamName": "CarecloudStatus",
    "message": "Dynamic message content",
    "toPhoneNumber": "7328735133",
    "smsType": "CarecloudStatus",
    "practiceCode": 9090998,
    "providerCode": 0,
    "patientAccount": 0
}
```

**Dynamic Parameters:**

-   `message` - Generated from the notification's `toSms()` method
-   `toPhoneNumber` - From the subscriber's `phone` field

**Static Parameters:**

-   All other fields use configured values from `config/services.php`

### 5. API Response

On success, the API returns:

```json
[
    {
        "smsId": "SM4b4b02f7809ebb712b0ec3efe03c27a2",
        "smsStatus": "accepted",
        "phoneNumber": "+12233324777"
    }
]
```

## Supported Notifications

All notifications now support SMS delivery:

### 1. **IncidentCreatedNotification**

Sent when a new incident is created.

```
SMS Format: "[{Impact}] {Incident Name}. Status: {Status}. View: {URL}"
Example: "[Critical] Database Connection Issues. Status: Investigating. View: https://status.carecloud.com/"
```

### 2. **IncidentUpdatedNotification**

Sent when an incident is updated.

```
SMS Format: "[Update] {Incident Name} - {Status}: {Update Message}"
Example: "[Update] Database Connection Issues - Monitoring: Database has been restored and we are monitoring stability."
```

### 3. **IncidentResolvedNotification**

Sent when an incident is resolved.

```
SMS Format: "[Resolved] {Incident Name}. All systems are now operational."
Example: "[Resolved] Database Connection Issues. All systems are now operational."
```

### 4. **ComponentStatusChangedNotification**

Sent when a component's status changes.

```
SMS Format: "[Status Change] {Component Name}: {Old Status} → {New Status}."
Example: "[Status Change] API Server: operational → degraded_performance."
```

## Testing

### Test SMS Delivery

1. Create or update a subscriber with a phone number
2. Subscribe them to a component
3. Trigger a status change or incident
4. Check logs for SMS delivery confirmation

### Check Logs

SMS delivery is logged in the Laravel logs:

```bash
# Success
[INFO] SMS notification sent successfully

# Failure
[ERROR] Failed to send SMS notification
```

## Phone Number Format

The system automatically handles phone numbers in various formats:

**Supported Formats:**

-   `7328735133` - 10 digits without country code
-   `+17328735133` - With country code and + prefix
-   `1-732-873-5133` - With dashes
-   `(732) 873-5133` - With parentheses and spaces
-   `+1 (732) 873-5133` - International format

**Normalization Process:**

1. Removes all spaces, dashes, parentheses, and dots
2. Strips the leading `+` symbol
3. Validates that only digits remain
4. Sends the cleaned number to the API

**Examples:**

-   `+1-732-873-5133` → `17328735133`
-   `(732) 873-5133` → `7328735133`
-   `+44 20 7946 0958` → `442079460958`

The API receives only numeric digits, making it compatible with both domestic and international numbers.

## Error Handling

-   **Missing phone number**: Logged as warning, notification skips SMS
-   **API failure**: Logged as error with status code and response
-   **Exception**: Logged with exception message

## Monitoring

Check the following logs for SMS delivery status:

-   `storage/logs/laravel.log` - All SMS activity is logged here
-   Look for entries with context: `notification`, `phone`, `response`

## Customization

### Customize SMS Messages

Edit the `toSms()` method in each notification class to change the message format:

```php
public function toSms(object $notifiable): string
{
    return "Your custom message here";
}
```

### Change API Parameters

Modify `config/services.php` or set environment variables to change static parameters.

### Add SMS to New Notifications

1. Add the SMS channel check in the `via()` method:

```php
public function via(object $notifiable): array
{
    $channels = ['mail'];

    if (isset($notifiable->phone)) {
        $channels[] = 'sms';
    }

    return $channels;
}
```

2. Implement the `toSms()` method:

```php
public function toSms(object $notifiable): string
{
    return "Your SMS message";
}
```

## Troubleshooting

### SMS Not Being Sent

1. Check if subscriber has a phone number: `$subscriber->phone`
2. Verify the subscriber is verified: `$subscriber->verified_at`
3. Check if subscriber is active: `$subscriber->is_active`
4. Review Laravel logs for errors

### API Errors

1. Verify API endpoint is accessible
2. Check credentials in `.env` file
3. Ensure phone number format is correct
4. Review API response in logs

### Phone Number Validation

The system automatically normalizes phone numbers. For input validation, consider:

```php
// In Subscriber model or validation rules - accepts various formats
'phone' => ['nullable', 'regex:/^[\d\s\-\(\)\.\+]+$/', 'min:10']
```

This allows users to enter phone numbers in their preferred format while the system handles normalization.
