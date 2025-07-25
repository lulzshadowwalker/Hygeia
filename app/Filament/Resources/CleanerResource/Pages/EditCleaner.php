<?php

namespace App\Filament\Resources\CleanerResource\Pages;

use App\Filament\Resources\CleanerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCleaner extends EditRecord
{
    protected static string $resource = CleanerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
