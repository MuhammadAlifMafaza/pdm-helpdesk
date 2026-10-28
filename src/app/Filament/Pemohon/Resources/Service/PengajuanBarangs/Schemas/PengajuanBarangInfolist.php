<?php

namespace App\Filament\Pemohon\Resources\Service\PengajuanBarangs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PengajuanBarangInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->schema([
                /*
                |--------------------------------------------------------------------------
                | Informasi Pengajuan
                |--------------------------------------------------------------------------
                */

                Section::make('Informasi Pengajuan')
                    ->description(
                        'Informasi utama pengajuan barang dan status proses layanan.'
                    )
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('kode_pengajuan')
                            ->label('Nomor Pengajuan')
                            ->weight('bold'),

                        TextEntry::make('user.name')
                            ->label('Nama Pemohon')
                            ->placeholder('Tidak diketahui'),

                        TextEntry::make('barang.kategoriBarang.nama_kategori')
                            ->label('Kategori Barang')
                            ->badge()
                            ->placeholder('Tidak tersedia'),

                        TextEntry::make('nama_barang')
                            ->label('Nama Barang')
                            ->weight('bold')
                            ->placeholder('Tidak tersedia'),

                        TextEntry::make('spesifikasi_barang')
                            ->label('Spesifikasi Barang')
                            ->placeholder('Tidak ada spesifikasi')
                            ->columnSpanFull(),

                        TextEntry::make('jumlah')
                            ->label('Jumlah Barang')
                            ->numeric(),

                        TextEntry::make('status')
                            ->label('Status Pengajuan')
                            ->badge()
                            ->icon(
                                fn(?string $state): string => match ($state) {
                                    'Open' => 'heroicon-o-folder-open',
                                    'In Progress' => 'heroicon-o-arrow-path',
                                    'Close' => 'heroicon-o-check-circle',
                                    default => 'heroicon-o-question-mark-circle',
                                }
                            )
                            ->color(
                                fn(?string $state): string => match ($state) {
                                    'Open' => 'info',
                                    'In Progress' => 'warning',
                                    'Close' => 'success',
                                    default => 'gray',
                                }
                            ),

                        TextEntry::make('status_outcome')
                            ->label('Hasil Pengajuan')
                            ->badge()
                            ->icon(
                                fn(?string $state): string => match ($state) {
                                    'Completed' => 'heroicon-o-check-circle',
                                    'Rejected' => 'heroicon-o-x-circle',
                                    'Reopen' => 'heroicon-o-arrow-path',
                                    default => 'heroicon-o-question-mark-circle',
                                }
                            )
                            ->color(
                                fn(?string $state): string => match ($state) {
                                    'Completed' => 'success',
                                    'Rejected' => 'danger',
                                    'Reopen' => 'warning',
                                    default => 'gray',
                                }
                            )
                            ->placeholder('Belum ditentukan'),

                        TextEntry::make('created_at')
                            ->label('Tanggal Pengajuan')
                            ->dateTime('d M Y H:i'),

                        TextEntry::make('updated_at')
                            ->label('Terakhir Diperbarui')
                            ->dateTime('d M Y H:i'),
                    ]),

                /*
                |--------------------------------------------------------------------------
                | Alasan Permintaan
                |--------------------------------------------------------------------------
                */

                Section::make('Alasan Permintaan')
                    ->description(
                        'Alasan atau kebutuhan barang yang disampaikan pada pengajuan.'
                    )
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('alasan')
                            ->hiddenLabel()
                            ->placeholder('Tidak ada alasan yang dicatat.')
                            ->columnSpanFull(),
                    ]),

                /*
                |--------------------------------------------------------------------------
                | Timeline Aktivitas
                |--------------------------------------------------------------------------
                */

                Section::make('Timeline Aktivitas')
                    ->columnSpan(1)
                    ->schema([
                        ViewEntry::make('id')
                            ->hiddenLabel()
                            ->view('filament.pages.tiket.timeline'),
                    ]),

                /*
                |--------------------------------------------------------------------------
                | Diskusi
                |--------------------------------------------------------------------------
                */

                Section::make('Diskusi')
                    ->columnSpan(1)
                    ->schema([
                        ViewEntry::make('id')
                            ->hiddenLabel()
                            ->view('filament.pages.tiket.chat'),
                    ]),
            ]);
    }
}
