<?php

namespace App\Filament\Resources\Master\MasterKategoriBarang\Schemas;

use Filament\Schemas\Schema;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;

class MasterKategoriBarangInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                /*
                 * =========================================================
                 * INFORMASI KATEGORI
                 * =========================================================
                 */
                Section::make('Informasi Kategori Barang')
                    ->schema([
                        TextEntry::make('nama_kategori')
                            ->label('Nama Kategori')
                            ->weight('bold'),

                        TextEntry::make('is_active')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(
                                fn(bool $state): string => $state
                                    ? 'Aktif'
                                    : 'Tidak Aktif'
                            )
                            ->color(
                                fn(bool $state): string => $state
                                    ? 'success'
                                    : 'gray'
                            ),

                        TextEntry::make('deskripsi')
                            ->label('Deskripsi')
                            ->placeholder('Tidak ada deskripsi.'),

                        TextEntry::make('jumlah_barang')
                            ->label('Jumlah Barang')
                            ->state(
                                fn($record): int => $record
                                    ->barang()
                                    ->count()
                            )
                            ->formatStateUsing(
                                fn(int $state): string => "{$state} barang"
                            )
                            ->badge()
                            ->color('info'),

                        TextEntry::make('created_at')
                            ->label('Dibuat')
                            ->dateTime('d M Y H:i')
                            ->timezone('Asia/Jakarta'),

                        TextEntry::make('updated_at')
                            ->label('Terakhir Diperbarui')
                            ->dateTime('d M Y H:i')
                            ->timezone('Asia/Jakarta'),
                    ])
                    ->columnSpanFull()
                    ->columns(2),

                /*
                 * =========================================================
                 * BARANG TERDAFTAR
                 * =========================================================
                 */
                Section::make('Barang Terdaftar')
                    ->description(
                        'Daftar barang yang terdaftar pada kategori ini, '
                        . 'termasuk barang yang sedang tidak aktif.'
                    )
                    ->schema([
                        RepeatableEntry::make('barang')
                            ->label('Daftar Barang')
                            ->state(
                                fn($record) => $record
                                    ->barang()
                                    ->withTrashed()
                                    ->orderBy('nama_barang')
                                    ->get()
                            )
                            ->schema([

                                TextEntry::make('nama_barang')
                                    ->hiddenLabel()
                                    ->weight('bold')
                                    ->columnSpan(1),

                                TextEntry::make('keterangan')
                                    ->hiddenLabel()
                                    ->placeholder('Tidak ada keterangan.')
                                    ->color('gray')
                                    ->columnSpan(1),

                                TextEntry::make('is_active')
                                    ->hiddenLabel()
                                    ->badge()
                                    ->formatStateUsing(
                                        fn(bool $state): string => $state
                                            ? 'Aktif'
                                            : 'Tidak Aktif'
                                    )
                                    ->color(
                                        fn(bool $state): string => $state
                                            ? 'success'
                                            : 'gray'
                                    )
                                    ->columnSpan(1),

                            ])
                            ->columns(3)
                            ->contained(false)
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->columnSpanFull(),

            ]);
    }
}
