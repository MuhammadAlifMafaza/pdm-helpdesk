<?php

namespace App\Filament\Resources\Laporan\LaporanPermintaanBarangs\Pages;

use App\Filament\Resources\Laporan\LaporanPermintaanBarangs\LaporanPermintaanBarangResource;
use Filament\Resources\Pages\ViewRecord;

class ViewLaporanPermintaanBarang extends ViewRecord
{
    protected static string $resource = LaporanPermintaanBarangResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
