<?php

namespace App\Filament\Resources\Master\MasterKategoriBarang\Tables;

use App\Models\Modules\Master\Barang\Models\MasterKategoriBarang;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class MasterKategoriBarangsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('nama_kategori')
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)

            ->columns([
                TextColumn::make('nama_kategori')
                    ->label('Nama Kategori')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('deskripsi')
                    ->label('Deskripsi')
                    ->limit(70)
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('barang_count')
                    ->label('Jumlah Barang')
                    ->counts('barang')
                    ->badge()
                    ->alignCenter(),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(
                        isToggledHiddenByDefault: true
                    ),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(
                        isToggledHiddenByDefault: true
                    ),
            ])

            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('Semua')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif'),

                TrashedFilter::make(),
            ])

            ->actions([
                ViewAction::make(),

                EditAction::make(),

                Action::make('toggle_active')
                    ->label(
                        fn(MasterKategoriBarang $record): string => $record->isActive()
                            ? 'Nonaktifkan'
                            : 'Aktifkan'
                    )
                    ->icon(
                        fn(MasterKategoriBarang $record): string => $record->isActive()
                            ? 'heroicon-o-x-circle'
                            : 'heroicon-o-check-circle'
                    )
                    ->color(
                        fn(MasterKategoriBarang $record): string => $record->isActive()
                            ? 'warning'
                            : 'success'
                    )
                    ->requiresConfirmation()
                    ->modalHeading(
                        fn(MasterKategoriBarang $record): string => $record->isActive()
                            ? 'Nonaktifkan Kategori?'
                            : 'Aktifkan Kategori?'
                    )
                    ->modalDescription(
                        fn(MasterKategoriBarang $record): string => $record->isActive()
                            ? 'Kategori tidak akan dapat digunakan '
                            . 'untuk data baru.'
                            : 'Kategori akan dapat digunakan kembali '
                            . 'untuk data baru.'
                    )
                    ->action(function (MasterKategoriBarang $record): void {
                        $record->update([
                            'is_active' => !$record->is_active,
                        ]);

                        Notification::make()
                            ->title(
                                $record->is_active
                                ? 'Kategori diaktifkan'
                                : 'Kategori dinonaktifkan'
                            )
                            ->success()
                            ->send();
                    }),

                DeleteAction::make()
                    ->label('Hapus')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Kategori Barang')
                    ->modalDescription(
                        'Kategori akan dihapus secara soft delete. '
                        . 'Data barang dan transaksi yang sudah ada '
                        . 'tetap dipertahankan.'
                    ),

                ForceDeleteAction::make()
                    ->label('Hapus Permanen')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->visible(
                        fn(MasterKategoriBarang $record): bool => $record->trashed()
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Kategori Secara Permanen?')
                    ->modalDescription(
                        'Data yang dihapus secara permanen tidak dapat dipulihkan. '
                        . 'Pastikan kategori tidak memiliki barang terkait.'
                    )
                    ->before(
                        function (MasterKategoriBarang $record, Action $action): void {
                            $jumlahBarang = $record->barang()
                                ->withTrashed()
                                ->count();

                            if ($jumlahBarang > 0) {
                                Notification::make()
                                    ->title('Kategori tidak dapat dihapus')
                                    ->body(
                                        'Kategori "'
                                        . $record->nama_kategori
                                        . '" masih memiliki '
                                        . $jumlahBarang
                                        . ' barang terkait. '
                                        . 'Hapus atau pindahkan barang tersebut terlebih dahulu.'
                                    )
                                    ->danger()
                                    ->persistent()
                                    ->send();

                                $action->halt();
                            }
                        }
                    ),

                RestoreAction::make()
                    ->label('Pulihkan')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success'),
            ])

            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->requiresConfirmation(),

                    ForceDeleteBulkAction::make()
                        ->label('Hapus Permanen')
                        ->modalHeading('Hapus Kategori Secara Permanen?')
                        ->modalDescription(
                            'Kategori yang masih memiliki barang terkait '
                            . 'tidak akan dihapus.'
                        )
                        ->successNotification(null)
                        ->action(function (Collection $records): void {
                            $deletedCount = 0;
                            $blocked = [];

                            foreach ($records as $record) {
                                // Hapus permanen hanya berlaku untuk kategori yang sudah di-soft-delete.
                                if (!$record->trashed()) {
                                    $blocked[] = [
                                        'nama' => $record->nama_kategori,
                                        'jumlah' => 0,
                                        'alasan' => 'data belum berada di tempat sampah',
                                    ];

                                    continue;
                                }

                                $jumlahBarang = $record->barang()
                                    ->withTrashed()
                                    ->count();

                                if ($jumlahBarang > 0) {
                                    $blocked[] = [
                                        'nama' => $record->nama_kategori,
                                        'jumlah' => $jumlahBarang,
                                        'alasan' => 'masih memiliki barang terkait',
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
                                        . ' kategori berhasil dihapus secara permanen.'
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
                                        fn(array $item): string => $item['nama']
                                            . ' ('
                                            . ($item['jumlah'] > 0
                                                ? $item['jumlah'] . ' barang'
                                                : $item['alasan'])
                                            . ')'
                                    )
                                    ->implode(', ');

                                Notification::make()
                                    ->title('Tidak ada kategori yang dihapus')
                                    ->body(
                                        count($blocked)
                                        . ' kategori tidak dapat dihapus secara permanen '
                                        . 'karena belum berada di tempat sampah atau masih memiliki barang terkait: '
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
                                    fn(array $item): string => $item['nama']
                                        . ' ('
                                        . $item['jumlah']
                                        . ' barang)'
                                )
                                ->implode(', ');

                            Notification::make()
                                ->title('Penghapusan sebagian berhasil')
                                ->body(
                                    $deletedCount
                                    . ' kategori berhasil dihapus permanen. '
                                    . count($blocked)
                                    . ' kategori tidak dapat dihapus karena belum berada '
                                    . 'di tempat sampah atau masih memiliki barang terkait: '
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
                        ->label('Aktifkan Terpilih')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Aktifkan Kategori Terpilih?')
                        ->modalDescription(
                            'Semua kategori yang dipilih akan diubah menjadi aktif '
                            . 'dan dapat digunakan untuk data baru.'
                        )
                        ->action(function (Collection $records): void {
                            $records->each->update([
                                'is_active' => true,
                            ]);

                            Notification::make()
                                ->title('Kategori berhasil diaktifkan')
                                ->success()
                                ->send();
                        }),

                    BulkAction::make('deactivate')
                        ->label('Nonaktifkan Terpilih')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Nonaktifkan Kategori Terpilih?')
                        ->modalDescription(
                            'Semua kategori yang dipilih akan dinonaktifkan '
                            . 'dan tidak dapat digunakan untuk data baru.'
                        )
                        ->action(function (Collection $records): void {
                            $records->each->update([
                                'is_active' => false,
                            ]);

                            Notification::make()
                                ->title('Kategori berhasil dinonaktifkan')
                                ->success()
                                ->send();
                        }),

                ]),
            ]);
    }
}
