<?php

namespace App\Filament\Widgets;

use App\Models\AdSpend;
use Filament\Widgets\ChartWidget;

class SpendEvolutionChart extends ChartWidget
{
    protected static ?string $heading = 'Spend & Leads — Last 30 days';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $from = now()->subDays(29)->startOfDay()->toDateString();
        $to = now()->toDateString();

        $rows = AdSpend::query()
            ->selectRaw('DATE(date) as d, SUM(spend) as spend, SUM(leads) as leads')
            ->whereBetween('date', [$from, $to])
            ->groupBy('d')
            ->orderBy('d')
            ->get()
            ->keyBy('d');

        $labels = [];
        $spends = [];
        $leads = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $labels[] = now()->subDays($i)->format('M d');
            $spends[] = (float) optional($rows->get($date))->spend ?? 0;
            $leads[] = (int) optional($rows->get($date))->leads ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Spend',
                    'data' => $spends,
                    'borderColor' => '#6366f1',
                    'backgroundColor' => 'rgba(99, 102, 241, 0.12)',
                    'fill' => true,
                    'tension' => 0.4,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Leads',
                    'data' => $leads,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.12)',
                    'fill' => false,
                    'tension' => 0.4,
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => ['beginAtZero' => true, 'position' => 'left'],
                'y1' => ['beginAtZero' => true, 'position' => 'right', 'grid' => ['drawOnChartArea' => false]],
            ],
            'plugins' => [
                'legend' => ['position' => 'bottom'],
            ],
        ];
    }
}
