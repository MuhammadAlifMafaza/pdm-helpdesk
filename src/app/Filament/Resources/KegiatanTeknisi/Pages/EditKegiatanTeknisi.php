<?php

namespace App\Filament\Resources\KegiatanTeknisi\Pages;

use App\Filament\Resources\KegiatanTeknisi\KegiatanTeknisiResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditKegiatanTeknisi extends EditRecord
{
    protected static string $resource = KegiatanTeknisiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    /**
     * Kepemilikan catatan tidak boleh berubah saat edit: teknisi_id dipaksa
     * ke nilai asli record, mengabaikan payload yang dimodifikasi client.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['teknisi_id'] = $this->getRecord()->teknisi_id;

        return $data;
    }

    /**
     * Judul & breadcrumb: "Ubah Kegiatan Edi Purwanto 22 Okt 2026".
     * Format deterministik, tidak tergantung isi catatan.
     * translatedFormat() (bukan format()) agar nama bulan ikut locale "id".
     */
    public function getRecordTitle(): string
    {
        $record = $this->getRecord();

        return sprintf(
            'Kegiatan %s %s',
            $record->teknisi?->name,
            $record->tanggal->translatedFormat('d M Y')
        );
    }
}
