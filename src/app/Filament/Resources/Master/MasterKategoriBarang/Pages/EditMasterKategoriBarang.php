<?php

namespace App\Filament\Resources\Master\MasterKategoriBarang\Pages;

use App\Filament\Resources\Master\MasterKategoriBarang\MasterKategoriBarangResource;
use Filament\Resources\Pages\EditRecord;

class EditMasterKategoriBarang extends EditRecord
{
    protected static string $resource = MasterKategoriBarangResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
