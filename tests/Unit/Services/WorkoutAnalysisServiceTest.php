<?php

use App\Enums\CardioType;
use App\Enums\ExerciseCategory;
use App\Enums\MuscleGroup;
use App\Models\CardioEntry;
use App\Models\Exercise;
use App\Models\SessionExercise;
use App\Models\SessionSet;
use App\Models\TrainingSession;
use App\Models\User;
use App\Services\WorkoutAnalysisService;

beforeEach(function () {
    $this->service = new WorkoutAnalysisService;
    $this->user = User::factory()->create();
});

it('returns empty analysis when user has no completed sessions', function () {
    $result = $this->service->analyzeUserWorkouts($this->user, 14);

    expect($result['session_summary']['total_sessions'])->toBe(0)
        ->and($result['muscle_groups']['frequency'])->toBeEmpty()
        ->and($result['cardio_analysis']['total_sessions'])->toBe(0);
});

it('analyses muscle group frequency from training sessions', function () {
    $chestExercise = Exercise::factory()->create([
        'primary_muscle' => MuscleGroup::Chest,
        'category' => ExerciseCategory::Strength,
    ]);

    $backExercise = Exercise::factory()->create([
        'primary_muscle' => MuscleGroup::Back,
        'category' => ExerciseCategory::Strength,
    ]);

    $session1 = TrainingSession::factory()->create([
        'user_id' => $this->user->id,
        'completed_at' => now()->subDays(5),
    ]);

    $sessionExercise1 = SessionExercise::factory()->create([
        'training_session_id' => $session1->id,
        'exercise_id' => $chestExercise->id,
    ]);

    SessionSet::factory()->count(3)->create([
        'session_exercise_id' => $sessionExercise1->id,
        'completed' => true,
    ]);

    $session2 = TrainingSession::factory()->create([
        'user_id' => $this->user->id,
        'completed_at' => now()->subDays(3),
    ]);

    $sessionExercise2 = SessionExercise::factory()->create([
        'training_session_id' => $session2->id,
        'exercise_id' => $backExercise->id,
    ]);

    SessionSet::factory()->count(4)->create([
        'session_exercise_id' => $sessionExercise2->id,
        'completed' => true,
    ]);

    $result = $this->service->analyzeUserWorkouts($this->user, 14);

    expect($result['session_summary']['total_sessions'])->toBe(2)
        ->and($result['muscle_groups']['frequency'])->toHaveKeys(['chest', 'back'])
        ->and($result['muscle_groups']['volume']['chest'])->toBe(3)
        ->and($result['muscle_groups']['volume']['back'])->toBe(4);
});

it('analyses cardio activity correctly', function () {
    CardioEntry::factory()->create([
        'user_id' => $this->user->id,
        'cardio_type' => CardioType::Boxing,
        'entry_date' => now()->subDays(2),
        'duration_seconds' => 1800,
        'distance' => null,
    ]);

    CardioEntry::factory()->create([
        'user_id' => $this->user->id,
        'cardio_type' => CardioType::Running,
        'entry_date' => now()->subDays(4),
        'duration_seconds' => 2400,
        'distance' => 5.5,
    ]);

    $result = $this->service->analyzeUserWorkouts($this->user, 14);

    expect($result['cardio_analysis']['total_sessions'])->toBe(2)
        ->and($result['cardio_analysis']['types'])->toHaveKeys(['boxing', 'running'])
        ->and($result['cardio_analysis']['total_duration_minutes'])->toBeGreaterThan(60);
});

it('calculates volume metrics accurately', function () {
    $exercise = Exercise::factory()->create([
        'primary_muscle' => MuscleGroup::Chest,
    ]);

    $session = TrainingSession::factory()->create([
        'user_id' => $this->user->id,
        'completed_at' => now()->subDays(1),
    ]);

    $sessionExercise = SessionExercise::factory()->create([
        'training_session_id' => $session->id,
        'exercise_id' => $exercise->id,
    ]);

    SessionSet::factory()->create([
        'session_exercise_id' => $sessionExercise->id,
        'completed' => true,
        'performed_reps' => 10,
        'performed_weight' => 50,
    ]);

    SessionSet::factory()->create([
        'session_exercise_id' => $sessionExercise->id,
        'completed' => true,
        'performed_reps' => 8,
        'performed_weight' => 55,
    ]);

    $result = $this->service->analyzeUserWorkouts($this->user, 14);

    expect($result['volume_metrics']['total_sets'])->toBe(2)
        ->and($result['volume_metrics']['total_volume'])->toBe((10 * 50) + (8 * 55))
        ->and($result['volume_metrics']['average_reps_per_set'])->toBe(9.0);
});

it('identifies weekly training patterns', function () {
    $exercise = Exercise::factory()->create([
        'primary_muscle' => MuscleGroup::Legs,
    ]);

    $mondaySession = TrainingSession::factory()->create([
        'user_id' => $this->user->id,
        'completed_at' => now()->startOfWeek()->addDays(0)->setTime(10, 0),
    ]);

    SessionExercise::factory()->create([
        'training_session_id' => $mondaySession->id,
        'exercise_id' => $exercise->id,
    ]);

    $wednesdaySession = TrainingSession::factory()->create([
        'user_id' => $this->user->id,
        'completed_at' => now()->startOfWeek()->addDays(2)->setTime(10, 0),
    ]);

    SessionExercise::factory()->create([
        'training_session_id' => $wednesdaySession->id,
        'exercise_id' => $exercise->id,
    ]);

    $result = $this->service->analyzeUserWorkouts($this->user, 14);

    expect($result['weekly_patterns']['training_days'])->toContain('Monday', 'Wednesday')
        ->and($result['weekly_patterns']['day_frequency'])->toHaveKeys(['Monday', 'Wednesday']);
});
