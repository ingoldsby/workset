<?php

namespace Database\Factories;

use App\Enums\SetType;
use App\Models\SessionExercise;
use App\Models\SessionSet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SessionSet>
 */
class SessionSetFactory extends Factory
{
    protected $model = SessionSet::class;

    public function definition(): array
    {
        $prescribedReps = $this->faker->numberBetween(6, 12);
        $prescribedWeight = $this->faker->randomFloat(2, 20, 100);

        return [
            'session_exercise_id' => SessionExercise::factory(),
            'set_number' => $this->faker->numberBetween(1, 5),
            'set_type' => SetType::Normal,
            'prescribed_reps' => $prescribedReps,
            'prescribed_weight' => $prescribedWeight,
            'prescribed_rpe' => $this->faker->numberBetween(7, 9),
            'performed_reps' => null,
            'performed_weight' => null,
            'performed_rpe' => null,
            'time_seconds' => null,
            'tempo' => null,
            'completed' => false,
            'completed_as_prescribed' => false,
            'skipped' => false,
            'completed_at' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            $performedReps = $attributes['prescribed_reps'] + $this->faker->numberBetween(-2, 2);
            $performedWeight = $attributes['prescribed_weight'];
            $asPreescribed = $performedReps === $attributes['prescribed_reps'];

            return [
                'performed_reps' => max(1, $performedReps),
                'performed_weight' => $performedWeight,
                'performed_rpe' => $this->faker->numberBetween(7, 9),
                'completed' => true,
                'completed_as_prescribed' => $asPreescribed,
                'completed_at' => now(),
            ];
        });
    }

    public function skipped(): static
    {
        return $this->state(fn (array $attributes) => [
            'skipped' => true,
            'completed' => false,
        ]);
    }
}
