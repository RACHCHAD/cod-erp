<?php

namespace App\Filament\Widgets;

use App\Models\AdSpend;
use Filament\Widgets\ChartWidget;

class PlatformBreakdownChart extends ChartWidget
{
    protected static ?string $heading = 'Spend by Platform';

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = ['md' => 6];

    protected static ?string $maxHeight = '320px';

    protected function getData(): array
    {
        $rows = AdSpend::query()
            ->selectRaw('platforms.name as platform, platforms.color as color, SUM(spend) as spend')
            ->join('platforms', 'platforms.id', '=', 'ad_spends.platform_id')
            ->whereBetween('date', [now()->subDays(29)->toDateString(), now()->toDateString()])
            ->groupBy('platforms.name', 'platforms.color')
            ->orderByDesc('spend')
            ->get();

        return [
            'datasets' => [[
                'label' => 'Spend',
                'data' => $rows->pluck('spend')->map(fn ($v) => (float) $v)->toArray(),
                'backgroundColor' => $rows->pluck('color')->toArray(),
            ]],
            'labels' => $rows->pluck('platform')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
