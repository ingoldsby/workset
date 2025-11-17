<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkoutPreference;

class WorkoutPreferencePolicy
{
    public function view(User $user, User $targetUser): bool
    {
        if ($user->id === $targetUser->id) {
            return true;
        }

        if ($user->isPt()) {
            return $user->memberAssignments()
                ->where('member_id', $targetUser->id)
                ->exists();
        }

        return false;
    }

    public function create(User $user, User $targetUser): bool
    {
        if ($user->id === $targetUser->id) {
            return true;
        }

        if ($user->isPt()) {
            return $user->memberAssignments()
                ->where('member_id', $targetUser->id)
                ->exists();
        }

        return false;
    }

    public function update(User $user, WorkoutPreference $workoutPreference): bool
    {
        if ($user->id === $workoutPreference->user_id) {
            return true;
        }

        if ($user->isPt()) {
            return $user->memberAssignments()
                ->where('member_id', $workoutPreference->user_id)
                ->exists();
        }

        return false;
    }

    public function delete(User $user, WorkoutPreference $workoutPreference): bool
    {
        if ($user->id === $workoutPreference->user_id) {
            return true;
        }

        if ($user->isPt()) {
            return $user->memberAssignments()
                ->where('member_id', $workoutPreference->user_id)
                ->exists();
        }

        return false;
    }
}
