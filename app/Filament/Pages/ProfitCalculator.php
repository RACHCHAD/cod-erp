<?php

namespace App\Filament\Pages;

use App\Models\AdSpend;
use App\Models\Country;
use App\Models\Expense;
use App\Models\Order;
use App\Models\Platform;
use App\Models\Product;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class ProfitCalculator extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationGroup = 'Insights';

    protected static ?string $navigationIcon = 'heroicon-o-calculator';

    protected static string $view = 'filament.pages.profit-calculator';

    protected static ?string $title = 'Profit Calculator';

    protected static ?int $navigationSort = 1;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'from' => now()->subDays(30)->toDateString(),
            'until' => now()->toDateString(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Filters')
                    ->columns(6)
                    ->schema([
                        DatePicker::make('from')->native(false),
                        DatePicker::make('until')->native(false),
                        Select::make('product_id')->label('Product')->options(Product::pluck('name', 'id'))->searchable(),
                        Select::make('country_id')->label('Country')->options(Country::pluck('name', 'id'))->searchable(),
                        Select::make('platform_id')->label('Platform')->options(Platform::pluck('name', 'id')),
                    ]),
            ])
            ->statePath('data');
    }

    public function getMetrics(): array
    {
        $d = $this->form->getState();
        $from = $d['from'] ?? now()->subDays(30)->toDateString();
        $until = $d['until'] ?? now()->toDateString();

        $orderQ = Order::query()
            ->whereBetween('created_at', [$from.' 00:00:00', $until.' 23:59:59'])
            ->where('status', Order::STATUS_DELIVERED)
            ->when($d['country_id'] ?? null, fn ($q, $v) => $q->where('country_id', $v))
            ->when($d['product_id'] ?? null, function ($q, $v) {
                $q->whereHas('items', fn ($qi) => $qi->where('product_id', $v));
            });

        $revenue = (float) (clone $orderQ)->sum('total');
        $productCost = (float) (clone $orderQ)->sum('product_cost');
        $deliveryCost = (float) (clone $orderQ)->sum('delivery_cost');
        $refunds = (float) (clone $orderQ)->sum('refund_amount');

        $adSpend = (float) AdSpend::query()
            ->whereBetween('date', [$from, $until])
            ->when($d['country_id'] ?? null, fn ($q, $v) => $q->where('country_id', $v))
            ->when($d['product_id'] ?? null, fn ($q, $v) => $q->where('product_id', $v))
            ->when($d['platform_id'] ?? null, fn ($q, $v) => $q->where('platform_id', $v))
            ->sum('spend');

        $miscExpenses = (float) Expense::query()
            ->whereBetween('date', [$from, $until])
            ->when($d['country_id'] ?? null, fn ($q, $v) => $q->where('country_id', $v))
            ->sum('amount');

        $netProfit = $revenue - $productCost - $deliveryCost - $adSpend - $refunds - $miscExpenses;
        $margin = $revenue > 0 ? ($netProfit / $revenue) * 100 : 0;
        $roas = $adSpend > 0 ? $revenue / $adSpend : 0;

        return [
            'revenue' => $revenue,
            'product_cost' => $productCost,
            'delivery_cost' => $deliveryCost,
            'ad_spend' => $adSpend,
            'refunds' => $refunds,
            'misc_expenses' => $miscExpenses,
            'net_profit' => $netProfit,
            'margin' => $margin,
            'roas' => $roas,
        ];
    }

    public function getProductBreakdown(): array
    {
        $d = $this->form->getState();
        $from = $d['from'] ?? now()->subDays(30)->toDateString();
        $until = $d['until'] ?? now()->toDateString();

        return DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->where('orders.status', Order::STATUS_DELIVERED)
            ->whereBetween('orders.created_at', [$from.' 00:00:00', $until.' 23:59:59'])
            ->groupBy('products.id', 'products.name')
            ->selectRaw('products.name as name,
                SUM(order_items.quantity * order_items.unit_price) as revenue,
                SUM(order_items.quantity * order_items.unit_cost) as cost,
                SUM(order_items.quantity * (order_items.unit_price - order_items.unit_cost)) as profit,
                SUM(order_items.quantity) as units')
            ->orderByDesc('profit')
            ->get()
            ->toArray();
    }

    public function getCountryBreakdown(): array
    {
        $d = $this->form->getState();
        $from = $d['from'] ?? now()->subDays(30)->toDateString();
        $until = $d['until'] ?? now()->toDateString();

        return DB::table('orders')
            ->join('countries', 'countries.id', '=', 'orders.country_id')
            ->where('orders.status', Order::STATUS_DELIVERED)
            ->whereBetween('orders.created_at', [$from.' 00:00:00', $until.' 23:59:59'])
            ->groupBy('countries.id', 'countries.name', 'countries.flag_emoji')
            ->selectRaw('countries.flag_emoji as flag, countries.name as name,
                SUM(orders.total) as revenue, SUM(orders.profit) as profit,
                COUNT(orders.id) as orders')
            ->orderByDesc('profit')
            ->get()
            ->toArray();
    }
}
