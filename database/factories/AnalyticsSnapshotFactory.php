<?php

namespace Database\Factories;

use App\Models\AnalyticsSnapshot;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AnalyticsSnapshot>
 */
class AnalyticsSnapshotFactory extends Factory
{
    protected $model = AnalyticsSnapshot::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->create()->id,
            'snapshot_type' => fake()->randomElement(['weekly', 'monthly', 'yearly']),
            'snapshot_date' => fake()->date(),
            'data' => [
                'total_sessions' => fake()->numberBetween(1, 50),
                'total_volume' => fake()->numberBetween(1000, 50000),
                'avg_session_duration' => fake()->numberBetween(30, 120),
            ],
        ];
    }
}
