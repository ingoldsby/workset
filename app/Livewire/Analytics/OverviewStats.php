<?php

namespace App\Livewire\Analytics;

use App\Models\TrainingSession;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class OverviewStats extends Component
{
    public string $period = 'week'; // week, month, year

    public function setPeriod(string $period): void
    {
        $this->period = $period;
    }

    public function getStats(): array
    {
        $startDate = match ($this->period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->startOfWeek(),
        };

        $sessions = TrainingSession::query()
            ->where('user_id', Auth::id())
            ->where('started_at', '>=', $startDate)
            ->whereNotNull('completed_at')
            ->with('sessionSets')
            ->get();

        $totalSessions = $sessions->count();
        $totalSets = $sessions->sum(fn($session) => $session->sessionSets->count());
        $totalVolume = $sessions->sum(function ($session) {
            return $session->sessionSets->sum(function ($set) {
                return ($set->weight_performed ?? 0) * ($set->reps_performed ?? 0);
            });
        });

        $totalDuration = $sessions->sum(function ($session) {
            return $session->completed_at?->diffInMinutes($session->started_at) ?? 0;
        });

        return [
            'totalSessions' => $totalSessions,
            'totalSets' => $totalSets,
            'totalVolume' => round($totalVolume, 2),
            'averageDuration' => $totalSessions > 0 ? round($totalDuration / $totalSessions) : 0,
        ];
    }

    public function render(): View
    {
        $stats = $this->getStats();

        return view('livewire.analytics.overview-stats', [
            'stats' => $stats,
        ]);
    }
}
