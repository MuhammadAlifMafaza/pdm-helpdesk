<?php

namespace App\Filament\Resources\KegiatanTeknisi\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;

class KegiatanTeknisiInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Informasi Kegiatan')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('teknisi.name')
                            ->label('Dibuat Oleh'),

                        TextEntry::make('tanggal')
                            ->label('Tanggal')
                            ->date('d M Y'),

                        TextEntry::make('ruangan.nama_ruangan')
                            ->label('Ruangan')
                            ->placeholder('-'),

                        TextEntry::make('petugas.name')
                            ->label('Petugas yang Mengerjakan')
                            ->placeholder('Belum ada petugas')
                            ->separator(', ')
                            ->listWithLineBreaks(),
                    ]),

                Section::make('Catatan Kegiatan')
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('deskripsi_kegiatan')
                            ->prose()
                            ->columnSpanFull(),
                    ]),

                Section::make('Riwayat')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Dibuat')
                            ->dateTime('d M Y H:i')
                            ->size(TextSize::Small)
                            ->color('gray'),

                        TextEntry::make('updated_at')
                            ->label('Terakhir Diubah')
                            ->dateTime('d M Y H:i')
                            ->size(TextSize::Small)
                            ->color('gray'),
                    ]),

            ]);
    }
}
