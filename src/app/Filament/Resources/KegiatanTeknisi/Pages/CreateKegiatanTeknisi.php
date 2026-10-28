<?php

namespace App\Filament\Resources\KegiatanTeknisi\Pages;

use App\Filament\Resources\KegiatanTeknisi\KegiatanTeknisiResource;
use Filament\Resources\Pages\CreateRecord;

class CreateKegiatanTeknisi extends CreateRecord
{
    protected static string $resource = KegiatanTeknisiResource::class;

    /**
     * Kunci kepemilikan catatan di server: teknisi_id selalu diambil dari
     * user yang login, mengabaikan nilai apa pun yang dikirim dari client.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['teknisi_id'] = auth()->id();

        return $data;
    }
}
