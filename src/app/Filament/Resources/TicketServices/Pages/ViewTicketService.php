<?php

namespace App\Filament\Resources\TicketServices\Pages;

use App\Filament\Resources\TicketServices\TicketServiceResource;
use App\Models\Modules\Perbaikan\models\TiketPerbaikan as TicketService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Livewire\Attributes\On;

class ViewTicketService extends ViewRecord
{
    protected static string $resource = TicketServiceResource::class;

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
            EditAction::make('edit')
                ->label('Edit Tiket')
                ->icon('heroicon-o-pencil'),

            Action::make('ambil_tiket')
                ->label('Ambil Tiket')
                ->icon('heroicon-o-wrench-screwdriver')
                ->visible(
                    fn ($record) => $record->status === 'Open'
                )
                ->action(function ($record) {

                    $record->updateStatus(
                        'In Progress',
                        'Tiket mulai dikerjakan oleh '
                        .auth()->user()->name
                    );

                }),

            Action::make('selesai')
                ->label('Selesai')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(
                    fn ($record) => $record->status === 'In Progress'
                    && ! $record->isMenungguKonfirmasi()
                )
                ->requiresConfirmation()
                ->form([
                    Textarea::make('catatan')
                        ->required(),
                ])
                ->action(function ($record, array $data) {

                    $record->mintaKonfirmasiSelesai(
                        $data['catatan']
                    );

                })
                ->modalHeading('Konfirmasi Penyelesaian')
                ->modalDescription(
                    'Pemohon akan diminta mengonfirmasi penyelesaian pekerjaan ini.'
                ),

            Action::make('tolak')
                ->label('Tolak')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->visible(
                    fn ($record) => $record->status === 'In Progress'
                )
                ->requiresConfirmation()
                ->form([
                    Textarea::make('catatan')
                        ->required(),
                ])
                ->action(function ($record, array $data) {

                    $record->closeAsRejected(
                        $data['catatan']
                    );

                })
                ->modalHeading('Konfirmasi Penolakan')
                ->modalDescription(
                    'Tiket akan ditutup sebagai ditolak dan pemohon menerima notifikasi penolakan.'
                ),

            Action::make('reopen')
                ->label('Reopen Ticket')
                ->color('primary')
                ->icon('heroicon-o-arrow-path')
                ->visible(
                    fn ($record) => $record->isClosed()
                )
                ->requiresConfirmation()
                ->form([
                    Textarea::make('catatan')
                        ->label('Alasan Reopen')
                        ->required(),
                ])
                ->action(function ($record, array $data) {

                    $record->reopen(
                        $data['catatan']
                    );

                }),

            Action::make('chat')
                ->label('Kirim Pesan')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('primary')
                ->form([

                    Textarea::make('message')
                        ->label('Pesan')
                        ->required(),

                ])
                ->action(function (TicketService $record, array $data) {

                    $record->sendMessage(
                        $data['message']
                    );

                    Notification::make()
                        ->success()
                        ->title(
                            'Pesan berhasil dikirim'
                        )
                        ->send();

                }),
        ];
    }

    public function getTimelineProperty()
    {
        return $this->record
            ->timeline()
            ->get();
    }
}
