<?php

namespace App\Policies;

use App\Models\TrainingSession;
use App\Models\User;

class TrainingSessionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, TrainingSession $trainingSession): bool
    {
        if ($user->id === $trainingSession->user_id) {
            return true;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isPt()) {
            return $user->memberAssignments()
                ->where('member_id', $trainingSession->user_id)
                ->where('unassigned_at', null)
                ->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, TrainingSession $trainingSession): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->id === $trainingSession->user_id) {
            return true;
        }

        if ($user->isPt()) {
            return $user->memberAssignments()
                ->where('member_id', $trainingSession->user_id)
                ->where('unassigned_at', null)
                ->exists();
        }

        return false;
    }

    public function delete(User $user, TrainingSession $trainingSession): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $trainingSession->user_id;
    }

    public function restore(User $user, TrainingSession $trainingSession): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $trainingSession->user_id;
    }

    public function forceDelete(User $user, TrainingSession $trainingSession): bool
    {
        return $user->isAdmin();
    }
}
