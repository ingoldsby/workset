<?php

namespace Database\Factories;

use App\Models\SessionPlan;
use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrainingSession>
 */
class TrainingSessionFactory extends Factory
{
    protected $model = TrainingSession::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'session_plan_id' => SessionPlan::factory(),
            'logged_by' => null,
            'scheduled_date' => $this->faker->date(),
            'started_at' => null,
            'completed_at' => null,
            'notes' => null,
        ];
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'started_at' => $this->faker->dateTimeBetween('-2 hours', 'now'),
        ]);
    }

    public function completed(): static
    {
        $startedAt = $this->faker->dateTimeBetween('-7 days', '-1 hour');
        $completedAt = $this->faker->dateTimeBetween($startedAt, 'now');

        return $this->state(fn (array $attributes) => [
            'started_at' => $startedAt,
            'completed_at' => $completedAt,
        ]);
    }
}
