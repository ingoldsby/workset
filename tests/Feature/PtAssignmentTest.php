<?php

use App\Enums\Role;
use App\Models\PtAssignment;
use App\Models\User;
use Carbon\Carbon;

describe('PT Assignment Logic', function () {
    describe('creating assignments', function () {
        it('allows admins to create PT assignments', function () {
            $admin = User::factory()->create(['role' => Role::Admin]);
            $pt = User::factory()->create(['role' => Role::PT]);
            $member = User::factory()->create(['role' => Role::Member]);

            $this->actingAs($admin);

            $assignment = PtAssignment::create([
                'pt_id' => $pt->id,
                'member_id' => $member->id,
                'assigned_at' => now(),
            ]);

            expect($assignment)
                ->not->toBeNull()
                ->and($assignment->pt_id)->toBe($pt->id)
                ->and($assignment->member_id)->toBe($member->id)
                ->and($assignment->assigned_at)->toBeInstanceOf(Carbon::class)
                ->and($assignment->unassigned_at)->toBeNull();
        });

        it('allows PTs to create assignments to themselves', function () {
            $pt = User::factory()->create(['role' => Role::PT]);
            $member = User::factory()->create(['role' => Role::Member]);

            $this->actingAs($pt);

            $assignment = PtAssignment::create([
                'pt_id' => $pt->id,
                'member_id' => $member->id,
                'assigned_at' => now(),
            ]);

            expect($assignment)
                ->not->toBeNull()
                ->and($assignment->isActive())->toBeTrue();
        });

        it('prevents duplicate active assignments for the same PT-member pair', function () {
            $pt = User::factory()->create(['role' => Role::PT]);
            $member = User::factory()->create(['role' => Role::Member]);

            PtAssignment::create([
                'pt_id' => $pt->id,
                'member_id' => $member->id,
                'assigned_at' => now(),
            ]);

            // Attempt to create duplicate should fail validation
            $this->expectException(\Illuminate\Database\QueryException::class);

            PtAssignment::create([
                'pt_id' => $pt->id,
                'member_id' => $member->id,
                'assigned_at' => now(),
            ]);
        });
    });

    describe('active assignment checks', function () {
        it('correctly identifies active assignments', function () {
            $pt = User::factory()->create(['role' => Role::PT]);
            $member = User::factory()->create(['role' => Role::Member]);

            $assignment = PtAssignment::create([
                'pt_id' => $pt->id,
                'member_id' => $member->id,
                'assigned_at' => now(),
            ]);

            expect($assignment->isActive())->toBeTrue();
        });

        it('correctly identifies inactive assignments', function () {
            $pt = User::factory()->create(['role' => Role::PT]);
            $member = User::factory()->create(['role' => Role::Member]);

            $assignment = PtAssignment::create([
                'pt_id' => $pt->id,
                'member_id' => $member->id,
                'assigned_at' => now()->subDays(10),
                'unassigned_at' => now()->subDays(2),
            ]);

            expect($assignment->isActive())->toBeFalse();
        });

        it('uses active scope to filter assignments', function () {
            $pt = User::factory()->create(['role' => Role::PT]);
            $member1 = User::factory()->create(['role' => Role::Member]);
            $member2 = User::factory()->create(['role' => Role::Member]);

            // Active assignment
            PtAssignment::create([
                'pt_id' => $pt->id,
                'member_id' => $member1->id,
                'assigned_at' => now(),
            ]);

            // Inactive assignment
            PtAssignment::create([
                'pt_id' => $pt->id,
                'member_id' => $member2->id,
                'assigned_at' => now()->subDays(10),
                'unassigned_at' => now()->subDays(2),
            ]);

            $activeAssignments = PtAssignment::active()->get();

            expect($activeAssignments)->toHaveCount(1)
                ->and($activeAssignments->first()->member_id)->toBe($member1->id);
        });
    });

    describe('unassigning members', function () {
        it('allows admins to unassign members from PTs', function () {
            $admin = User::factory()->create(['role' => Role::Admin]);
            $pt = User::factory()->create(['role' => Role::PT]);
            $member = User::factory()->create(['role' => Role::Member]);

            $assignment = PtAssignment::create([
                'pt_id' => $pt->id,
                'member_id' => $member->id,
                'assigned_at' => now(),
            ]);

            $this->actingAs($admin);

            $assignment->update(['unassigned_at' => now()]);

            $assignment->refresh();

            expect($assignment->unassigned_at)->toBeInstanceOf(Carbon::class)
                ->and($assignment->isActive())->toBeFalse();
        });

        it('allows PTs to unassign their own members', function () {
            $pt = User::factory()->create(['role' => Role::PT]);
            $member = User::factory()->create(['role' => Role::Member]);

            $assignment = PtAssignment::create([
                'pt_id' => $pt->id,
                'member_id' => $member->id,
                'assigned_at' => now(),
            ]);

            $this->actingAs($pt);

            $assignment->update(['unassigned_at' => now()]);

            expect($assignment->refresh()->isActive())->toBeFalse();
        });
    });

    describe('relationship access', function () {
        it('correctly loads PT relationship', function () {
            $pt = User::factory()->create(['role' => Role::PT]);
            $member = User::factory()->create(['role' => Role::Member]);

            $assignment = PtAssignment::create([
                'pt_id' => $pt->id,
                'member_id' => $member->id,
                'assigned_at' => now(),
            ]);

            $assignment->load('pt');

            expect($assignment->pt)->not->toBeNull()
                ->and($assignment->pt->id)->toBe($pt->id)
                ->and($assignment->pt->role)->toBe(Role::PT);
        });

        it('correctly loads member relationship', function () {
            $pt = User::factory()->create(['role' => Role::PT]);
            $member = User::factory()->create(['role' => Role::Member]);

            $assignment = PtAssignment::create([
                'pt_id' => $pt->id,
                'member_id' => $member->id,
                'assigned_at' => now(),
            ]);

            $assignment->load('member');

            expect($assignment->member)->not->toBeNull()
                ->and($assignment->member->id)->toBe($member->id)
                ->and($assignment->member->role)->toBe(Role::Member);
        });
    });

    describe('PT member access', function () {
        it('allows PTs to view their assigned members', function () {
            $pt = User::factory()->create(['role' => Role::PT]);
            $member1 = User::factory()->create(['role' => Role::Member]);
            $member2 = User::factory()->create(['role' => Role::Member]);
            $otherPt = User::factory()->create(['role' => Role::PT]);
            $otherMember = User::factory()->create(['role' => Role::Member]);

            // Create assignments for this PT
            PtAssignment::create([
                'pt_id' => $pt->id,
                'member_id' => $member1->id,
                'assigned_at' => now(),
            ]);

            PtAssignment::create([
                'pt_id' => $pt->id,
                'member_id' => $member2->id,
                'assigned_at' => now(),
            ]);

            // Create assignment for other PT
            PtAssignment::create([
                'pt_id' => $otherPt->id,
                'member_id' => $otherMember->id,
                'assigned_at' => now(),
            ]);

            $myMembers = PtAssignment::active()
                ->where('pt_id', $pt->id)
                ->get();

            expect($myMembers)->toHaveCount(2)
                ->and($myMembers->pluck('member_id')->toArray())->toContain($member1->id, $member2->id)
                ->and($myMembers->pluck('member_id')->toArray())->not->toContain($otherMember->id);
        });

        it('prevents PTs from accessing other PTs members without permission', function () {
            $pt1 = User::factory()->create(['role' => Role::PT]);
            $pt2 = User::factory()->create(['role' => Role::PT]);
            $member = User::factory()->create(['role' => Role::Member]);

            PtAssignment::create([
                'pt_id' => $pt2->id,
                'member_id' => $member->id,
                'assigned_at' => now(),
            ]);

            $pt1Assignments = PtAssignment::active()
                ->where('pt_id', $pt1->id)
                ->get();

            expect($pt1Assignments)->toHaveCount(0);
        });
    });

    describe('reassignment scenarios', function () {
        it('allows reassigning a member from one PT to another', function () {
            $pt1 = User::factory()->create(['role' => Role::PT]);
            $pt2 = User::factory()->create(['role' => Role::PT]);
            $member = User::factory()->create(['role' => Role::Member]);

            // Initial assignment to PT1
            $assignment1 = PtAssignment::create([
                'pt_id' => $pt1->id,
                'member_id' => $member->id,
                'assigned_at' => now(),
            ]);

            // Unassign from PT1
            $assignment1->update(['unassigned_at' => now()]);

            // Assign to PT2
            $assignment2 = PtAssignment::create([
                'pt_id' => $pt2->id,
                'member_id' => $member->id,
                'assigned_at' => now(),
            ]);

            expect($assignment1->refresh()->isActive())->toBeFalse()
                ->and($assignment2->isActive())->toBeTrue()
                ->and(PtAssignment::active()->where('member_id', $member->id)->count())->toBe(1)
                ->and(PtAssignment::active()->where('member_id', $member->id)->first()->pt_id)->toBe($pt2->id);
        });
    });
});
