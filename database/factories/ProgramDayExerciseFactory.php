<?php

namespace Database\Factories;

use App\Models\Exercise;
use App\Models\ProgramDay;
use App\Models\ProgramDayExercise;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProgramDayExercise>
 */
class ProgramDayExerciseFactory extends Factory
{
    protected $model = ProgramDayExercise::class;

    public function definition(): array
    {
        return [
            'program_day_id' => ProgramDay::factory(),
            'exercise_id' => Exercise::factory(),
            'member_exercise_id' => null,
            'order' => fake()->numberBetween(1, 10),
            'superset_group' => null,
            'sets' => fake()->numberBetween(3, 5),
            'reps_min' => fake()->numberBetween(6, 10),
            'reps_max' => fake()->numberBetween(10, 15),
            'rpe' => fake()->numberBetween(7, 9),
            'rest_seconds' => fake()->numberBetween(60, 180),
            'tempo' => null,
            'notes' => null,
            'progression_rules' => null,
        ];
    }

    public function withSuperset(): static
    {
        return $this->state(fn (array $attributes) => [
            'superset_group' => 'A',
        ]);
    }
}
