<?php

use App\Enums\Role;
use App\Enums\SuggestionType;
use App\Models\AiWorkoutSuggestion;
use App\Models\PtAssignment;
use App\Models\User;
use App\Models\WorkoutPreference;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->pt = User::factory()->create(['role' => Role::PT]);
    $this->member = User::factory()->create(['role' => Role::Member]);

    PtAssignment::factory()->create([
        'pt_id' => $this->pt->id,
        'member_id' => $this->member->id,
    ]);
});

it('requires PT role to generate workout suggestions', function () {
    $member = User::factory()->create(['role' => Role::Member]);

    $response = $this->actingAs($member)
        ->postJson(route('aiWorkoutSuggestion.generate'), [
            'user_id' => $this->member->id,
            'suggestion_type' => 'single_session',
        ]);

    $response->assertForbidden();
});

it('prevents PT from generating suggestions for non-assigned members', function () {
    $otherMember = User::factory()->create(['role' => Role::Member]);

    $response = $this->actingAs($this->pt)
        ->postJson(route('aiWorkoutSuggestion.generate'), [
            'user_id' => $otherMember->id,
            'suggestion_type' => 'single_session',
        ]);

    $response->assertForbidden();
});

it('generates AI workout suggestion successfully', function () {
    WorkoutPreference::factory()->create([
        'user_id' => $this->member->id,
        'analysis_window_days' => 14,
    ]);

    Http::fake([
        'api.openai.com/*' => Http::response([
            'choices' => [
                [
                    'message' => [
                        'content' => json_encode([
                            'suggestion' => [
                                'title' => 'Upper Body Focus Session',
                                'rationale' => 'Based on recent training gaps',
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
                                        'notes' => 'Focus on controlled eccentric',
                                    ],
                                ],
                                'cardio' => [],
                                'programNotes' => 'Progressive overload focus',
                            ],
                        ]),
                    ],
                ],
            ],
        ], 200),
    ]);

    $response = $this->actingAs($this->pt)
        ->postJson(route('aiWorkoutSuggestion.generate'), [
            'user_id' => $this->member->id,
            'suggestion_type' => SuggestionType::SingleSession->value,
            'custom_prompt' => 'Focus on upper body',
        ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'message',
            'data' => [
                'id',
                'user_id',
                'generated_by',
                'suggestion_type',
                'suggestion_data',
                'analysis_data',
            ],
        ]);

    $this->assertDatabaseHas('ai_workout_suggestions', [
        'user_id' => $this->member->id,
        'generated_by' => $this->pt->id,
        'suggestion_type' => SuggestionType::SingleSession->value,
    ]);
});

it('retrieves suggestions for a member', function () {
    $suggestion = AiWorkoutSuggestion::factory()->create([
        'user_id' => $this->member->id,
        'generated_by' => $this->pt->id,
    ]);

    $response = $this->actingAs($this->pt)
        ->getJson(route('aiWorkoutSuggestion.index', ['user_id' => $this->member->id]));

    $response->assertOk()
        ->assertJsonFragment(['id' => $suggestion->id]);
});

it('marks suggestion as applied', function () {
    $suggestion = AiWorkoutSuggestion::factory()->create([
        'user_id' => $this->member->id,
        'generated_by' => $this->pt->id,
    ]);

    $response = $this->actingAs($this->pt)
        ->postJson(route('aiWorkoutSuggestion.apply', $suggestion), [
            'session_id' => null,
            'program_id' => null,
        ]);

    $response->assertOk();

    expect($suggestion->fresh()->isApplied())->toBeTrue()
        ->and($suggestion->fresh()->applied_at)->not->toBeNull();
});

it('validates suggestion type in request', function () {
    $response = $this->actingAs($this->pt)
        ->postJson(route('aiWorkoutSuggestion.generate'), [
            'user_id' => $this->member->id,
            'suggestion_type' => 'invalid_type',
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['suggestion_type']);
});

it('deletes suggestion successfully', function () {
    $suggestion = AiWorkoutSuggestion::factory()->create([
        'user_id' => $this->member->id,
        'generated_by' => $this->pt->id,
    ]);

    $response = $this->actingAs($this->pt)
        ->deleteJson(route('aiWorkoutSuggestion.destroy', $suggestion));

    $response->assertOk();

    $this->assertDatabaseMissing('ai_workout_suggestions', [
        'id' => $suggestion->id,
    ]);
});
