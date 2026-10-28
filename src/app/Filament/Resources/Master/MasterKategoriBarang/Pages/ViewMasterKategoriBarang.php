<?php

namespace App\Filament\Resources\Master\MasterKategoriBarang\Pages;

use App\Filament\Resources\Master\MasterKategoriBarang\MasterKategoriBarangResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMasterKategoriBarang extends ViewRecord
{
    protected static string $resource = MasterKategoriBarangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
