<?php

namespace Database\Factories;

use App\Models\Exercise;
use App\Models\SessionExercise;
use App\Models\TrainingSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SessionExercise>
 */
class SessionExerciseFactory extends Factory
{
    protected $model = SessionExercise::class;

    public function definition(): array
    {
        return [
            'training_session_id' => TrainingSession::factory(),
            'exercise_id' => Exercise::factory(),
            'member_exercise_id' => null,
            'order' => $this->faker->numberBetween(1, 10),
            'superset_group' => null,
            'notes' => null,
        ];
    }

    public function withSuperset(): static
    {
        return $this->state(fn (array $attributes) => [
            'superset_group' => 'A',
        ]);
    }
}
