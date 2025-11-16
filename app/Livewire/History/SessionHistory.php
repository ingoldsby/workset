<?php

namespace App\Livewire\History;

use App\Models\TrainingSession;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class SessionHistory extends Component
{
    use WithPagination;

    public string $search = '';
    public ?string $dateFrom = null;
    public ?string $dateTo = null;
    public bool $showCompleted = true;
    public bool $showIncomplete = false;

    public function mount(): void
    {
        $this->dateFrom = now()->subDays(30)->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    public function updatingShowCompleted(): void
    {
        $this->resetPage();
    }

    public function updatingShowIncomplete(): void
    {
        $this->resetPage();
    }

    public function viewSession(string $sessionId): void
    {
        $this->redirect(route('history.show', $sessionId));
    }

    public function render(): View
    {
        $query = TrainingSession::query()
            ->where('user_id', Auth::id())
            ->with(['sessionSets.exercise', 'sessionSets.memberExercise']);

        if ($this->dateFrom) {
            $query->whereDate('started_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('started_at', '<=', $this->dateTo);
        }

        if ($this->showCompleted && ! $this->showIncomplete) {
            $query->whereNotNull('completed_at');
        } elseif ($this->showIncomplete && ! $this->showCompleted) {
            $query->whereNull('completed_at');
        }

        if ($this->search) {
            $query->whereHas('sessionSets', function ($q) {
                $q->whereHas('exercise', function ($q) {
                    $q->where('name', 'like', "%{$this->search}%");
                })->orWhereHas('memberExercise', function ($q) {
                    $q->where('name', 'like', "%{$this->search}%");
                });
            });
        }

        $sessions = $query->latest('started_at')->paginate(10);

        return view('livewire.history.session-history', [
            'sessions' => $sessions,
        ]);
    }
}
