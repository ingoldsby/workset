<?php

namespace App\Livewire\Exercises;

use App\Enums\EquipmentType;
use App\Enums\MuscleGroup;
use App\Models\Exercise;
use App\Models\MemberExercise;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class ExerciseLibrary extends Component
{
    use WithPagination;

    public string $search = '';
    public ?string $muscleGroupFilter = null;
    public ?string $equipmentFilter = null;
    public string $tab = 'global'; // global, custom, recent, favourites

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingMuscleGroupFilter(): void
    {
        $this->resetPage();
    }

    public function updatingEquipmentFilter(): void
    {
        $this->resetPage();
    }

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
        $this->resetPage();
    }

    public function createCustomExercise(): void
    {
        $this->redirect(route('exercises.create'));
    }

    public function getExercises(): Collection
    {
        $query = match ($this->tab) {
            'global' => Exercise::query(),
            'custom' => MemberExercise::query()->where('user_id', Auth::id()),
            'recent' => Exercise::query()
                ->whereHas('sessionSets', function ($q) {
                    $q->whereHas('trainingSession', function ($q) {
                        $q->where('user_id', Auth::id());
                    });
                })
                ->withCount(['sessionSets' => function ($q) {
                    $q->whereHas('trainingSession', function ($q) {
                        $q->where('user_id', Auth::id());
                    });
                }])
                ->orderBy('session_sets_count', 'desc'),
            'favourites' => Exercise::query()
                ->where('is_favourite', true)
                ->where('user_id', Auth::id()),
            default => Exercise::query(),
        };

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('aliases', 'like', "%{$this->search}%");
            });
        }

        if ($this->muscleGroupFilter) {
            $query->where('primary_muscle_group', $this->muscleGroupFilter);
        }

        if ($this->equipmentFilter) {
            $query->where('equipment_type', $this->equipmentFilter);
        }

        return $query->take(50)->get();
    }

    public function render(): View
    {
        $exercises = $this->getExercises();
        $muscleGroups = MuscleGroup::cases();
        $equipmentTypes = EquipmentType::cases();

        return view('livewire.exercises.exercise-library', [
            'exercises' => $exercises,
            'muscleGroups' => $muscleGroups,
            'equipmentTypes' => $equipmentTypes,
        ]);
    }
}
