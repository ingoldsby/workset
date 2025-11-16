<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, User $model): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->id === $model->id) {
            return true;
        }

        if ($user->isPt()) {
            return $user->memberAssignments()
                ->where('member_id', $model->id)
                ->where('unassigned_at', null)
                ->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, User $model): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $model->id;
    }

    public function delete(User $user, User $model): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $model->id;
    }

    public function restore(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    public function viewPtArea(User $user): bool
    {
        return $user->isAdmin() || $user->isPt();
    }
}
