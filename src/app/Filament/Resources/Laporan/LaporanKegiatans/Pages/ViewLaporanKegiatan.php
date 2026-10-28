<?php

namespace App\Filament\Resources\Laporan\LaporanKegiatans\Pages;

use App\Filament\Resources\Laporan\LaporanKegiatans\LaporanKegiatanResource;
use Filament\Resources\Pages\ViewRecord;

class ViewLaporanKegiatan extends ViewRecord
{
    protected static string $resource = LaporanKegiatanResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
