<?php

namespace App\Policies;

use App\Models\CardioEntry;
use App\Models\User;

class CardioEntryPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, CardioEntry $cardioEntry): bool
    {
        if ($user->id === $cardioEntry->user_id) {
            return true;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isPt()) {
            return $user->memberAssignments()
                ->where('member_id', $cardioEntry->user_id)
                ->where('unassigned_at', null)
                ->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, CardioEntry $cardioEntry): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->id === $cardioEntry->user_id) {
            return true;
        }

        if ($user->isPt()) {
            return $user->memberAssignments()
                ->where('member_id', $cardioEntry->user_id)
                ->where('unassigned_at', null)
                ->exists();
        }

        return false;
    }

    public function delete(User $user, CardioEntry $cardioEntry): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $cardioEntry->user_id;
    }
}
