<?php

namespace App\Policies;

use App\Models\AiWorkoutSuggestion;
use App\Models\User;

class AiWorkoutSuggestionPolicy
{
    public function viewAny(User $user, User $targetUser): bool
    {
        if (! $user->isPt()) {
            return false;
        }

        return $user->memberAssignments()
            ->where('member_id', $targetUser->id)
            ->exists();
    }

    public function view(User $user, AiWorkoutSuggestion $suggestion): bool
    {
        if (! $user->isPt()) {
            return false;
        }

        if ($user->id === $suggestion->generated_by) {
            return true;
        }

        return $user->memberAssignments()
            ->where('member_id', $suggestion->user_id)
            ->exists();
    }

    public function create(User $user, User $targetUser): bool
    {
        if (! $user->isPt()) {
            return false;
        }

        return $user->memberAssignments()
            ->where('member_id', $targetUser->id)
            ->exists();
    }

    public function update(User $user, AiWorkoutSuggestion $suggestion): bool
    {
        if (! $user->isPt()) {
            return false;
        }

        if ($user->id === $suggestion->generated_by) {
            return true;
        }

        return $user->memberAssignments()
            ->where('member_id', $suggestion->user_id)
            ->exists();
    }

    public function delete(User $user, AiWorkoutSuggestion $suggestion): bool
    {
        if (! $user->isPt()) {
            return false;
        }

        if ($user->id === $suggestion->generated_by) {
            return true;
        }

        return $user->memberAssignments()
            ->where('member_id', $suggestion->user_id)
            ->exists();
    }
}
