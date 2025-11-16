<?php

namespace Database\Factories;

use App\Models\ProgramDay;
use App\Models\ProgramVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProgramDay>
 */
class ProgramDayFactory extends Factory
{
    protected $model = ProgramDay::class;

    public function definition(): array
    {
        return [
            'program_version_id' => ProgramVersion::factory(),
            'day_number' => fake()->numberBetween(1, 7),
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'rest_days_after' => fake()->numberBetween(0, 2),
        ];
    }
}
