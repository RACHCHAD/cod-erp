<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdSpendResource\Pages;
use App\Models\AdSpend;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AdSpendResource extends Resource
{
    protected static ?string $model = AdSpend::class;

    protected static ?string $navigationGroup = 'Operations';

    protected static ?string $navigationLabel = 'Daily Spend';

    protected static ?string $modelLabel = 'Spend Entry';

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Spend Details')
                ->columns(3)
                ->schema([
                    Forms\Components\DatePicker::make('date')
                        ->default(now())
                        ->required()
                        ->native(false),
                    Forms\Components\Select::make('product_id')
                        ->relationship('product', 'name')
                        ->preload()
                        ->searchable()
                        ->required(),
                    Forms\Components\Select::make('country_id')
                        ->relationship('country', 'name')
                        ->preload()
                        ->searchable()
                        ->required(),
                    Forms\Components\Select::make('platform_id')
                        ->relationship('platform', 'name')
                        ->preload()
                        ->required(),
                    Forms\Components\Select::make('ad_account_id')
                        ->relationship('adAccount', 'name')
                        ->preload()
                        ->searchable(),
                    Forms\Components\Select::make('user_id')
                        ->label('Media buyer')
                        ->relationship('user', 'name')
                        ->preload()
                        ->searchable()
                        ->default(fn () => auth()->id()),
                ]),

            Forms\Components\Section::make('Performance')
                ->columns(4)
                ->schema([
                    Forms\Components\TextInput::make('spend')
                        ->numeric()
                        ->prefix('$')
                        ->minValue(0)
                        ->required(),
                    Forms\Components\TextInput::make('leads')
                        ->numeric()
                        ->minValue(0)
                        ->required(),
                    Forms\Components\TextInput::make('impressions')
                        ->numeric()
                        ->minValue(0),
                    Forms\Components\TextInput::make('clicks')
                        ->numeric()
                        ->minValue(0),
                    Forms\Components\Textarea::make('notes')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->date('M d, Y')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('country.label')
                    ->label('Country')
                    ->searchable(['name', 'code'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('platform.name')
                    ->label('Platform')
                    ->badge()
                    ->color(fn ($record) => match ($record->platform?->slug) {
                        'facebook' => 'info',
                        'tiktok' => 'gray',
                        'google' => 'warning',
                        'snapchat' => 'warning',
                        default => 'primary',
                    }),
                Tables\Columns\TextColumn::make('adAccount.name')
                    ->label('Ad Account')
                    ->toggleable()
                    ->limit(20),
                Tables\Columns\TextColumn::make('spend')
                    ->money('USD')
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total spend')->money('USD')),
                Tables\Columns\TextColumn::make('leads')
                    ->numeric()
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total leads')),
                Tables\Columns\TextColumn::make('cpl')
                    ->label('CPL')
                    ->money('USD')
                    ->sortable()
                    ->badge()
                    ->color(fn ($record) => match ($record->profitability) {
                        'profitable' => 'success',
                        'average' => 'warning',
                        'losing' => 'danger',
                        default => 'gray',
                    })
                    ->summarize(Tables\Columns\Summarizers\Average::make()->label('Avg CPL')->money('USD')),
                Tables\Columns\TextColumn::make('impressions')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('clicks')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Buyer')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('product_id')
                    ->label('Product')
                    ->relationship('product', 'name')
                    ->multiple()
                    ->preload(),
                SelectFilter::make('country_id')
                    ->label('Country')
                    ->relationship('country', 'name')
                    ->multiple()
                    ->preload(),
                SelectFilter::make('platform_id')
                    ->label('Platform')
                    ->relationship('platform', 'name')
                    ->multiple()
                    ->preload(),
                SelectFilter::make('ad_account_id')
                    ->label('Ad Account')
                    ->relationship('adAccount', 'name')
                    ->multiple()
                    ->preload(),
                SelectFilter::make('user_id')
                    ->label('Media buyer')
                    ->relationship('user', 'name')
                    ->multiple()
                    ->preload(),
                Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('from')->native(false),
                        Forms\Components\DatePicker::make('until')->native(false),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn ($q, $d) => $q->whereDate('date', '>=', $d))
                            ->when($data['until'] ?? null, fn ($q, $d) => $q->whereDate('date', '<=', $d));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if (! empty($data['from'])) {
                            $indicators['from'] = 'From '.Carbon::parse($data['from'])->toFormattedDateString();
                        }
                        if (! empty($data['until'])) {
                            $indicators['until'] = 'Until '.Carbon::parse($data['until'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export_csv')
                    ->label('Export CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(function () {
                        return response()->streamDownload(function () {
                            $out = fopen('php://output', 'w');
                            fputcsv($out, ['Date', 'Product', 'Country', 'Platform', 'Ad Account', 'Buyer', 'Spend', 'Leads', 'CPL']);
                            AdSpend::with(['product', 'country', 'platform', 'adAccount', 'user'])
                                ->orderByDesc('date')
                                ->chunk(500, function ($rows) use ($out) {
                                    foreach ($rows as $r) {
                                        fputcsv($out, [
                                            optional($r->date)->toDateString(),
                                            $r->product?->name,
                                            $r->country?->name,
                                            $r->platform?->name,
                                            $r->adAccount?->name,
                                            $r->user?->name,
                                            $r->spend,
                                            $r->leads,
                                            $r->cpl,
                                        ]);
                                    }
                                });
                            fclose($out);
                        }, 'ad-spends-'.now()->format('Y-m-d').'.csv');
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdSpends::route('/'),
            'create' => Pages\CreateAdSpend::route('/create'),
            'edit' => Pages\EditAdSpend::route('/{record}/edit'),
        ];
    }
}
