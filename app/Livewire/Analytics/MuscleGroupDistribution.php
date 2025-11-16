<?php

namespace App\Livewire\Analytics;

use App\Models\SessionExercise;
use App\Models\TrainingSession;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class MuscleGroupDistribution extends Component
{
    public string $period = 'month'; // week, month, year

    public function setPeriod(string $period): void
    {
        $this->period = $period;
    }

    public function getMuscleGroupData(): array
    {
        $startDate = match ($this->period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        $sessionIds = TrainingSession::query()
            ->where('user_id', Auth::id())
            ->where('started_at', '>=', $startDate)
            ->whereNotNull('completed_at')
            ->pluck('id');

        $muscleGroups = SessionExercise::query()
            ->whereIn('training_session_id', $sessionIds)
            ->whereHas('exercise')
            ->with('exercise')
            ->get()
            ->pluck('exercise.primary_muscle')
            ->filter()
            ->countBy(fn ($muscle) => $muscle->value)
            ->sortDesc();

        return [
            'labels' => $muscleGroups->keys()->toArray(),
            'data' => $muscleGroups->values()->toArray(),
        ];
    }

    public function getFrequencyData(): Collection
    {
        $startDate = match ($this->period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        return TrainingSession::query()
            ->where('user_id', Auth::id())
            ->where('started_at', '>=', $startDate)
            ->whereNotNull('completed_at')
            ->select(DB::raw('DATE(started_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    public function render(): View
    {
        return view('livewire.analytics.muscle-group-distribution', [
            'muscleGroupData' => $this->getMuscleGroupData(),
            'frequencyData' => $this->getFrequencyData(),
        ]);
    }
}
