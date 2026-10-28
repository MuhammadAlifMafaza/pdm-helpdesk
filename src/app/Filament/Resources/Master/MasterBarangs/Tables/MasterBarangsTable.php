<?php

namespace App\Filament\Resources\Master\MasterBarangs\Tables;

use App\Models\Modules\Master\Barang\Models\MasterBarang;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Illuminate\Database\Eloquent\Collection;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class MasterBarangsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('nama_barang')
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)

            ->columns([
                TextColumn::make('kategoriBarang.nama_kategori')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable()
                    ->badge(),

                TextColumn::make('nama_barang')
                    ->label('Nama Barang')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(50)
                    ->wrap()
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('deleted_at')
                    ->label('Dihapus')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                SelectFilter::make('kategori_barang_id')
                    ->label('Kategori')
                    ->relationship(
                        'kategoriBarang',
                        'nama_kategori'
                    )
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('Semua')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif'),

                TrashedFilter::make(),
            ])

            ->recordActions([
                ViewAction::make(),

                EditAction::make(),

                Action::make('toggle_active')
                    ->label(
                        fn(MasterBarang $record): string =>
                            $record->isActive()
                            ? 'Nonaktifkan'
                            : 'Aktifkan'
                    )
                    ->icon(
                        fn(MasterBarang $record): string =>
                            $record->isActive()
                            ? 'heroicon-o-x-circle'
                            : 'heroicon-o-check-circle'
                    )
                    ->color(
                        fn(MasterBarang $record): string =>
                            $record->isActive()
                            ? 'warning'
                            : 'success'
                    )
                    ->requiresConfirmation()
                    ->modalHeading(
                        fn(MasterBarang $record): string =>
                            $record->isActive()
                            ? 'Nonaktifkan Barang?'
                            : 'Aktifkan Barang?'
                    )
                    ->modalDescription(
                        fn(MasterBarang $record): string =>
                            $record->isActive()
                            ? 'Barang tidak akan tersedia untuk transaksi baru.'
                            : 'Barang akan tersedia kembali untuk transaksi baru.'
                    )
                    ->action(function (MasterBarang $record): void {
                        $record->update([
                            'is_active' => !$record->is_active,
                        ]);

                        Notification::make()
                            ->title(
                                $record->is_active
                                ? 'Barang diaktifkan'
                                : 'Barang dinonaktifkan'
                            )
                            ->success()
                            ->send();
                    }),

                DeleteAction::make(),

                RestoreAction::make(),

                ForceDeleteAction::make()
                    ->visible(
                        fn(MasterBarang $record): bool =>
                            $record->trashed()
                    )
                    ->before(function (MasterBarang $record, ForceDeleteAction $action): void {
                        if (!$record->canBeForceDeleted()) {
                            Notification::make()
                                ->title('Barang tidak dapat dihapus permanen')
                                ->body(
                                    'Barang ini sudah digunakan pada transaksi pengajuan barang.'
                                )
                                ->danger()
                                ->persistent()
                                ->send();

                            $action->halt();
                        }
                    }),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->requiresConfirmation(),

                    ForceDeleteBulkAction::make()
                        ->label('Hapus Permanen')
                        ->modalHeading('Hapus Barang Secara Permanen?')
                        ->modalDescription(
                            'Barang yang sudah digunakan pada pengajuan barang '
                            . 'tidak akan dihapus.'
                        )
                        ->successNotification(null)
                        ->action(function (Collection $records): void {
                            $deletedCount = 0;
                            $blocked = [];

                            foreach ($records as $record) {
                                // Hapus permanen hanya berlaku untuk data yang sudah di-soft-delete.
                                if (!$record->trashed()) {
                                    $blocked[] = [
                                        'nama' => $record->nama_barang,
                                        'jumlah' => 0,
                                        'alasan' => 'data belum berada di tempat sampah',
                                    ];

                                    continue;
                                }

                                $jumlahPengajuan = $record->pengajuanBarang()
                                    ->withTrashed()
                                    ->count();

                                if ($jumlahPengajuan > 0) {
                                    $blocked[] = [
                                        'nama' => $record->nama_barang,
                                        'jumlah' => $jumlahPengajuan,
                                        'alasan' => 'masih memiliki transaksi pengajuan',
                                    ];

                                    continue;
                                }

                                $record->forceDelete();
                                $deletedCount++;
                            }

                            /*
                             * Semua berhasil.
                             */
                            if (empty($blocked)) {
                                Notification::make()
                                    ->title('Penghapusan berhasil')
                                    ->body(
                                        $deletedCount
                                        . ' barang berhasil dihapus secara permanen.'
                                    )
                                    ->success()
                                    ->send();

                                return;
                            }

                            /*
                             * Tidak ada yang berhasil.
                             */
                            if ($deletedCount === 0) {
                                $detail = collect($blocked)
                                    ->map(
                                        fn(array $item): string =>
                                            $item['nama']
                                            . ' ('
                                            . ($item['jumlah'] > 0
                                                ? $item['jumlah'] . ' pengajuan'
                                                : $item['alasan'])
                                            . ')'
                                    )
                                    ->implode(', ');

                                Notification::make()
                                    ->title('Tidak ada barang yang dihapus')
                                    ->body(
                                        count($blocked)
                                        . ' barang tidak dapat dihapus secara permanen '
                                        . 'karena belum berada di tempat sampah atau masih memiliki transaksi: '
                                        . $detail
                                        . '.'
                                    )
                                    ->danger()
                                    ->persistent()
                                    ->send();

                                return;
                            }

                            /*
                             * Sebagian berhasil.
                             */
                            $detail = collect($blocked)
                                ->map(
                                    fn(array $item): string =>
                                        $item['nama']
                                        . ' ('
                                        . $item['jumlah']
                                        . ' pengajuan)'
                                )
                                ->implode(', ');

                            Notification::make()
                                ->title('Penghapusan sebagian berhasil')
                                ->body(
                                    $deletedCount
                                    . ' barang berhasil dihapus permanen. '
                                    . count($blocked)
                                    . ' barang tidak dapat dihapus karena belum berada '
                                    . 'di tempat sampah atau masih memiliki transaksi: '
                                    . $detail
                                    . '.'
                                )
                                ->warning()
                                ->persistent()
                                ->send();
                        }),

                    RestoreBulkAction::make()
                        ->label('Pulihkan Terpilih'),

                    BulkAction::make('activate')
                        ->label('Aktifkan')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Aktifkan Barang Terpilih?')
                        ->modalDescription(
                            'Barang yang dipilih akan diaktifkan dan dapat digunakan '
                            . 'untuk transaksi baru.'
                        )
                        ->action(function ($records): void {
                            $records->each(function (MasterBarang $record): void {
                                $record->update([
                                    'is_active' => true,
                                ]);
                            });

                            Notification::make()
                                ->title('Barang berhasil diaktifkan')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('deactivate')
                        ->label('Nonaktifkan')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Nonaktifkan Barang Terpilih?')
                        ->modalDescription(
                            'Barang yang dipilih tidak akan tersedia untuk transaksi baru.'
                        )
                        ->action(function ($records): void {
                            $records->each(function (MasterBarang $record): void {
                                $record->update([
                                    'is_active' => false,
                                ]);
                            });

                            Notification::make()
                                ->title('Barang berhasil dinonaktifkan')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}
