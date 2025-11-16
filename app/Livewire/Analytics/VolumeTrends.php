<?php

namespace App\Livewire\Analytics;

use App\Models\SessionSet;
use App\Models\TrainingSession;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class VolumeTrends extends Component
{
    public string $period = 'month'; // week, month, year, all
    public string $metric = 'volume'; // volume, sets, sessions

    public function setPeriod(string $period): void
    {
        $this->period = $period;
    }

    public function setMetric(string $metric): void
    {
        $this->metric = $metric;
    }

    public function getTrendsData(): Collection
    {
        $startDate = match ($this->period) {
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'year' => now()->subYear(),
            'all' => now()->subYears(10),
            default => now()->subMonth(),
        };

        $sessionIds = TrainingSession::query()
            ->where('user_id', Auth::id())
            ->where('started_at', '>=', $startDate)
            ->whereNotNull('completed_at')
            ->pluck('id');

        if ($this->metric === 'volume') {
            return DB::table('session_sets')
                ->join('session_exercises', 'session_sets.session_exercise_id', '=', 'session_exercises.id')
                ->whereIn('session_exercises.training_session_id', $sessionIds)
                ->where('session_sets.completed', true)
                ->whereNotNull('session_sets.performed_weight')
                ->whereNotNull('session_sets.performed_reps')
                ->select(
                    DB::raw('DATE(session_sets.completed_at) as date'),
                    DB::raw('SUM(session_sets.performed_weight * session_sets.performed_reps) as total_volume')
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get();
        }

        if ($this->metric === 'sets') {
            return DB::table('session_sets')
                ->join('session_exercises', 'session_sets.session_exercise_id', '=', 'session_exercises.id')
                ->whereIn('session_exercises.training_session_id', $sessionIds)
                ->where('session_sets.completed', true)
                ->select(
                    DB::raw('DATE(session_sets.completed_at) as date'),
                    DB::raw('COUNT(*) as total_sets')
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get();
        }

        return TrainingSession::query()
            ->where('user_id', Auth::id())
            ->where('started_at', '>=', $startDate)
            ->whereNotNull('completed_at')
            ->select(
                DB::raw('DATE(started_at) as date'),
                DB::raw('COUNT(*) as total_sessions')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    public function render(): View
    {
        return view('livewire.analytics.volume-trends', [
            'trendsData' => $this->getTrendsData(),
        ]);
    }
}
