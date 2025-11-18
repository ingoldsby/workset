<?php

namespace App\Livewire\Programs;

use App\Models\Program;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class ProgramList extends Component
{
    public Collection $programs;

    public function mount(): void
    {
        $this->loadPrograms();
    }

    public function loadPrograms(): void
    {
        $query = Program::with(['activeVersion', 'owner'])
            ->where(function ($q) {
                $q->where('owner_id', Auth::id())
                    ->orWhere('created_by_pt_id', Auth::id());
            });

        if (Auth::user()->isPt()) {
            // PTs can see programs for their assigned members
            $memberIds = Auth::user()->memberAssignments()
                ->whereNull('unassigned_at')
                ->pluck('member_id');

            $query->orWhereIn('owner_id', $memberIds);
        }

        $this->programs = $query->latest()->get();
    }

    public function createProgram(): void
    {
        $this->redirect(route('programs.create'));
    }

    public function viewProgram(string $programId): void
    {
        $this->redirect(route('programs.show', $programId));
    }

    public function render(): View
    {
        return view('livewire.programs.program-list');
    }
}
