<?php

namespace App\Filament\Pemohon\Resources\Service\TiketPerbaikans\Pages;

use App\Filament\Pemohon\Resources\Service\TiketPerbaikans\TiketPerbaikanResource;
use App\Models\Modules\Perbaikan\Models\TiketPerbaikan;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditTiketPerbaikan extends EditRecord
{
    protected static string $resource = TiketPerbaikanResource::class;

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

            DeleteAction::make()
                ->label('Batalkan Permintaan')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(
                    fn (TiketPerbaikan $record): bool => $record->canPemohonDelete()
                )
                ->modalHeading('Batalkan Tiket Perbaikan')
                ->modalDescription(
                    'Tiket perbaikan yang dibatalkan tidak akan ditampilkan lagi pada daftar tiket aktif.'
                )
                ->modalSubmitActionLabel('Ya, Batalkan')
                ->successNotificationTitle(
                    'Tiket perbaikan berhasil dibatalkan'
                )
                ->before(
                    function (TiketPerbaikan $record): void {
                        abort_unless(
                            $record->canPemohonDelete(),
                            403,
                            'Tiket perbaikan tidak dapat dibatalkan pada status saat ini.'
                        );
                    }
                )
                ->using(
                    function (TiketPerbaikan $record): bool {
                        return $record->cancelByPemohon();
                    }
                ),
        ];
    }

    protected function authorizeAccess(): void
    {
        parent::authorizeAccess();

        abort_unless(
            $this->record->canPemohonEdit(),
            403,
            'Tiket perbaikan tidak dapat diubah pada status saat ini.'
        );
    }
}
