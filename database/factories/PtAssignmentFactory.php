<?php

namespace Database\Factories;

use App\Models\PtAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PtAssignment>
 */
class PtAssignmentFactory extends Factory
{
    protected $model = PtAssignment::class;

    public function definition(): array
    {
        return [
            'pt_id' => User::factory(),
            'member_id' => User::factory(),
            'assigned_at' => now(),
            'unassigned_at' => null,
        ];
    }

    public function unassigned(): static
    {
        return $this->state(fn (array $attributes) => [
            'unassigned_at' => fake()->dateTimeBetween($attributes['assigned_at'], 'now'),
        ]);
    }
}
