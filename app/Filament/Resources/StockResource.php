<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockResource\Pages;
use App\Models\Stock;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class StockResource extends Resource
{
    protected static ?string $model = Stock::class;

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?string $navigationIcon = 'heroicon-o-cube-transparent';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Stock')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('product_id')->relationship('product', 'name')->required()->preload()->searchable(),
                    Forms\Components\Select::make('warehouse_id')->relationship('warehouse', 'name')->required()->preload(),
                    Forms\Components\TextInput::make('quantity')->numeric()->default(0)->required(),
                    Forms\Components\TextInput::make('reserved_quantity')->numeric()->default(0),
                    Forms\Components\TextInput::make('damaged_quantity')->numeric()->default(0),
                    Forms\Components\TextInput::make('returned_quantity')->numeric()->default(0),
                    Forms\Components\TextInput::make('low_stock_threshold')->numeric()->default(10),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('product.name')->label('Product')->searchable()->sortable()->weight('medium'),
                Tables\Columns\TextColumn::make('warehouse.name')->sortable(),
                Tables\Columns\TextColumn::make('quantity')->numeric()->sortable()
                    ->badge()
                    ->color(fn (Stock $record) => $record->is_low ? 'danger' : ($record->available <= ($record->low_stock_threshold * 2) ? 'warning' : 'success')),
                Tables\Columns\TextColumn::make('reserved_quantity')->label('Reserved')->numeric()->toggleable(),
                Tables\Columns\TextColumn::make('available')->label('Available')->getStateUsing(fn (Stock $record) => $record->available),
                Tables\Columns\TextColumn::make('damaged_quantity')->label('Damaged')->numeric()->toggleable(),
                Tables\Columns\TextColumn::make('returned_quantity')->label('Returned')->numeric()->toggleable(),
                Tables\Columns\TextColumn::make('low_stock_threshold')->label('Threshold')->numeric()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('warehouse_id')->relationship('warehouse', 'name')->preload(),
                SelectFilter::make('product_id')->relationship('product', 'name')->preload()->searchable(),
                TernaryFilter::make('low_stock')
                    ->label('Low stock')
                    ->placeholder('All stocks')
                    ->queries(
                        true: fn ($q) => $q->whereColumn('quantity', '<=', 'low_stock_threshold'),
                        false: fn ($q) => $q->whereColumn('quantity', '>', 'low_stock_threshold'),
                        blank: fn ($q) => $q,
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStocks::route('/'),
            'create' => Pages\CreateStock::route('/create'),
            'edit' => Pages\EditStock::route('/{record}/edit'),
        ];
    }
}
