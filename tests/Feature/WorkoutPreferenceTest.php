<?php

use App\Enums\CardioType;
use App\Enums\MuscleGroup;
use App\Enums\Role;
use App\Models\PtAssignment;
use App\Models\User;
use App\Models\WorkoutPreference;

beforeEach(function () {
    $this->member = User::factory()->create(['role' => Role::Member]);
    $this->pt = User::factory()->create(['role' => Role::PT]);

    PtAssignment::factory()->create([
        'pt_id' => $this->pt->id,
        'member_id' => $this->member->id,
    ]);
});

it('allows member to create their own workout preferences', function () {
    $response = $this->actingAs($this->member)
        ->postJson(route('workoutPreference.store'), [
            'user_id' => $this->member->id,
            'weekly_schedule' => [
                'monday' => ['focus' => 'upper_body'],
                'saturday' => ['cardio_type' => CardioType::Boxing->value],
            ],
            'focus_areas' => [MuscleGroup::Chest->value, MuscleGroup::Back->value],
            'analysis_window_days' => 21,
        ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'message',
            'data' => [
                'id',
                'user_id',
                'weekly_schedule',
                'focus_areas',
                'analysis_window_days',
            ],
        ]);

    $this->assertDatabaseHas('workout_preferences', [
        'user_id' => $this->member->id,
        'analysis_window_days' => 21,
    ]);
});

it('allows PT to create preferences for their assigned member', function () {
    $response = $this->actingAs($this->pt)
        ->postJson(route('workoutPreference.store'), [
            'user_id' => $this->member->id,
            'weekly_schedule' => [
                'wednesday' => ['focus' => 'legs'],
            ],
            'focus_areas' => [MuscleGroup::Quads->value],
        ]);

    $response->assertCreated();

    $this->assertDatabaseHas('workout_preferences', [
        'user_id' => $this->member->id,
    ]);
});

it('prevents creating duplicate preferences', function () {
    WorkoutPreference::factory()->create([
        'user_id' => $this->member->id,
    ]);

    $response = $this->actingAs($this->member)
        ->postJson(route('workoutPreference.store'), [
            'user_id' => $this->member->id,
            'focus_areas' => [MuscleGroup::Chest->value],
        ]);

    $response->assertStatus(409);
});

it('allows updating workout preferences', function () {
    $preference = WorkoutPreference::factory()->create([
        'user_id' => $this->member->id,
        'analysis_window_days' => 14,
    ]);

    $response = $this->actingAs($this->member)
        ->putJson(route('workoutPreference.update', $preference), [
            'analysis_window_days' => 30,
            'focus_areas' => [MuscleGroup::Shoulders->value, MuscleGroup::Biceps->value],
        ]);

    $response->assertOk();

    expect($preference->fresh()->analysis_window_days)->toBe(30);
});

it('retrieves workout preferences for a user', function () {
    $preference = WorkoutPreference::factory()->create([
        'user_id' => $this->member->id,
    ]);

    $response = $this->actingAs($this->member)
        ->getJson(route('workoutPreference.show', $this->member));

    $response->assertOk()
        ->assertJsonFragment(['id' => $preference->id]);
});

it('returns 404 when preferences do not exist', function () {
    $response = $this->actingAs($this->member)
        ->getJson(route('workoutPreference.show', $this->member));

    $response->assertNotFound();
});

it('validates weekly schedule cardio types', function () {
    $response = $this->actingAs($this->member)
        ->postJson(route('workoutPreference.store'), [
            'user_id' => $this->member->id,
            'weekly_schedule' => [
                'monday' => ['cardio_type' => 'invalid_cardio_type'],
            ],
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['weekly_schedule.monday.cardio_type']);
});

it('validates focus areas are valid muscle groups', function () {
    $response = $this->actingAs($this->member)
        ->postJson(route('workoutPreference.store'), [
            'user_id' => $this->member->id,
            'focus_areas' => ['invalid_muscle_group'],
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['focus_areas.0']);
});

it('deletes workout preferences', function () {
    $preference = WorkoutPreference::factory()->create([
        'user_id' => $this->member->id,
    ]);

    $response = $this->actingAs($this->member)
        ->deleteJson(route('workoutPreference.destroy', $preference));

    $response->assertOk();

    $this->assertDatabaseMissing('workout_preferences', [
        'id' => $preference->id,
    ]);
});
