<?php

namespace Database\Factories;

use App\Enums\EquipmentType;
use App\Enums\ExerciseCategory;
use App\Enums\ExerciseMechanics;
use App\Enums\MuscleGroup;
use App\Models\MemberExercise;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MemberExercise>
 */
class MemberExerciseFactory extends Factory
{
    protected $model = MemberExercise::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'category' => fake()->randomElement(ExerciseCategory::cases()),
            'primary_muscle' => fake()->randomElement(MuscleGroup::cases()),
            'secondary_muscles' => fake()->randomElements(
                array_map(fn ($case) => $case->value, MuscleGroup::cases()),
                fake()->numberBetween(0, 3)
            ),
            'equipment' => fake()->randomElement(EquipmentType::cases()),
            'mechanics' => fake()->randomElement(ExerciseMechanics::cases()),
        ];
    }
}
