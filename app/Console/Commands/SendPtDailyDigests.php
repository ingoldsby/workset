<?php

namespace App\Console\Commands;

use App\Enums\Role;
use App\Models\SessionPlan;
use App\Models\TrainingSession;
use App\Models\User;
use App\Notifications\PtDailyDigest;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendPtDailyDigests extends Command
{
    protected $signature = 'workset:send-pt-daily-digests';
    protected $description = 'Send daily digest emails to PTs at 20:00 local time';

    public function handle(): int
    {
        $this->info('Sending PT daily digests...');

        $pts = User::query()
            ->where('role', Role::PT)
            ->whereNotNull('notification_preferences->daily_digest')
            ->whereJsonContains('notification_preferences->daily_digest', true)
            ->get();

        $count = 0;

        foreach ($pts as $pt) {
            // Get member IDs for this PT
            $memberIds = $pt->memberAssignments()
                ->whereNull('unassigned_at')
                ->pluck('member_id');

            if ($memberIds->isEmpty()) {
                continue;
            }

            // Get today's completed sessions
            $completedSessions = TrainingSession::query()
                ->whereIn('user_id', $memberIds)
                ->whereNotNull('completed_at')
                ->whereDate('completed_at', today())
                ->with(['user', 'sessionSets'])
                ->get();

            // Get tomorrow's scheduled sessions
            $upcomingSessions = SessionPlan::query()
                ->whereIn('user_id', $memberIds)
                ->whereDate('scheduled_date', today()->addDay())
                ->with(['user', 'programDay'])
                ->get();

            // Get today's missed sessions
            $missedSessions = SessionPlan::query()
                ->whereIn('user_id', $memberIds)
                ->whereDate('scheduled_date', today())
                ->whereDoesntHave('trainingSessions', function ($query) {
                    $query->whereNotNull('completed_at');
                })
                ->with(['user', 'programDay'])
                ->get();

            // Only send if there's some activity
            if ($completedSessions->isNotEmpty() || $upcomingSessions->isNotEmpty() || $missedSessions->isNotEmpty()) {
                $pt->notify(new PtDailyDigest($completedSessions, $upcomingSessions, $missedSessions));
                $count++;
                $this->info("Sent digest to {$pt->name} ({$pt->email})");
            }
        }

        $this->info("Sent {$count} PT daily digests.");

        return self::SUCCESS;
    }
}
