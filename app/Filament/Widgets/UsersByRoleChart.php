<?php

namespace App\Filament\Widgets;

use App\Enums\Role;
use App\Models\User;
use Filament\Widgets\ChartWidget;

class UsersByRoleChart extends ChartWidget
{
    protected static ?string $heading = 'Users by Role';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $adminCount = User::where('role', Role::Admin)->count();
        $ptCount = User::where('role', Role::PT)->count();
        $memberCount = User::where('role', Role::Member)->count();

        return [
            'datasets' => [
                [
                    'label' => 'Users by Role',
                    'data' => [$adminCount, $ptCount, $memberCount],
                    'backgroundColor' => [
                        'rgba(239, 68, 68, 0.7)',   // Red for Admin
                        'rgba(251, 191, 36, 0.7)',  // Amber for PT
                        'rgba(34, 197, 94, 0.7)',   // Green for Member
                    ],
                ],
            ],
            'labels' => ['Admins', 'PTs', 'Members'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
