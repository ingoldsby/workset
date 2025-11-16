<?php

use App\Enums\Role;
use App\Models\Invite;
use App\Models\PtAssignment;
use App\Models\User;

describe('Invite Acceptance', function () {
    it('displays the invite acceptance form for valid invites', function () {
        $invite = Invite::factory()->create();

        $response = $this->get(route('invite.accept', $invite->token));

        $response
            ->assertOk()
            ->assertViewIs('auth.accept-invite')
            ->assertViewHas('invite', fn ($viewInvite) => $viewInvite->id === $invite->id);
    });

    it('redirects to login for invalid invite tokens', function () {
        $response = $this->get(route('invite.accept', 'invalid-token'));

        $response
            ->assertRedirect(route('login'))
            ->assertSessionHas('error');
    });

    it('redirects to login for already accepted invites', function () {
        $invite = Invite::factory()->accepted()->create();

        $response = $this->get(route('invite.accept', $invite->token));

        $response
            ->assertRedirect(route('login'))
            ->assertSessionHas('error');
    });

    it('redirects to login for expired invites', function () {
        $invite = Invite::factory()->expired()->create();

        $response = $this->get(route('invite.accept', $invite->token));

        $response
            ->assertRedirect(route('login'))
            ->assertSessionHas('error');
    });

    it('creates a user account when accepting a valid invite', function () {
        $invite = Invite::factory()->create([
            'email' => 'newuser@example.com',
            'role' => Role::Member,
        ]);

        $response = $this->post(route('invite.store', $invite->token), [
            'name' => 'New User',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));

        $user = User::where('email', 'newuser@example.com')->first();

        expect($user)
            ->not->toBeNull()
            ->and($user->name)->toBe('New User')
            ->and($user->role)->toBe(Role::Member);

        $invite->refresh();
        expect($invite->accepted_at)->not->toBeNull();

        $this->assertAuthenticatedAs($user);
    });

    it('assigns role from invite to new user', function () {
        $invite = Invite::factory()->create([
            'email' => 'pt@example.com',
            'role' => Role::PT,
        ]);

        $this->post(route('invite.store', $invite->token), [
            'name' => 'PT User',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'pt@example.com')->first();

        expect($user->role)->toBe(Role::PT)
            ->and($user->isPt())->toBeTrue();
    });

    it('creates PT assignment when invite includes a PT', function () {
        $pt = User::factory()->create(['role' => Role::PT]);

        $invite = Invite::factory()->create([
            'email' => 'member@example.com',
            'role' => Role::Member,
            'pt_id' => $pt->id,
        ]);

        $this->post(route('invite.store', $invite->token), [
            'name' => 'Member User',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $member = User::where('email', 'member@example.com')->first();

        $assignment = PtAssignment::where('member_id', $member->id)
            ->where('pt_id', $pt->id)
            ->first();

        expect($assignment)
            ->not->toBeNull()
            ->and($assignment->assigned_at)->not->toBeNull();
    });

    it('validates required fields when accepting invite', function () {
        $invite = Invite::factory()->create();

        $response = $this->post(route('invite.store', $invite->token), [
            'name' => '',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'password']);
    });

    it('validates password confirmation when accepting invite', function () {
        $invite = Invite::factory()->create();

        $response = $this->post(route('invite.store', $invite->token), [
            'name' => 'Test User',
            'password' => 'password123',
            'password_confirmation' => 'different',
        ]);

        $response->assertSessionHasErrors(['password']);
    });

    it('prevents accepting the same invite twice', function () {
        $invite = Invite::factory()->create(['email' => 'test@example.com']);

        // First acceptance
        $this->post(route('invite.store', $invite->token), [
            'name' => 'First User',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // Logout
        auth()->logout();

        // Try to accept again
        $response = $this->post(route('invite.store', $invite->token), [
            'name' => 'Second User',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response
            ->assertRedirect(route('login'))
            ->assertSessionHas('error');

        expect(User::where('email', 'test@example.com')->count())->toBe(1);
    });

    it('sets custom timezone when provided during invite acceptance', function () {
        $invite = Invite::factory()->create(['email' => 'timezone@example.com']);

        $this->post(route('invite.store', $invite->token), [
            'name' => 'Timezone User',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'timezone' => 'America/New_York',
        ]);

        $user = User::where('email', 'timezone@example.com')->first();

        expect($user->timezone)->toBe('America/New_York');
    });
});
