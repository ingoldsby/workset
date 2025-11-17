<?php

namespace App\Livewire\Today;

use App\Models\TrainingSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class QuickStart extends Component
{
    public function startAdHocSession(): void
    {
        $session = TrainingSession::create([
            'user_id' => Auth::id(),
            'scheduled_date' => today(),
            'started_at' => now(),
            'is_planned' => false,
        ]);

        $this->redirect(route('log.index', ['session' => $session->id]));
    }

    public function render(): View
    {
        return view('livewire.today.quick-start');
    }
}
