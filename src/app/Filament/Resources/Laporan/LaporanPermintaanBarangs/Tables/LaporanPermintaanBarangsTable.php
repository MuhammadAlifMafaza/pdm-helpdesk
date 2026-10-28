<?php

namespace App\Filament\Resources\Laporan\LaporanPermintaanBarangs\Tables;

use App\Services\Laporan\LaporanPeriodFormatter;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LaporanPermintaanBarangsTable
{
    public static function configure(Table $table): Table
    {
        return $table

            ->columns([
                TextColumn::make('rowIndex')
                    ->label('No')
                    ->rowIndex(),

                TextColumn::make('pengajuan.kode_pengajuan')
                    ->label('Kode Pengajuan')
                    ->searchable(['no_pengajuan']) // Sesuai kolom di DB
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('nama_pemohon')
                    ->label('Pemohon')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nama_barang')
                    ->label('Barang')
                    ->searchable()
                    ->wrap(),

                // Menggunakan Helper dari Model yang baru kita buat
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($record) => $record->status_label)
                    ->color(fn ($record) => $record->status_color)
                    ->icon(fn ($record) => $record->status_icon),

                TextColumn::make('outcome')
                    ->label('Hasil Persetujuan')
                    ->badge()
                    ->formatStateUsing(fn ($record) => $record->outcome_label)
                    ->color(fn ($record) => $record->outcome_color)
                    ->placeholder('Masih belum ada persetujuan'),

                // 1. Kolom Waktu Mulai dengan deskripsi (misal: "2 hari yang lalu")
                TextColumn::make('pengajuan.waktu_mulai')
                    ->label('Waktu Mulai')
                    ->dateTime('d M Y, H:i')
                    ->description(fn ($record) => $record->pengajuan?->waktu_mulai?->diffForHumans())
                    ->placeholder('masih belum dimulai')
                    ->sortable()
                    ->toggleable(),

                // 2. Kolom Waktu Selesai
                TextColumn::make('pengajuan.waktu_selesai')
                    ->label('Waktu Selesai')
                    ->dateTime('d M Y, H:i')
                    ->placeholder('masih belum dimulai')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // 3. Kolom Durasi dengan Deskripsi Penggunaan Jam & Kategori
                TextColumn::make('pengajuan.durasi_pengerjaan')
                    ->label('Durasi Pengerjaan')
                    // Menambahkan teks kecil di bawahnya: "Kategori: Cepat (≈ 2.5 Jam)"
                    ->description(function ($record) {
                        if (! $record->pengajuan?->waktu_selesai && $record->status === 'In Progress') {
                            return 'Sedang Dihitung...';
                        }

                        // Memanggil helper getDurasiJamAttribute() dari model LaporanPermintaanBarang
                        $jam = $record->durasi_jam > 0 ? " (≈ {$record->durasi_jam} Jam)" : '';

                        // Memanggil helper getProcessCategoryAttribute()
                        return "Kategori: {$record->process_category}".$jam;
                    })
                    ->placeholder('Masih dikerjakan')
                    ->toggleable(),

            ])
            // 2. Definisikan Filter (Menggunakan Scope dari Model)
            ->filters([
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

                SelectFilter::make('status')
                    ->options([
                        'Open' => 'Menunggu Persetujuan',
                        'In Progress' => 'Sedang Diproses',
                        'Close' => 'Selesai',
                    ])
                    ->query(fn (Builder $query, array $data) => $query->status($data['value'])),
            ])

            ->actions([
                // ViewAction::make(),
            ])
            ->bulkActions([]);
    }
}
