<?php

namespace App\Livewire\Programs;

use App\Models\Program;
use App\Models\ProgramVersion;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class VersionEditor extends Component
{
    public Program $program;
    public ProgramVersion $version;

    public function mount(string $programId, string $versionId): void
    {
        $this->program = Program::with(['owner'])->findOrFail($programId);
        $this->version = ProgramVersion::with(['days.exercises'])->findOrFail($versionId);

        // Authorise access
        if ($this->program->owner_id !== Auth::id() && !Auth::user()->isPt()) {
            abort(403, 'Unauthorised access to this program.');
        }

        // Verify version belongs to program
        if ($this->version->program_id !== $this->program->id) {
            abort(404, 'Version not found for this program.');
        }
    }

    public function backToProgram(): void
    {
        $this->redirect(route('programs.show', $this->program->id));
    }

    public function render(): View
    {
        return view('livewire.programs.version-editor');
    }
}
