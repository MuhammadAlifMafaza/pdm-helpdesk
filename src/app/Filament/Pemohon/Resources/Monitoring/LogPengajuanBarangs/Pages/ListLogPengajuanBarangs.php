<?php

namespace App\Filament\Pemohon\Resources\Monitoring\LogPengajuanBarangs\Pages;

use App\Filament\Pemohon\Resources\Monitoring\LogPengajuanBarangs\LogPengajuanBarangResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLogPengajuanBarangs extends ListRecords
{
    protected static string $resource = LogPengajuanBarangResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
