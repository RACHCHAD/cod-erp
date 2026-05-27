<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationGroup = 'Catalog';

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Product')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')->required()->maxLength(255),
                    Forms\Components\TextInput::make('sku')->required()->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('image_url')->url()->columnSpanFull(),
                    Forms\Components\Textarea::make('description')->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Pricing & Status')
                ->columns(4)
                ->schema([
                    Forms\Components\TextInput::make('cost')->numeric()->prefix('$')->default(0)->required(),
                    Forms\Components\TextInput::make('selling_price')->numeric()->prefix('$')->default(0)->required(),
                    Forms\Components\TextInput::make('avg_delivery_cost')->label('Avg. delivery cost')->numeric()->prefix('$')->default(0),
                    Forms\Components\Select::make('status')->options([
                        'testing' => 'Testing',
                        'winning' => 'Winning',
                        'scaling' => 'Scaling',
                        'paused' => 'Paused',
                        'killed' => 'Killed',
                    ])->default('testing')->required(),
                    Forms\Components\Select::make('supplier_id')
                        ->relationship('supplier', 'name')
                        ->preload()
                        ->searchable()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')->required(),
                            Forms\Components\TextInput::make('website')->url(),
                        ]),
                    Forms\Components\TagsInput::make('tags'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')->circular()->size(40)->label(''),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable()->weight('medium')
                    ->description(fn (Product $record) => $record->sku),
                Tables\Columns\TextColumn::make('cost')->money('USD')->sortable(),
                Tables\Columns\TextColumn::make('selling_price')->label('Price')->money('USD')->sortable(),
                Tables\Columns\TextColumn::make('margin')->label('Margin')
                    ->getStateUsing(fn (Product $record) => number_format($record->margin, 1).'%')
                    ->badge()
                    ->color(fn (Product $record) => $record->margin >= 70 ? 'success' : ($record->margin >= 50 ? 'warning' : 'danger')),
                Tables\Columns\TextColumn::make('total_stock')
                    ->label('Stock')
                    ->getStateUsing(fn (Product $record) => $record->total_stock)
                    ->badge()
                    ->color(fn (Product $record) => $record->total_stock <= 20 ? 'danger' : ($record->total_stock <= 100 ? 'warning' : 'success')),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                    'winning' => 'success',
                    'scaling' => 'info',
                    'testing' => 'warning',
                    'paused' => 'gray',
                    'killed' => 'danger',
                    default => 'gray',
                }),
                Tables\Columns\TextColumn::make('supplier.name')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')->dateTime('M d')->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'testing' => 'Testing',
                    'winning' => 'Winning',
                    'scaling' => 'Scaling',
                    'paused' => 'Paused',
                    'killed' => 'Killed',
                ])->multiple(),
                SelectFilter::make('supplier_id')
                    ->relationship('supplier', 'name')
                    ->preload()
                    ->searchable(),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
