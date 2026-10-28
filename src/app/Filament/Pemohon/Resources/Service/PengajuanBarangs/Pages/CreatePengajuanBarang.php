<?php

namespace App\Filament\Pemohon\Resources\Service\PengajuanBarangs\Pages;

use App\Filament\Pemohon\Resources\Service\PengajuanBarangs\PengajuanBarangResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePengajuanBarang extends CreateRecord
{
    protected static string $resource = PengajuanBarangResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        $data['status'] = 'Open';

        return $data;
    }
}
