<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CountryPerformanceChart;
use App\Filament\Widgets\LowStockTable;
use App\Filament\Widgets\PlatformBreakdownChart;
use App\Filament\Widgets\ProductPerformanceChart;
use App\Filament\Widgets\RecentOrdersTable;
use App\Filament\Widgets\RevenueProfitChart;
use App\Filament\Widgets\SpendEvolutionChart;
use App\Filament\Widgets\StatsOverviewWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Overview';

    protected static ?int $navigationSort = 0;

    public function getColumns(): int|string|array
    {
        return 12;
    }

    public function getWidgets(): array
    {
        return [
            StatsOverviewWidget::class,
            SpendEvolutionChart::class,
            RevenueProfitChart::class,
            CountryPerformanceChart::class,
            ProductPerformanceChart::class,
            PlatformBreakdownChart::class,
            RecentOrdersTable::class,
            LowStockTable::class,
        ];
    }
}
