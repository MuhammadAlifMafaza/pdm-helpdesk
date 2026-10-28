<?php

namespace App\Filament\Resources\KegiatanTeknisi\Pages;

use App\Filament\Resources\KegiatanTeknisi\KegiatanTeknisiResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewKegiatanTeknisi extends ViewRecord
{
    protected static string $resource = KegiatanTeknisiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    /**
     * Judul & breadcrumb: "Lihat Kegiatan Edi Purwanto 22 Okt 2026".
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
