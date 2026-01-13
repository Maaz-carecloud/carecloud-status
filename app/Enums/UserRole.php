<?php

namespace App\Enums;

enum UserRole: string
{
    case SUPER_ADMIN = 'super_admin';
    case ADMIN = 'admin';
    case EDITOR = 'editor';

    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Admin',
            self::ADMIN => 'Admin',
            self::EDITOR => 'Editor',
        };
    }

    public function permissions(): array
    {
        return match ($this) {
            self::SUPER_ADMIN => ['*'],
            self::ADMIN => ['manage_incidents', 'manage_components', 'manage_subscribers', 'view_analytics'],
            self::EDITOR => ['manage_incidents', 'view_components', 'view_subscribers'],
        };
    }

    public function canManageUsers(): bool
    {
        return $this === self::SUPER_ADMIN;
    }

    public function canManageComponents(): bool
    {
        return in_array($this, [self::SUPER_ADMIN, self::ADMIN]);
    }

    public function canManageIncidents(): bool
    {
        return in_array($this, [self::SUPER_ADMIN, self::ADMIN, self::EDITOR]);
    }
}
