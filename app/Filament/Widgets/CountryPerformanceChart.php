<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class CountryPerformanceChart extends ChartWidget
{
    protected static ?string $heading = 'Country Performance (Revenue)';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = ['md' => 6];

    protected static ?string $maxHeight = '320px';

    protected function getData(): array
    {
        $rows = Order::query()
            ->selectRaw('countries.name as country, SUM(orders.total) as revenue')
            ->join('countries', 'countries.id', '=', 'orders.country_id')
            ->where('orders.status', Order::STATUS_DELIVERED)
            ->whereBetween('orders.created_at', [now()->subDays(29)->startOfDay(), now()->endOfDay()])
            ->groupBy('countries.name')
            ->orderByDesc('revenue')
            ->limit(8)
            ->get();

        return [
            'datasets' => [[
                'label' => 'Revenue',
                'data' => $rows->pluck('revenue')->map(fn ($v) => (float) $v)->toArray(),
                'backgroundColor' => ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#0ea5e9', '#a855f7', '#ec4899', '#84cc16'],
                'borderRadius' => 6,
            ]],
            'labels' => $rows->pluck('country')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
