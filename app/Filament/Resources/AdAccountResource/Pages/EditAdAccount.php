<?php

namespace App\Filament\Resources\AdAccountResource\Pages;

use App\Filament\Resources\AdAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAdAccount extends EditRecord
{
    protected static string $resource = AdAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
