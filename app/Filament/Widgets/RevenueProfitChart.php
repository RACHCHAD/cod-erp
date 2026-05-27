<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class RevenueProfitChart extends ChartWidget
{
    protected static ?string $heading = 'Revenue & Profit — Last 30 days';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $from = now()->subDays(29)->startOfDay();
        $to = now()->endOfDay();

        $rows = Order::query()
            ->selectRaw('DATE(created_at) as d, SUM(total) as revenue, SUM(profit) as profit')
            ->where('status', Order::STATUS_DELIVERED)
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('d')
            ->orderBy('d')
            ->get()
            ->keyBy('d');

        $labels = [];
        $revenue = [];
        $profit = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $labels[] = now()->subDays($i)->format('M d');
            $row = $rows->get($date);
            $revenue[] = (float) optional($row)->revenue ?? 0;
            $profit[] = (float) optional($row)->profit ?? 0;
        }

        return [
            'datasets' => [
                ['label' => 'Revenue', 'data' => $revenue, 'borderColor' => '#0ea5e9', 'backgroundColor' => 'rgba(14, 165, 233, 0.18)', 'fill' => true, 'tension' => 0.4],
                ['label' => 'Profit', 'data' => $profit, 'borderColor' => '#10b981', 'backgroundColor' => 'rgba(16, 185, 129, 0.18)', 'fill' => true, 'tension' => 0.4],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
