<?php

namespace App\Livewire\History;

use App\Models\TrainingSession;
use Illuminate\View\View;
use Livewire\Component;

class SessionDetail extends Component
{
    public ?string $sessionId = null;
    public ?TrainingSession $session = null;

    public function mount(?string $sessionId = null): void
    {
        if ($sessionId) {
            $this->session = TrainingSession::with([
                'exercises.exercise',
                'exercises.memberExercise',
                'exercises.sets'
            ])->findOrFail($sessionId);

            $this->authorize('view', $this->session);
        }
    }

    public function render(): View
    {
        return view('livewire.history.session-detail');
    }
}
