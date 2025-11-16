<?php

use App\Enums\Role;
use App\Enums\SetType;
use App\Models\Exercise;
use App\Models\SessionExercise;
use App\Models\SessionSet;
use App\Models\TrainingSession;
use App\Models\User;
use Carbon\Carbon;

describe('Session Logging', function () {
    describe('creating sessions', function () {
        it('creates a new training session for a user', function () {
            $user = User::factory()->create(['role' => Role::Member]);

            $session = TrainingSession::create([
                'user_id' => $user->id,
                'logged_by' => $user->id,
                'scheduled_date' => now()->toDateString(),
                'started_at' => now(),
                'notes' => 'Morning workout',
            ]);

            expect($session)->not->toBeNull()
                ->and($session->user_id)->toBe($user->id)
                ->and($session->logged_by)->toBe($user->id)
                ->and($session->notes)->toBe('Morning workout');
        });

        it('allows PTs to log sessions on behalf of members', function () {
            $pt = User::factory()->create(['role' => Role::PT]);
            $member = User::factory()->create(['role' => Role::Member]);

            $session = TrainingSession::create([
                'user_id' => $member->id,
                'logged_by' => $pt->id,
                'scheduled_date' => now()->toDateString(),
                'started_at' => now(),
            ]);

            expect($session->user_id)->toBe($member->id)
                ->and($session->logged_by)->toBe($pt->id);
        });
    });

    describe('session status', function () {
        it('correctly identifies pending sessions', function () {
            $user = User::factory()->create(['role' => Role::Member]);

            $session = TrainingSession::create([
                'user_id' => $user->id,
                'logged_by' => $user->id,
                'scheduled_date' => now()->toDateString(),
            ]);

            expect($session->isPending())->toBeTrue()
                ->and($session->isInProgress())->toBeFalse()
                ->and($session->isCompleted())->toBeFalse();
        });

        it('correctly identifies in-progress sessions', function () {
            $user = User::factory()->create(['role' => Role::Member]);

            $session = TrainingSession::create([
                'user_id' => $user->id,
                'logged_by' => $user->id,
                'scheduled_date' => now()->toDateString(),
                'started_at' => now(),
            ]);

            expect($session->isInProgress())->toBeTrue()
                ->and($session->isPending())->toBeFalse()
                ->and($session->isCompleted())->toBeFalse();
        });

        it('correctly identifies completed sessions', function () {
            $user = User::factory()->create(['role' => Role::Member]);

            $session = TrainingSession::create([
                'user_id' => $user->id,
                'logged_by' => $user->id,
                'scheduled_date' => now()->toDateString(),
                'started_at' => now()->subHour(),
                'completed_at' => now(),
            ]);

            expect($session->isCompleted())->toBeTrue()
                ->and($session->isInProgress())->toBeFalse()
                ->and($session->isPending())->toBeFalse();
        });
    });

    describe('adding exercises to sessions', function () {
        it('adds exercises to a training session', function () {
            $user = User::factory()->create(['role' => Role::Member]);
            $exercise1 = Exercise::factory()->create(['name' => 'Bench Press']);
            $exercise2 = Exercise::factory()->create(['name' => 'Squats']);

            $session = TrainingSession::create([
                'user_id' => $user->id,
                'logged_by' => $user->id,
                'scheduled_date' => now()->toDateString(),
                'started_at' => now(),
            ]);

            SessionExercise::create([
                'training_session_id' => $session->id,
                'exercise_id' => $exercise1->id,
                'order' => 1,
            ]);

            SessionExercise::create([
                'training_session_id' => $session->id,
                'exercise_id' => $exercise2->id,
                'order' => 2,
            ]);

            $session->load('exercises');

            expect($session->exercises)->toHaveCount(2)
                ->and($session->exercises->first()->order)->toBe(1)
                ->and($session->exercises->last()->order)->toBe(2);
        });

        it('supports superset grouping for exercises', function () {
            $user = User::factory()->create(['role' => Role::Member]);
            $exercise1 = Exercise::factory()->create(['name' => 'Pull-ups']);
            $exercise2 = Exercise::factory()->create(['name' => 'Dips']);

            $session = TrainingSession::create([
                'user_id' => $user->id,
                'logged_by' => $user->id,
                'scheduled_date' => now()->toDateString(),
                'started_at' => now(),
            ]);

            SessionExercise::create([
                'training_session_id' => $session->id,
                'exercise_id' => $exercise1->id,
                'order' => 1,
                'superset_group' => 'A',
            ]);

            SessionExercise::create([
                'training_session_id' => $session->id,
                'exercise_id' => $exercise2->id,
                'order' => 2,
                'superset_group' => 'A',
            ]);

            $supersetExercises = $session->exercises()->where('superset_group', 'A')->get();

            expect($supersetExercises)->toHaveCount(2);
        });
    });

    describe('logging sets', function () {
        it('logs sets with prescribed and performed data', function () {
            $user = User::factory()->create(['role' => Role::Member]);
            $exercise = Exercise::factory()->create(['name' => 'Deadlift']);

            $session = TrainingSession::create([
                'user_id' => $user->id,
                'logged_by' => $user->id,
                'scheduled_date' => now()->toDateString(),
                'started_at' => now(),
            ]);

            $sessionExercise = SessionExercise::create([
                'training_session_id' => $session->id,
                'exercise_id' => $exercise->id,
                'order' => 1,
            ]);

            $set = SessionSet::create([
                'session_exercise_id' => $sessionExercise->id,
                'set_number' => 1,
                'set_type' => SetType::Normal,
                'prescribed_reps' => 5,
                'prescribed_weight' => 100.00,
                'prescribed_rpe' => 8,
                'performed_reps' => 5,
                'performed_weight' => 100.00,
                'performed_rpe' => 7,
                'completed' => true,
                'completed_as_prescribed' => true,
                'completed_at' => now(),
            ]);

            expect($set)->not->toBeNull()
                ->and($set->prescribed_reps)->toBe(5)
                ->and($set->prescribed_weight)->toBe("100.00")
                ->and($set->performed_reps)->toBe(5)
                ->and($set->performed_weight)->toBe("100.00")
                ->and($set->isCompleted())->toBeTrue()
                ->and($set->completedAsPrescribed())->toBeTrue();
        });

        it('tracks sets completed not as prescribed', function () {
            $user = User::factory()->create(['role' => Role::Member]);
            $exercise = Exercise::factory()->create(['name' => 'Squat']);

            $session = TrainingSession::create([
                'user_id' => $user->id,
                'logged_by' => $user->id,
                'scheduled_date' => now()->toDateString(),
                'started_at' => now(),
            ]);

            $sessionExercise = SessionExercise::create([
                'training_session_id' => $session->id,
                'exercise_id' => $exercise->id,
                'order' => 1,
            ]);

            $set = SessionSet::create([
                'session_exercise_id' => $sessionExercise->id,
                'set_number' => 1,
                'set_type' => SetType::Normal,
                'prescribed_reps' => 10,
                'prescribed_weight' => 80.00,
                'performed_reps' => 8,
                'performed_weight' => 75.00,
                'completed' => true,
                'completed_as_prescribed' => false,
                'completed_at' => now(),
            ]);

            expect($set->isCompleted())->toBeTrue()
                ->and($set->completedAsPrescribed())->toBeFalse()
                ->and($set->performed_reps)->toBe(8)
                ->and($set->performed_weight)->toBe("75.00");
        });

        it('tracks skipped sets', function () {
            $user = User::factory()->create(['role' => Role::Member]);
            $exercise = Exercise::factory()->create(['name' => 'Romanian Deadlift']);

            $session = TrainingSession::create([
                'user_id' => $user->id,
                'logged_by' => $user->id,
                'scheduled_date' => now()->toDateString(),
                'started_at' => now(),
            ]);

            $sessionExercise = SessionExercise::create([
                'training_session_id' => $session->id,
                'exercise_id' => $exercise->id,
                'order' => 1,
            ]);

            $set = SessionSet::create([
                'session_exercise_id' => $sessionExercise->id,
                'set_number' => 1,
                'set_type' => SetType::Normal,
                'prescribed_reps' => 10,
                'prescribed_weight' => 60.00,
                'completed' => false,
                'skipped' => true,
                'completed_at' => now(),
            ]);

            expect($set->wasSkipped())->toBeTrue()
                ->and($set->isCompleted())->toBeFalse();
        });

        it('supports different set types', function () {
            $user = User::factory()->create(['role' => Role::Member]);
            $exercise = Exercise::factory()->create(['name' => 'Bench Press']);

            $session = TrainingSession::create([
                'user_id' => $user->id,
                'logged_by' => $user->id,
                'scheduled_date' => now()->toDateString(),
                'started_at' => now(),
            ]);

            $sessionExercise = SessionExercise::create([
                'training_session_id' => $session->id,
                'exercise_id' => $exercise->id,
                'order' => 1,
            ]);

            $warmupSet = SessionSet::create([
                'session_exercise_id' => $sessionExercise->id,
                'set_number' => 1,
                'set_type' => SetType::WarmUp,
                'prescribed_reps' => 10,
                'prescribed_weight' => 40.00,
                'performed_reps' => 10,
                'performed_weight' => 40.00,
                'completed' => true,
            ]);

            $workingSet = SessionSet::create([
                'session_exercise_id' => $sessionExercise->id,
                'set_number' => 2,
                'set_type' => SetType::Normal,
                'prescribed_reps' => 5,
                'prescribed_weight' => 80.00,
                'performed_reps' => 5,
                'performed_weight' => 80.00,
                'completed' => true,
            ]);

            expect($warmupSet->set_type)->toBe(SetType::WarmUp)
                ->and($workingSet->set_type)->toBe(SetType::Normal);
        });
    });

    describe('completing sessions', function () {
        it('marks session as completed when completed_at is set', function () {
            $user = User::factory()->create(['role' => Role::Member]);

            $session = TrainingSession::create([
                'user_id' => $user->id,
                'logged_by' => $user->id,
                'scheduled_date' => now()->toDateString(),
                'started_at' => now()->subMinutes(60),
            ]);

            $session->update(['completed_at' => now()]);

            expect($session->refresh()->isCompleted())->toBeTrue()
                ->and($session->completed_at)->toBeInstanceOf(Carbon::class);
        });

        it('calculates session duration from start to completion', function () {
            $user = User::factory()->create(['role' => Role::Member]);

            $startTime = now()->subMinutes(90);
            $endTime = now();

            $session = TrainingSession::create([
                'user_id' => $user->id,
                'logged_by' => $user->id,
                'scheduled_date' => now()->toDateString(),
                'started_at' => $startTime,
                'completed_at' => $endTime,
            ]);

            $duration = $session->started_at->diffInMinutes($session->completed_at);

            expect($duration)->toBe(90.0);
        });
    });

    describe('session relationships', function () {
        it('correctly loads user relationship', function () {
            $user = User::factory()->create(['role' => Role::Member]);

            $session = TrainingSession::create([
                'user_id' => $user->id,
                'logged_by' => $user->id,
                'scheduled_date' => now()->toDateString(),
                'started_at' => now(),
            ]);

            $session->load('user');

            expect($session->user)->not->toBeNull()
                ->and($session->user->id)->toBe($user->id);
        });

        it('correctly loads logger relationship', function () {
            $pt = User::factory()->create(['role' => Role::PT]);
            $member = User::factory()->create(['role' => Role::Member]);

            $session = TrainingSession::create([
                'user_id' => $member->id,
                'logged_by' => $pt->id,
                'scheduled_date' => now()->toDateString(),
                'started_at' => now(),
            ]);

            $session->load('logger');

            expect($session->logger)->not->toBeNull()
                ->and($session->logger->id)->toBe($pt->id)
                ->and($session->logger->role)->toBe(Role::PT);
        });

        it('correctly loads exercises and sets relationships', function () {
            $user = User::factory()->create(['role' => Role::Member]);
            $exercise = Exercise::factory()->create(['name' => 'Overhead Press']);

            $session = TrainingSession::create([
                'user_id' => $user->id,
                'logged_by' => $user->id,
                'scheduled_date' => now()->toDateString(),
                'started_at' => now(),
            ]);

            $sessionExercise = SessionExercise::create([
                'training_session_id' => $session->id,
                'exercise_id' => $exercise->id,
                'order' => 1,
            ]);

            SessionSet::create([
                'session_exercise_id' => $sessionExercise->id,
                'set_number' => 1,
                'set_type' => SetType::Normal,
                'prescribed_reps' => 8,
                'prescribed_weight' => 50.00,
                'performed_reps' => 8,
                'performed_weight' => 50.00,
                'completed' => true,
            ]);

            SessionSet::create([
                'session_exercise_id' => $sessionExercise->id,
                'set_number' => 2,
                'set_type' => SetType::Normal,
                'prescribed_reps' => 8,
                'prescribed_weight' => 50.00,
                'performed_reps' => 7,
                'performed_weight' => 50.00,
                'completed' => true,
            ]);

            $session->load('exercises.sets');

            expect($session->exercises)->toHaveCount(1)
                ->and($session->exercises->first()->sets)->toHaveCount(2);
        });
    });

    describe('soft deletion', function () {
        it('soft deletes training sessions', function () {
            $user = User::factory()->create(['role' => Role::Member]);

            $session = TrainingSession::create([
                'user_id' => $user->id,
                'logged_by' => $user->id,
                'scheduled_date' => now()->toDateString(),
                'started_at' => now(),
                'completed_at' => now(),
            ]);

            $sessionId = $session->id;
            $session->delete();

            $foundSession = TrainingSession::find($sessionId);
            $trashedSession = TrainingSession::withTrashed()->find($sessionId);

            expect($foundSession)->toBeNull()
                ->and($trashedSession)->not->toBeNull()
                ->and($trashedSession->deleted_at)->not->toBeNull();
        });
    });
});
