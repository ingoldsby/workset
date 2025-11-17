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
            'snapshot_type' => $this->faker->randomElement(['weekly', 'monthly', 'yearly']),
            'snapshot_date' => $this->faker->date(),
            'data' => [
                'total_sessions' => $this->faker->numberBetween(1, 50),
                'total_volume' => $this->faker->numberBetween(1000, 50000),
                'avg_session_duration' => $this->faker->numberBetween(30, 120),
            ],
        ];
    }
}
