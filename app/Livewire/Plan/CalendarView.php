<?php

namespace App\Livewire\Plan;

use App\Models\SessionPlan;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class CalendarView extends Component
{
    public int $currentYear;
    public int $currentMonth;
    public Collection $sessionPlans;
    public ?int $selectedUserId = null;

    public function mount(): void
    {
        $this->currentYear = now()->year;
        $this->currentMonth = now()->month;
        $this->selectedUserId = Auth::user()->isPt() ? null : Auth::id();
        $this->loadSessionPlans();
    }

    public function loadSessionPlans(): void
    {
        $startOfMonth = Carbon::create($this->currentYear, $this->currentMonth, 1)->startOfDay();
        $endOfMonth = $startOfMonth->copy()->endOfMonth()->endOfDay();

        $query = SessionPlan::query()
            ->whereBetween('scheduled_date', [$startOfMonth, $endOfMonth])
            ->with(['programDay', 'user']);

        if ($this->selectedUserId) {
            $query->where('user_id', $this->selectedUserId);
        } elseif (Auth::user()->isPt()) {
            // Show all assigned members' sessions for PTs
            $memberIds = Auth::user()->memberAssignments()
                ->whereNull('unassigned_at')
                ->pluck('member_id');
            $query->whereIn('user_id', $memberIds);
        } else {
            $query->where('user_id', Auth::id());
        }

        $this->sessionPlans = $query->get();
    }

    public function previousMonth(): void
    {
        if ($this->currentMonth === 1) {
            $this->currentMonth = 12;
            $this->currentYear--;
        } else {
            $this->currentMonth--;
        }

        $this->loadSessionPlans();
    }

    public function nextMonth(): void
    {
        if ($this->currentMonth === 12) {
            $this->currentMonth = 1;
            $this->currentYear++;
        } else {
            $this->currentMonth++;
        }

        $this->loadSessionPlans();
    }

    public function goToToday(): void
    {
        $this->currentYear = now()->year;
        $this->currentMonth = now()->month;
        $this->loadSessionPlans();
    }

    public function getCalendarDays(): array
    {
        $startOfMonth = Carbon::create($this->currentYear, $this->currentMonth, 1);
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        // Start from the Sunday before the first day of the month
        $startDate = $startOfMonth->copy()->startOfWeek(Carbon::SUNDAY);

        // End on the Saturday after the last day of the month
        $endDate = $endOfMonth->copy()->endOfWeek(Carbon::SATURDAY);

        $days = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $dateString = $currentDate->format('Y-m-d');
            $sessionsForDay = $this->sessionPlans->filter(function ($plan) use ($dateString) {
                return $plan->scheduled_date->format('Y-m-d') === $dateString;
            });

            $days[] = [
                'date' => $currentDate->copy(),
                'isCurrentMonth' => $currentDate->month === $this->currentMonth,
                'isToday' => $currentDate->isToday(),
                'sessions' => $sessionsForDay,
            ];

            $currentDate->addDay();
        }

        return $days;
    }

    public function render(): View
    {
        $calendarDays = $this->getCalendarDays();
        $monthName = Carbon::create($this->currentYear, $this->currentMonth, 1)->format('F Y');

        return view('livewire.plan.calendar-view', [
            'calendarDays' => $calendarDays,
            'monthName' => $monthName,
            'canManageSchedule' => Auth::user()->isPt() || Auth::user()->isAdmin(),
        ]);
    }
}
