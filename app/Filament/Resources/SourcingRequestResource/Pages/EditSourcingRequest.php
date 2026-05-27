<?php

namespace App\Filament\Resources\SourcingRequestResource\Pages;

use App\Filament\Resources\SourcingRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSourcingRequest extends EditRecord
{
    protected static string $resource = SourcingRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
