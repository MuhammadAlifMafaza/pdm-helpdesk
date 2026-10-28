<?php

namespace App\Filament\Resources\LogPerbaikans\Pages;

use App\Filament\Resources\LogPerbaikans\LogPerbaikanResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewLogPerbaikan extends ViewRecord
{
    protected static string $resource = LogPerbaikanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
