<?php

namespace App\Filament\Widgets;

use App\Enums\Role;
use App\Models\Invite;
use App\Models\Program;
use App\Models\PtAssignment;
use App\Models\TrainingSession;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::count())
                ->description('All registered users')
                ->descriptionIcon('heroicon-o-users')
                ->color('success'),

            Stat::make('Personal Trainers', User::where('role', Role::PT)->count())
                ->description('Active PTs')
                ->descriptionIcon('heroicon-o-academic-cap')
                ->color('warning'),

            Stat::make('Members', User::where('role', Role::Member)->count())
                ->description('Total members')
                ->descriptionIcon('heroicon-o-user-group')
                ->color('info'),

            Stat::make('Active PT Assignments', PtAssignment::whereNull('unassigned_at')->count())
                ->description('Current PT-Member pairs')
                ->descriptionIcon('heroicon-o-link')
                ->color('primary'),

            Stat::make('Total Programs', Program::count())
                ->description('Created programs')
                ->descriptionIcon('heroicon-o-document-text')
                ->color('success'),

            Stat::make('Pending Invites', Invite::whereNull('accepted_at')
                ->where('expires_at', '>', now())
                ->count())
                ->description('Awaiting acceptance')
                ->descriptionIcon('heroicon-o-envelope')
                ->color('warning'),

            Stat::make('Training Sessions (30d)', TrainingSession::where('created_at', '>=', now()->subDays(30))->count())
                ->description('Last 30 days')
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color('info'),

            Stat::make('New Users (7d)', User::where('created_at', '>=', now()->subDays(7))->count())
                ->description('Last 7 days')
                ->descriptionIcon('heroicon-o-user-plus')
                ->color('success'),
        ];
    }
}
