<?php

namespace App\Filament\Pemohon\Resources\Service\PengajuanBarangs\Pages;

use App\Filament\Pemohon\Resources\Service\PengajuanBarangs\PengajuanBarangResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditPengajuanBarang extends EditRecord
{
    protected static string $resource = PengajuanBarangResource::class;

    protected function handleRecordUpdate(
        Model $record,
        array $data
    ): Model {
        unset(
            $data['user_id'],
            $data['status'],
            $data['kode_pengajuan'],
            $data['pemohon'],
        );

        $record->updateDataPemohon($data);

        return $record->refresh();
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }

    protected function authorizeAccess(): void
    {
        parent::authorizeAccess();

        abort_unless(
            $this->record->canPemohonEdit(),
            403,
            'Pengajuan barang tidak dapat diubah pada status saat ini.'
        );
    }
}
