<?php

namespace App\Livewire\Log;

use App\Models\Exercise;
use App\Models\MemberExercise;
use App\Models\SessionSet;
use App\Models\TrainingSession;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class SessionLogger extends Component
{
    public ?string $sessionId = null;
    public ?TrainingSession $session = null;
    public Collection $exercises;
    public int $restTimerSeconds = 0;
    public bool $timerActive = false;

    public function mount(?string $sessionId = null): void
    {
        if ($sessionId) {
            $this->session = TrainingSession::with(['sessionSets.exercise', 'sessionSets.memberExercise'])
                ->findOrFail($sessionId);

            $this->authorize('update', $this->session);
        }

        $this->exercises = collect();
    }

    public function addExercise(string $exerciseId, string $exerciseType): void
    {
        if (! $this->session) {
            $this->session = TrainingSession::create([
                'user_id' => Auth::id(),
                'started_at' => now(),
                'is_planned' => false,
            ]);
        }

        $exercise = $exerciseType === 'global'
            ? Exercise::find($exerciseId)
            : MemberExercise::find($exerciseId);

        if (! $exercise) {
            return;
        }

        $this->exercises->push([
            'id' => $exerciseId,
            'type' => $exerciseType,
            'name' => $exercise->name,
            'sets' => [],
        ]);
    }

    public function addSet(int $exerciseIndex, array $setData): void
    {
        $exercise = $this->exercises->get($exerciseIndex);

        if (! $exercise) {
            return;
        }

        $set = SessionSet::create([
            'training_session_id' => $this->session->id,
            'exercise_id' => $exercise['type'] === 'global' ? $exercise['id'] : null,
            'member_exercise_id' => $exercise['type'] === 'member' ? $exercise['id'] : null,
            'set_number' => count($exercise['sets']) + 1,
            'weight_performed' => $setData['weight'] ?? null,
            'reps_performed' => $setData['reps'] ?? null,
            'rpe_performed' => $setData['rpe'] ?? null,
            'notes' => $setData['notes'] ?? null,
        ]);

        $exercise['sets'][] = $set;
        $this->exercises->put($exerciseIndex, $exercise);

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
