<?php

namespace Database\Factories;

use App\Models\RecycleBin;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecycleBin>
 */
class RecycleBinFactory extends Factory
{
    protected $model = RecycleBin::class;

    public function definition(): array
    {
        $deletedAt = $this->faker->dateTimeBetween('-30 days', 'now');

        return [
            'user_id' => User::factory()->create()->id,
            'recyclable_type' => 'App\\Models\\Program',
            'recyclable_id' => $this->faker->uuid(),
            'data' => [
                'name' => $this->faker->words(3, true),
                'description' => $this->faker->paragraph(),
            ],
            'deleted_at' => $deletedAt,
            'expires_at' => $this->faker->dateTimeBetween($deletedAt, '+60 days'),
        ];
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => $this->faker->dateTimeBetween('-60 days', '-1 day'),
        ]);
    }
}
