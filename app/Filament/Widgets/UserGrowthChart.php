<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class UserGrowthChart extends ChartWidget
{
    protected static ?string $heading = 'User Growth (Last 30 Days)';

    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $data = User::query()
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Fill in missing dates with zero
        $dates = collect();
        $counts = collect();

        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dates->push(now()->subDays($i)->format('M j'));

            $count = $data->firstWhere('date', $date)?->count ?? 0;
            $counts->push($count);
        }

        return [
            'datasets' => [
                [
                    'label' => 'New Users',
                    'data' => $counts->toArray(),
                    'borderColor' => 'rgba(251, 191, 36, 1)',
                    'backgroundColor' => 'rgba(251, 191, 36, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $dates->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
