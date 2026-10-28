<?php

namespace App\Filament\Pemohon\Resources\Service\TiketPerbaikans\Tables;

use App\Models\Modules\Perbaikan\Models\TiketPerbaikan;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TiketPerbaikansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')

            ->columns([

                TextColumn::make('index')
                    ->label('No')
                    ->rowIndex(),

                TextColumn::make('kode_tiket')
                    ->label('Kode Tiket')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('user.name')
                    ->label('Pemohon')
                    ->searchable(),

                TextColumn::make('ruangan.nama_ruangan')
                    ->label('Ruangan')
                    ->searchable(),

                TextColumn::make('keluhan')
                    ->label('Keluhan')
                    ->wrap()
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->icon(fn (string $state): string => match ($state) {
                        'Open' => 'heroicon-o-exclamation-circle',
                        'In Progress' => 'heroicon-o-wrench-screwdriver',
                        'Close' => 'heroicon-o-check-badge',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Open' => 'info',
                        'In Progress' => 'warning',
                        'Close' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('status_outcome')
                    ->label('Hasil')
                    ->badge()
                    ->placeholder('-')
                    ->icon(
                        fn (?string $state): string => match ($state) {
                            'Completed' => 'heroicon-o-check-circle',
                            'Rejected' => 'heroicon-o-x-circle',
                            'Reopen' => 'heroicon-o-arrow-path',
                            default => 'heroicon-o-question-mark-circle',
                        }
                    )
                    ->color(
                        fn (?string $state): string => match ($state) {
                            'Completed' => 'success',
                            'Rejected' => 'danger',
                            'Reopen' => 'warning',
                            default => 'gray',
                        }
                    ),

                TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->dateTime('d M Y')
                    ->timezone('Asia/Jakarta')
                    ->description(
                        fn (TiketPerbaikan $record): string => $record->created_at?->timezone('Asia/Jakarta')->format('H:i:s')
                        ?? '-'
                    ),

                TextColumn::make('waktu_mulai')
                    ->label('Mulai')
                    ->dateTime('d M Y')
                    ->timezone('Asia/Jakarta')
                    ->placeholder('-')
                    ->description(
                        fn (TiketPerbaikan $record): string => $record->waktu_mulai
                        ? $record->waktu_mulai
                            ->timezone('Asia/Jakarta')
                            ->format('H:i:s')
                        : '-'
                    ),

                TextColumn::make('waktu_selesai')
                    ->label('Selesai')
                    ->dateTime('d M Y')
                    ->timezone('Asia/Jakarta')
                    ->placeholder('-')
                    ->description(
                        fn (TiketPerbaikan $record): string => $record->waktu_selesai
                        ? $record->waktu_selesai
                            ->timezone('Asia/Jakarta')
                            ->format('H:i:s')
                        : '-'
                    ),

                TextColumn::make('durasi_pengerjaan')
                    ->label('Durasi')
                    ->placeholder('-'),

            ])

            ->filters([
                //
            ])

            ->recordActions([

                /*
                |--------------------------------------------------------------------------
                | View
                |--------------------------------------------------------------------------
                */
                ViewAction::make(),

                /*
                |--------------------------------------------------------------------------
                | Edit
                |--------------------------------------------------------------------------
                */
                EditAction::make()
                    ->label('Edit')
                    ->icon('heroicon-o-pencil')
                    ->color('primary')
                    ->visible(
                        fn (TiketPerbaikan $record): bool => $record->canPemohonEdit()
                    ),

                /*
                |--------------------------------------------------------------------------
                | Konfirmasi Penyelesaian
                |--------------------------------------------------------------------------
                */
                Action::make('konfirmasi_selesai')
                    ->label('')
                    ->tooltip('Konfirmasi Selesai')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(
                        fn (TiketPerbaikan $record): bool => $record->isMenungguKonfirmasi()
                    )
                    ->modalHeading('Konfirmasi Penyelesaian')
                    ->modalDescription(
                        'Pastikan pekerjaan sudah sesuai. Tiket akan ditutup sebagai selesai setelah dikonfirmasi.'
                    )
                    ->modalSubmitActionLabel('Ya, Selesai')
                    ->form([
                        Textarea::make('catatan')
                            ->label('Catatan (opsional)')
                            ->rows(3),
                    ])
                    ->action(function (TiketPerbaikan $record, array $data): void {

                        abort_unless(
                            $record->canBeAccessedBy(auth()->user()),
                            403,
                            'Anda tidak memiliki akses ke tiket ini.'
                        );

                        $record->konfirmasiSelesai($data['catatan'] ?? null);
                    }),

                Action::make('tolak_konfirmasi')
                    ->label('')
                    ->tooltip('Minta Revisi')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(
                        fn (TiketPerbaikan $record): bool => $record->isMenungguKonfirmasi()
                    )
                    ->modalHeading('Tolak Konfirmasi')
                    ->modalDescription(
                        'Tiket akan tetap terbuka dan dikembalikan kepada staf untuk diperbaiki.'
                    )
                    ->modalSubmitActionLabel('Kirim Permintaan Revisi')
                    ->form([
                        Textarea::make('catatan')
                            ->label('Alasan Revisi')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (TiketPerbaikan $record, array $data): void {

                        abort_unless(
                            $record->canBeAccessedBy(auth()->user()),
                            403,
                            'Anda tidak memiliki akses ke tiket ini.'
                        );

                        $record->tolakKonfirmasi($data['catatan']);
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
            ]);
    }
}
