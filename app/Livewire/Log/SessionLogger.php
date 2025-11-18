<?php

namespace App\Livewire\Log;

use App\Models\Exercise;
use App\Models\MemberExercise;
use App\Models\SessionExercise;
use App\Models\SessionSet;
use App\Models\TrainingSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class SessionLogger extends Component
{
    public ?string $sessionId = null;
    public ?TrainingSession $session = null;
    public array $exercises = [];
    public int $restTimerSeconds = 0;
    public bool $timerActive = false;
    public bool $showExerciseSelector = false;
    public string $exerciseSearch = '';
    public array $availableExercises = [];

    public function mount(?string $sessionId = null): void
    {
        if ($sessionId) {
            $this->session = TrainingSession::with(['exercises.exercise', 'exercises.memberExercise', 'exercises.sets'])
                ->findOrFail($sessionId);

            $this->authorize('update', $this->session);
        }
    }

    public function openExerciseSelector(): void
    {
        $this->showExerciseSelector = true;
        $this->loadAvailableExercises();
    }

    public function closeExerciseSelector(): void
    {
        $this->showExerciseSelector = false;
        $this->exerciseSearch = '';
    }

    public function updatedExerciseSearch(): void
    {
        $this->loadAvailableExercises();
    }

    public function loadAvailableExercises(): void
    {
        $globalExercises = Exercise::query()
            ->when($this->exerciseSearch, fn($q) => $q->where('name', 'like', "%{$this->exerciseSearch}%"))
            ->orderBy('name')
            ->limit(20)
            ->get()
            ->map(fn($ex) => ['id' => $ex->id, 'name' => $ex->name, 'type' => 'global'])
            ->all();

        $memberExercises = MemberExercise::query()
            ->where('user_id', Auth::id())
            ->when($this->exerciseSearch, fn($q) => $q->where('name', 'like', "%{$this->exerciseSearch}%"))
            ->orderBy('name')
            ->limit(20)
            ->get()
            ->map(fn($ex) => ['id' => $ex->id, 'name' => $ex->name, 'type' => 'member'])
            ->all();

        $this->availableExercises = array_merge($memberExercises, $globalExercises);
    }

    public function selectExercise(string $exerciseId, string $exerciseType): void
    {
        $this->addExercise($exerciseId, $exerciseType);
        $this->closeExerciseSelector();
    }

    public function addExercise(string $exerciseId, string $exerciseType): void
    {
        if (! $this->session) {
            $this->session = TrainingSession::create([
                'user_id' => Auth::id(),
                'scheduled_date' => today(),
                'started_at' => now(),
            ]);
        }

        $exercise = $exerciseType === 'global'
            ? Exercise::find($exerciseId)
            : MemberExercise::find($exerciseId);

        if (! $exercise) {
            return;
        }

        // Create SessionExercise record
        $sessionExercise = SessionExercise::create([
            'training_session_id' => $this->session->id,
            'exercise_id' => $exerciseType === 'global' ? $exerciseId : null,
            'member_exercise_id' => $exerciseType === 'member' ? $exerciseId : null,
            'order' => count($this->exercises),
        ]);

        $this->exercises[] = [
            'session_exercise_id' => $sessionExercise->id,
            'id' => $exerciseId,
            'type' => $exerciseType,
            'name' => $exercise->name,
            'sets' => [],
        ];
    }

    public function addSet(int $exerciseIndex, array $setData): void
    {
        if (! isset($this->exercises[$exerciseIndex])) {
            return;
        }

        $exercise = $this->exercises[$exerciseIndex];

        $set = SessionSet::create([
            'session_exercise_id' => $exercise['session_exercise_id'],
            'set_number' => count($exercise['sets']) + 1,
            'performed_weight' => $setData['weight'] ?? null,
            'performed_reps' => $setData['reps'] ?? null,
            'performed_rpe' => $setData['rpe'] ?? null,
        ]);

        $this->exercises[$exerciseIndex]['sets'][] = $set;

        // Start rest timer if configured
        if (isset($setData['restTime']) && $setData['restTime'] > 0) {
            $this->startRestTimer($setData['restTime']);
        }
    }

    public function startRestTimer(int $seconds): void
    {
        $this->restTimerSeconds = $seconds;
        $this->timerActive = true;
    }

    public function stopRestTimer(): void
    {
        $this->timerActive = false;
        $this->restTimerSeconds = 0;
    }

    public function completeSession(): void
    {
        if (! $this->session) {
            return;
        }

        $this->session->update([
            'completed_at' => now(),
        ]);

        session()->flash('success', __('Session completed successfully!'));

        $this->redirect(route('history.index'));
    }

    public function render(): View
    {
        return view('livewire.log.session-logger');
    }
}
