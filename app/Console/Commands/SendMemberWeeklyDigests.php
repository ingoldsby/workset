<?php

namespace App\Console\Commands;

use App\Enums\Role;
use App\Models\SessionPlan;
use App\Models\TrainingSession;
use App\Models\User;
use App\Notifications\MemberWeeklyDigest;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendMemberWeeklyDigests extends Command
{
    protected $signature = 'workset:send-member-weekly-digests {--day=}';
    protected $description = 'Send weekly digest emails to members';

    public function handle(): int
    {
        $targetDay = $this->option('day') ?? strtolower(now()->format('l'));

        $this->info("Sending member weekly digests for {$targetDay}...");

        $members = User::query()
            ->where('role', Role::Member)
            ->whereNotNull('notification_preferences->weekly_digest')
            ->whereJsonContains('notification_preferences->weekly_digest', true)
            ->whereJsonContains('notification_preferences->weekly_digest_day', $targetDay)
            ->get();

        $count = 0;

        foreach ($members as $member) {
            // Get past week's sessions
            $weekSessions = TrainingSession::query()
                ->where('user_id', $member->id)
                ->whereNotNull('completed_at')
                ->whereBetween('completed_at', [now()->subWeek()->startOfDay(), now()->endOfDay()])
                ->with('sessionSets')
                ->get();

            // Calculate stats
            $totalSets = $weekSessions->sum(fn($session) => $session->sessionSets->count());
            $totalVolume = $weekSessions->sum(function ($session) {
                return $session->sessionSets->sum(function ($set) {
                    return ($set->weight_performed ?? 0) * ($set->reps_performed ?? 0);
                });
            });

            // Get next week's scheduled sessions
            $upcomingWeek = SessionPlan::query()
                ->where('user_id', $member->id)
                ->whereBetween('scheduled_date', [now()->addDay()->startOfDay(), now()->addWeek()->endOfDay()])
                ->with('programDay')
                ->orderBy('scheduled_date')
                ->get();

            $member->notify(new MemberWeeklyDigest($weekSessions, $totalSets, $totalVolume, $upcomingWeek));
            $count++;
            $this->info("Sent digest to {$member->name} ({$member->email})");
        }

        $this->info("Sent {$count} member weekly digests.");

        return self::SUCCESS;
    }
}
