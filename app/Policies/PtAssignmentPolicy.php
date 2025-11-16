<?php

namespace App\Policies;

use App\Models\PtAssignment;
use App\Models\User;

class PtAssignmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isPt();
    }

    public function view(User $user, PtAssignment $ptAssignment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->id === $ptAssignment->pt_id || $user->id === $ptAssignment->member_id) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isPt();
    }

    public function update(User $user, PtAssignment $ptAssignment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $ptAssignment->pt_id;
    }

    public function delete(User $user, PtAssignment $ptAssignment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $ptAssignment->pt_id;
    }
}
