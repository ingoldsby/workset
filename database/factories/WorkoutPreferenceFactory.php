<?php

namespace Database\Factories;

use App\Enums\CardioType;
use App\Enums\MuscleGroup;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkoutPreferenceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'weekly_schedule' => [
                'monday' => ['focus' => 'upper_body'],
                'wednesday' => ['focus' => 'lower_body'],
                'saturday' => ['cardio_type' => CardioType::Boxing->value],
            ],
            'focus_areas' => [
                MuscleGroup::Chest->value,
                MuscleGroup::Back->value,
                MuscleGroup::Quads->value,
            ],
            'analysis_window_days' => 14,
            'preferences' => [
                'training_experience' => 'intermediate',
                'goals' => 'strength_and_hypertrophy',
            ],
        ];
    }
}
