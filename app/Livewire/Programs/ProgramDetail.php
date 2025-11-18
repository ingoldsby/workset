<?php

namespace App\Livewire\Programs;

use App\Models\Program;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class ProgramDetail extends Component
{
    public Program $program;

    public function mount(string $programId): void
    {
        $this->program = Program::with(['owner', 'activeVersion', 'versions'])
            ->findOrFail($programId);

        // Authorise access to this program
        if ($this->program->owner_id !== Auth::id() && !Auth::user()->isPt()) {
            abort(403, 'Unauthorised access to this program.');
        }

        // PTs can only view their assigned members' programs
        if (Auth::user()->isPt() && $this->program->owner_id !== Auth::id()) {
            $memberIds = Auth::user()->memberAssignments()
                ->whereNull('unassigned_at')
                ->pluck('member_id');

            if (!$memberIds->contains($this->program->owner_id)) {
                abort(403, 'Unauthorised access to this program.');
            }
        }
    }

    public function render(): View
    {
        return view('livewire.programs.program-detail');
    }
}
