<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentOrdersTable extends BaseWidget
{
    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = ['md' => 6];

    protected static ?string $heading = 'Recent Orders';

    public function table(Table $table): Table
    {
        return $table
            ->query(Order::query()->latest()->limit(8))
            ->columns([
                Tables\Columns\TextColumn::make('order_number')->label('#')->weight('medium'),
                Tables\Columns\TextColumn::make('country.label')->label('Country'),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                    Order::STATUS_DELIVERED => 'success',
                    Order::STATUS_CONFIRMED, Order::STATUS_SHIPPED => 'info',
                    Order::STATUS_PENDING => 'warning',
                    default => 'danger',
                }),
                Tables\Columns\TextColumn::make('total')->money('USD'),
                Tables\Columns\TextColumn::make('profit')->money('USD')->color(fn ($state) => (float) $state >= 0 ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('created_at')->since(),
            ])
            ->paginated(false);
    }
}
