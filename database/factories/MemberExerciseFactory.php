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
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'category' => $this->faker->randomElement(ExerciseCategory::cases()),
            'primary_muscle' => $this->faker->randomElement(MuscleGroup::cases()),
            'secondary_muscles' => $this->faker->randomElements(
                array_map(fn ($case) => $case->value, MuscleGroup::cases()),
                $this->faker->numberBetween(0, 3)
            ),
            'equipment' => $this->faker->randomElement(EquipmentType::cases()),
            'mechanics' => $this->faker->randomElement(ExerciseMechanics::cases()),
        ];
    }
}
