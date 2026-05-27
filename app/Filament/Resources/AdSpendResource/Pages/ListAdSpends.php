<?php

namespace App\Filament\Resources\AdSpendResource\Pages;

use App\Filament\Resources\AdSpendResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAdSpends extends ListRecords
{
    protected static string $resource = AdSpendResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
