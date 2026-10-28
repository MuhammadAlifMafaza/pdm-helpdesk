<?php

namespace App\Filament\Resources\KegiatanTeknisi\Schemas;

use Filament\Forms\Components\DatePicker;
// Filament Action Imports
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class KegiatanTeknisiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('tanggal')
                    ->label('Tanggal')
                    ->required()
                    ->default(now()),

                Select::make('ruangan_id')
                    ->label('Ruangan')
                    ->relationship('ruangan', 'nama_ruangan')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('petugas')
                    ->label('Nama Petugas')
                    ->relationship(
                        'petugas',
                        'name',
                        fn (Builder $query) => $query->role(['teknisi', 'admin'])
                    )
                    ->multiple()
                    ->default([auth()->id()])
                    ->searchable()
                    ->preload()
                    ->nullable(),

                Textarea::make('deskripsi_kegiatan')
                    ->label('Catatan Kegiatan')
                    ->placeholder('Tuliskan catatan kegiatan teknisi di sini...')
                    ->rows(8)
                    ->required()
                    ->columnSpanFull(),

            ]);
    }
}
