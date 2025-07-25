<?php

namespace App\Filament\Resources\CleanerResource\Pages;

use App\Filament\Resources\CleanerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCleaners extends ListRecords
{
    protected static string $resource = CleanerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
