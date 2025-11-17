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
            'day_number' => $this->faker->numberBetween(1, 7),
            'name' => $this->faker->words(2, true),
            'description' => $this->faker->sentence(),
            'rest_days_after' => $this->faker->numberBetween(0, 2),
        ];
    }
}
