<?php

use App\Enums\Role;
use App\Mail\InviteMail;
use App\Models\Invite;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();
});

describe('Invite Management', function () {
    describe('viewing invites', function () {
        it('allows admins to view all invites', function () {
            $admin = User::factory()->create(['role' => Role::Admin]);
            $otherUser = User::factory()->create(['role' => Role::PT]);

            Invite::factory()->create(['invited_by' => $admin->id]);
            Invite::factory()->create(['invited_by' => $otherUser->id]);

            $response = $this->actingAs($admin)
                ->get(route('invites.index'));

            $response
                ->assertOk()
                ->assertViewHas('invites', fn ($invites) => $invites->count() === 2);
        });

        it('allows PTs to view only their own invites', function () {
            $pt = User::factory()->create(['role' => Role::PT]);
            $otherPt = User::factory()->create(['role' => Role::PT]);

            Invite::factory()->create(['invited_by' => $pt->id]);
            Invite::factory()->create(['invited_by' => $otherPt->id]);

            $response = $this->actingAs($pt)
                ->get(route('invites.index'));

            $response
                ->assertOk()
                ->assertViewHas('invites', fn ($invites) => $invites->count() === 1);
        });

        it('prevents members from viewing invites', function () {
            $member = User::factory()->create(['role' => Role::Member]);

            $response = $this->actingAs($member)
                ->get(route('invites.index'));

            $response->assertForbidden();
        });
    });

    describe('creating invites', function () {
        it('allows admins to send invites', function () {
            $admin = User::factory()->create(['role' => Role::Admin]);

            $response = $this->actingAs($admin)
                ->post(route('invites.store'), [
                    'email' => 'newuser@example.com',
                    'role' => Role::Member->value,
                ]);

            $response->assertRedirect(route('invites.index'));

            expect(Invite::where('email', 'newuser@example.com')->exists())->toBeTrue();

            Mail::assertSent(InviteMail::class, function ($mail) {
                return $mail->hasTo('newuser@example.com');
            });
        });

        it('allows PTs to send invites', function () {
            $pt = User::factory()->create(['role' => Role::PT]);

            $response = $this->actingAs($pt)
                ->post(route('invites.store'), [
                    'email' => 'newmember@example.com',
                    'role' => Role::Member->value,
                    'pt_id' => $pt->id,
                ]);

            $response->assertRedirect(route('invites.index'));

            $invite = Invite::where('email', 'newmember@example.com')->first();

            expect($invite)
                ->not->toBeNull()
                ->and($invite->pt_id)->toBe($pt->id)
                ->and($invite->role)->toBe(Role::Member);
        });

        it('prevents members from sending invites', function () {
            $member = User::factory()->create(['role' => Role::Member]);

            $response = $this->actingAs($member)
                ->post(route('invites.store'), [
                    'email' => 'test@example.com',
                    'role' => Role::Member->value,
                ]);

            $response->assertForbidden();
        });

        it('prevents duplicate invites to the same email', function () {
            $admin = User::factory()->create(['role' => Role::Admin]);

            Invite::factory()->create([
                'email' => 'existing@example.com',
                'invited_by' => $admin->id,
            ]);

            $response = $this->actingAs($admin)
                ->post(route('invites.store'), [
                    'email' => 'existing@example.com',
                    'role' => Role::Member->value,
                ]);

            $response->assertSessionHasErrors('email');
        });

        it('prevents invites to already registered email addresses', function () {
            $admin = User::factory()->create(['role' => Role::Admin]);
            User::factory()->create(['email' => 'registered@example.com']);

            $response = $this->actingAs($admin)
                ->post(route('invites.store'), [
                    'email' => 'registered@example.com',
                    'role' => Role::Member->value,
                ]);

            $response->assertSessionHasErrors('email');
        });
    });

    describe('deleting invites', function () {
        it('allows admins to delete pending invites', function () {
            $admin = User::factory()->create(['role' => Role::Admin]);
            $invite = Invite::factory()->create();

            $response = $this->actingAs($admin)
                ->delete(route('invites.destroy', $invite));

            $response->assertRedirect(route('invites.index'));

            expect(Invite::find($invite->id))->toBeNull();
        });

        it('allows PTs to delete their own pending invites', function () {
            $pt = User::factory()->create(['role' => Role::PT]);
            $invite = Invite::factory()->create(['invited_by' => $pt->id]);

            $response = $this->actingAs($pt)
                ->delete(route('invites.destroy', $invite));

            $response->assertRedirect(route('invites.index'));

            expect(Invite::find($invite->id))->toBeNull();
        });

        it('prevents PTs from deleting other PTs invites', function () {
            $pt = User::factory()->create(['role' => Role::PT]);
            $otherPt = User::factory()->create(['role' => Role::PT]);
            $invite = Invite::factory()->create(['invited_by' => $otherPt->id]);

            $response = $this->actingAs($pt)
                ->delete(route('invites.destroy', $invite));

            $response->assertForbidden();
        });
    });

    describe('resending invites', function () {
        it('allows resending pending invites', function () {
            $admin = User::factory()->create(['role' => Role::Admin]);
            $invite = Invite::factory()->create(['invited_by' => $admin->id]);

            $response = $this->actingAs($admin)
                ->post(route('invites.resend', $invite));

            $response->assertRedirect(route('invites.index'));

            Mail::assertSent(InviteMail::class, function ($mail) use ($invite) {
                return $mail->hasTo($invite->email);
            });
        });

        it('prevents resending accepted invites', function () {
            $admin = User::factory()->create(['role' => Role::Admin]);
            $invite = Invite::factory()->accepted()->create(['invited_by' => $admin->id]);

            $response = $this->actingAs($admin)
                ->post(route('invites.resend', $invite));

            $response->assertRedirect();
            $response->assertSessionHas('error');
        });

        it('prevents resending expired invites', function () {
            $admin = User::factory()->create(['role' => Role::Admin]);
            $invite = Invite::factory()->expired()->create(['invited_by' => $admin->id]);

            $response = $this->actingAs($admin)
                ->post(route('invites.resend', $invite));

            $response->assertRedirect();
            $response->assertSessionHas('error');
        });
    });
});
