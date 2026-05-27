<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SourcingRequestResource\Pages;
use App\Models\Country;
use App\Models\SourcingRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SourcingRequestResource extends Resource
{
    protected static ?string $model = SourcingRequest::class;

    protected static ?string $navigationGroup = 'Catalog';

    protected static ?string $navigationLabel = 'Sourcing';

    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass-circle';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Product to Source')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('product_name')->required(),
                    Forms\Components\TextInput::make('supplier_link')->url(),
                    Forms\Components\Select::make('supplier_id')->relationship('supplier', 'name')->preload()->searchable()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')->required(),
                            Forms\Components\TextInput::make('website')->url(),
                        ]),
                    Forms\Components\Select::make('target_countries')
                        ->multiple()
                        ->options(Country::query()->orderBy('sort_order')->pluck('name', 'code'))
                        ->preload()
                        ->searchable(),
                ]),

            Forms\Components\Section::make('Costs')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('product_cost')->numeric()->prefix('$')->default(0)->required(),
                    Forms\Components\TextInput::make('shipping_cost')->numeric()->prefix('$')->default(0),
                    Forms\Components\TextInput::make('estimated_selling_price')->numeric()->prefix('$')->default(0),
                ]),

            Forms\Components\Section::make('Workflow')
                ->columns(3)
                ->schema([
                    Forms\Components\Select::make('status')->options([
                        'pending' => 'Pending',
                        'reviewing' => 'Reviewing',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'testing' => 'Testing',
                        'winning' => 'Winning',
                    ])->default('pending')->required(),
                    Forms\Components\Select::make('requested_by')->relationship('requester', 'name')->preload()->searchable()
                        ->default(fn () => auth()->id()),
                    Forms\Components\Textarea::make('notes')->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('product_name')->searchable()->sortable()->weight('medium'),
                Tables\Columns\TextColumn::make('supplier.name')->toggleable(),
                Tables\Columns\TextColumn::make('product_cost')->money('USD')->sortable(),
                Tables\Columns\TextColumn::make('shipping_cost')->money('USD')->toggleable(),
                Tables\Columns\TextColumn::make('estimated_selling_price')->label('Est. price')->money('USD'),
                Tables\Columns\TextColumn::make('estimated_margin')
                    ->label('Est. margin')
                    ->getStateUsing(fn (SourcingRequest $record) => number_format($record->estimated_margin, 1).'%')
                    ->badge()
                    ->color(fn (SourcingRequest $record) => $record->estimated_margin >= 65 ? 'success' : ($record->estimated_margin >= 50 ? 'warning' : 'danger')),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                    'approved', 'winning' => 'success',
                    'reviewing', 'testing' => 'info',
                    'pending' => 'warning',
                    'rejected' => 'danger',
                    default => 'gray',
                }),
                Tables\Columns\TextColumn::make('requester.name')->label('Requested by')->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime('M d, H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->multiple()->options([
                    'pending' => 'Pending',
                    'reviewing' => 'Reviewing',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                    'testing' => 'Testing',
                    'winning' => 'Winning',
                ]),
                SelectFilter::make('supplier_id')->relationship('supplier', 'name')->preload(),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->icon('heroicon-o-check-circle')->color('success')
                    ->visible(fn (SourcingRequest $record) => in_array($record->status, ['pending', 'reviewing']))
                    ->requiresConfirmation()
                    ->action(function (SourcingRequest $record) {
                        $record->update(['status' => 'approved', 'approved_by' => auth()->id(), 'approved_at' => now()]);
                        Notification::make()->success()->title('Sourcing request approved')->send();
                    }),
                Tables\Actions\Action::make('reject')
                    ->icon('heroicon-o-x-circle')->color('danger')
                    ->visible(fn (SourcingRequest $record) => in_array($record->status, ['pending', 'reviewing']))
                    ->requiresConfirmation()
                    ->action(function (SourcingRequest $record) {
                        $record->update(['status' => 'rejected', 'reviewed_by' => auth()->id()]);
                        Notification::make()->warning()->title('Sourcing request rejected')->send();
                    }),
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
            'index' => Pages\ListSourcingRequests::route('/'),
            'create' => Pages\CreateSourcingRequest::route('/create'),
            'edit' => Pages\EditSourcingRequest::route('/{record}/edit'),
        ];
    }
}
