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
            'name' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'category' => fake()->randomElement(ExerciseCategory::cases()),
            'primary_muscle' => fake()->randomElement(MuscleGroup::cases()),
            'secondary_muscles' => fake()->randomElements(
                array_map(fn ($case) => $case->value, MuscleGroup::cases()),
                fake()->numberBetween(0, 3)
            ),
            'equipment' => fake()->randomElement(EquipmentType::cases()),
            'equipment_variants' => null,
            'mechanics' => fake()->randomElement(ExerciseMechanics::cases()),
            'level' => fake()->randomElement(ExerciseLevel::cases()),
            'aliases' => null,
            'wger_id' => null,
            'language' => 'en-AU',
        ];
    }

    public function fromWger(): static
    {
        return $this->state(fn (array $attributes) => [
            'wger_id' => fake()->unique()->numberBetween(1, 10000),
        ]);
    }
}
