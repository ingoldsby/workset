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
    public bool $showCreateModal = false;
    public string $name = '';
    public string $description = '';

    public function mount(): void
    {
        $this->loadPrograms();
    }

    public function loadPrograms(): void
    {
        $query = Program::with(['owner', 'activeVersion'])
            ->where('owner_id', Auth::id());

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
        $this->showCreateModal = true;
    }

    public function saveProgram(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Program::create([
            'owner_id' => Auth::id(),
            'name' => $this->name,
            'description' => $this->description,
            'visibility' => 'private',
        ]);

        $this->reset(['name', 'description', 'showCreateModal']);
        $this->loadPrograms();

        session()->flash('message', 'Program created successfully.');
    }

    public function cancelCreate(): void
    {
        $this->reset(['name', 'description', 'showCreateModal']);
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
