<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationGroup = 'Operations';

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Order')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('order_number')->disabled()->dehydrated(false),
                    Forms\Components\Select::make('lead_id')->relationship('lead', 'customer_name')->searchable()->preload(),
                    Forms\Components\Select::make('country_id')->relationship('country', 'name')->required()->preload()->searchable(),
                    Forms\Components\Select::make('agent_id')->relationship('agent', 'name')->preload()->searchable(),
                    Forms\Components\Select::make('warehouse_id')->relationship('warehouse', 'name')->preload(),
                    Forms\Components\Select::make('status')->required()->options([
                        Order::STATUS_PENDING => 'Pending',
                        Order::STATUS_CONFIRMED => 'Confirmed',
                        Order::STATUS_SHIPPED => 'Shipped',
                        Order::STATUS_DELIVERED => 'Delivered',
                        Order::STATUS_CANCELLED => 'Cancelled',
                        Order::STATUS_RETURNED => 'Returned',
                        Order::STATUS_REFUNDED => 'Refunded',
                    ])->default(Order::STATUS_PENDING),
                ]),

            Forms\Components\Section::make('Financials')
                ->columns(4)
                ->schema([
                    Forms\Components\TextInput::make('subtotal')->numeric()->prefix('$')->default(0),
                    Forms\Components\TextInput::make('delivery_cost')->numeric()->prefix('$')->default(0),
                    Forms\Components\TextInput::make('refund_amount')->numeric()->prefix('$')->default(0),
                    Forms\Components\TextInput::make('total')->numeric()->prefix('$')->default(0),
                    Forms\Components\TextInput::make('product_cost')->numeric()->prefix('$')->default(0),
                    Forms\Components\TextInput::make('profit')->numeric()->prefix('$')->default(0),
                    Forms\Components\DateTimePicker::make('confirmed_at')->native(false),
                    Forms\Components\DateTimePicker::make('shipped_at')->native(false),
                    Forms\Components\DateTimePicker::make('delivered_at')->native(false),
                    Forms\Components\Textarea::make('notes')->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('order_number')->label('Order #')->searchable()->copyable()->weight('medium'),
                Tables\Columns\TextColumn::make('lead.customer_name')->label('Customer')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('country.label')->label('Country')->sortable(),
                Tables\Columns\TextColumn::make('agent.name')->label('Agent')->toggleable(),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                    Order::STATUS_DELIVERED => 'success',
                    Order::STATUS_CONFIRMED, Order::STATUS_SHIPPED => 'info',
                    Order::STATUS_PENDING => 'warning',
                    Order::STATUS_CANCELLED, Order::STATUS_RETURNED, Order::STATUS_REFUNDED => 'danger',
                    default => 'gray',
                }),
                Tables\Columns\TextColumn::make('total')->money('USD')->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->money('USD')->label('Total revenue')),
                Tables\Columns\TextColumn::make('profit')->money('USD')->sortable()
                    ->color(fn (Order $record) => $record->profit > 0 ? 'success' : 'danger')
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->money('USD')->label('Total profit')),
                Tables\Columns\TextColumn::make('delivered_at')->date('M d')->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime('M d, H:i')->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')->multiple()->options([
                    Order::STATUS_PENDING => 'Pending',
                    Order::STATUS_CONFIRMED => 'Confirmed',
                    Order::STATUS_SHIPPED => 'Shipped',
                    Order::STATUS_DELIVERED => 'Delivered',
                    Order::STATUS_CANCELLED => 'Cancelled',
                    Order::STATUS_RETURNED => 'Returned',
                    Order::STATUS_REFUNDED => 'Refunded',
                ]),
                SelectFilter::make('country_id')->relationship('country', 'name')->multiple()->preload(),
                SelectFilter::make('agent_id')->relationship('agent', 'name')->multiple()->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
