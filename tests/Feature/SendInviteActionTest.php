<?php

use App\Actions\SendInviteAction;
use App\Enums\Role;
use App\Mail\InviteMail;
use App\Models\Invite;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();
});

describe('SendInviteAction', function () {
    it('creates an invite with correct attributes', function () {
        $admin = User::factory()->create(['role' => Role::Admin]);
        $action = new SendInviteAction();

        $invite = $action->execute(
            inviter: $admin,
            email: 'test@example.com',
            role: Role::Member,
        );

        expect($invite)
            ->toBeInstanceOf(Invite::class)
            ->and($invite->email)->toBe('test@example.com')
            ->and($invite->role)->toBe(Role::Member)
            ->and($invite->invited_by)->toBe($admin->id)
            ->and($invite->token)->not->toBeNull()
            ->and($invite->expires_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
    });

    it('sends an invitation email', function () {
        $admin = User::factory()->create(['role' => Role::Admin]);
        $action = new SendInviteAction();

        $invite = $action->execute(
            inviter: $admin,
            email: 'test@example.com',
            role: Role::Member,
        );

        Mail::assertSent(InviteMail::class, function ($mail) use ($invite, $admin) {
            return $mail->hasTo('test@example.com')
                && $mail->invite->id === $invite->id
                && $mail->inviter->id === $admin->id;
        });
    });

    it('assigns PT when provided', function () {
        $admin = User::factory()->create(['role' => Role::Admin]);
        $pt = User::factory()->create(['role' => Role::PT]);
        $action = new SendInviteAction();

        $invite = $action->execute(
            inviter: $admin,
            email: 'member@example.com',
            role: Role::Member,
            ptId: $pt->id,
        );

        expect($invite->pt_id)->toBe($pt->id);
    });

    it('sets expiry days correctly', function () {
        $admin = User::factory()->create(['role' => Role::Admin]);
        $action = new SendInviteAction();

        $invite = $action->execute(
            inviter: $admin,
            email: 'test@example.com',
            role: Role::Member,
            expiryDays: 14,
        );

        expect($invite->expires_at->diffInDays(now(), absolute: true))->toBe(14);
    });
});
