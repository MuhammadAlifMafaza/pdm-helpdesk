<?php

namespace App\Filament\Resources\Master\MasterRuangan\Pages;

use App\Filament\Resources\Master\MasterRuangan\MasterRuanganResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMasterRuangan extends ViewRecord
{
    protected static string $resource = MasterRuanganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
