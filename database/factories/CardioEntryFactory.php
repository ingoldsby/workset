<?php

namespace Database\Factories;

use App\Enums\CardioType;
use App\Models\CardioEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CardioEntry>
 */
class CardioEntryFactory extends Factory
{
    protected $model = CardioEntry::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'training_session_id' => null,
            'cardio_type' => $this->faker->randomElement(CardioType::cases()),
            'entry_date' => $this->faker->date(),
            'duration_seconds' => $this->faker->numberBetween(600, 3600),
            'distance' => $this->faker->randomFloat(2, 1, 20),
            'distance_unit' => 'km',
            'avg_heart_rate' => $this->faker->numberBetween(120, 160),
            'max_heart_rate' => $this->faker->numberBetween(160, 190),
            'calories_burned' => $this->faker->numberBetween(100, 800),
            'notes' => null,
        ];
    }
}
