<?php

namespace App\Livewire\Programs;

use App\Models\Program;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class ProgramDetail extends Component
{
    public Program $program;
    public bool $showEditModal = false;
    public string $editName = '';
    public string $editDescription = '';
    public bool $showCreateVersionModal = false;
    public string $versionChangeNotes = '';

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

    public function editProgram(): void
    {
        $this->editName = $this->program->name;
        $this->editDescription = $this->program->description ?? '';
        $this->showEditModal = true;
    }

    public function saveEdit(): void
    {
        $this->validate([
            'editName' => 'required|string|max:255',
            'editDescription' => 'nullable|string',
        ]);

        $this->program->update([
            'name' => $this->editName,
            'description' => $this->editDescription,
        ]);

        $this->showEditModal = false;
        $this->program->refresh();

        session()->flash('message', 'Program updated successfully.');
    }

    public function cancelEdit(): void
    {
        $this->reset(['editName', 'editDescription', 'showEditModal']);
    }

    public function activateVersion(string $versionId): void
    {
        // Deactivate all versions
        $this->program->versions()->update(['is_active' => false]);

        // Activate the selected version
        $this->program->versions()->where('id', $versionId)->update(['is_active' => true]);

        $this->program->refresh();
        $this->program->load(['activeVersion', 'versions']);

        session()->flash('message', 'Version activated successfully.');
    }

    public function createVersion(): void
    {
        $this->versionChangeNotes = '';
        $this->showCreateVersionModal = true;
    }

    public function saveVersion(): void
    {
        $this->validate([
            'versionChangeNotes' => 'nullable|string|max:500',
        ]);

        // Get the next version number
        $nextVersionNumber = $this->program->versions()->max('version_number') + 1;

        // Create the new version
        $newVersion = $this->program->versions()->create([
            'created_by' => Auth::id(),
            'version_number' => $nextVersionNumber,
            'change_notes' => $this->versionChangeNotes ?: null,
            'is_active' => $this->program->versions()->count() === 0, // Make first version active
        ]);

        $this->showCreateVersionModal = false;
        $this->versionChangeNotes = '';

        $this->program->refresh();
        $this->program->load(['activeVersion', 'versions']);

        session()->flash('message', 'Version created successfully.');
    }

    public function cancelCreateVersion(): void
    {
        $this->reset(['versionChangeNotes', 'showCreateVersionModal']);
    }

    public function render(): View
    {
        return view('livewire.programs.program-detail');
    }
}
