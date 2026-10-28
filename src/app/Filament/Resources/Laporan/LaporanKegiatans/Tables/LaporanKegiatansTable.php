<?php

namespace App\Filament\Resources\Laporan\LaporanKegiatans\Tables;

use App\Services\Laporan\LaporanPeriodFormatter;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LaporanKegiatansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('rowIndex')
                    ->label('No')
                    ->rowIndex(),

                TextColumn::make('tanggal_kegiatan')
                    ->label('Hari / Tanggal')
                    // Format bahasa Indonesia bawaan Carbon, misal: "Monday, 10 Jul 2026"
                    ->formatStateUsing(fn ($state) => $state ? $state->locale('id')->translatedFormat('l, d F Y') : '-')
                    ->description(fn ($record) => $record->tanggal_kegiatan?->diffForHumans())
                    ->sortable()
                    ->searchable(['tanggal']),

                TextColumn::make('nama_teknisi')
                    ->label('Nama Teknisi')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->color('primary'),

                TextColumn::make('lokasi')
                    ->label('Lokasi')
                    ->state(fn ($record) => $record->lokasi ?? '-')
                    ->placeholder('-'),

                TextColumn::make('deskripsi')
                    ->label('Deskripsi Kegiatan')
                    ->searchable(['deskripsi_kegiatan'])
                    ->wrap(),
            ])
            ->filters([
                // Filter berdasarkan rentang tanggal kegiatan
                Filter::make('periode')
                    ->label('Periode Laporan')
                    ->form([
                        Select::make('bulan')
                            ->label('Bulan')
                            ->placeholder('Pilih bulan')
                            ->options([
                                1 => 'Januari',
                                2 => 'Februari',
                                3 => 'Maret',
                                4 => 'April',
                                5 => 'Mei',
                                6 => 'Juni',
                                7 => 'Juli',
                                8 => 'Agustus',
                                9 => 'September',
                                10 => 'Oktober',
                                11 => 'November',
                                12 => 'Desember',
                            ]),
                        Select::make('tahun')
                            ->label('Tahun')
                            ->placeholder('Pilih tahun')
                            ->options(fn () => collect(range(now()->year + 1, now()->year - 5))
                                ->mapWithKeys(fn ($year) => [$year => (string) $year])
                                ->all()),
                        DatePicker::make('dari_tanggal')
                            ->label('Tanggal Awal'),
                        DatePicker::make('sampai_tanggal')
                            ->label('Tanggal Akhir'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $range = LaporanPeriodFormatter::range($data);

                        return $query->periode($range['from'], $range['until']);
                    }),
            ])
            ->actions([
                // ViewAction::make(),
            ])
            ->bulkActions([]);
    }
}
