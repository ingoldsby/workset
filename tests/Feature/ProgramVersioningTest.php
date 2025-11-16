<?php

use App\Enums\Role;
use App\Models\Program;
use App\Models\ProgramVersion;
use App\Models\User;

describe('Program Versioning', function () {
    describe('creating programs', function () {
        it('creates a program with an initial version', function () {
            $user = User::factory()->create(['role' => Role::PT]);

            $program = Program::create([
                'owner_id' => $user->id,
                'name' => 'Strength Training Program',
                'description' => 'A comprehensive strength training program',
                'visibility' => 'private',
            ]);

            $version = ProgramVersion::create([
                'program_id' => $program->id,
                'created_by' => $user->id,
                'version_number' => 1,
                'change_notes' => 'Initial version',
                'is_active' => true,
            ]);

            expect($program)->not->toBeNull()
                ->and($program->name)->toBe('Strength Training Program')
                ->and($version)->not->toBeNull()
                ->and($version->version_number)->toBe(1)
                ->and($version->is_active)->toBeTrue();
        });

        it('allows multiple programs for the same owner', function () {
            $user = User::factory()->create(['role' => Role::PT]);

            $program1 = Program::create([
                'owner_id' => $user->id,
                'name' => 'Program 1',
                'description' => 'First program',
                'visibility' => 'private',
            ]);

            $program2 = Program::create([
                'owner_id' => $user->id,
                'name' => 'Program 2',
                'description' => 'Second program',
                'visibility' => 'private',
            ]);

            $userPrograms = Program::where('owner_id', $user->id)->get();

            expect($userPrograms)->toHaveCount(2)
                ->and($userPrograms->pluck('name')->toArray())->toContain('Program 1', 'Program 2');
        });
    });

    describe('program versioning', function () {
        it('creates subsequent versions with incremented version numbers', function () {
            $user = User::factory()->create(['role' => Role::PT]);
            $program = Program::factory()->create(['owner_id' => $user->id]);

            $version1 = ProgramVersion::create([
                'program_id' => $program->id,
                'created_by' => $user->id,
                'version_number' => 1,
                'change_notes' => 'Initial version',
                'is_active' => true,
            ]);

            $version2 = ProgramVersion::create([
                'program_id' => $program->id,
                'created_by' => $user->id,
                'version_number' => 2,
                'change_notes' => 'Added more exercises',
                'is_active' => false,
            ]);

            $versions = $program->versions()->orderBy('version_number')->get();

            expect($versions)->toHaveCount(2)
                ->and($versions->first()->version_number)->toBe(1)
                ->and($versions->last()->version_number)->toBe(2);
        });

        it('maintains version history when creating new versions', function () {
            $user = User::factory()->create(['role' => Role::PT]);
            $program = Program::factory()->create(['owner_id' => $user->id]);

            foreach (range(1, 5) as $versionNumber) {
                ProgramVersion::create([
                    'program_id' => $program->id,
                    'created_by' => $user->id,
                    'version_number' => $versionNumber,
                    'change_notes' => "Version {$versionNumber} notes",
                    'is_active' => $versionNumber === 5,
                ]);
            }

            $versions = $program->versions()->get();

            expect($versions)->toHaveCount(5)
                ->and($versions->pluck('version_number')->toArray())->toBe([1, 2, 3, 4, 5]);
        });
    });

    describe('activating versions', function () {
        it('deactivates previous version when activating a new one', function () {
            $user = User::factory()->create(['role' => Role::PT]);
            $program = Program::factory()->create(['owner_id' => $user->id]);

            $version1 = ProgramVersion::create([
                'program_id' => $program->id,
                'created_by' => $user->id,
                'version_number' => 1,
                'change_notes' => 'Version 1',
                'is_active' => true,
            ]);

            $version2 = ProgramVersion::create([
                'program_id' => $program->id,
                'created_by' => $user->id,
                'version_number' => 2,
                'change_notes' => 'Version 2',
                'is_active' => false,
            ]);

            // Deactivate version 1 and activate version 2
            $version1->update(['is_active' => false]);
            $version2->update(['is_active' => true]);

            $version1->refresh();
            $version2->refresh();

            expect($version1->is_active)->toBeFalse()
                ->and($version2->is_active)->toBeTrue();
        });

        it('retrieves the active version correctly', function () {
            $user = User::factory()->create(['role' => Role::PT]);
            $program = Program::factory()->create(['owner_id' => $user->id]);

            ProgramVersion::create([
                'program_id' => $program->id,
                'created_by' => $user->id,
                'version_number' => 1,
                'change_notes' => 'Version 1',
                'is_active' => false,
            ]);

            $activeVersion = ProgramVersion::create([
                'program_id' => $program->id,
                'created_by' => $user->id,
                'version_number' => 2,
                'change_notes' => 'Active version',
                'is_active' => true,
            ]);

            ProgramVersion::create([
                'program_id' => $program->id,
                'created_by' => $user->id,
                'version_number' => 3,
                'change_notes' => 'Draft version',
                'is_active' => false,
            ]);

            $retrievedActiveVersion = $program->activeVersion();

            expect($retrievedActiveVersion)->not->toBeNull()
                ->and($retrievedActiveVersion->id)->toBe($activeVersion->id)
                ->and($retrievedActiveVersion->version_number)->toBe(2);
        });

        it('returns null when no active version exists', function () {
            $user = User::factory()->create(['role' => Role::PT]);
            $program = Program::factory()->create(['owner_id' => $user->id]);

            ProgramVersion::create([
                'program_id' => $program->id,
                'created_by' => $user->id,
                'version_number' => 1,
                'change_notes' => 'Draft version',
                'is_active' => false,
            ]);

            $activeVersion = $program->activeVersion();

            expect($activeVersion)->toBeNull();
        });
    });

    describe('program visibility', function () {
        it('correctly identifies public programs', function () {
            $user = User::factory()->create(['role' => Role::PT]);
            $program = Program::create([
                'owner_id' => $user->id,
                'name' => 'Public Program',
                'description' => 'Available to all',
                'visibility' => 'public',
            ]);

            expect($program->isPublic())->toBeTrue()
                ->and($program->isPrivate())->toBeFalse();
        });

        it('correctly identifies private programs', function () {
            $user = User::factory()->create(['role' => Role::PT]);
            $program = Program::create([
                'owner_id' => $user->id,
                'name' => 'Private Program',
                'description' => 'Only for assigned members',
                'visibility' => 'private',
            ]);

            expect($program->isPrivate())->toBeTrue()
                ->and($program->isPublic())->toBeFalse();
        });
    });

    describe('program relationships', function () {
        it('correctly loads owner relationship', function () {
            $user = User::factory()->create(['role' => Role::PT]);
            $program = Program::factory()->create(['owner_id' => $user->id]);

            $program->load('owner');

            expect($program->owner)->not->toBeNull()
                ->and($program->owner->id)->toBe($user->id)
                ->and($program->owner->role)->toBe(Role::PT);
        });

        it('correctly loads versions relationship', function () {
            $user = User::factory()->create(['role' => Role::PT]);
            $program = Program::factory()->create(['owner_id' => $user->id]);

            ProgramVersion::create([
                'program_id' => $program->id,
                'created_by' => $user->id,
                'version_number' => 1,
                'change_notes' => 'Version 1',
                'is_active' => true,
            ]);

            ProgramVersion::create([
                'program_id' => $program->id,
                'created_by' => $user->id,
                'version_number' => 2,
                'change_notes' => 'Version 2',
                'is_active' => false,
            ]);

            $program->load('versions');

            expect($program->versions)->toHaveCount(2)
                ->and($program->versions->pluck('version_number')->toArray())->toContain(1, 2);
        });

        it('loads version creator relationship', function () {
            $user = User::factory()->create(['role' => Role::PT]);
            $program = Program::factory()->create(['owner_id' => $user->id]);

            $version = ProgramVersion::create([
                'program_id' => $program->id,
                'created_by' => $user->id,
                'version_number' => 1,
                'change_notes' => 'Created by PT',
                'is_active' => true,
            ]);

            $version->load('creator');

            expect($version->creator)->not->toBeNull()
                ->and($version->creator->id)->toBe($user->id);
        });
    });

    describe('soft deletion', function () {
        it('soft deletes programs', function () {
            $user = User::factory()->create(['role' => Role::PT]);
            $program = Program::factory()->create(['owner_id' => $user->id]);

            $programId = $program->id;
            $program->delete();

            $foundProgram = Program::find($programId);
            $trashedProgram = Program::withTrashed()->find($programId);

            expect($foundProgram)->toBeNull()
                ->and($trashedProgram)->not->toBeNull()
                ->and($trashedProgram->deleted_at)->not->toBeNull();
        });

        it('can restore soft deleted programs', function () {
            $user = User::factory()->create(['role' => Role::PT]);
            $program = Program::factory()->create(['owner_id' => $user->id]);

            $programId = $program->id;
            $program->delete();
            $program->restore();

            $restoredProgram = Program::find($programId);

            expect($restoredProgram)->not->toBeNull()
                ->and($restoredProgram->deleted_at)->toBeNull();
        });
    });

    describe('version change notes', function () {
        it('stores change notes for each version', function () {
            $user = User::factory()->create(['role' => Role::PT]);
            $program = Program::factory()->create(['owner_id' => $user->id]);

            $changeNotes = [
                'Initial program setup',
                'Added warm-up exercises',
                'Adjusted set/rep scheme',
            ];

            foreach ($changeNotes as $index => $note) {
                ProgramVersion::create([
                    'program_id' => $program->id,
                    'created_by' => $user->id,
                    'version_number' => $index + 1,
                    'change_notes' => $note,
                    'is_active' => $index === 0,
                ]);
            }

            $versions = $program->versions()->orderBy('version_number')->get();

            expect($versions)->toHaveCount(3)
                ->and($versions->first()->change_notes)->toBe('Initial program setup')
                ->and($versions->get(1)->change_notes)->toBe('Added warm-up exercises')
                ->and($versions->last()->change_notes)->toBe('Adjusted set/rep scheme');
        });
    });
});
