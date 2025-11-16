<?php

namespace App\Policies;

use App\Models\Exercise;
use App\Models\User;

class ExercisePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Exercise $exercise): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Exercise $exercise): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Exercise $exercise): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, Exercise $exercise): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Exercise $exercise): bool
    {
        return $user->isAdmin();
    }
}
