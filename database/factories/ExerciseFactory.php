<?php

namespace Database\Factories;

use App\Enums\EquipmentType;
use App\Enums\ExerciseCategory;
use App\Enums\ExerciseLevel;
use App\Enums\ExerciseMechanics;
use App\Enums\MuscleGroup;
use App\Models\Exercise;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Exercise>
 */
class ExerciseFactory extends Factory
{
    protected $model = Exercise::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'category' => $this->faker->randomElement(ExerciseCategory::cases()),
            'primary_muscle' => $this->faker->randomElement(MuscleGroup::cases()),
            'secondary_muscles' => $this->faker->randomElements(
                array_map(fn ($case) => $case->value, MuscleGroup::cases()),
                $this->faker->numberBetween(0, 3)
            ),
            'equipment' => $this->faker->randomElement(EquipmentType::cases()),
            'equipment_variants' => null,
            'mechanics' => $this->faker->randomElement(ExerciseMechanics::cases()),
            'level' => $this->faker->randomElement(ExerciseLevel::cases()),
            'aliases' => null,
            'wger_id' => null,
            'language' => 'en-AU',
        ];
    }

    public function fromWger(): static
    {
        return $this->state(fn (array $attributes) => [
            'wger_id' => $this->faker->unique()->numberBetween(1, 10000),
        ]);
    }
}
