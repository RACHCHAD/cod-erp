<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationGroup = 'Team';

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Profile')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')->required(),
                    Forms\Components\TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('phone')->tel(),
                    Forms\Components\TextInput::make('avatar_url')->url(),
                    Forms\Components\Select::make('country_id')->relationship('country', 'name')->preload()->searchable(),
                    Forms\Components\Toggle::make('is_active')->default(true),
                ]),
            Forms\Components\Section::make('Security & Access')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('password')
                        ->password()
                        ->revealable()
                        ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                        ->dehydrated(fn ($state) => filled($state))
                        ->required(fn (string $context) => $context === 'create'),
                    Forms\Components\Select::make('roles')
                        ->relationship('roles', 'name')
                        ->preload()
                        ->multiple()
                        ->options(Role::pluck('name', 'name')),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                Tables\Columns\ImageColumn::make('avatar_url')->circular()->label('')->size(36),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable()->weight('medium')
                    ->description(fn (User $record) => $record->email),
                Tables\Columns\TextColumn::make('roles.name')->badge()->color('primary'),
                Tables\Columns\TextColumn::make('country.label')->toggleable(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('orders_handled_count')->counts('ordersHandled')->label('Orders'),
                Tables\Columns\TextColumn::make('leads_handled_count')->counts('leadsHandled')->label('Leads'),
                Tables\Columns\TextColumn::make('last_active_at')->dateTime()->since()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
                TernaryFilter::make('is_active')->label('Active'),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
