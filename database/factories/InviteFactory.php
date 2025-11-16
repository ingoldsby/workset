<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Models\Invite;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Invite>
 */
class InviteFactory extends Factory
{
    protected $model = Invite::class;

    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'token' => Str::random(32),
            'invited_by' => User::factory(),
            'pt_id' => null,
            'role' => Role::Member,
            'expires_at' => now()->addDays(30),
            'accepted_at' => null,
        ];
    }

    public function forPt(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => Role::PT,
        ]);
    }

    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'accepted_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => fake()->dateTimeBetween('-60 days', '-1 day'),
        ]);
    }

    public function withPt(): static
    {
        return $this->state(fn (array $attributes) => [
            'pt_id' => User::factory(),
        ]);
    }
}
