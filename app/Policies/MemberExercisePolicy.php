<?php

namespace App\Policies;

use App\Models\MemberExercise;
use App\Models\User;

class MemberExercisePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, MemberExercise $memberExercise): bool
    {
        if ($user->id === $memberExercise->user_id) {
            return true;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isPt()) {
            return $user->memberAssignments()
                ->where('member_id', $memberExercise->user_id)
                ->where('unassigned_at', null)
                ->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, MemberExercise $memberExercise): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $memberExercise->user_id;
    }

    public function delete(User $user, MemberExercise $memberExercise): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $memberExercise->user_id;
    }

    public function restore(User $user, MemberExercise $memberExercise): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $memberExercise->user_id;
    }

    public function forceDelete(User $user, MemberExercise $memberExercise): bool
    {
        return $user->isAdmin();
    }
}
