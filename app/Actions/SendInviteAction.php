<?php

namespace App\Actions;

use App\Enums\Role;
use App\Mail\InviteMail;
use App\Models\Invite;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SendInviteAction
{
    public function execute(
        User $inviter,
        string $email,
        Role $role,
        ?string $ptId = null,
        int $expiryDays = 7
    ): Invite {
        // Create the invite
        $invite = Invite::create([
            'email' => $email,
            'token' => Str::random(64),
            'invited_by' => $inviter->id,
            'pt_id' => $ptId,
            'role' => $role,
            'expires_at' => now()->addDays($expiryDays),
        ]);

        // Send the invitation email
        Mail::to($email)->send(new InviteMail($invite, $inviter));

        return $invite;
    }
}
