<?php

namespace Database\Factories;

use App\Models\ProgramDay;
use App\Models\SessionPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SessionPlan>
 */
class SessionPlanFactory extends Factory
{
    protected $model = SessionPlan::class;

    public function definition(): array
    {
        return [
            'program_day_id' => ProgramDay::factory(),
            'user_id' => User::factory(),
            'created_by' => User::factory(),
            'name' => fake()->words(3, true),
            'notes' => fake()->sentence(),
        ];
    }
}
