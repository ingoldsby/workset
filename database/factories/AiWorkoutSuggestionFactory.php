<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Enums\SuggestionType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AiWorkoutSuggestionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(['role' => Role::Member]),
            'generated_by' => User::factory(['role' => Role::PT]),
            'suggestion_type' => fake()->randomElement(SuggestionType::cases()),
            'prompt_context' => [
                'type' => 'single_session',
                'analysis_window_days' => 14,
                'custom_prompt' => 'Generate an upper body workout',
                'has_preferences' => true,
            ],
            'suggestion_data' => [
                'suggestion' => [
                    'title' => 'Upper Body Strength Session',
                    'rationale' => 'Based on recent training analysis',
                    'exercises' => [
                        [
                            'name' => 'Bench Press',
                            'muscleGroup' => 'chest',
                            'category' => 'strength',
                            'sets' => 4,
                            'reps' => '8-10',
                            'rpe' => 8,
                            'restSeconds' => 120,
                            'supersetGroup' => null,
                            'order' => 1,
                            'notes' => 'Controlled tempo',
                        ],
                        [
                            'name' => 'Bent Over Row',
                            'muscleGroup' => 'back',
                            'category' => 'strength',
                            'sets' => 4,
                            'reps' => '8-12',
                            'rpe' => 8,
                            'restSeconds' => 120,
                            'supersetGroup' => null,
                            'order' => 2,
                            'notes' => 'Keep core tight',
                        ],
                    ],
                    'cardio' => [],
                    'programNotes' => 'Focus on progressive overload',
                ],
            ],
            'analysis_data' => [
                'period' => [
                    'days' => 14,
                    'start_date' => now()->subDays(14)->toDateString(),
                    'end_date' => now()->toDateString(),
                ],
                'session_summary' => [
                    'total_sessions' => 8,
                    'average_per_week' => 4.0,
                    'completion_rate' => 100,
                ],
                'muscle_groups' => [
                    'frequency' => [
                        'chest' => 3,
                        'back' => 3,
                        'legs' => 2,
                    ],
                ],
            ],
            'applied_to_session_id' => null,
            'applied_to_program_id' => null,
            'applied_at' => null,
        ];
    }

    public function applied(): static
    {
        return $this->state(fn (array $attributes) => [
            'applied_at' => now(),
        ]);
    }
}
