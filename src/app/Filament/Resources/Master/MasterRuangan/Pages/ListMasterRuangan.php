<?php

namespace App\Filament\Resources\Master\MasterRuangan\Pages;

use App\Filament\Resources\Master\MasterRuangan\MasterRuanganResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMasterRuangan extends ListRecords
{
    protected static string $resource = MasterRuanganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
