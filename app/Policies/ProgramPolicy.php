<?php

namespace App\Policies;

use App\Models\Program;
use App\Models\User;

class ProgramPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Program $program): bool
    {
        if ($program->isPublic()) {
            return true;
        }

        if ($user->id === $program->owner_id) {
            return true;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isPt()) {
            return $user->memberAssignments()
                ->where('member_id', $program->owner_id)
                ->where('unassigned_at', null)
                ->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isPt();
    }

    public function update(User $user, Program $program): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $program->owner_id;
    }

    public function delete(User $user, Program $program): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $program->owner_id;
    }

    public function restore(User $user, Program $program): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $program->owner_id;
    }

    public function forceDelete(User $user, Program $program): bool
    {
        return $user->isAdmin();
    }
}
