<?php

namespace App\Filament\Resources\LogPengajuanBarang\Pages;

use App\Filament\Resources\LogPengajuanBarang\LogPengajuanBarangResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLogPengajuanBarang extends ListRecords
{
    protected static string $resource = LogPengajuanBarangResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
