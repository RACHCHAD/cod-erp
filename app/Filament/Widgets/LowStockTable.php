<?php

namespace App\Filament\Widgets;

use App\Models\Stock;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockTable extends BaseWidget
{
    protected static ?int $sort = 8;

    protected int|string|array $columnSpan = ['md' => 6];

    protected static ?string $heading = 'Low Stock Alerts';

    public function table(Table $table): Table
    {
        return $table
            ->query(Stock::query()->whereColumn('quantity', '<=', 'low_stock_threshold')->orderBy('quantity'))
            ->columns([
                Tables\Columns\TextColumn::make('product.name')->label('Product')->weight('medium'),
                Tables\Columns\TextColumn::make('warehouse.name')->label('Warehouse'),
                Tables\Columns\TextColumn::make('quantity')->badge()->color('danger'),
                Tables\Columns\TextColumn::make('low_stock_threshold')->label('Threshold'),
            ])
            ->emptyStateHeading('No low stock')
            ->paginated(false);
    }
}
