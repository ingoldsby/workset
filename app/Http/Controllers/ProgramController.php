<?php

namespace App\Http\Controllers;

use App\Models\Program;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProgramController extends Controller
{
    public function show(Program $program): View
    {
        $this->authorize('view', $program);

        $program->load(['activeVersion.days.exercises.exercise', 'owner']);

        return view('programs.show', compact('program'));
    }

    public function create(): View
    {
        return view('programs.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'visibility' => ['required', 'in:private,public'],
            'is_template' => ['boolean'],
            'category' => ['nullable', 'string', 'max:100'],
            'tags' => ['nullable', 'array'],
        ]);

        $program = Program::create([
            'owner_id' => Auth::id(),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'visibility' => $validated['visibility'],
            'is_template' => $validated['is_template'] ?? false,
            'category' => $validated['category'] ?? null,
            'tags' => $validated['tags'] ?? null,
        ]);

        return to_route('programs.edit', $program)
            ->with('success', 'Program created successfully. Now add workout days and exercises.');
    }

    public function edit(Program $program): View
    {
        $this->authorize('update', $program);

        $program->load(['activeVersion.days.exercises.exercise', 'owner']);

        return view('programs.edit', compact('program'));
    }

    public function update(Request $request, Program $program): RedirectResponse
    {
        $this->authorize('update', $program);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'visibility' => ['required', 'in:private,public'],
            'is_template' => ['boolean'],
            'category' => ['nullable', 'string', 'max:100'],
            'tags' => ['nullable', 'array'],
        ]);

        $program->update($validated);

        return to_route('programs.show', $program)
            ->with('success', 'Program updated successfully.');
    }

    public function destroy(Program $program): RedirectResponse
    {
        $this->authorize('delete', $program);

        $program->delete();

        return to_route('programs.index')
            ->with('success', 'Program deleted successfully.');
    }
}
