<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'carecloud_sms' => [
        'api_url' => env('CARECLOUD_SMS_API_URL', 'https://ccldsms.carecloud.com/api/AcuTwilio/SendSms'),
        'team_id' => env('CARECLOUD_SMS_TEAM_ID', '5BDEFA67-7519-4570-B97A-879AEAAC5A24'),
        'team_name' => env('CARECLOUD_SMS_TEAM_NAME', 'CarecloudStatus'),
        'sms_type' => env('CARECLOUD_SMS_TYPE', 'CarecloudStatus'),
        'practice_code' => env('CARECLOUD_SMS_PRACTICE_CODE', 9090998),
        'provider_code' => env('CARECLOUD_SMS_PROVIDER_CODE', 0),
        'patient_account' => env('CARECLOUD_SMS_PATIENT_ACCOUNT', 0),
    ],

];
