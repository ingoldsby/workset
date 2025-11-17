<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->create()->id,
            'event' => $this->faker->randomElement(['created', 'updated', 'deleted']),
            'auditable_type' => 'App\\Models\\Program',
            'auditable_id' => $this->faker->uuid(),
            'old_values' => null,
            'new_values' => ['name' => $this->faker->words(3, true)],
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
        ];
    }
}
