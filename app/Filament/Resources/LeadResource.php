<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeadResource\Pages;
use App\Models\Lead;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static ?string $navigationGroup = 'Operations';

    protected static ?string $navigationIcon = 'heroicon-o-inbox-stack';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Customer')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('customer_name')->required(),
                    Forms\Components\TextInput::make('phone')->tel()->required(),
                    Forms\Components\TextInput::make('city'),
                    Forms\Components\Textarea::make('address')->columnSpanFull(),
                ]),
            Forms\Components\Section::make('Lead')
                ->columns(3)
                ->schema([
                    Forms\Components\Select::make('product_id')->relationship('product', 'name')->required()->preload()->searchable(),
                    Forms\Components\Select::make('country_id')->relationship('country', 'name')->required()->preload()->searchable(),
                    Forms\Components\Select::make('platform_id')->relationship('platform', 'name')->preload(),
                    Forms\Components\Select::make('agent_id')->label('Agent')->relationship('agent', 'name')->preload()->searchable(),
                    Forms\Components\Select::make('status')->options([
                        'new' => 'New', 'contacted' => 'Contacted', 'confirmed' => 'Confirmed',
                        'unreachable' => 'Unreachable', 'cancelled' => 'Cancelled', 'duplicate' => 'Duplicate',
                    ])->default('new')->required(),
                    Forms\Components\DateTimePicker::make('contacted_at')->native(false),
                    Forms\Components\Textarea::make('notes')->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('customer_name')->searchable()->sortable()->weight('medium'),
                Tables\Columns\TextColumn::make('phone')->searchable()->copyable()->icon('heroicon-m-phone'),
                Tables\Columns\TextColumn::make('product.name')->label('Product')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('country.label')->label('Country')->sortable(),
                Tables\Columns\TextColumn::make('platform.name')->label('Platform')->toggleable(),
                Tables\Columns\TextColumn::make('agent.name')->label('Agent')->toggleable(),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                    'new' => 'gray',
                    'contacted' => 'info',
                    'confirmed' => 'success',
                    'unreachable' => 'warning',
                    'cancelled', 'duplicate' => 'danger',
                    default => 'gray',
                }),
                Tables\Columns\TextColumn::make('created_at')->dateTime('M d, H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->multiple()->options([
                    'new' => 'New', 'contacted' => 'Contacted', 'confirmed' => 'Confirmed',
                    'unreachable' => 'Unreachable', 'cancelled' => 'Cancelled', 'duplicate' => 'Duplicate',
                ]),
                SelectFilter::make('country_id')->relationship('country', 'name')->multiple()->preload(),
                SelectFilter::make('product_id')->relationship('product', 'name')->multiple()->preload(),
                SelectFilter::make('agent_id')->relationship('agent', 'name')->multiple()->preload(),
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
            'index' => Pages\ListLeads::route('/'),
            'create' => Pages\CreateLead::route('/create'),
            'edit' => Pages\EditLead::route('/{record}/edit'),
        ];
    }
}
