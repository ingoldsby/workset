<?php

namespace App\Policies;

use App\Models\Invite;
use App\Models\User;

class InvitePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isPt();
    }

    public function view(User $user, Invite $invite): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $invite->invited_by;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isPt();
    }

    public function update(User $user, Invite $invite): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $invite->invited_by;
    }

    public function delete(User $user, Invite $invite): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $invite->invited_by;
    }
}
