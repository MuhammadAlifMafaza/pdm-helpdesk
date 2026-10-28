<?php

namespace App\Filament\Pemohon\Resources\Service\TiketPerbaikans\Pages;

use App\Filament\Pemohon\Resources\Service\TiketPerbaikans\TiketPerbaikanResource;
use App\Models\Modules\Perbaikan\Models\TiketPerbaikan;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\ViewRecord;
use Livewire\Attributes\On;

class ViewTiketPerbaikan extends ViewRecord
{
    protected static string $resource = TiketPerbaikanResource::class;

    public string $chatMessage = '';

    #[On('helpdesk-realtime-update')]
    public function refreshRealtimeData(?string $module = null, int|string|null $reference_id = null): void
    {
        if ($module !== 'perbaikan' || (string) $this->record->getKey() !== (string) $reference_id) {
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
            /*
                            |--------------------------------------------------------------------------
                            | Edit
                            |--------------------------------------------------------------------------
                            */
            EditAction::make()
                ->label('Edit Tiket')
                ->icon('heroicon-o-pencil')
                ->color('edit')
                ->visible(
                    fn (TiketPerbaikan $record): bool => $record->canPemohonEdit()
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
                    fn (TiketPerbaikan $record): bool => $record->isMenungguKonfirmasi()
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
                    'Pastikan pekerjaan sudah sesuai. Tiket akan ditutup sebagai selesai setelah dikonfirmasi.'
                )
                ->modalSubmitActionLabel('Ya, Selesai')
                ->action(function (TiketPerbaikan $record, array $data): void {

                    abort_unless(
                        $record->canBeAccessedBy(auth()->user()),
                        403,
                        'Anda tidak memiliki akses ke tiket ini.'
                    );

                    $record->konfirmasiSelesai($data['catatan'] ?? null);

                    $this->record->refresh();
                }),

            Action::make('tolak_konfirmasi')
                ->label('Minta Revisi')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->visible(
                    fn (TiketPerbaikan $record): bool => $record->isMenungguKonfirmasi()
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
                    'Tiket akan tetap terbuka dan dikembalikan kepada staf untuk diperbaiki.'
                )
                ->modalSubmitActionLabel('Kirim Permintaan Revisi')
                ->action(function (TiketPerbaikan $record, array $data): void {

                    abort_unless(
                        $record->canBeAccessedBy(auth()->user()),
                        403,
                        'Anda tidak memiliki akses ke tiket ini.'
                    );

                    $record->tolakKonfirmasi($data['catatan']);

                    $this->record->refresh();
                }),

            /*
            |--------------------------------------------------------------------------
            | Batalkan Tiket
            |--------------------------------------------------------------------------
            */
            DeleteAction::make()
                ->label('Batalkan')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(
                    fn (TiketPerbaikan $record): bool => $record->canPemohonDelete()
                )
                ->modalHeading('Batalkan Tiket')
                ->modalDescription(
                    'Tiket yang dibatalkan tidak akan ditampilkan lagi pada daftar tiket aktif.'
                )
                ->modalSubmitActionLabel('Ya, Batalkan')
                ->successNotificationTitle(
                    'Tiket berhasil dibatalkan'
                )
                ->before(
                    function (TiketPerbaikan $record): void {

                        if (! $record->canPemohonDelete()) {
                            abort(
                                403,
                                'Tiket tidak dapat dibatalkan pada status saat ini.'
                            );
                        }
                    }
                ),
        ];
    }
}
