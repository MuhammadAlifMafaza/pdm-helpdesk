<?php

namespace App\Filament\Pemohon\Resources\Service\TiketPerbaikans\Pages;

use App\Filament\Pemohon\Resources\Service\TiketPerbaikans\TiketPerbaikanResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTiketPerbaikan extends CreateRecord
{
    protected static string $resource = TiketPerbaikanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        $data['status'] = 'Open';

        return $data;
    }
}
