<?php

namespace App\Filament\Widgets;

use App\Models\AdSpend;
use App\Models\Country;
use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected static ?string $pollingInterval = null;

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $from = now()->subDays(29)->startOfDay();
        $to = now()->endOfDay();

        $spendQuery = AdSpend::query()->whereBetween('date', [$from, $to]);
        $totalSpend = (float) $spendQuery->sum('spend');
        $totalLeads = (int) $spendQuery->sum('leads');
        $avgCpl = $totalLeads > 0 ? $totalSpend / $totalLeads : 0;

        $orders = Order::query()->whereBetween('created_at', [$from, $to]);
        $totalRevenue = (float) (clone $orders)->where('status', Order::STATUS_DELIVERED)->sum('total');
        $netProfit = (float) (clone $orders)->where('status', Order::STATUS_DELIVERED)->sum('profit') - (float) $totalSpend;
        $delivered = (clone $orders)->where('status', Order::STATUS_DELIVERED)->count();

        $topProduct = Order::query()
            ->selectRaw('order_items.product_id, SUM(order_items.quantity * order_items.unit_price) as rev')
            ->join('order_items', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', Order::STATUS_DELIVERED)
            ->whereBetween('orders.created_at', [$from, $to])
            ->groupBy('order_items.product_id')
            ->orderByDesc('rev')
            ->first();
        $topProductName = $topProduct ? optional(Product::find($topProduct->product_id))->name : '—';

        $topCountry = Order::query()
            ->selectRaw('country_id, SUM(total) as rev')
            ->where('status', Order::STATUS_DELIVERED)
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('country_id')
            ->orderByDesc('rev')
            ->first();
        $topCountryName = $topCountry ? optional(Country::find($topCountry->country_id))->name : '—';

        return [
            Stat::make('Total Spend', '$'.number_format($totalSpend, 2))
                ->description('Last 30 days')
                ->descriptionIcon('heroicon-m-banknotes')
                ->chart($this->dailySeries($spendQuery, 'spend')),
            Stat::make('Total Leads', number_format($totalLeads))
                ->description('Last 30 days')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info')
                ->chart($this->dailySeries(AdSpend::query()->whereBetween('date', [$from, $to]), 'leads')),
            Stat::make('Avg. CPL', '$'.number_format($avgCpl, 2))
                ->description('Cost per lead')
                ->descriptionIcon('heroicon-m-calculator')
                ->color($avgCpl > 0 ? ($avgCpl < 2 ? 'success' : ($avgCpl < 4 ? 'warning' : 'danger')) : 'gray'),
            Stat::make('Total Revenue', '$'.number_format($totalRevenue, 2))
                ->description('Delivered orders')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),
            Stat::make('Net Profit', '$'.number_format($netProfit, 2))
                ->description('Rev. – COGS – Delivery – Ads')
                ->descriptionIcon($netProfit >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($netProfit >= 0 ? 'success' : 'danger'),
            Stat::make('Delivered Orders', number_format($delivered))
                ->description('Last 30 days')
                ->descriptionIcon('heroicon-m-truck')
                ->color('info'),
            Stat::make('Top Product', $topProductName)
                ->description('By revenue')
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary'),
            Stat::make('Top Country', $topCountryName)
                ->description('By revenue')
                ->descriptionIcon('heroicon-m-globe-europe-africa')
                ->color('primary'),
        ];
    }

    protected function dailySeries($query, string $column): array
    {
        $rows = (clone $query)
            ->selectRaw('DATE(date) as d, SUM('.$column.') as v')
            ->groupBy('d')
            ->orderBy('d')
            ->pluck('v')
            ->toArray();

        return array_map(fn ($v) => (float) $v, $rows ?: [0]);
    }
}
