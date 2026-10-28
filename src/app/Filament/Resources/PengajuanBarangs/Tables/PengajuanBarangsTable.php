<?php

namespace App\Filament\Resources\PengajuanBarangs\Tables;

use App\Models\Modules\Pengajuan\Models\PengajuanBarang;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PengajuanBarangsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->columns([

                TextColumn::make('index')
                    ->label('No.')
                    ->rowIndex(),

                TextColumn::make('kode_pengajuan')
                    ->label('Kode Pengajuan')
                    ->searchable(
                        query: function (Builder $query, string $search): Builder {
                            return $query->whereRaw(
                                "CONCAT('PJB-', DATE_FORMAT(pengajuan_barang.created_at, '%d%m%Y'), '-', LPAD(pengajuan_barang.id - (SELECT MIN(pb2.id) FROM pengajuan_barang AS pb2 WHERE DATE(pb2.created_at) = DATE(pengajuan_barang.created_at)) + 1, 4, '0')) LIKE ?",
                                ['%'.addcslashes($search, '\\%_').'%']
                            );
                        }
                    )
                    ->copyable()
                    ->copyMessage('Kode pengajuan disalin')
                    ->copyMessageDuration(1500)
                    ->weight('bold'),

                TextColumn::make('user.name')
                    ->label('Pemohon')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('barang.kategoriBarang.nama_kategori')
                    ->label('Kategori Barang')
                    ->searchable()
                    ->placeholder('Tidak tersedia')
                    ->wrap(),

                TextColumn::make('nama_barang')
                    ->label('Nama Barang')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-')
                    ->wrap(),

                TextColumn::make('spesifikasi_barang')
                    ->label('Spesifikasi Barang')
                    ->wrap()
                    ->limit(60)
                    ->tooltip(
                        fn (?string $state): ?string => filled($state) ? $state : null
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
                        fn (PengajuanBarang $record): ?string => $record->created_at?->
                                timezone('Asia/Jakarta')->format('H:i:s')
                    )
                    ->sortable(),

                TextColumn::make('waktu_mulai')
                    ->label('Waktu Diterima')
                    ->dateTime('d M Y')
                    ->timezone('Asia/Jakarta')
                    ->description(
                        fn (PengajuanBarang $record): ?string => $record->waktu_mulai?->
                                timezone('Asia/Jakarta')->format('H:i:s')
                    )
                    ->toggleable()
                    ->placeholder('-'),

                TextColumn::make('waktu_selesai')
                    ->label('Waktu Selesai')
                    ->dateTime('d M Y')
                    ->timezone('Asia/Jakarta')
                    ->description(
                        fn (PengajuanBarang $record): ?string => $record->waktu_selesai?->
                                timezone('Asia/Jakarta')->format('H:i:s')
                    )
                    ->toggleable()
                    ->placeholder('-'),

                TextColumn::make('durasi_pengerjaan')
                    ->label('Durasi')
                    ->toggleable()
                    ->placeholder('-'),
            ])

            ->filters([

                SelectFilter::make('status')
                    ->label('Status Pengajuan')
                    ->options([
                        'Open' => 'Open',
                        'In Progress' => 'In Progress',
                        'Close' => 'Close',
                    ]),

                SelectFilter::make('status_outcome')
                    ->label('Hasil Pengajuan')
                    ->options([
                        'Completed' => 'Selesai',
                        'Rejected' => 'Ditolak',
                    ])
                    ->query(
                        function (Builder $query, array $data): Builder {
                            $value = $data['value'] ?? null;

                            if (blank($value)) {
                                return $query;
                            }

                            return $query
                                ->where('status', 'Close')
                                ->whereHas('logs', function (Builder $query) use ($value): void {
                                    $query
                                        ->where('kategori_log', 'Status')
                                        ->where('data_baru', 'Close')
                                        ->where('id', function ($latestCloseLog): void {
                                            $latestCloseLog
                                                ->select('id')
                                                ->from('log_data_pengajuan_barang as latest_close_logs')
                                                ->whereColumn('latest_close_logs.pengajuan_id', 'pengajuan_barang.id')
                                                ->where('latest_close_logs.kategori_log', 'Status')
                                                ->where('latest_close_logs.data_baru', 'Close')
                                                ->orderByDesc('latest_close_logs.created_at')
                                                ->orderByDesc('latest_close_logs.id')
                                                ->limit(1);
                                        })
                                        ->when(
                                            $value === 'Completed',
                                            fn (Builder $query) => $query->where('keterangan', 'like', '%[SELESAI]%')
                                        )
                                        ->when(
                                            $value === 'Rejected',
                                            fn (Builder $query) => $query->where('keterangan', 'like', '%[DITOLAK]%')
                                        );
                                });
                        }
                    ),

                TrashedFilter::make()
                    ->label('Data Terhapus'),
            ])

            /*
            |--------------------------------------------------------------------------
            | Record Actions Buttons
            |--------------------------------------------------------------------------
            */
            ->recordActions([

                /* Button Ambil Permintaan */
                Action::make('ambil_tiket')
                    ->tooltip('Ambil Pengajuan')
                    ->label('')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->color('warning')
                    ->visible(
                        fn (PengajuanBarang $record): bool => auth()->user()->hasAnyRole(['admin', 'super_admin'])
                            && $record->isOpen()
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Ambil Pengajuan')
                    ->modalDescription(
                        'Pengajuan akan dipindahkan ke status In Progress.'
                    )
                    ->modalSubmitActionLabel('Ya, Ambil Pengajuan')
                    ->action(
                        function (PengajuanBarang $record): void {

                            $namaUser =
                                auth()->user()?->name
                                ?? 'System';

                            $record->updateStatus(
                                'In Progress',
                                "Pengajuan mulai diproses oleh {$namaUser}"
                            );

                            $record->sendMessage(
                                "Pengajuan diambil oleh {$namaUser}."
                            );
                        }
                    ),

                /* Button Selesaikan Permintaan */
                Action::make('selesai')
                    ->tooltip('Selesaikan Pengajuan')
                    ->label('')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(
                        fn (PengajuanBarang $record): bool => auth()->user()->hasAnyRole(['admin', 'super_admin'])
                            && $record->isInProgress()
                            && ! $record->isMenungguKonfirmasi()
                    )
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('catatan')
                            ->label('Catatan Penyelesaian')
                            ->default(
                                fn (PengajuanBarang $record): string => "Pengajuan #{$record->kodePengajuan} telah selesai diproses. "
                                    .'Dan Barang telah diserahkan kepada pemohon.'
                            )
                            ->placeholder(
                                'Tuliskan catatan tambahan mengenai penyelesaian pengajuan...'
                            )
                            ->required()
                            ->rows(4),
                    ])
                    ->modalHeading('Konfirmasi Penyelesaian')
                    ->modalDescription(
                        'Pemohon akan diminta mengonfirmasi penyelesaian pengajuan ini.'
                    )
                    ->modalSubmitActionLabel('Minta Konfirmasi')
                    ->action(
                        function (PengajuanBarang $record, array $data): void {

                            $record->mintaKonfirmasiSelesai(
                                $data['catatan']
                            );
                        }
                    ),

                /* Button Tolak Permintaan */
                Action::make('tolak')
                    ->tooltip('Tolak Pengajuan')
                    ->label('')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(
                        fn (PengajuanBarang $record): bool => auth()->user()->hasAnyRole(['admin', 'super_admin'])
                            && $record->isOpen()
                    )
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('catatan')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->rows(4),
                    ])
                    ->modalHeading('Konfirmasi Penolakan')
                    ->modalDescription(
                        'Pengajuan akan ditutup sebagai pengajuan yang ditolak.'
                    )
                    ->modalSubmitActionLabel('Tolak Pengajuan')
                    ->action(
                        function (PengajuanBarang $record, array $data): void {

                            $record->closeAsRejected(
                                $data['catatan']
                            );
                        }
                    ),

                /* Button Buka Kembali */
                Action::make('reopen')
                    ->tooltip('Buka Kembali')
                    ->label('')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(
                        fn (PengajuanBarang $record): bool => auth()->user()->hasAnyRole(['admin', 'super_admin'])
                            && $record->isClosed()
                    )
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('catatan')
                            ->label('Alasan Buka Kembali')
                            ->required()
                            ->rows(4),
                    ])
                    ->modalHeading('Buka Kembali Pengajuan')
                    ->modalDescription(
                        'Status pengajuan akan dikembalikan menjadi In Progress.'
                    )
                    ->action(
                        function (PengajuanBarang $record, array $data): void {

                            $record->reopen(
                                $data['catatan']
                            );
                        }
                    ),

                /* Button Lihat Detail Data Pengajuan */
                ViewAction::make()
                    ->label('')
                    ->tooltip('Lihat Detail'),

                /* Button Pulihkan Data Pengajuan */
                RestoreAction::make()
                    ->label('')
                    ->tooltip('Pulihkan Data')
                    ->visible(
                        fn (PengajuanBarang $record): bool => $record->canStaffRestore()
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Pulihkan Pengajuan Barang')
                    ->modalDescription(
                        'Pengajuan yang dipulihkan akan dikembalikan ke daftar pengajuan aktif.'
                    )
                    ->modalSubmitActionLabel('Ya, Pulihkan')
                    ->successNotification(null)
                    ->action(
                        function (PengajuanBarang $record): void {
                            $record->restore();

                            Notification::make()
                                ->success()
                                ->title('Pengajuan berhasil dipulihkan')
                                ->body(
                                    'Pengajuan telah dikembalikan ke daftar pengajuan aktif.'
                                )
                                ->send();
                        }
                    ),

                /* Button Edit Data Pengajuan */
                EditAction::make()
                    ->label('')
                    ->tooltip('Edit Pengajuan')
                    ->visible(
                        fn (PengajuanBarang $record): bool => $record->canStaffEdit()
                    ),

                /* Button Delete Data Pengajuan */
                DeleteAction::make()
                    ->label('')
                    ->tooltip('Hapus Data')
                    ->visible(
                        fn (PengajuanBarang $record): bool => $record->canStaffDelete()
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Pengajuan Barang')
                    ->modalDescription(
                        'Pengajuan yang telah selesai atau ditolak akan dipindahkan ke tempat sampah.'
                    )
                    ->modalSubmitActionLabel('Ya, Hapus')
                    ->successNotification(null)
                    ->action(
                        function (PengajuanBarang $record): void {
                            $record->delete();

                            Notification::make()
                                ->success()
                                ->title('Pengajuan berhasil dihapus')
                                ->body(
                                    'Pengajuan telah dipindahkan ke tempat sampah.'
                                )
                                ->send();
                        }
                    ),

                /* Button Delete Data Permanen */
                ForceDeleteAction::make()
                    ->label('')
                    ->tooltip('Hapus Permanen')
                    ->visible(
                        fn (PengajuanBarang $record): bool => $record->canStaffForceDelete()
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Permanen')
                    ->modalDescription(
                        'Data akan dihapus secara permanen dari sistem dan tidak dapat dipulihkan.'
                    )
                    ->modalSubmitActionLabel('Hapus Permanen')
                    ->successNotification(null)
                    ->action(function (PengajuanBarang $record): void {
                        $record->forceDelete();

                        Notification::make()
                            ->success()
                            ->title('Pengajuan berhasil dihapus permanen')
                            ->body(
                                'Pengajuan telah dihapus secara permanen dari database.'
                            )
                            ->send();
                    }),

            ])

            /*
            |--------------------------------------------------------------------------
            | Bulk Actions
            |--------------------------------------------------------------------------
            */

            ->toolbarActions([
                BulkActionGroup::make([

                    RestoreBulkAction::make()
                        ->label('Pulihkan Data')
                        ->visible(
                            fn (): bool => auth()->user()?->hasAnyRole(['admin', 'super_admin']) ?? false
                        )
                        ->requiresConfirmation()
                        ->modalHeading('Pulihkan Pengajuan Barang')
                        ->modalDescription(
                            'Pengajuan yang dipulihkan akan dikembalikan ke daftar pengajuan aktif.'
                        )
                        ->modalSubmitActionLabel('Ya, Pulihkan')
                        ->successNotification(null)
                        ->action(function ($records): void {
                            $restorableRecords = $records->filter(
                                fn (PengajuanBarang $record): bool => $record->canStaffRestore()
                            );

                            $skippedCount = $records->count() - $restorableRecords->count();

                            $restorableRecords->each(
                                fn (PengajuanBarang $record) => $record->restore()
                            );

                            if ($restorableRecords->isEmpty()) {
                                Notification::make()
                                    ->warning()
                                    ->title('Tidak ada pengajuan yang dipulihkan')
                                    ->body('Data yang dipilih tidak memenuhi ketentuan pemulihan.')
                                    ->send();

                                return;
                            }

                            $message = $restorableRecords->count()
                                .' pengajuan berhasil dipulihkan.';

                            if ($skippedCount > 0) {
                                $message .= ' '.$skippedCount
                                    .' data dilewati karena tidak memenuhi ketentuan pemulihan.';
                            }

                            Notification::make()
                                ->success()
                                ->title('Pengajuan berhasil dipulihkan')
                                ->body($message)
                                ->send();
                        }),

                    DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Pengajuan Barang')
                        ->modalDescription(
                            'Hanya pengajuan yang sudah selesai atau memenuhi ketentuan penghapusan yang akan dipindahkan ke tempat sampah.'
                        )
                        ->modalSubmitActionLabel('Ya, Hapus')
                        ->visible(
                            fn (): bool => auth()->user()?->hasAnyRole(['admin', 'super_admin']) ?? false
                        )
                        ->successNotification(null)
                        ->action(function ($records): void {

                            $deletableRecords = $records->filter(
                                fn (PengajuanBarang $record): bool => $record->canStaffDelete()
                            );

                            $skippedCount =
                                $records->count() - $deletableRecords->count();

                            $deletableRecords->each(
                                fn (PengajuanBarang $record): mixed => $record->delete()
                            );

                            if ($deletableRecords->isEmpty()) {
                                Notification::make()
                                    ->warning()
                                    ->title('Tidak ada pengajuan yang dihapus')
                                    ->body('Pengajuan yang dipilih tidak memenuhi ketentuan penghapusan.')
                                    ->send();

                                return;
                            }

                            $message =
                                $deletableRecords->count()
                                .' pengajuan berhasil dipindahkan ke tempat sampah.';

                            if ($skippedCount > 0) {
                                $message .= ' '.$skippedCount
                                    .' pengajuan dilewati karena tidak memenuhi ketentuan penghapusan.';
                            }

                            Notification::make()
                                ->success()
                                ->title('Pengajuan berhasil dihapus')
                                ->body($message)
                                ->send();
                        }),

                    ForceDeleteBulkAction::make()
                        ->label('Hapus Permanen')
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Permanen')
                        ->modalDescription(
                            'Data yang dipilih akan dihapus secara permanen dari database dan tidak dapat dipulihkan.'
                        )
                        ->modalSubmitActionLabel('Hapus Permanen')
                        ->successNotification(null)
                        ->visible(
                            fn (): bool => auth()->user()?->hasRole('super_admin') ?? false
                        )
                        ->action(function ($records): void {
                            $forceDeletableRecords = $records->filter(
                                fn (PengajuanBarang $record): bool => $record->canStaffForceDelete()
                            );

                            $skippedCount = $records->count() - $forceDeletableRecords->count();

                            $forceDeletableRecords->each(
                                fn (PengajuanBarang $record) => $record->forceDelete()
                            );

                            if ($forceDeletableRecords->isEmpty()) {
                                Notification::make()
                                    ->warning()
                                    ->title('Tidak ada pengajuan yang dihapus permanen')
                                    ->body('Data yang dipilih tidak memenuhi ketentuan penghapusan permanen.')
                                    ->send();

                                return;
                            }

                            $message = $forceDeletableRecords->count()
                                .' pengajuan berhasil dihapus permanen.';

                            if ($skippedCount > 0) {
                                $message .= ' '.$skippedCount
                                    .' data dilewati karena tidak memenuhi ketentuan penghapusan permanen.';
                            }

                            Notification::make()
                                ->success()
                                ->title('Pengajuan berhasil dihapus permanen')
                                ->body($message)
                                ->send();
                        }),

                ]),
            ]);
    }
}
