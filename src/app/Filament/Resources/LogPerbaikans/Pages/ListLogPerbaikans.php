<?php

namespace App\Filament\Resources\LogPerbaikans\Pages;

use App\Filament\Resources\LogPerbaikans\LogPerbaikanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLogPerbaikans extends ListRecords
{
    protected static string $resource = LogPerbaikanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }
}
