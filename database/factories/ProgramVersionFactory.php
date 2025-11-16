<?php

namespace Database\Factories;

use App\Models\Program;
use App\Models\ProgramVersion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProgramVersion>
 */
class ProgramVersionFactory extends Factory
{
    protected $model = ProgramVersion::class;

    public function definition(): array
    {
        return [
            'program_id' => Program::factory(),
            'created_by' => User::factory(),
            'version_number' => 1,
            'change_notes' => fake()->sentence(),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
