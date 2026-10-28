<?php

namespace App\Filament\Pemohon\Resources\Monitoring\LogPengajuanBarangs\Pages;

use App\Filament\Pemohon\Resources\Monitoring\LogPengajuanBarangs\LogPengajuanBarangResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLogPengajuanBarang extends EditRecord
{
    protected static string $resource = LogPengajuanBarangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
