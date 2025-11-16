<?php

namespace App\Livewire\Analytics;

use App\Models\PersonalRecord;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class PersonalRecords extends Component
{
    public string $filterType = 'all'; // all, max_weight, max_volume, max_reps_at_weight

    public function setFilter(string $type): void
    {
        $this->filterType = $type;
    }

    public function getPersonalRecords(): Collection
    {
        $query = PersonalRecord::query()
            ->where('user_id', Auth::id())
            ->with(['exercise', 'memberExercise', 'sessionSet'])
            ->orderBy('achieved_at', 'desc');

        if ($this->filterType !== 'all') {
            $query->where('record_type', $this->filterType);
        }

        return $query->get()
            ->groupBy(fn ($record) => $record->exercise_id ?? $record->member_exercise_id)
            ->map(fn ($records) => $records->groupBy('record_type'))
            ->take(10);
    }

    public function getRecentRecords(): Collection
    {
        return PersonalRecord::query()
            ->where('user_id', Auth::id())
            ->with(['exercise', 'memberExercise'])
            ->orderBy('achieved_at', 'desc')
            ->limit(5)
            ->get();
    }

    public function render(): View
    {
        return view('livewire.analytics.personal-records', [
            'personalRecords' => $this->getPersonalRecords(),
            'recentRecords' => $this->getRecentRecords(),
        ]);
    }
}
