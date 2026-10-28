<?php

namespace App\Filament\Pemohon\Resources\Service\PengajuanBarangs\Tables;

use App\Models\Modules\Pengajuan\Models\PengajuanBarang;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PengajuanBarangsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            /*
            |--------------------------------------------------------------------------
            | Tabel Configuration
            |--------------------------------------------------------------------------
            */
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)

            /*
            |--------------------------------------------------------------------------
            | Columns
            |--------------------------------------------------------------------------
            */
            ->columns([

                TextColumn::make('index')
                    ->label('No.')
                    ->rowIndex(),

                TextColumn::make('kode_pengajuan')
                    ->label('Kode Pengajuan')
                    ->copyable()
                    ->copyMessage('Kode pengajuan disalin')
                    ->copyMessageDuration(1500)
                    ->weight('bold'),

                TextColumn::make('barang.kategoriBarang.nama_kategori')
                    ->label('Kategori Barang')
                    ->badge()
                    ->searchable()
                    ->placeholder('Tidak tersedia')
                    ->wrap(),

                TextColumn::make('nama_barang')
                    ->label('Nama Barang')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->weight('medium'),

                TextColumn::make('spesifikasi_barang')
                    ->label('Spesifikasi Barang')
                    ->wrap()
                    ->limit(60)
                    ->tooltip(
                        fn (?string $state): ?string => filled($state)
                            ? $state
                            : null
                    )
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('jumlah')
                    ->label('Jumlah')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->icon(
                        fn (?string $state): string => match ($state) {
                            'Open' => 'heroicon-o-folder-open',
                            'In Progress' => 'heroicon-o-arrow-path',
                            'Close' => 'heroicon-o-check-circle',
                            default => 'heroicon-o-question-mark-circle',
                        }
                    )
                    ->color(
                        fn (?string $state): string => match ($state) {
                            'Open' => 'info',
                            'In Progress' => 'warning',
                            'Close' => 'success',
                            default => 'gray',
                        }
                    )
                    ->sortable(),

                TextColumn::make('status_outcome')
                    ->label('Hasil')
                    ->badge()
                    ->icon(
                        fn (?string $state): string => match ($state) {
                            'Completed' => 'heroicon-o-check-circle',
                            'Rejected' => 'heroicon-o-x-circle',
                            default => 'heroicon-o-minus-circle',
                        }
                    )
                    ->color(
                        fn (?string $state): string => match ($state) {
                            'Completed' => 'success',
                            'Rejected' => 'danger',
                            default => 'gray',
                        }
                    )
                    ->placeholder('Belum ditentukan'),

                TextColumn::make('created_at')
                    ->label('Waktu Pengajuan')
                    ->dateTime('d M Y')
                    ->timezone('Asia/Jakarta')
                    ->description(
                        fn (PengajuanBarang $record): ?string => $record->created_at?->timezone('Asia/Jakarta')->format('H:i:s')
                    )
                    ->sortable(),

                TextColumn::make('waktu_mulai')
                    ->label('Waktu Diterima')
                    ->dateTime('d M Y')
                    ->timezone('Asia/Jakarta')
                    ->description(
                        fn (PengajuanBarang $record): ?string => $record->waktu_mulai?->timezone('Asia/Jakarta')->format('H:i:s')
                    )
                    ->toggleable()
                    ->placeholder('-'),

                TextColumn::make('waktu_selesai')
                    ->label('Waktu Selesai')
                    ->dateTime('d M Y')
                    ->timezone('Asia/Jakarta')
                    ->description(
                        fn (PengajuanBarang $record): ?string => $record->waktu_selesai?->timezone('Asia/Jakarta')->format('H:i:s')
                    )
                    ->toggleable()
                    ->placeholder('-'),

                TextColumn::make('durasi_pengerjaan')
                    ->label('Durasi')
                    ->toggleable()
                    ->placeholder('-'),
            ])

            /*
            |--------------------------------------------------------------------------
            | Filters
            |--------------------------------------------------------------------------
            */
            ->filters([

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'Open' => 'Open',
                        'In Progress' => 'In Progress',
                        'Close' => 'Close',
                    ]),

                SelectFilter::make('status_outcome')
                    ->label('Hasil')
                    ->options([
                        'Completed' => 'Selesai',
                        'Rejected' => 'Ditolak',
                    ])
                    ->query(
                        function (Builder $query, array $data): Builder {

                            $outcome = $data['value'] ?? null;

                            if (blank($outcome)) {
                                return $query;
                            }

                            return $query->whereHas(
                                'logs',
                                function (Builder $query) use ($outcome): void {

                                    $query
                                        ->where('kategori_log', 'Status')
                                        ->when(
                                            in_array(
                                                $outcome,
                                                ['Completed', 'Rejected'],
                                                true
                                            ),
                                            fn (Builder $query) => $query->where(
                                                'data_baru',
                                                'Close'
                                            )
                                        )
                                        ->when(
                                            $outcome === 'Completed',
                                            fn (Builder $query) => $query->where(
                                                'keterangan',
                                                'like',
                                                '%[SELESAI]%'
                                            )
                                        )
                                        ->when(
                                            $outcome === 'Rejected',
                                            fn (Builder $query) => $query->where(
                                                'keterangan',
                                                'like',
                                                '%[DITOLAK]%'
                                            )
                                        );
                                }
                            );
                        }
                    ),
            ])

            /*
            |--------------------------------------------------------------------------
            | Record Actions
            |--------------------------------------------------------------------------
            */
            ->recordActions([

                ViewAction::make()
                    ->label('')
                    ->tooltip('Lihat Detail'),

                EditAction::make()
                    ->label('')
                    ->tooltip('Edit Pengajuan')
                    ->visible(
                        fn (PengajuanBarang $record): bool => $record->canPemohonEdit()
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
                        fn (PengajuanBarang $record): bool => $record->isMenungguKonfirmasi()
                    )
                    ->modalHeading('Konfirmasi Penyelesaian')
                    ->modalDescription(
                        'Pastikan pengajuan sudah sesuai. Pengajuan akan ditutup '
                        .'sebagai selesai setelah dikonfirmasi.'
                    )
                    ->modalSubmitActionLabel('Ya, Selesai')
                    ->form([
                        Textarea::make('catatan')
                            ->label('Catatan (opsional)')
                            ->rows(3),
                    ])
                    ->action(function (PengajuanBarang $record, array $data): void {

                        abort_unless(
                            $record->canBeAccessedBy(auth()->user()),
                            403,
                            'Anda tidak memiliki akses ke pengajuan ini.'
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
                        fn (PengajuanBarang $record): bool => $record->isMenungguKonfirmasi()
                    )
                    ->modalHeading('Tolak Konfirmasi')
                    ->modalDescription(
                        'Pengajuan akan tetap terbuka dan dikembalikan kepada '
                        .'staf untuk diproses ulang.'
                    )
                    ->modalSubmitActionLabel('Kirim Permintaan Revisi')
                    ->form([
                        Textarea::make('catatan')
                            ->label('Alasan Revisi')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (PengajuanBarang $record, array $data): void {

                        abort_unless(
                            $record->canBeAccessedBy(auth()->user()),
                            403,
                            'Anda tidak memiliki akses ke pengajuan ini.'
                        );

                        $record->tolakKonfirmasi($data['catatan']);
                    }),

                DeleteAction::make()
                    ->label('Batalkan')
                    ->tooltip('Batalkan Pengajuan')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()

                    ->visible(
                        fn (PengajuanBarang $record): bool => $record->canPemohonDelete()
                    )

                    ->modalHeading('Batalkan Pengajuan')

                    ->modalDescription(
                        'Pengajuan yang dibatalkan tidak akan ditampilkan lagi '
                        .'pada daftar pengajuan aktif.'
                    )

                    ->modalSubmitActionLabel('Ya, Batalkan')

                    ->form([
                        Textarea::make('catatan')
                            ->label('Alasan Pembatalan')
                            ->placeholder(
                                'Tuliskan alasan pembatalan pengajuan (opsional).'
                            )
                            ->rows(4)
                            ->maxLength(1000),
                    ])

                    ->action(
                        function (PengajuanBarang $record, array $data): void {

                            if (! $record->canPemohonDelete()) {
                                abort(
                                    403,
                                    'Pengajuan tidak dapat dibatalkan '
                                    .'pada status saat ini.'
                                );
                            }

                            $success = $record->cancelByPemohon(
                                $data['catatan'] ?? null
                            );

                            if (! $success) {
                                abort(
                                    403,
                                    'Pengajuan tidak dapat dibatalkan '
                                    .'pada status saat ini.'
                                );
                            }
                        }
                    )

                    ->successNotificationTitle(
                        'Pengajuan berhasil dibatalkan'
                    ),
            ]);
    }
}
