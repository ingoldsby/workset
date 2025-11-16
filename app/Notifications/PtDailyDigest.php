<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class PtDailyDigest extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Collection $completedSessions,
        private Collection $upcomingSessions,
        private Collection $missedSessions,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject(__('Your Daily Athlete Summary'))
            ->greeting(__('Hello :name!', ['name' => $notifiable->name]))
            ->line(__('Here\'s your daily summary of athlete activity.'));

        // Completed sessions
        if ($this->completedSessions->isNotEmpty()) {
            $message->line(__('**:count Sessions Completed Today**', [
                'count' => $this->completedSessions->count(),
            ]));

            foreach ($this->completedSessions->take(5) as $session) {
                $message->line('• ' . $session->user->name . ' - ' .
                    $session->sessionSets->groupBy(fn($set) => $set->exercise_id ?? $set->member_exercise_id)->count() .
                    ' exercises, ' . $session->sessionSets->count() . ' sets');
            }

            if ($this->completedSessions->count() > 5) {
                $message->line(__('...and :count more', [
                    'count' => $this->completedSessions->count() - 5,
                ]));
            }
        }

        // Upcoming sessions
        if ($this->upcomingSessions->isNotEmpty()) {
            $message->line('');
            $message->line(__('**:count Sessions Scheduled for Tomorrow**', [
                'count' => $this->upcomingSessions->count(),
            ]));

            foreach ($this->upcomingSessions->take(5) as $plan) {
                $message->line('• ' . $plan->user->name . ' - ' . ($plan->programDay?->name ?? __('Ad-hoc session')));
            }
        }

        // Missed sessions
        if ($this->missedSessions->isNotEmpty()) {
            $message->line('');
            $message->line(__('**:count Scheduled Sessions Not Completed**', [
                'count' => $this->missedSessions->count(),
            ]));

            foreach ($this->missedSessions->take(5) as $plan) {
                $message->line('• ' . $plan->user->name . ' - ' . $plan->scheduled_date->format('l, j F'));
            }
        }

        $message->action(__('View PT Dashboard'), route('pt.index'));

        return $message;
    }
}
