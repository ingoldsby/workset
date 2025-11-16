<?php

namespace App\Notifications;

use App\Models\Invite;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InviteCreated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Invite $invite,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $acceptUrl = route('invite.accept', ['token' => $this->invite->token]);

        return (new MailMessage)
            ->subject(__('You\'ve been invited to Workset'))
            ->greeting(__('Hello!'))
            ->line(__('You\'ve been invited to join Workset by :name.', [
                'name' => $this->invite->inviter->name,
            ]))
            ->line(__('Workset is a fitness tracking application that helps you track your workouts, monitor progress, and achieve your fitness goals.'))
            ->action(__('Accept Invitation'), $acceptUrl)
            ->line(__('This invitation will expire in :days days.', [
                'days' => $this->invite->created_at->diffInDays($this->invite->expires_at),
            ]))
            ->line(__('If you did not expect to receive an invitation, no further action is required.'));
    }
}
