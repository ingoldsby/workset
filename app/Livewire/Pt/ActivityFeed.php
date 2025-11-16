<?php

namespace App\Livewire\Pt;

use App\Models\AuditLog;
use App\Models\TrainingSession;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class ActivityFeed extends Component
{
    public function getRecentActivity(): Collection
    {
        // Get assigned member IDs
        $memberIds = Auth::user()->memberAssignments()
            ->whereNull('unassigned_at')
            ->pluck('member_id');

        // Get recent training sessions from assigned members
        $recentSessions = TrainingSession::query()
            ->whereIn('user_id', $memberIds)
            ->whereNotNull('completed_at')
            ->with(['user', 'sessionSets'])
            ->latest('completed_at')
            ->take(10)
            ->get()
            ->map(function ($session) {
                return [
                    'type' => 'session_completed',
                    'timestamp' => $session->completed_at,
                    'user' => $session->user,
                    'data' => [
                        'sets' => $session->sessionSets->count(),
                        'exercises' => $session->sessionSets->groupBy(fn($set) => $set->exercise_id ?? $set->member_exercise_id)->count(),
                        'duration' => $session->completed_at->diffInMinutes($session->started_at),
                    ],
                ];
            });

        return $recentSessions->sortByDesc('timestamp')->take(10);
    }

    public function render(): View
    {
        $activities = $this->getRecentActivity();

        return view('livewire.pt.activity-feed', [
            'activities' => $activities,
        ]);
    }
}
