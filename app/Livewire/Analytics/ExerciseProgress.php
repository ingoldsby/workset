<?php

namespace App\Livewire\Analytics;

use App\Models\Exercise;
use App\Models\SessionSet;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class ExerciseProgress extends Component
{
    public ?string $selectedExerciseId = null;

    public function selectExercise(string $exerciseId): void
    {
        $this->selectedExerciseId = $exerciseId;
    }

    public function getTopExercises(): Collection
    {
        return SessionSet::query()
            ->whereHas('trainingSession', function ($q) {
                $q->where('user_id', Auth::id())
                    ->whereNotNull('completed_at');
            })
            ->whereNotNull('exercise_id')
            ->with('exercise')
            ->selectRaw('exercise_id, COUNT(*) as set_count')
            ->groupBy('exercise_id')
            ->orderBy('set_count', 'desc')
            ->take(10)
            ->get()
            ->map(function ($set) {
                return [
                    'id' => $set->exercise_id,
                    'name' => $set->exercise?->name,
                    'setCount' => $set->set_count,
                ];
            });
    }

    public function getPersonalRecords(): Collection
    {
        if (! $this->selectedExerciseId) {
            return collect();
        }

        return SessionSet::query()
            ->where('exercise_id', $this->selectedExerciseId)
            ->whereHas('trainingSession', function ($q) {
                $q->where('user_id', Auth::id())
                    ->whereNotNull('completed_at');
            })
            ->whereNotNull('weight_performed')
            ->whereNotNull('reps_performed')
            ->orderBy('weight_performed', 'desc')
            ->take(5)
            ->get();
    }

    public function render(): View
    {
        $topExercises = $this->getTopExercises();
        $personalRecords = $this->getPersonalRecords();

        return view('livewire.analytics.exercise-progress', [
            'topExercises' => $topExercises,
            'personalRecords' => $personalRecords,
        ]);
    }
}
