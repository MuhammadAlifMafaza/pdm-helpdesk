<?php

namespace App\Filament\Resources\Laporan\LaporanPerbaikans\Pages;

use App\Filament\Resources\Laporan\LaporanPerbaikans\LaporanPerbaikanResource;
use Filament\Resources\Pages\ViewRecord;

class ViewLaporanPerbaikan extends ViewRecord
{
    protected static string $resource = LaporanPerbaikanResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
