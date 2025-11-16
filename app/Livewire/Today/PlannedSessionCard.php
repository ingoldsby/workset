<?php

namespace App\Livewire\Today;

use App\Models\SessionPlan;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class PlannedSessionCard extends Component
{
    public ?SessionPlan $plannedSession = null;

    public function mount(): void
    {
        $this->loadPlannedSession();
    }

    public function loadPlannedSession(): void
    {
        $this->plannedSession = SessionPlan::query()
            ->where('user_id', Auth::id())
            ->whereDate('scheduled_date', today())
            ->with([
                'sessionExercises.exercise',
                'sessionExercises.memberExercise',
                'programDayExercises.exercise',
                'programDayExercises.memberExercise',
            ])
            ->first();
    }

    public function startSession(): void
    {
        if (! $this->plannedSession) {
            return;
        }

        $this->redirect(route('log.index', ['session' => $this->plannedSession->id]));
    }

    public function render(): View
    {
        return view('livewire.today.planned-session-card');
    }
}
