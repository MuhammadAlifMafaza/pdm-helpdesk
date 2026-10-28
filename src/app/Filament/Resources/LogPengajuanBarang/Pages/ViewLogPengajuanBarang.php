<?php

namespace App\Filament\Resources\LogPengajuanBarang\Pages;

use App\Filament\Resources\LogPengajuanBarang\LogPengajuanBarangResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewLogPengajuanBarang extends ViewRecord
{
    protected static string $resource = LogPengajuanBarangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // EditAction::make(),

        ];
    }
}
