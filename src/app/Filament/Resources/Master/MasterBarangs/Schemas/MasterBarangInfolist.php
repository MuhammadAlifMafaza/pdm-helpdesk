<?php

namespace App\Filament\Resources\Master\MasterBarangs\Schemas;

use Filament\Schemas\Schema;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Infolist;


class MasterBarangInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informasi Barang')
                    ->schema([
                        TextEntry::make('nama_barang')
                            ->label('Nama Barang')
                            ->weight('bold'),

                        TextEntry::make('kategoriBarang.nama_kategori')
                            ->label('Kategori Barang')
                            ->badge(),

                        TextEntry::make('keterangan')
                            ->label('Keterangan')
                            ->placeholder('Tidak ada keterangan')
                            ->columnSpanFull(),

                        IconEntry::make('is_active')
                            ->label('Status')
                            ->boolean(),
                    ])
                    ->columns(2),

                Section::make('Informasi Sistem')
                    ->schema([
                        TextEntry::make('id')
                            ->label('ID'),

                        TextEntry::make('created_at')
                            ->label('Dibuat')
                            ->dateTime('d M Y H:i'),

                        TextEntry::make('updated_at')
                            ->label('Diperbarui')
                            ->dateTime('d M Y H:i'),

                        TextEntry::make('deleted_at')
                            ->label('Dihapus')
                            ->dateTime('d M Y H:i')
                            ->placeholder('Belum dihapus'),
                    ])
                    ->columns(2),
            ]);
    }
}
