<?php

use App\Enums\Role;
use App\Enums\SetType;
use App\Models\Exercise;
use App\Models\SessionExercise;
use App\Models\SessionSet;
use App\Models\TrainingSession;
use App\Models\User;
use Carbon\Carbon;

describe('Analytics Calculations', function () {
    describe('volume calculations', function () {
        it('calculates total volume for a session', function () {
            $user = User::factory()->create(['role' => Role::Member]);
            $exercise = Exercise::factory()->create(['name' => 'Squat']);

            $session = TrainingSession::create([
                'user_id' => $user->id,
                'logged_by' => $user->id,
                'scheduled_date' => now()->toDateString(),
                'started_at' => now()->subHour(),
                'completed_at' => now(),
            ]);

            $sessionExercise = SessionExercise::create([
                'training_session_id' => $session->id,
                'exercise_id' => $exercise->id,
                'order' => 1,
            ]);

            // 3 sets: 100kg x 5 reps each = 500kg per set
            SessionSet::create([
                'session_exercise_id' => $sessionExercise->id,
                'set_number' => 1,
                'set_type' => SetType::Normal,
                'performed_reps' => 5,
                'performed_weight' => 100.00,
                'completed' => true,
            ]);

            SessionSet::create([
                'session_exercise_id' => $sessionExercise->id,
                'set_number' => 2,
                'set_type' => SetType::Normal,
                'performed_reps' => 5,
                'performed_weight' => 100.00,
                'completed' => true,
            ]);

            SessionSet::create([
                'session_exercise_id' => $sessionExercise->id,
                'set_number' => 3,
                'set_type' => SetType::Normal,
                'performed_reps' => 5,
                'performed_weight' => 100.00,
                'completed' => true,
            ]);

            // Calculate volume: weight * reps * sets
            $sets = SessionSet::where('session_exercise_id', $sessionExercise->id)
                ->where('completed', true)
                ->get();

            $totalVolume = $sets->sum(function ($set) {
                return floatval($set->performed_weight) * $set->performed_reps;
            });

            expect($totalVolume)->toBe(1500.0); // 100 * 5 * 3
        });

        it('excludes skipped sets from volume calculations', function () {
            $user = User::factory()->create(['role' => Role::Member]);
            $exercise = Exercise::factory()->create(['name' => 'Bench Press']);

            $session = TrainingSession::create([
                'user_id' => $user->id,
                'logged_by' => $user->id,
                'scheduled_date' => now()->toDateString(),
                'started_at' => now()->subHour(),
                'completed_at' => now(),
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
                'performed_reps' => 8,
                'performed_weight' => 80.00,
                'completed' => true,
                'skipped' => false,
            ]);

            SessionSet::create([
                'session_exercise_id' => $sessionExercise->id,
                'set_number' => 2,
                'set_type' => SetType::Normal,
                'prescribed_reps' => 8,
                'prescribed_weight' => 80.00,
                'completed' => false,
                'skipped' => true,
            ]);

            $sets = SessionSet::where('session_exercise_id', $sessionExercise->id)
                ->where('completed', true)
                ->where('skipped', false)
                ->get();

            $totalVolume = $sets->sum(function ($set) {
                return floatval($set->performed_weight) * $set->performed_reps;
            });

            expect($totalVolume)->toBe(640.0); // Only first set: 80 * 8
        });

        it('calculates volume across multiple exercises in a session', function () {
            $user = User::factory()->create(['role' => Role::Member]);
            $exercise1 = Exercise::factory()->create(['name' => 'Deadlift']);
            $exercise2 = Exercise::factory()->create(['name' => 'Rows']);

            $session = TrainingSession::create([
                'user_id' => $user->id,
                'logged_by' => $user->id,
                'scheduled_date' => now()->toDateString(),
                'started_at' => now()->subHour(),
                'completed_at' => now(),
            ]);

            $sessionExercise1 = SessionExercise::create([
                'training_session_id' => $session->id,
                'exercise_id' => $exercise1->id,
                'order' => 1,
            ]);

            $sessionExercise2 = SessionExercise::create([
                'training_session_id' => $session->id,
                'exercise_id' => $exercise2->id,
                'order' => 2,
            ]);

            // Deadlift: 140kg x 5 reps
            SessionSet::create([
                'session_exercise_id' => $sessionExercise1->id,
                'set_number' => 1,
                'set_type' => SetType::Normal,
                'performed_reps' => 5,
                'performed_weight' => 140.00,
                'completed' => true,
            ]);

            // Rows: 60kg x 10 reps
            SessionSet::create([
                'session_exercise_id' => $sessionExercise2->id,
                'set_number' => 1,
                'set_type' => SetType::Normal,
                'performed_reps' => 10,
                'performed_weight' => 60.00,
                'completed' => true,
            ]);

            $allSets = SessionSet::whereIn('session_exercise_id', [$sessionExercise1->id, $sessionExercise2->id])
                ->where('completed', true)
                ->get();

            $totalVolume = $allSets->sum(function ($set) {
                return floatval($set->performed_weight) * $set->performed_reps;
            });

            expect($totalVolume)->toBe(1300.0); // (140 * 5) + (60 * 10)
        });

        it('calculates weekly volume for a user', function () {
            $user = User::factory()->create(['role' => Role::Member]);
            $exercise = Exercise::factory()->create(['name' => 'Squat']);

            $startOfWeek = now()->startOfWeek();

            // Session 1 - Monday
            $session1 = TrainingSession::create([
                'user_id' => $user->id,
                'logged_by' => $user->id,
                'scheduled_date' => $startOfWeek->toDateString(),
                'started_at' => $startOfWeek,
                'completed_at' => $startOfWeek->copy()->addHour(),
            ]);

            $sessionExercise1 = SessionExercise::create([
                'training_session_id' => $session1->id,
                'exercise_id' => $exercise->id,
                'order' => 1,
            ]);

            SessionSet::create([
                'session_exercise_id' => $sessionExercise1->id,
                'set_number' => 1,
                'set_type' => SetType::Normal,
                'performed_reps' => 5,
                'performed_weight' => 100.00,
                'completed' => true,
            ]);

            // Session 2 - Wednesday
            $session2 = TrainingSession::create([
                'user_id' => $user->id,
                'logged_by' => $user->id,
                'scheduled_date' => $startOfWeek->copy()->addDays(2)->toDateString(),
                'started_at' => $startOfWeek->copy()->addDays(2),
                'completed_at' => $startOfWeek->copy()->addDays(2)->addHour(),
            ]);

            $sessionExercise2 = SessionExercise::create([
                'training_session_id' => $session2->id,
                'exercise_id' => $exercise->id,
                'order' => 1,
            ]);

            SessionSet::create([
                'session_exercise_id' => $sessionExercise2->id,
                'set_number' => 1,
                'set_type' => SetType::Normal,
                'performed_reps' => 5,
                'performed_weight' => 102.50,
                'completed' => true,
            ]);

            $sessions = TrainingSession::where('user_id', $user->id)
                ->whereBetween('started_at', [$startOfWeek, $startOfWeek->copy()->endOfWeek()])
                ->get();

            $exerciseIds = SessionExercise::whereIn('training_session_id', $sessions->pluck('id'))->pluck('id');

            $sets = SessionSet::whereIn('session_exercise_id', $exerciseIds)
                ->where('completed', true)
                ->get();

            $weeklyVolume = $sets->sum(function ($set) {
                return floatval($set->performed_weight) * $set->performed_reps;
            });

            expect($weeklyVolume)->toBe(1012.5); // (100 * 5) + (102.5 * 5)
        });
    });

    describe('personal records tracking', function () {
        it('identifies highest weight for an exercise', function () {
            $user = User::factory()->create(['role' => Role::Member]);
            $exercise = Exercise::factory()->create(['name' => 'Bench Press']);

            $sessions = [];
            $weights = [80.0, 85.0, 90.0, 92.5, 95.0];

            foreach ($weights as $index => $weight) {
                $session = TrainingSession::create([
                    'user_id' => $user->id,
                    'logged_by' => $user->id,
                    'scheduled_date' => now()->subDays(count($weights) - $index)->toDateString(),
                    'started_at' => now()->subDays(count($weights) - $index),
                    'completed_at' => now()->subDays(count($weights) - $index)->addHour(),
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
                    'performed_reps' => 5,
                    'performed_weight' => $weight,
                    'completed' => true,
                ]);
            }

            // Find PR weight
            $exerciseIds = SessionExercise::whereHas('trainingSession', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->where('exercise_id', $exercise->id)->pluck('id');

            $prWeight = SessionSet::whereIn('session_exercise_id', $exerciseIds)
                ->where('completed', true)
                ->max('performed_weight');

            expect(floatval($prWeight))->toBe(95.0);
        });

        it('identifies most reps performed at a specific weight', function () {
            $user = User::factory()->create(['role' => Role::Member]);
            $exercise = Exercise::factory()->create(['name' => 'Deadlift']);

            $targetWeight = 140.0;
            $repCounts = [3, 4, 5, 5, 6];

            foreach ($repCounts as $index => $reps) {
                $session = TrainingSession::create([
                    'user_id' => $user->id,
                    'logged_by' => $user->id,
                    'scheduled_date' => now()->subDays(count($repCounts) - $index)->toDateString(),
                    'started_at' => now()->subDays(count($repCounts) - $index),
                    'completed_at' => now()->subDays(count($repCounts) - $index)->addHour(),
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
                    'performed_reps' => $reps,
                    'performed_weight' => $targetWeight,
                    'completed' => true,
                ]);
            }

            $exerciseIds = SessionExercise::whereHas('trainingSession', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->where('exercise_id', $exercise->id)->pluck('id');

            $prReps = SessionSet::whereIn('session_exercise_id', $exerciseIds)
                ->where('performed_weight', $targetWeight)
                ->where('completed', true)
                ->max('performed_reps');

            expect($prReps)->toBe(6);
        });

        it('tracks PRs by rep range', function () {
            $user = User::factory()->create(['role' => Role::Member]);
            $exercise = Exercise::factory()->create(['name' => 'Squat']);

            // 1RM PR
            $session1 = TrainingSession::create([
                'user_id' => $user->id,
                'logged_by' => $user->id,
                'scheduled_date' => now()->subDays(10)->toDateString(),
                'started_at' => now()->subDays(10),
                'completed_at' => now()->subDays(10)->addHour(),
            ]);

            $sessionExercise1 = SessionExercise::create([
                'training_session_id' => $session1->id,
                'exercise_id' => $exercise->id,
                'order' => 1,
            ]);

            SessionSet::create([
                'session_exercise_id' => $sessionExercise1->id,
                'set_number' => 1,
                'set_type' => SetType::Normal,
                'performed_reps' => 1,
                'performed_weight' => 180.00,
                'completed' => true,
            ]);

            // 5RM PR
            $session2 = TrainingSession::create([
                'user_id' => $user->id,
                'logged_by' => $user->id,
                'scheduled_date' => now()->subDays(5)->toDateString(),
                'started_at' => now()->subDays(5),
                'completed_at' => now()->subDays(5)->addHour(),
            ]);

            $sessionExercise2 = SessionExercise::create([
                'training_session_id' => $session2->id,
                'exercise_id' => $exercise->id,
                'order' => 1,
            ]);

            SessionSet::create([
                'session_exercise_id' => $sessionExercise2->id,
                'set_number' => 1,
                'set_type' => SetType::Normal,
                'performed_reps' => 5,
                'performed_weight' => 150.00,
                'completed' => true,
            ]);

            $exerciseIds = SessionExercise::whereHas('trainingSession', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->where('exercise_id', $exercise->id)->pluck('id');

            $oneRepMax = SessionSet::whereIn('session_exercise_id', $exerciseIds)
                ->where('performed_reps', 1)
                ->where('completed', true)
                ->max('performed_weight');

            $fiveRepMax = SessionSet::whereIn('session_exercise_id', $exerciseIds)
                ->where('performed_reps', 5)
                ->where('completed', true)
                ->max('performed_weight');

            expect(floatval($oneRepMax))->toBe(180.0)
                ->and(floatval($fiveRepMax))->toBe(150.0);
        });
    });

    describe('training frequency and trends', function () {
        it('calculates sessions per week for a user', function () {
            $user = User::factory()->create(['role' => Role::Member]);

            $startOfWeek = now()->startOfWeek();

            // Create 3 sessions this week
            for ($i = 0; $i < 3; $i++) {
                TrainingSession::create([
                    'user_id' => $user->id,
                    'logged_by' => $user->id,
                    'scheduled_date' => $startOfWeek->copy()->addDays($i)->toDateString(),
                    'started_at' => $startOfWeek->copy()->addDays($i),
                    'completed_at' => $startOfWeek->copy()->addDays($i)->addHour(),
                ]);
            }

            $sessionsThisWeek = TrainingSession::where('user_id', $user->id)
                ->whereBetween('started_at', [$startOfWeek, $startOfWeek->copy()->endOfWeek()])
                ->where('completed_at', '!=', null)
                ->count();

            expect($sessionsThisWeek)->toBe(3);
        });

        it('tracks total sets completed over time period', function () {
            $user = User::factory()->create(['role' => Role::Member]);
            $exercise = Exercise::factory()->create();

            $startDate = now()->subDays(30);

            $totalSets = 0;

            for ($day = 0; $day < 30; $day += 3) {
                $session = TrainingSession::create([
                    'user_id' => $user->id,
                    'logged_by' => $user->id,
                    'scheduled_date' => $startDate->copy()->addDays($day)->toDateString(),
                    'started_at' => $startDate->copy()->addDays($day),
                    'completed_at' => $startDate->copy()->addDays($day)->addHour(),
                ]);

                $sessionExercise = SessionExercise::create([
                    'training_session_id' => $session->id,
                    'exercise_id' => $exercise->id,
                    'order' => 1,
                ]);

                // 3 sets per session
                for ($set = 1; $set <= 3; $set++) {
                    SessionSet::create([
                        'session_exercise_id' => $sessionExercise->id,
                        'set_number' => $set,
                        'set_type' => SetType::Normal,
                        'performed_reps' => 10,
                        'performed_weight' => 50.00,
                        'completed' => true,
                    ]);
                    $totalSets++;
                }
            }

            $sessions = TrainingSession::where('user_id', $user->id)
                ->whereBetween('started_at', [$startDate, now()])
                ->get();

            $exerciseIds = SessionExercise::whereIn('training_session_id', $sessions->pluck('id'))->pluck('id');

            $completedSets = SessionSet::whereIn('session_exercise_id', $exerciseIds)
                ->where('completed', true)
                ->count();

            expect($completedSets)->toBe($totalSets)
                ->and($completedSets)->toBe(30); // 10 sessions * 3 sets
        });

        it('calculates average session duration', function () {
            $user = User::factory()->create(['role' => Role::Member]);

            $durations = [45, 50, 55, 60, 65]; // minutes

            foreach ($durations as $duration) {
                $startTime = now()->subDays(count($durations));
                TrainingSession::create([
                    'user_id' => $user->id,
                    'logged_by' => $user->id,
                    'scheduled_date' => $startTime->toDateString(),
                    'started_at' => $startTime,
                    'completed_at' => $startTime->copy()->addMinutes($duration),
                ]);
            }

            $sessions = TrainingSession::where('user_id', $user->id)
                ->whereNotNull('completed_at')
                ->get();

            $totalMinutes = $sessions->sum(function ($session) {
                return $session->started_at->diffInMinutes($session->completed_at);
            });

            $averageDuration = $totalMinutes / $sessions->count();

            expect($averageDuration)->toBe(55.0); // Average of 45, 50, 55, 60, 65
        });
    });
});
