<?php

namespace App\Filament\Resources\SourcingRequestResource\Pages;

use App\Filament\Resources\SourcingRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSourcingRequests extends ListRecords
{
    protected static string $resource = SourcingRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
