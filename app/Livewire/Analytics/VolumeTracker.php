<?php

namespace App\Livewire\Analytics;

use App\Models\TrainingSession;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class VolumeTracker extends Component
{
    public function getWeeklyVolume(): array
    {
        $weeks = collect();
        $startDate = now()->subWeeks(12)->startOfWeek();

        for ($i = 0; $i < 12; $i++) {
            $weekStart = $startDate->copy()->addWeeks($i);
            $weekEnd = $weekStart->copy()->endOfWeek();

            $volume = TrainingSession::query()
                ->where('user_id', Auth::id())
                ->whereBetween('started_at', [$weekStart, $weekEnd])
                ->whereNotNull('completed_at')
                ->with('sessionSets')
                ->get()
                ->sum(function ($session) {
                    return $session->sessionSets->sum(function ($set) {
                        return ($set->weight_performed ?? 0) * ($set->reps_performed ?? 0);
                    });
                });

            $weeks->push([
                'week' => $weekStart->format('M j'),
                'volume' => round($volume, 2),
            ]);
        }

        return $weeks->toArray();
    }

    public function render(): View
    {
        $weeklyVolume = $this->getWeeklyVolume();

        return view('livewire.analytics.volume-tracker', [
            'weeklyVolume' => $weeklyVolume,
        ]);
    }
}
