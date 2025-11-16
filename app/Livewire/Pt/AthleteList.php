<?php

namespace App\Livewire\Pt;

use App\Models\PtAssignment;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class AthleteList extends Component
{
    public Collection $athletes;
    public string $filter = 'active'; // active, inactive, all

    public function mount(): void
    {
        $this->loadAthletes();
    }

    public function loadAthletes(): void
    {
        $query = PtAssignment::query()
            ->where('pt_id', Auth::id())
            ->with(['member' => function ($q) {
                $q->withCount('trainingSessions');
            }]);

        if ($this->filter === 'active') {
            $query->whereNull('unassigned_at');
        } elseif ($this->filter === 'inactive') {
            $query->whereNotNull('unassigned_at');
        }

        $this->athletes = $query->latest()->get()->map(function ($assignment) {
            return $assignment->member;
        });
    }

    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
        $this->loadAthletes();
    }

    public function viewAthlete(string $athleteId): void
    {
        $this->redirect(route('pt.athletes.show', $athleteId));
    }

    public function render(): View
    {
        return view('livewire.pt.athlete-list');
    }
}
