<?php

use App\Enums\ProgressionRuleType;
use App\Enums\Role;
use App\Models\Exercise;
use App\Models\Program;
use App\Models\ProgramDay;
use App\Models\ProgramDayExercise;
use App\Models\ProgramVersion;
use App\Models\User;

describe('Progression Rules', function () {
    describe('storing progression rules', function () {
        it('stores linear progression rules on exercises', function () {
            $user = User::factory()->create(['role' => Role::PT]);
            $program = Program::factory()->create(['owner_id' => $user->id]);
            $version = ProgramVersion::factory()->create(['program_id' => $program->id]);
            $day = ProgramDay::factory()->create(['program_version_id' => $version->id]);
            $exercise = Exercise::factory()->create();

            $progressionRule = [
                'type' => ProgressionRuleType::LinearProgression->value,
                'increment' => 2.5,
                'frequency' => 'per_session',
                'weight_cap' => 100.0,
            ];

            $programExercise = ProgramDayExercise::create([
                'program_day_id' => $day->id,
                'exercise_id' => $exercise->id,
                'order' => 1,
                'sets' => 3,
                'reps_min' => 8,
                'reps_max' => 12,
                'progression_rules' => [$progressionRule],
            ]);

            expect($programExercise->progression_rules)->toBeArray()
                ->toHaveCount(1)
                ->and($programExercise->progression_rules[0]['type'])->toBe(ProgressionRuleType::LinearProgression->value)
                ->and($programExercise->progression_rules[0]['increment'])->toBe(2.5)
                ->and($programExercise->progression_rules[0]['frequency'])->toBe('per_session')
                ->and($programExercise->progression_rules[0]['weight_cap'])->toBe(100);
        });

        it('stores double progression rules', function () {
            $user = User::factory()->create(['role' => Role::PT]);
            $program = Program::factory()->create(['owner_id' => $user->id]);
            $version = ProgramVersion::factory()->create(['program_id' => $program->id]);
            $day = ProgramDay::factory()->create(['program_version_id' => $version->id]);
            $exercise = Exercise::factory()->create();

            $progressionRule = [
                'type' => ProgressionRuleType::DoubleProgression->value,
                'rep_min' => 8,
                'rep_max' => 12,
                'weight_increment' => 2.5,
            ];

            $programExercise = ProgramDayExercise::create([
                'program_day_id' => $day->id,
                'exercise_id' => $exercise->id,
                'order' => 1,
                'sets' => 3,
                'reps_min' => 8,
                'reps_max' => 12,
                'progression_rules' => [$progressionRule],
            ]);

            expect($programExercise->progression_rules)->toBeArray()
                ->and($programExercise->progression_rules[0]['type'])->toBe(ProgressionRuleType::DoubleProgression->value)
                ->and($programExercise->progression_rules[0]['rep_min'])->toBe(8)
                ->and($programExercise->progression_rules[0]['rep_max'])->toBe(12)
                ->and($programExercise->progression_rules[0]['weight_increment'])->toBe(2.5);
        });

        it('stores top set backoff rules', function () {
            $user = User::factory()->create(['role' => Role::PT]);
            $program = Program::factory()->create(['owner_id' => $user->id]);
            $version = ProgramVersion::factory()->create(['program_id' => $program->id]);
            $day = ProgramDay::factory()->create(['program_version_id' => $version->id]);
            $exercise = Exercise::factory()->create();

            $progressionRule = [
                'type' => ProgressionRuleType::TopSetBackoff->value,
                'top_sets' => 1,
                'top_reps' => 5,
                'backoff_sets' => 3,
                'backoff_reps' => 8,
                'backoff_percentage' => 85,
            ];

            $programExercise = ProgramDayExercise::create([
                'program_day_id' => $day->id,
                'exercise_id' => $exercise->id,
                'order' => 1,
                'sets' => 4,
                'reps_min' => 5,
                'reps_max' => 8,
                'progression_rules' => [$progressionRule],
            ]);

            expect($programExercise->progression_rules[0]['type'])->toBe(ProgressionRuleType::TopSetBackoff->value)
                ->and($programExercise->progression_rules[0]['top_sets'])->toBe(1)
                ->and($programExercise->progression_rules[0]['backoff_percentage'])->toBe(85);
        });

        it('stores RPE target rules', function () {
            $user = User::factory()->create(['role' => Role::PT]);
            $program = Program::factory()->create(['owner_id' => $user->id]);
            $version = ProgramVersion::factory()->create(['program_id' => $program->id]);
            $day = ProgramDay::factory()->create(['program_version_id' => $version->id]);
            $exercise = Exercise::factory()->create();

            $progressionRule = [
                'type' => ProgressionRuleType::RpeTarget->value,
                'target_rpe' => 8,
                'tolerance' => 1,
                'weight_adjustment' => 2.5,
            ];

            $programExercise = ProgramDayExercise::create([
                'program_day_id' => $day->id,
                'exercise_id' => $exercise->id,
                'order' => 1,
                'sets' => 3,
                'reps_min' => 5,
                'reps_max' => 5,
                'rpe' => 8,
                'progression_rules' => [$progressionRule],
            ]);

            expect($programExercise->progression_rules[0]['type'])->toBe(ProgressionRuleType::RpeTarget->value)
                ->and($programExercise->progression_rules[0]['target_rpe'])->toBe(8)
                ->and($programExercise->progression_rules[0]['tolerance'])->toBe(1)
                ->and($programExercise->progression_rules[0]['weight_adjustment'])->toBe(2.5);
        });

        it('stores planned deload rules', function () {
            $user = User::factory()->create(['role' => Role::PT]);
            $program = Program::factory()->create(['owner_id' => $user->id]);
            $version = ProgramVersion::factory()->create(['program_id' => $program->id]);
            $day = ProgramDay::factory()->create(['program_version_id' => $version->id]);
            $exercise = Exercise::factory()->create();

            $progressionRule = [
                'type' => ProgressionRuleType::PlannedDeload->value,
                'frequency_weeks' => 4,
                'deload_percentage' => 60,
            ];

            $programExercise = ProgramDayExercise::create([
                'program_day_id' => $day->id,
                'exercise_id' => $exercise->id,
                'order' => 1,
                'sets' => 3,
                'reps_min' => 8,
                'reps_max' => 12,
                'progression_rules' => [$progressionRule],
            ]);

            expect($programExercise->progression_rules[0]['type'])->toBe(ProgressionRuleType::PlannedDeload->value)
                ->and($programExercise->progression_rules[0]['frequency_weeks'])->toBe(4)
                ->and($programExercise->progression_rules[0]['deload_percentage'])->toBe(60);
        });

        it('stores weekly undulation rules', function () {
            $user = User::factory()->create(['role' => Role::PT]);
            $program = Program::factory()->create(['owner_id' => $user->id]);
            $version = ProgramVersion::factory()->create(['program_id' => $program->id]);
            $day = ProgramDay::factory()->create(['program_version_id' => $version->id]);
            $exercise = Exercise::factory()->create();

            $progressionRule = [
                'type' => ProgressionRuleType::WeeklyUndulation->value,
                'heavy_percentage' => 100,
                'medium_percentage' => 85,
                'light_percentage' => 70,
            ];

            $programExercise = ProgramDayExercise::create([
                'program_day_id' => $day->id,
                'exercise_id' => $exercise->id,
                'order' => 1,
                'sets' => 3,
                'reps_min' => 5,
                'reps_max' => 5,
                'progression_rules' => [$progressionRule],
            ]);

            expect($programExercise->progression_rules[0]['type'])->toBe(ProgressionRuleType::WeeklyUndulation->value)
                ->and($programExercise->progression_rules[0]['heavy_percentage'])->toBe(100)
                ->and($programExercise->progression_rules[0]['medium_percentage'])->toBe(85)
                ->and($programExercise->progression_rules[0]['light_percentage'])->toBe(70);
        });

        it('stores custom warmup rules', function () {
            $user = User::factory()->create(['role' => Role::PT]);
            $program = Program::factory()->create(['owner_id' => $user->id]);
            $version = ProgramVersion::factory()->create(['program_id' => $program->id]);
            $day = ProgramDay::factory()->create(['program_version_id' => $version->id]);
            $exercise = Exercise::factory()->create();

            $progressionRule = [
                'type' => ProgressionRuleType::CustomWarmup->value,
                'warmup_sets' => [
                    ['reps' => 10, 'percentage' => 40],
                    ['reps' => 5, 'percentage' => 60],
                    ['reps' => 3, 'percentage' => 80],
                ],
            ];

            $programExercise = ProgramDayExercise::create([
                'program_day_id' => $day->id,
                'exercise_id' => $exercise->id,
                'order' => 1,
                'sets' => 3,
                'reps_min' => 5,
                'reps_max' => 5,
                'progression_rules' => [$progressionRule],
            ]);

            expect($programExercise->progression_rules[0]['type'])->toBe(ProgressionRuleType::CustomWarmup->value)
                ->and($programExercise->progression_rules[0]['warmup_sets'])->toBeArray()
                ->toHaveCount(3)
                ->and($programExercise->progression_rules[0]['warmup_sets'][0]['reps'])->toBe(10)
                ->and($programExercise->progression_rules[0]['warmup_sets'][2]['percentage'])->toBe(80);
        });
    });

    describe('multiple progression rules', function () {
        it('allows multiple rules on a single exercise', function () {
            $user = User::factory()->create(['role' => Role::PT]);
            $program = Program::factory()->create(['owner_id' => $user->id]);
            $version = ProgramVersion::factory()->create(['program_id' => $program->id]);
            $day = ProgramDay::factory()->create(['program_version_id' => $version->id]);
            $exercise = Exercise::factory()->create();

            $rules = [
                [
                    'type' => ProgressionRuleType::LinearProgression->value,
                    'increment' => 2.5,
                    'frequency' => 'per_session',
                ],
                [
                    'type' => ProgressionRuleType::PlannedDeload->value,
                    'frequency_weeks' => 4,
                    'deload_percentage' => 60,
                ],
                [
                    'type' => ProgressionRuleType::CustomWarmup->value,
                    'warmup_sets' => [
                        ['reps' => 10, 'percentage' => 50],
                    ],
                ],
            ];

            $programExercise = ProgramDayExercise::create([
                'program_day_id' => $day->id,
                'exercise_id' => $exercise->id,
                'order' => 1,
                'sets' => 3,
                'reps_min' => 5,
                'reps_max' => 5,
                'progression_rules' => $rules,
            ]);

            expect($programExercise->progression_rules)->toBeArray()
                ->toHaveCount(3)
                ->and($programExercise->progression_rules[0]['type'])->toBe(ProgressionRuleType::LinearProgression->value)
                ->and($programExercise->progression_rules[1]['type'])->toBe(ProgressionRuleType::PlannedDeload->value)
                ->and($programExercise->progression_rules[2]['type'])->toBe(ProgressionRuleType::CustomWarmup->value);
        });

        it('maintains rule order when retrieved', function () {
            $user = User::factory()->create(['role' => Role::PT]);
            $program = Program::factory()->create(['owner_id' => $user->id]);
            $version = ProgramVersion::factory()->create(['program_id' => $program->id]);
            $day = ProgramDay::factory()->create(['program_version_id' => $version->id]);
            $exercise = Exercise::factory()->create();

            $rules = [
                ['type' => ProgressionRuleType::CustomWarmup->value, 'warmup_sets' => []],
                ['type' => ProgressionRuleType::LinearProgression->value, 'increment' => 2.5],
                ['type' => ProgressionRuleType::PlannedDeload->value, 'frequency_weeks' => 4],
            ];

            $programExercise = ProgramDayExercise::create([
                'program_day_id' => $day->id,
                'exercise_id' => $exercise->id,
                'order' => 1,
                'sets' => 3,
                'reps_min' => 5,
                'reps_max' => 5,
                'progression_rules' => $rules,
            ]);

            $programExercise->refresh();

            $retrievedTypes = array_column($programExercise->progression_rules, 'type');

            expect($retrievedTypes)->toBe([
                ProgressionRuleType::CustomWarmup->value,
                ProgressionRuleType::LinearProgression->value,
                ProgressionRuleType::PlannedDeload->value,
            ]);
        });
    });

    describe('updating progression rules', function () {
        it('allows updating existing rules', function () {
            $user = User::factory()->create(['role' => Role::PT]);
            $program = Program::factory()->create(['owner_id' => $user->id]);
            $version = ProgramVersion::factory()->create(['program_id' => $program->id]);
            $day = ProgramDay::factory()->create(['program_version_id' => $version->id]);
            $exercise = Exercise::factory()->create();

            $originalRule = [
                'type' => ProgressionRuleType::LinearProgression->value,
                'increment' => 2.5,
                'frequency' => 'per_session',
            ];

            $programExercise = ProgramDayExercise::create([
                'program_day_id' => $day->id,
                'exercise_id' => $exercise->id,
                'order' => 1,
                'sets' => 3,
                'reps_min' => 5,
                'reps_max' => 5,
                'progression_rules' => [$originalRule],
            ]);

            $updatedRule = [
                'type' => ProgressionRuleType::LinearProgression->value,
                'increment' => 5.0,
                'frequency' => 'per_week',
                'weight_cap' => 120.0,
            ];

            $programExercise->update(['progression_rules' => [$updatedRule]]);
            $programExercise->refresh();

            expect($programExercise->progression_rules[0]['increment'])->toBe(5)
                ->and($programExercise->progression_rules[0]['frequency'])->toBe('per_week')
                ->and($programExercise->progression_rules[0]['weight_cap'])->toBe(120);
        });

        it('allows adding new rules to existing exercises', function () {
            $user = User::factory()->create(['role' => Role::PT]);
            $program = Program::factory()->create(['owner_id' => $user->id]);
            $version = ProgramVersion::factory()->create(['program_id' => $program->id]);
            $day = ProgramDay::factory()->create(['program_version_id' => $version->id]);
            $exercise = Exercise::factory()->create();

            $programExercise = ProgramDayExercise::create([
                'program_day_id' => $day->id,
                'exercise_id' => $exercise->id,
                'order' => 1,
                'sets' => 3,
                'reps_min' => 5,
                'reps_max' => 5,
                'progression_rules' => [],
            ]);

            expect($programExercise->progression_rules)->toBeArray()->toHaveCount(0);

            $newRules = [
                ['type' => ProgressionRuleType::LinearProgression->value, 'increment' => 2.5],
                ['type' => ProgressionRuleType::PlannedDeload->value, 'frequency_weeks' => 4],
            ];

            $programExercise->update(['progression_rules' => $newRules]);
            $programExercise->refresh();

            expect($programExercise->progression_rules)->toHaveCount(2);
        });

        it('allows removing all rules', function () {
            $user = User::factory()->create(['role' => Role::PT]);
            $program = Program::factory()->create(['owner_id' => $user->id]);
            $version = ProgramVersion::factory()->create(['program_id' => $program->id]);
            $day = ProgramDay::factory()->create(['program_version_id' => $version->id]);
            $exercise = Exercise::factory()->create();

            $programExercise = ProgramDayExercise::create([
                'program_day_id' => $day->id,
                'exercise_id' => $exercise->id,
                'order' => 1,
                'sets' => 3,
                'reps_min' => 5,
                'reps_max' => 5,
                'progression_rules' => [
                    ['type' => ProgressionRuleType::LinearProgression->value, 'increment' => 2.5],
                ],
            ]);

            expect($programExercise->progression_rules)->toHaveCount(1);

            $programExercise->update(['progression_rules' => []]);
            $programExercise->refresh();

            expect($programExercise->progression_rules)->toBeArray()->toHaveCount(0);
        });
    });

    describe('rule type validation', function () {
        it('validates progression rule type enum values', function () {
            $allTypes = [
                ProgressionRuleType::LinearProgression,
                ProgressionRuleType::DoubleProgression,
                ProgressionRuleType::TopSetBackoff,
                ProgressionRuleType::RpeTarget,
                ProgressionRuleType::PlannedDeload,
                ProgressionRuleType::WeeklyUndulation,
                ProgressionRuleType::CustomWarmup,
            ];

            expect($allTypes)->toHaveCount(7);

            foreach ($allTypes as $type) {
                expect($type)->toBeInstanceOf(ProgressionRuleType::class)
                    ->and($type->label())->toBeString()
                    ->and($type->description())->toBeString();
            }
        });
    });
});
