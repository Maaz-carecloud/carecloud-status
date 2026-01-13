<?php

namespace App\Enums;

enum SubscriberType: string
{
    case EMAIL = 'email';
    case SMS = 'sms';
    case TEAMS = 'teams';

    public function label(): string
    {
        return match ($this) {
            self::EMAIL => 'Email',
            self::SMS => 'SMS',
            self::TEAMS => 'Microsoft Teams',
        };
    }
}
