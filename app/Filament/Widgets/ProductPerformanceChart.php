<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class ProductPerformanceChart extends ChartWidget
{
    protected static ?string $heading = 'Product Performance (Profit)';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = ['md' => 6];

    protected static ?string $maxHeight = '320px';

    protected function getData(): array
    {
        $rows = Order::query()
            ->selectRaw('products.name as product, SUM(order_items.quantity * (order_items.unit_price - order_items.unit_cost)) as profit')
            ->join('order_items', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->where('orders.status', Order::STATUS_DELIVERED)
            ->whereBetween('orders.created_at', [now()->subDays(29)->startOfDay(), now()->endOfDay()])
            ->groupBy('products.name')
            ->orderByDesc('profit')
            ->limit(8)
            ->get();

        return [
            'datasets' => [[
                'label' => 'Profit',
                'data' => $rows->pluck('profit')->map(fn ($v) => (float) $v)->toArray(),
                'backgroundColor' => ['#10b981', '#6366f1', '#f59e0b', '#0ea5e9', '#a855f7', '#ec4899', '#ef4444', '#84cc16'],
                'borderRadius' => 6,
            ]],
            'labels' => $rows->pluck('product')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
