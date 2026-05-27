<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdAccountResource\Pages;
use App\Models\AdAccount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AdAccountResource extends Resource
{
    protected static ?string $model = AdAccount::class;

    protected static ?string $navigationGroup = 'Operations';

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\Select::make('platform_id')->relationship('platform', 'name')->required()->preload(),
            Forms\Components\TextInput::make('external_id'),
            Forms\Components\Select::make('currency_id')->relationship('currency', 'name')->preload(),
            Forms\Components\TextInput::make('daily_limit')->numeric()->prefix('$'),
            Forms\Components\Select::make('status')->options([
                'active' => 'Active', 'paused' => 'Paused', 'banned' => 'Banned', 'review' => 'Under Review',
            ])->default('active')->required(),
            Forms\Components\Textarea::make('notes')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->weight('medium'),
                Tables\Columns\TextColumn::make('platform.name')->badge(),
                Tables\Columns\TextColumn::make('external_id')->copyable()->toggleable(),
                Tables\Columns\TextColumn::make('currency.code')->toggleable(),
                Tables\Columns\TextColumn::make('daily_limit')->money('USD')->toggleable(),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                    'active' => 'success',
                    'paused' => 'warning',
                    'banned' => 'danger',
                    'review' => 'info',
                    default => 'gray',
                }),
            ])
            ->filters([
                SelectFilter::make('platform_id')->relationship('platform', 'name'),
                SelectFilter::make('status')->options([
                    'active' => 'Active', 'paused' => 'Paused', 'banned' => 'Banned', 'review' => 'Under Review',
                ]),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdAccounts::route('/'),
            'create' => Pages\CreateAdAccount::route('/create'),
            'edit' => Pages\EditAdAccount::route('/{record}/edit'),
        ];
    }
}
