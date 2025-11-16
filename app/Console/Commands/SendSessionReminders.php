<?php

namespace App\Console\Commands;

use App\Models\SessionPlan;
use App\Notifications\SessionReminder;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendSessionReminders extends Command
{
    protected $signature = 'workset:send-session-reminders';
    protected $description = 'Send session reminder notifications for sessions scheduled in the next hour';

    public function handle(): int
    {
        $this->info('Sending session reminders...');

        // Get sessions scheduled in the next hour
        $upcomingSessions = SessionPlan::query()
            ->whereBetween('scheduled_date', [
                now()->addMinutes(50),
                now()->addMinutes(70),
            ])
            ->with('user')
            ->get();

        $count = 0;

        foreach ($upcomingSessions as $sessionPlan) {
            // Check if reminder was already sent (to avoid duplicates)
            if ($sessionPlan->reminder_sent_at) {
                continue;
            }

            $sessionPlan->user->notify(new SessionReminder($sessionPlan));

            // Mark as sent
            $sessionPlan->update(['reminder_sent_at' => now()]);

            $count++;
            $this->info("Sent reminder to {$sessionPlan->user->name} for session at {$sessionPlan->scheduled_date->format('H:i')}");
        }

        $this->info("Sent {$count} session reminders.");

        return self::SUCCESS;
    }
}
