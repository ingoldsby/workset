<?php

namespace App\Policies;

use App\Models\Invite;
use App\Models\User;

class InvitePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role->canInvite();
    }

    public function view(User $user, Invite $invite): bool
    {
        // Admins can view all invites
        if ($user->isAdmin()) {
            return true;
        }

        // PTs can view invites they sent
        return $user->isPt() && $invite->invited_by === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->role->canInvite();
    }

    public function delete(User $user, Invite $invite): bool
    {
        // Can only delete pending invites
        if (! $invite->isPending()) {
            return false;
        }

        // Admins can delete any pending invite
        if ($user->isAdmin()) {
            return true;
        }

        // PTs can delete invites they sent
        return $user->isPt() && $invite->invited_by === $user->id;
    }

    public function resend(User $user, Invite $invite): bool
    {
        // Can only resend pending invites
        if (! $invite->isPending()) {
            return false;
        }

        // Admins can resend any pending invite
        if ($user->isAdmin()) {
            return true;
        }

        // PTs can resend invites they sent
        return $user->isPt() && $invite->invited_by === $user->id;
    }
}
