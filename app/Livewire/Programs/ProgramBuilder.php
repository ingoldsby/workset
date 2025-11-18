<?php

namespace App\Livewire\Programs;

use App\Models\Exercise;
use App\Models\Program;
use App\Models\ProgramDay;
use App\Models\ProgramDayExercise;
use App\Models\ProgramVersion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class ProgramBuilder extends Component
{
    public ?Program $program = null;
    public ?ProgramVersion $version = null;

    // Program metadata
    public string $name = '';
    public string $description = '';
    public string $visibility = 'private';
    public bool $isTemplate = false;
    public string $category = '';

    // Days and exercises
    public Collection $days;
    public Collection $availableExercises;

    // UI state
    public bool $showAddDayModal = false;
    public bool $showAddExerciseModal = false;
    public ?int $selectedDayId = null;
    public ?int $editingDayId = null;

    // Day form fields
    public string $dayName = '';
    public string $dayDescription = '';
    public int $dayNumber = 1;
    public int $restDaysAfter = 0;

    // Exercise form fields
    public ?int $selectedExerciseId = null;
    public int $sets = 3;
    public ?int $repsMin = null;
    public ?int $repsMax = null;
    public ?int $rpe = null;
    public int $restSeconds = 90;
    public string $tempo = '';
    public string $exerciseNotes = '';
    public ?int $supersetGroup = null;

    protected array $rules = [
        'name' => ['required', 'string', 'max:255'],
        'description' => ['nullable', 'string', 'max:1000'],
        'visibility' => ['required', 'in:private,public'],
        'dayName' => ['required', 'string', 'max:255'],
        'dayDescription' => ['nullable', 'string', 'max:1000'],
        'dayNumber' => ['required', 'integer', 'min:1'],
        'restDaysAfter' => ['required', 'integer', 'min:0', 'max:7'],
        'selectedExerciseId' => ['required', 'exists:exercises,id'],
        'sets' => ['required', 'integer', 'min:1', 'max:20'],
        'repsMin' => ['nullable', 'integer', 'min:1', 'max:1000'],
        'repsMax' => ['nullable', 'integer', 'min:1', 'max:1000'],
        'rpe' => ['nullable', 'integer', 'min:1', 'max:10'],
        'restSeconds' => ['required', 'integer', 'min:0', 'max:600'],
        'tempo' => ['nullable', 'string', 'max:20'],
        'exerciseNotes' => ['nullable', 'string', 'max:500'],
        'supersetGroup' => ['nullable', 'integer', 'min:1'],
    ];

    public function mount(?Program $program = null): void
    {
        if ($program && $program->exists) {
            $this->program = $program;
            $this->name = $program->name;
            $this->description = $program->description ?? '';
            $this->visibility = $program->visibility;
            $this->isTemplate = $program->is_template;
            $this->category = $program->category ?? '';

            $this->version = $program->activeVersion ?? $this->createInitialVersion($program);
        }

        $this->loadDays();
        $this->loadAvailableExercises();
    }

    protected function createInitialVersion(Program $program): ProgramVersion
    {
        return ProgramVersion::create([
            'program_id' => $program->id,
            'created_by' => Auth::id(),
            'version_number' => 1,
            'is_active' => true,
        ]);
    }

    public function loadDays(): void
    {
        if ($this->version) {
            $this->days = ProgramDay::where('program_version_id', $this->version->id)
                ->with(['exercises.exercise'])
                ->orderBy('day_number')
                ->get();
        } else {
            $this->days = collect();
        }
    }

    public function loadAvailableExercises(): void
    {
        $this->availableExercises = Exercise::orderBy('name')->get();
    }

    public function saveProgram(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'visibility' => ['required', 'in:private,public'],
        ]);

        if ($this->program) {
            $this->program->update([
                'name' => $this->name,
                'description' => $this->description,
                'visibility' => $this->visibility,
                'is_template' => $this->isTemplate,
                'category' => $this->category,
            ]);

            session()->flash('success', 'Program updated successfully.');
        } else {
            $this->program = Program::create([
                'owner_id' => Auth::id(),
                'name' => $this->name,
                'description' => $this->description,
                'visibility' => $this->visibility,
                'is_template' => $this->isTemplate,
                'category' => $this->category,
            ]);

            $this->version = $this->createInitialVersion($this->program);

            session()->flash('success', 'Program created successfully.');
        }
    }

    public function openAddDayModal(): void
    {
        $this->resetDayForm();
        $this->dayNumber = $this->days->count() + 1;
        $this->showAddDayModal = true;
    }

    public function openEditDayModal(int $dayId): void
    {
        $day = $this->days->firstWhere('id', $dayId);

        if ($day) {
            $this->editingDayId = $dayId;
            $this->dayName = $day->name;
            $this->dayDescription = $day->description ?? '';
            $this->dayNumber = $day->day_number;
            $this->restDaysAfter = $day->rest_days_after;
            $this->showAddDayModal = true;
        }
    }

    public function saveDay(): void
    {
        $this->validate([
            'dayName' => ['required', 'string', 'max:255'],
            'dayDescription' => ['nullable', 'string', 'max:1000'],
            'dayNumber' => ['required', 'integer', 'min:1'],
            'restDaysAfter' => ['required', 'integer', 'min:0', 'max:7'],
        ]);

        if (! $this->version) {
            $this->saveProgram();
        }

        if ($this->editingDayId) {
            $day = ProgramDay::find($this->editingDayId);
            $day->update([
                'name' => $this->dayName,
                'description' => $this->dayDescription,
                'day_number' => $this->dayNumber,
                'rest_days_after' => $this->restDaysAfter,
            ]);
        } else {
            ProgramDay::create([
                'program_version_id' => $this->version->id,
                'name' => $this->dayName,
                'description' => $this->dayDescription,
                'day_number' => $this->dayNumber,
                'rest_days_after' => $this->restDaysAfter,
            ]);
        }

        $this->loadDays();
        $this->closeDayModal();
        session()->flash('success', 'Day saved successfully.');
    }

    public function deleteDay(int $dayId): void
    {
        $day = ProgramDay::find($dayId);

        if ($day && $day->program_version_id === $this->version->id) {
            $day->delete();
            $this->loadDays();
            session()->flash('success', 'Day deleted successfully.');
        }
    }

    public function openAddExerciseModal(int $dayId): void
    {
        $this->selectedDayId = $dayId;
        $this->resetExerciseForm();
        $this->showAddExerciseModal = true;
    }

    public function saveExercise(): void
    {
        $this->validate([
            'selectedExerciseId' => ['required', 'exists:exercises,id'],
            'sets' => ['required', 'integer', 'min:1', 'max:20'],
            'repsMin' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'repsMax' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'rpe' => ['nullable', 'integer', 'min:1', 'max:10'],
            'restSeconds' => ['required', 'integer', 'min:0', 'max:600'],
            'tempo' => ['nullable', 'string', 'max:20'],
            'exerciseNotes' => ['nullable', 'string', 'max:500'],
            'supersetGroup' => ['nullable', 'integer', 'min:1'],
        ]);

        $day = $this->days->firstWhere('id', $this->selectedDayId);

        if ($day) {
            $lastOrder = ProgramDayExercise::where('program_day_id', $day->id)
                ->max('order') ?? 0;

            ProgramDayExercise::create([
                'program_day_id' => $day->id,
                'exercise_id' => $this->selectedExerciseId,
                'order' => $lastOrder + 1,
                'sets' => $this->sets,
                'reps_min' => $this->repsMin,
                'reps_max' => $this->repsMax,
                'rpe' => $this->rpe,
                'rest_seconds' => $this->restSeconds,
                'tempo' => $this->tempo,
                'notes' => $this->exerciseNotes,
                'superset_group' => $this->supersetGroup,
            ]);

            $this->loadDays();
            $this->closeExerciseModal();
            session()->flash('success', 'Exercise added successfully.');
        }
    }

    public function deleteExercise(int $exerciseId): void
    {
        $exercise = ProgramDayExercise::find($exerciseId);

        if ($exercise) {
            $exercise->delete();
            $this->loadDays();
            session()->flash('success', 'Exercise removed successfully.');
        }
    }

    public function moveDay(int $dayId, string $direction): void
    {
        $day = $this->days->firstWhere('id', $dayId);

        if (! $day) {
            return;
        }

        $swapWith = $direction === 'up'
            ? $this->days->where('day_number', '<', $day->day_number)->sortByDesc('day_number')->first()
            : $this->days->where('day_number', '>', $day->day_number)->sortBy('day_number')->first();

        if ($swapWith) {
            DB::transaction(function () use ($day, $swapWith) {
                $tempNumber = $day->day_number;
                $day->update(['day_number' => $swapWith->day_number]);
                $swapWith->update(['day_number' => $tempNumber]);
            });

            $this->loadDays();
        }
    }

    public function moveExercise(int $exerciseId, string $direction): void
    {
        $exercise = ProgramDayExercise::find($exerciseId);

        if (! $exercise) {
            return;
        }

        $siblings = ProgramDayExercise::where('program_day_id', $exercise->program_day_id)
            ->orderBy('order')
            ->get();

        $swapWith = $direction === 'up'
            ? $siblings->where('order', '<', $exercise->order)->sortByDesc('order')->first()
            : $siblings->where('order', '>', $exercise->order)->sortBy('order')->first();

        if ($swapWith) {
            DB::transaction(function () use ($exercise, $swapWith) {
                $tempOrder = $exercise->order;
                $exercise->update(['order' => $swapWith->order]);
                $swapWith->update(['order' => $tempOrder]);
            });

            $this->loadDays();
        }
    }

    protected function resetDayForm(): void
    {
        $this->editingDayId = null;
        $this->dayName = '';
        $this->dayDescription = '';
        $this->dayNumber = 1;
        $this->restDaysAfter = 0;
    }

    protected function resetExerciseForm(): void
    {
        $this->selectedExerciseId = null;
        $this->sets = 3;
        $this->repsMin = null;
        $this->repsMax = null;
        $this->rpe = null;
        $this->restSeconds = 90;
        $this->tempo = '';
        $this->exerciseNotes = '';
        $this->supersetGroup = null;
    }

    public function closeDayModal(): void
    {
        $this->showAddDayModal = false;
        $this->resetDayForm();
    }

    public function closeExerciseModal(): void
    {
        $this->showAddExerciseModal = false;
        $this->resetExerciseForm();
    }

    public function render(): View
    {
        return view('livewire.programs.program-builder');
    }
}
