<?php

use App\Enums\Role;
use App\Models\Program;
use App\Models\PtAssignment;
use App\Models\TrainingSession;
use App\Models\User;

describe('Program Policy', function () {
    describe('viewing programs', function () {
        it('allows users to view public programs', function () {
            $user = User::factory()->create(['role' => Role::Member]);
            $otherUser = User::factory()->create(['role' => Role::PT]);
            $program = Program::factory()->create([
                'owner_id' => $otherUser->id,
                'visibility' => 'public',
            ]);

            $canView = $user->can('view', $program);

            expect($canView)->toBeTrue();
        });

        it('allows owners to view their own private programs', function () {
            $user = User::factory()->create(['role' => Role::PT]);
            $program = Program::factory()->create([
                'owner_id' => $user->id,
                'visibility' => 'private',
            ]);

            $canView = $user->can('view', $program);

            expect($canView)->toBeTrue();
        });

        it('allows admins to view any program', function () {
            $admin = User::factory()->create(['role' => Role::Admin]);
            $otherUser = User::factory()->create(['role' => Role::PT]);
            $program = Program::factory()->create([
                'owner_id' => $otherUser->id,
                'visibility' => 'private',
            ]);

            $canView = $admin->can('view', $program);

            expect($canView)->toBeTrue();
        });

        it('allows PTs to view programs of their assigned members', function () {
            $pt = User::factory()->create(['role' => Role::PT]);
            $member = User::factory()->create(['role' => Role::Member]);
            $program = Program::factory()->create([
                'owner_id' => $member->id,
                'visibility' => 'private',
            ]);

            PtAssignment::create([
                'pt_id' => $pt->id,
                'member_id' => $member->id,
                'assigned_at' => now(),
            ]);

            $canView = $pt->can('view', $program);

            expect($canView)->toBeTrue();
        });

        it('prevents users from viewing private programs of others', function () {
            $user = User::factory()->create(['role' => Role::Member]);
            $otherUser = User::factory()->create(['role' => Role::PT]);
            $program = Program::factory()->create([
                'owner_id' => $otherUser->id,
                'visibility' => 'private',
            ]);

            $canView = $user->can('view', $program);

            expect($canView)->toBeFalse();
        });
    });

    describe('creating programs', function () {
        it('allows admins to create programs', function () {
            $admin = User::factory()->create(['role' => Role::Admin]);

            $canCreate = $admin->can('create', Program::class);

            expect($canCreate)->toBeTrue();
        });

        it('allows PTs to create programs', function () {
            $pt = User::factory()->create(['role' => Role::PT]);

            $canCreate = $pt->can('create', Program::class);

            expect($canCreate)->toBeTrue();
        });

        it('prevents members from creating programs', function () {
            $member = User::factory()->create(['role' => Role::Member]);

            $canCreate = $member->can('create', Program::class);

            expect($canCreate)->toBeFalse();
        });
    });

    describe('updating programs', function () {
        it('allows owners to update their programs', function () {
            $user = User::factory()->create(['role' => Role::PT]);
            $program = Program::factory()->create(['owner_id' => $user->id]);

            $canUpdate = $user->can('update', $program);

            expect($canUpdate)->toBeTrue();
        });

        it('allows admins to update any program', function () {
            $admin = User::factory()->create(['role' => Role::Admin]);
            $otherUser = User::factory()->create(['role' => Role::PT]);
            $program = Program::factory()->create(['owner_id' => $otherUser->id]);

            $canUpdate = $admin->can('update', $program);

            expect($canUpdate)->toBeTrue();
        });

        it('prevents users from updating programs they do not own', function () {
            $user = User::factory()->create(['role' => Role::PT]);
            $otherUser = User::factory()->create(['role' => Role::PT]);
            $program = Program::factory()->create(['owner_id' => $otherUser->id]);

            $canUpdate = $user->can('update', $program);

            expect($canUpdate)->toBeFalse();
        });
    });

    describe('deleting programs', function () {
        it('allows owners to delete their programs', function () {
            $user = User::factory()->create(['role' => Role::PT]);
            $program = Program::factory()->create(['owner_id' => $user->id]);

            $canDelete = $user->can('delete', $program);

            expect($canDelete)->toBeTrue();
        });

        it('allows admins to delete any program', function () {
            $admin = User::factory()->create(['role' => Role::Admin]);
            $otherUser = User::factory()->create(['role' => Role::PT]);
            $program = Program::factory()->create(['owner_id' => $otherUser->id]);

            $canDelete = $admin->can('delete', $program);

            expect($canDelete)->toBeTrue();
        });

        it('prevents users from deleting programs they do not own', function () {
            $user = User::factory()->create(['role' => Role::PT]);
            $otherUser = User::factory()->create(['role' => Role::PT]);
            $program = Program::factory()->create(['owner_id' => $otherUser->id]);

            $canDelete = $user->can('delete', $program);

            expect($canDelete)->toBeFalse();
        });
    });

    describe('force deleting programs', function () {
        it('allows only admins to force delete programs', function () {
            $admin = User::factory()->create(['role' => Role::Admin]);
            $program = Program::factory()->create(['owner_id' => $admin->id]);

            $canForceDelete = $admin->can('forceDelete', $program);

            expect($canForceDelete)->toBeTrue();
        });

        it('prevents non-admins from force deleting programs', function () {
            $user = User::factory()->create(['role' => Role::PT]);
            $program = Program::factory()->create(['owner_id' => $user->id]);

            $canForceDelete = $user->can('forceDelete', $program);

            expect($canForceDelete)->toBeFalse();
        });
    });
});

describe('Training Session Policy', function () {
    describe('viewing sessions', function () {
        it('allows users to view their own sessions', function () {
            $user = User::factory()->create(['role' => Role::Member]);
            $session = TrainingSession::factory()->create(['user_id' => $user->id]);

            $canView = $user->can('view', $session);

            expect($canView)->toBeTrue();
        });

        it('allows admins to view any session', function () {
            $admin = User::factory()->create(['role' => Role::Admin]);
            $member = User::factory()->create(['role' => Role::Member]);
            $session = TrainingSession::factory()->create(['user_id' => $member->id]);

            $canView = $admin->can('view', $session);

            expect($canView)->toBeTrue();
        });

        it('allows PTs to view sessions of their assigned members', function () {
            $pt = User::factory()->create(['role' => Role::PT]);
            $member = User::factory()->create(['role' => Role::Member]);
            $session = TrainingSession::factory()->create(['user_id' => $member->id]);

            PtAssignment::create([
                'pt_id' => $pt->id,
                'member_id' => $member->id,
                'assigned_at' => now(),
            ]);

            $canView = $pt->can('view', $session);

            expect($canView)->toBeTrue();
        });

        it('prevents users from viewing sessions of others', function () {
            $user = User::factory()->create(['role' => Role::Member]);
            $otherUser = User::factory()->create(['role' => Role::Member]);
            $session = TrainingSession::factory()->create(['user_id' => $otherUser->id]);

            $canView = $user->can('view', $session);

            expect($canView)->toBeFalse();
        });
    });

    describe('creating sessions', function () {
        it('allows all authenticated users to create sessions', function () {
            $member = User::factory()->create(['role' => Role::Member]);
            $pt = User::factory()->create(['role' => Role::PT]);
            $admin = User::factory()->create(['role' => Role::Admin]);

            expect($member->can('create', TrainingSession::class))->toBeTrue()
                ->and($pt->can('create', TrainingSession::class))->toBeTrue()
                ->and($admin->can('create', TrainingSession::class))->toBeTrue();
        });
    });

    describe('updating sessions', function () {
        it('allows users to update their own sessions', function () {
            $user = User::factory()->create(['role' => Role::Member]);
            $session = TrainingSession::factory()->create(['user_id' => $user->id]);

            $canUpdate = $user->can('update', $session);

            expect($canUpdate)->toBeTrue();
        });

        it('allows admins to update any session', function () {
            $admin = User::factory()->create(['role' => Role::Admin]);
            $member = User::factory()->create(['role' => Role::Member]);
            $session = TrainingSession::factory()->create(['user_id' => $member->id]);

            $canUpdate = $admin->can('update', $session);

            expect($canUpdate)->toBeTrue();
        });

        it('allows PTs to update sessions of their assigned members', function () {
            $pt = User::factory()->create(['role' => Role::PT]);
            $member = User::factory()->create(['role' => Role::Member]);
            $session = TrainingSession::factory()->create(['user_id' => $member->id]);

            PtAssignment::create([
                'pt_id' => $pt->id,
                'member_id' => $member->id,
                'assigned_at' => now(),
            ]);

            $canUpdate = $pt->can('update', $session);

            expect($canUpdate)->toBeTrue();
        });

        it('prevents users from updating sessions of others', function () {
            $user = User::factory()->create(['role' => Role::Member]);
            $otherUser = User::factory()->create(['role' => Role::Member]);
            $session = TrainingSession::factory()->create(['user_id' => $otherUser->id]);

            $canUpdate = $user->can('update', $session);

            expect($canUpdate)->toBeFalse();
        });
    });

    describe('deleting sessions', function () {
        it('allows users to delete their own sessions', function () {
            $user = User::factory()->create(['role' => Role::Member]);
            $session = TrainingSession::factory()->create(['user_id' => $user->id]);

            $canDelete = $user->can('delete', $session);

            expect($canDelete)->toBeTrue();
        });

        it('allows admins to delete any session', function () {
            $admin = User::factory()->create(['role' => Role::Admin]);
            $member = User::factory()->create(['role' => Role::Member]);
            $session = TrainingSession::factory()->create(['user_id' => $member->id]);

            $canDelete = $admin->can('delete', $session);

            expect($canDelete)->toBeTrue();
        });

        it('prevents users from deleting sessions of others', function () {
            $user = User::factory()->create(['role' => Role::Member]);
            $otherUser = User::factory()->create(['role' => Role::Member]);
            $session = TrainingSession::factory()->create(['user_id' => $otherUser->id]);

            $canDelete = $user->can('delete', $session);

            expect($canDelete)->toBeFalse();
        });
    });

    describe('force deleting sessions', function () {
        it('allows only admins to force delete sessions', function () {
            $admin = User::factory()->create(['role' => Role::Admin]);
            $session = TrainingSession::factory()->create(['user_id' => $admin->id]);

            $canForceDelete = $admin->can('forceDelete', $session);

            expect($canForceDelete)->toBeTrue();
        });

        it('prevents non-admins from force deleting sessions', function () {
            $user = User::factory()->create(['role' => Role::Member]);
            $session = TrainingSession::factory()->create(['user_id' => $user->id]);

            $canForceDelete = $user->can('forceDelete', $session);

            expect($canForceDelete)->toBeFalse();
        });
    });
});
