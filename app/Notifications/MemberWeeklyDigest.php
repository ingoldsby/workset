<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class MemberWeeklyDigest extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Collection $weekSessions,
        private int $totalSets,
        private float $totalVolume,
        private Collection $upcomingWeek,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject(__('Your Weekly Training Summary'))
            ->greeting(__('Hello :name!', ['name' => $notifiable->name]))
            ->line(__('Here\'s your training summary for the past week.'));

        // Weekly stats
        $message->line(__('**This Week\'s Stats**'));
        $message->line('• ' . __(':count sessions completed', ['count' => $this->weekSessions->count()]));
        $message->line('• ' . __(':count total sets', ['count' => $this->totalSets]));
        $message->line('• ' . __(':volume kg total volume', ['volume' => number_format($this->totalVolume, 0)]));

        // Session breakdown
        if ($this->weekSessions->isNotEmpty()) {
            $message->line('');
            $message->line(__('**Your Sessions**'));

            foreach ($this->weekSessions as $session) {
                $duration = $session->completed_at?->diffInMinutes($session->started_at) ?? 0;
                $message->line('• ' . $session->started_at->format('l') . ' - ' .
                    $session->sessionSets->groupBy(fn($set) => $set->exercise_id ?? $set->member_exercise_id)->count() .
                    ' exercises (' . $duration . ' min)');
            }
        }

        // Upcoming week
        if ($this->upcomingWeek->isNotEmpty()) {
            $message->line('');
            $message->line(__('**Next Week\'s Plan**'));

            foreach ($this->upcomingWeek as $plan) {
                $message->line('• ' . $plan->scheduled_date->format('l, j F') . ' - ' .
                    ($plan->programDay?->name ?? __('Ad-hoc session')));
            }
        }

        $message->action(__('View Full History'), route('history.index'));

        if ($this->weekSessions->isEmpty()) {
            $message->line('');
            $message->line(__('No sessions logged this week. Let\'s get training!'));
        }

        return $message;
    }
}
