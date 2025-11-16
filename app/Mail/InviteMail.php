<?php

namespace App\Mail;

use App\Models\Invite;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InviteMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Invite $invite,
        public User $inviter,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You've been invited to join Workset",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.invite',
            with: [
                'acceptUrl' => route('invite.accept', ['token' => $this->invite->token]),
                'inviterName' => $this->inviter->name,
                'role' => $this->invite->role->label(),
                'expiresAt' => $this->invite->expires_at->format('j F Y'),
            ],
        );
    }
}
