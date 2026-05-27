<?php

namespace App\Filament\Pages;

use App\Models\AdSpend;
use App\Models\Order;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class Analytics extends Page
{
    protected static ?string $navigationGroup = 'Insights';

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static string $view = 'filament.pages.analytics';

    protected static ?string $title = 'Analytics';

    protected static ?int $navigationSort = 2;

    public function getMetrics(): array
    {
        $from = now()->subDays(29)->startOfDay();
        $to = now()->endOfDay();

        $totalSpend = (float) AdSpend::whereBetween('date', [$from, $to])->sum('spend');
        $totalLeads = (int) AdSpend::whereBetween('date', [$from, $to])->sum('leads');
        $confirmed = Order::whereBetween('created_at', [$from, $to])->whereIn('status', [Order::STATUS_CONFIRMED, Order::STATUS_SHIPPED, Order::STATUS_DELIVERED])->count();
        $delivered = Order::whereBetween('created_at', [$from, $to])->where('status', Order::STATUS_DELIVERED)->count();
        $revenue = (float) Order::whereBetween('created_at', [$from, $to])->where('status', Order::STATUS_DELIVERED)->sum('total');
        $profit = (float) Order::whereBetween('created_at', [$from, $to])->where('status', Order::STATUS_DELIVERED)->sum('profit');

        return [
            'cpl' => $totalLeads > 0 ? $totalSpend / $totalLeads : 0,
            'cpa' => $delivered > 0 ? $totalSpend / $delivered : 0,
            'roas' => $totalSpend > 0 ? $revenue / $totalSpend : 0,
            'margin' => $revenue > 0 ? ($profit / $revenue) * 100 : 0,
            'confirmation_rate' => $totalLeads > 0 ? ($confirmed / $totalLeads) * 100 : 0,
            'delivery_rate' => $confirmed > 0 ? ($delivered / $confirmed) * 100 : 0,
        ];
    }

    public function getPlatformBreakdown(): array
    {
        return DB::table('ad_spends')
            ->join('platforms', 'platforms.id', '=', 'ad_spends.platform_id')
            ->whereBetween('date', [now()->subDays(29)->toDateString(), now()->toDateString()])
            ->groupBy('platforms.id', 'platforms.name', 'platforms.color')
            ->selectRaw('platforms.name as name, platforms.color as color,
                SUM(spend) as spend, SUM(leads) as leads,
                CASE WHEN SUM(leads) > 0 THEN SUM(spend) / SUM(leads) ELSE 0 END as cpl')
            ->orderByDesc('spend')
            ->get()
            ->toArray();
    }

    public function getTeamPerformance(): array
    {
        $from = now()->subDays(29)->startOfDay();
        $to = now()->endOfDay();

        return DB::table('users')
            ->leftJoin('orders', function ($j) use ($from, $to) {
                $j->on('orders.agent_id', '=', 'users.id')
                    ->whereBetween('orders.created_at', [$from, $to]);
            })
            ->groupBy('users.id', 'users.name')
            ->selectRaw('users.name,
                COUNT(orders.id) as total_orders,
                SUM(CASE WHEN orders.status = "delivered" THEN 1 ELSE 0 END) as delivered_orders,
                SUM(CASE WHEN orders.status = "delivered" THEN orders.total ELSE 0 END) as revenue,
                SUM(CASE WHEN orders.status = "delivered" THEN orders.profit ELSE 0 END) as profit')
            ->havingRaw('total_orders > 0')
            ->orderByDesc('profit')
            ->get()
            ->toArray();
    }
}
