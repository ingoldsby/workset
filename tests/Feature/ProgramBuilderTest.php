<?php

use App\Models\Exercise;
use App\Models\Program;
use App\Models\ProgramDay;
use App\Models\ProgramDayExercise;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

describe('Program Builder UI', function () {
    it('displays the create program page for authenticated users', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('programs.create'))
            ->assertOk()
            ->assertSeeLivewire('programs.program-builder');
    });

    it('redirects unauthenticated users to login', function () {
        $this->get(route('programs.create'))
            ->assertRedirect(route('login'));
    });

    it('can create a new program with basic details', function () {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('programs.program-builder')
            ->set('name', 'Test Program')
            ->set('description', 'A test program')
            ->set('visibility', 'private')
            ->set('category', 'Strength')
            ->call('saveProgram')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('programs', [
            'name' => 'Test Program',
            'description' => 'A test program',
            'visibility' => 'private',
            'category' => 'Strength',
            'owner_id' => $user->id,
        ]);
    });

    it('validates required fields when creating a program', function () {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('programs.program-builder')
            ->set('name', '')
            ->call('saveProgram')
            ->assertHasErrors(['name']);
    });
});

describe('Program Days Management', function () {
    it('can add a workout day to a program', function () {
        $user = User::factory()->create();
        $program = Program::factory()->create(['owner_id' => $user->id]);

        Livewire::actingAs($user)
            ->test('programs.program-builder', ['program' => $program])
            ->call('openAddDayModal')
            ->assertSet('showAddDayModal', true)
            ->set('dayName', 'Upper Body Day')
            ->set('dayDescription', 'Focus on chest and back')
            ->set('dayNumber', 1)
            ->set('restDaysAfter', 1)
            ->call('saveDay')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('program_days', [
            'name' => 'Upper Body Day',
            'description' => 'Focus on chest and back',
            'day_number' => 1,
            'rest_days_after' => 1,
        ]);
    });

    it('validates day fields when adding a workout day', function () {
        $user = User::factory()->create();
        $program = Program::factory()->create(['owner_id' => $user->id]);

        Livewire::actingAs($user)
            ->test('programs.program-builder', ['program' => $program])
            ->set('dayName', '')
            ->call('saveDay')
            ->assertHasErrors(['dayName']);
    });

    it('can edit an existing workout day', function () {
        $user = User::factory()->create();
        $program = Program::factory()->create(['owner_id' => $user->id]);
        $version = $program->activeVersion;
        $day = ProgramDay::factory()->create([
            'program_version_id' => $version->id,
            'name' => 'Old Name',
            'day_number' => 1,
        ]);

        Livewire::actingAs($user)
            ->test('programs.program-builder', ['program' => $program])
            ->call('openEditDayModal', $day->id)
            ->assertSet('editingDayId', $day->id)
            ->assertSet('dayName', 'Old Name')
            ->set('dayName', 'Updated Name')
            ->call('saveDay')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('program_days', [
            'id' => $day->id,
            'name' => 'Updated Name',
        ]);
    });

    it('can delete a workout day', function () {
        $user = User::factory()->create();
        $program = Program::factory()->create(['owner_id' => $user->id]);
        $version = $program->activeVersion;
        $day = ProgramDay::factory()->create([
            'program_version_id' => $version->id,
        ]);

        Livewire::actingAs($user)
            ->test('programs.program-builder', ['program' => $program])
            ->call('deleteDay', $day->id);

        $this->assertDatabaseMissing('program_days', [
            'id' => $day->id,
        ]);
    });

    it('can reorder workout days', function () {
        $user = User::factory()->create();
        $program = Program::factory()->create(['owner_id' => $user->id]);
        $version = $program->activeVersion;

        $day1 = ProgramDay::factory()->create([
            'program_version_id' => $version->id,
            'day_number' => 1,
        ]);

        $day2 = ProgramDay::factory()->create([
            'program_version_id' => $version->id,
            'day_number' => 2,
        ]);

        Livewire::actingAs($user)
            ->test('programs.program-builder', ['program' => $program])
            ->call('moveDay', $day2->id, 'up');

        $day1->refresh();
        $day2->refresh();

        expect($day1->day_number)->toBe(2)
            ->and($day2->day_number)->toBe(1);
    });
});

describe('Exercise Management', function () {
    it('can add an exercise to a workout day', function () {
        $user = User::factory()->create();
        $program = Program::factory()->create(['owner_id' => $user->id]);
        $version = $program->activeVersion;
        $day = ProgramDay::factory()->create(['program_version_id' => $version->id]);
        $exercise = Exercise::factory()->create(['name' => 'Bench Press']);

        Livewire::actingAs($user)
            ->test('programs.program-builder', ['program' => $program])
            ->call('openAddExerciseModal', $day->id)
            ->assertSet('showAddExerciseModal', true)
            ->assertSet('selectedDayId', $day->id)
            ->set('selectedExerciseId', $exercise->id)
            ->set('sets', 4)
            ->set('repsMin', 8)
            ->set('repsMax', 12)
            ->set('rpe', 8)
            ->set('restSeconds', 120)
            ->set('tempo', '3-0-1-0')
            ->set('exerciseNotes', 'Focus on form')
            ->call('saveExercise')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('program_day_exercises', [
            'program_day_id' => $day->id,
            'exercise_id' => $exercise->id,
            'sets' => 4,
            'reps_min' => 8,
            'reps_max' => 12,
            'rpe' => 8,
            'rest_seconds' => 120,
            'tempo' => '3-0-1-0',
            'notes' => 'Focus on form',
        ]);
    });

    it('validates exercise fields when adding an exercise', function () {
        $user = User::factory()->create();
        $program = Program::factory()->create(['owner_id' => $user->id]);
        $version = $program->activeVersion;
        $day = ProgramDay::factory()->create(['program_version_id' => $version->id]);

        Livewire::actingAs($user)
            ->test('programs.program-builder', ['program' => $program])
            ->call('openAddExerciseModal', $day->id)
            ->set('selectedExerciseId', null)
            ->set('sets', 0)
            ->call('saveExercise')
            ->assertHasErrors(['selectedExerciseId', 'sets']);
    });

    it('can delete an exercise from a workout day', function () {
        $user = User::factory()->create();
        $program = Program::factory()->create(['owner_id' => $user->id]);
        $version = $program->activeVersion;
        $day = ProgramDay::factory()->create(['program_version_id' => $version->id]);
        $exercise = Exercise::factory()->create();
        $programExercise = ProgramDayExercise::factory()->create([
            'program_day_id' => $day->id,
            'exercise_id' => $exercise->id,
        ]);

        Livewire::actingAs($user)
            ->test('programs.program-builder', ['program' => $program])
            ->call('deleteExercise', $programExercise->id);

        $this->assertDatabaseMissing('program_day_exercises', [
            'id' => $programExercise->id,
        ]);
    });

    it('can reorder exercises within a workout day', function () {
        $user = User::factory()->create();
        $program = Program::factory()->create(['owner_id' => $user->id]);
        $version = $program->activeVersion;
        $day = ProgramDay::factory()->create(['program_version_id' => $version->id]);
        $exercise1 = Exercise::factory()->create();
        $exercise2 = Exercise::factory()->create();

        $programExercise1 = ProgramDayExercise::factory()->create([
            'program_day_id' => $day->id,
            'exercise_id' => $exercise1->id,
            'order' => 1,
        ]);

        $programExercise2 = ProgramDayExercise::factory()->create([
            'program_day_id' => $day->id,
            'exercise_id' => $exercise2->id,
            'order' => 2,
        ]);

        Livewire::actingAs($user)
            ->test('programs.program-builder', ['program' => $program])
            ->call('moveExercise', $programExercise2->id, 'up');

        $programExercise1->refresh();
        $programExercise2->refresh();

        expect($programExercise1->order)->toBe(2)
            ->and($programExercise2->order)->toBe(1);
    });

    it('correctly assigns exercise order when adding multiple exercises', function () {
        $user = User::factory()->create();
        $program = Program::factory()->create(['owner_id' => $user->id]);
        $version = $program->activeVersion;
        $day = ProgramDay::factory()->create(['program_version_id' => $version->id]);
        $exercise1 = Exercise::factory()->create();
        $exercise2 = Exercise::factory()->create();

        $component = Livewire::actingAs($user)
            ->test('programs.program-builder', ['program' => $program]);

        // Add first exercise
        $component
            ->call('openAddExerciseModal', $day->id)
            ->set('selectedExerciseId', $exercise1->id)
            ->set('sets', 3)
            ->call('saveExercise');

        // Add second exercise
        $component
            ->call('openAddExerciseModal', $day->id)
            ->set('selectedExerciseId', $exercise2->id)
            ->set('sets', 3)
            ->call('saveExercise');

        $exercises = ProgramDayExercise::where('program_day_id', $day->id)
            ->orderBy('order')
            ->get();

        expect($exercises)->toHaveCount(2)
            ->and($exercises[0]->order)->toBe(1)
            ->and($exercises[0]->exercise_id)->toBe($exercise1->id)
            ->and($exercises[1]->order)->toBe(2)
            ->and($exercises[1]->exercise_id)->toBe($exercise2->id);
    });
});

describe('Program Controller', function () {
    it('shows a program to authorized users', function () {
        $user = User::factory()->create();
        $program = Program::factory()->create(['owner_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('programs.show', $program))
            ->assertOk()
            ->assertSee($program->name);
    });

    it('prevents unauthorized users from viewing private programs', function () {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $program = Program::factory()->create([
            'owner_id' => $owner->id,
            'visibility' => 'private',
        ]);

        $this->actingAs($otherUser)
            ->get(route('programs.show', $program))
            ->assertForbidden();
    });

    it('allows any authenticated user to view public programs', function () {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $program = Program::factory()->create([
            'owner_id' => $owner->id,
            'visibility' => 'public',
        ]);

        $this->actingAs($otherUser)
            ->get(route('programs.show', $program))
            ->assertOk()
            ->assertSee($program->name);
    });

    it('can update a program the user owns', function () {
        $user = User::factory()->create();
        $program = Program::factory()->create(['owner_id' => $user->id]);

        $this->actingAs($user)
            ->put(route('programs.update', $program), [
                'name' => 'Updated Program Name',
                'description' => 'Updated description',
                'visibility' => 'public',
            ])
            ->assertRedirect(route('programs.show', $program));

        $program->refresh();

        expect($program->name)->toBe('Updated Program Name')
            ->and($program->description)->toBe('Updated description')
            ->and($program->visibility)->toBe('public');
    });

    it('prevents unauthorized users from updating a program', function () {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $program = Program::factory()->create(['owner_id' => $owner->id]);

        $this->actingAs($otherUser)
            ->put(route('programs.update', $program), [
                'name' => 'Hacked Name',
                'visibility' => 'private',
            ])
            ->assertForbidden();

        $program->refresh();
        expect($program->name)->not->toBe('Hacked Name');
    });

    it('can delete a program the user owns', function () {
        $user = User::factory()->create();
        $program = Program::factory()->create(['owner_id' => $user->id]);

        $this->actingAs($user)
            ->delete(route('programs.destroy', $program))
            ->assertRedirect(route('programs.index'));

        $this->assertSoftDeleted('programs', [
            'id' => $program->id,
        ]);
    });

    it('prevents unauthorized users from deleting a program', function () {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $program = Program::factory()->create(['owner_id' => $owner->id]);

        $this->actingAs($otherUser)
            ->delete(route('programs.destroy', $program))
            ->assertForbidden();

        $this->assertDatabaseHas('programs', [
            'id' => $program->id,
            'deleted_at' => null,
        ]);
    });
});
