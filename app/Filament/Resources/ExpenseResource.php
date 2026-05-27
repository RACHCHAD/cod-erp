<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Models\Expense;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static ?string $navigationGroup = 'Finance';

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Expense')
                ->columns(3)
                ->schema([
                    Forms\Components\Select::make('category_id')->relationship('category', 'name')->required()->preload(),
                    Forms\Components\DatePicker::make('date')->default(now())->native(false)->required(),
                    Forms\Components\TextInput::make('amount')->numeric()->prefix('$')->required(),
                    Forms\Components\TextInput::make('title')->required()->columnSpanFull(),
                    Forms\Components\Select::make('country_id')->relationship('country', 'name')->preload()->searchable(),
                    Forms\Components\Select::make('currency_id')->relationship('currency', 'name')->preload(),
                    Forms\Components\Select::make('user_id')->label('Created by')->relationship('user', 'name')->preload()->default(fn () => auth()->id()),
                    Forms\Components\Textarea::make('description')->columnSpanFull(),
                    Forms\Components\FileUpload::make('invoice_path')->label('Invoice')->directory('invoices')->columnSpanFull(),
                ]),
            Forms\Components\Section::make('Recurring')
                ->columns(3)
                ->schema([
                    Forms\Components\Toggle::make('is_recurring')->live(),
                    Forms\Components\Select::make('recurring_interval')
                        ->options(['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly', 'yearly' => 'Yearly'])
                        ->visible(fn (Forms\Get $get) => (bool) $get('is_recurring')),
                    Forms\Components\DatePicker::make('next_due_at')->native(false)
                        ->visible(fn (Forms\Get $get) => (bool) $get('is_recurring')),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('date')->date('M d, Y')->sortable(),
                Tables\Columns\TextColumn::make('title')->searchable()->sortable()->weight('medium'),
                Tables\Columns\TextColumn::make('category.name')->badge()
                    ->color(fn (Expense $record) => 'primary'),
                Tables\Columns\TextColumn::make('amount')->money('USD')->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->money('USD')),
                Tables\Columns\TextColumn::make('country.label')->label('Country')->toggleable(),
                Tables\Columns\IconColumn::make('is_recurring')->label('Recurring')->boolean(),
                Tables\Columns\TextColumn::make('user.name')->label('By')->toggleable(),
            ])
            ->filters([
                SelectFilter::make('category_id')->relationship('category', 'name')->multiple()->preload(),
                Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('from')->native(false),
                        Forms\Components\DatePicker::make('until')->native(false),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn ($q, $v) => $q->whereDate('date', '>=', $v))
                            ->when($data['until'] ?? null, fn ($q, $v) => $q->whereDate('date', '<=', $v));
                    }),
                SelectFilter::make('is_recurring')->label('Recurring')->options([1 => 'Yes', 0 => 'No']),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
