<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case PT = 'pt';
    case Member = 'member';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::PT => 'PT',
            self::Member => 'Member',
        };
    }

    public function canManageUsers(): bool
    {
        return match ($this) {
            self::Admin, self::PT => true,
            self::Member => false,
        };
    }

    public function canInvite(): bool
    {
        return match ($this) {
            self::Admin, self::PT => true,
            self::Member => false,
        };
    }

    public function canAccessAdmin(): bool
    {
        return match ($this) {
            self::Admin => true,
            self::PT, self::Member => false,
        };
    }
}
