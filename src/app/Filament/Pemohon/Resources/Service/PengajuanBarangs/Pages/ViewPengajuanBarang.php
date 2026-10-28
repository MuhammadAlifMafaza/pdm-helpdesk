<?php

namespace App\Filament\Pemohon\Resources\Service\PengajuanBarangs\Pages;

use App\Filament\Pemohon\Resources\Service\PengajuanBarangs\PengajuanBarangResource;
use App\Models\Modules\Pengajuan\Models\PengajuanBarang;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\ViewRecord;
use Livewire\Attributes\On;

class ViewPengajuanBarang extends ViewRecord
{
    protected static string $resource = PengajuanBarangResource::class;

    public string $chatMessage = '';

    #[On('helpdesk-realtime-update')]
    public function refreshRealtimeData(?string $module = null, int|string|null $reference_id = null): void
    {
        if ($module !== 'pengajuan' || (string) $this->record->getKey() !== (string) $reference_id) {
            return;
        }

        $this->record->refresh();
    }

    public function sendChatMessage(): void
    {
        $this->validate([
            'chatMessage' => [
                'required',
                'string',
                'max:2000',
            ],
        ]);

        $record = $this->record;

        /*
        |--------------------------------------------------------------------------
        | Authorization
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $record->canBeAccessedBy(auth()->user()),
            403,
            'Anda tidak memiliki akses ke tiket ini.'
        );

        /*
        |--------------------------------------------------------------------------
        | Ticket Status
        |--------------------------------------------------------------------------
        */

        abort_if(
            $record->isClosed(),
            403,
            'Tiket telah ditutup.'
        );

        /*
        |--------------------------------------------------------------------------
        | Save Message
        |--------------------------------------------------------------------------
        */

        $record->sendMessage(
            trim($this->chatMessage)
        );

        /*
        |--------------------------------------------------------------------------
        | Reset
        |--------------------------------------------------------------------------
        */

        $this->chatMessage = '';

        /*
        |--------------------------------------------------------------------------
        | Refresh
        |--------------------------------------------------------------------------
        */

        $this->record->refresh();
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make('edit')
                ->label('Edit Tiket')
                ->icon('heroicon-o-pencil')
                ->color('edit')
                ->visible(
                    fn ($record): bool => $record->canPemohonEdit()
                ),

            /*
            |--------------------------------------------------------------------------
            | Konfirmasi Penyelesaian
            |--------------------------------------------------------------------------
            */
            Action::make('konfirmasi_selesai')
                ->label('Konfirmasi Selesai')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->visible(
                    fn (PengajuanBarang $record): bool => $record->isMenungguKonfirmasi()
                        && $record->canBeAccessedBy(auth()->user())
                )
                ->requiresConfirmation()
                ->form([
                    Textarea::make('catatan')
                        ->label('Catatan (opsional)')
                        ->rows(3),
                ])
                ->modalHeading('Konfirmasi Penyelesaian')
                ->modalDescription(
                    'Pastikan pengajuan sudah sesuai. Pengajuan akan ditutup sebagai selesai setelah dikonfirmasi.'
                )
                ->modalSubmitActionLabel('Ya, Selesai')
                ->action(function (PengajuanBarang $record, array $data): void {

                    abort_unless(
                        $record->canBeAccessedBy(auth()->user()),
                        403,
                        'Anda tidak memiliki akses ke pengajuan ini.'
                    );

                    $record->konfirmasiSelesai($data['catatan'] ?? null);

                    $this->record->refresh();
                }),

            Action::make('tolak_konfirmasi')
                ->label('Minta Revisi')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->visible(
                    fn (PengajuanBarang $record): bool => $record->isMenungguKonfirmasi()
                        && $record->canBeAccessedBy(auth()->user())
                )
                ->requiresConfirmation()
                ->form([
                    Textarea::make('catatan')
                        ->label('Alasan Revisi')
                        ->required()
                        ->rows(3),
                ])
                ->modalHeading('Tolak Konfirmasi')
                ->modalDescription(
                    'Pengajuan akan tetap terbuka dan dikembalikan kepada staf untuk diproses ulang.'
                )
                ->modalSubmitActionLabel('Kirim Permintaan Revisi')
                ->action(function (PengajuanBarang $record, array $data): void {

                    abort_unless(
                        $record->canBeAccessedBy(auth()->user()),
                        403,
                        'Anda tidak memiliki akses ke pengajuan ini.'
                    );

                    $record->tolakKonfirmasi($data['catatan']);

                    $this->record->refresh();
                }),

            DeleteAction::make()
                ->label('Batalkan Permintaan')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(
                    fn (PengajuanBarang $record): bool => $record->canPemohonDelete()
                )
                ->modalHeading('Batalkan Pengajuan Barang')
                ->modalDescription(
                    'Pengajuan barang yang dibatalkan tidak akan ditampilkan lagi pada daftar pengajuan aktif.'
                )
                ->modalSubmitActionLabel('Ya, Batalkan')
                ->successNotificationTitle(
                    'Pengajuan barang berhasil dibatalkan'
                )
                ->before(
                    function (PengajuanBarang $record): void {
                        abort_unless(
                            $record->canPemohonDelete(),
                            403,
                            'Pengajuan barang tidak dapat dibatalkan pada status saat ini.'
                        );
                    }
                )
                ->using(
                    function (PengajuanBarang $record): bool {
                        return $record->cancelByPemohon();
                    }
                ),
        ];
    }
}
