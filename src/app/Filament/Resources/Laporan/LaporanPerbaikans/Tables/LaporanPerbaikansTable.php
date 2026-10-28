<?php

namespace App\Filament\Resources\Laporan\LaporanPerbaikans\Tables;

use App\Models\Modules\Laporan\Models\LaporanPerbaikan;
use App\Services\Laporan\LaporanPeriodFormatter;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
// Filament Filters
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LaporanPerbaikansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('waktu_mulai', 'desc')
            ->striped()

            ->columns([

                TextColumn::make('index')
                    ->label('#')
                    ->rowIndex()
                    ->alignCenter(),

                TextColumn::make('kode_tiket')
                    ->label('Kode Tiket')
                    ->copyable()
                    ->weight('bold')
                    ->searchable(
                        query: fn (
                            Builder $query,
                            string $search
                        ) => $query->searchLaporan($search)
                    ),

                TextColumn::make('nama_pemohon')
                    ->label('Nama Pemohon')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tiket.keluhan')
                    ->label('Keluhan Kerusakan'),

                TextColumn::make('lokasi')
                    ->label('Lokasi')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                BadgeColumn::make('ownership_label')
                    ->label('Kepemilikan')
                    ->color(fn ($record) => $record->ownership_color)->label('Kepemilikan Barang')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nama_teknisi_label')
                    ->label('Nama Teknisi')
                    ->sortable(),

                BadgeColumn::make('status_label')
                    ->label('Status')
                    ->color(fn ($record) => $record->status_color),

                BadgeColumn::make('service_category')
                    ->label('Kategori')
                    ->color(fn ($record) => $record->service_category_color),

                TextColumn::make('tanggal_mulai')
                    ->label('Mulai')
                    ->placeholder('Tiket Masih Belum Dikerjakan')
                    ->description(
                        fn ($record) => $record->Jam_mulai
                    ),

                TextColumn::make('tanggal_selesai')
                    ->label('Selesai')
                    ->placeholder('Masih Dikerjakan')
                    ->description(
                        fn ($record) => $record->jam_selesai
                    ),

                TextColumn::make('durasi')
                    ->label('Durasi Pengerjaan')
                    ->timezone('Asia/Jakarta'),

            ])

            ->filters([
                //
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
                        DatePicker::make('from')
                            ->label('Tanggal Awal'),
                        DatePicker::make('until')
                            ->label('Tanggal Akhir'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $range = LaporanPeriodFormatter::range($data);

                        return $query->periode($range['from'], $range['until']);
                    }),

                Filter::make('status')
                    ->form([
                        Select::make('status')
                            ->options([
                                'Open' => 'Open',
                                'In Progress' => 'Sedang Dikerjakan',
                                'Close' => 'Selesai',
                            ])
                            ->label('Status'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->status(
                            $data['status'] ?? null
                        );
                    }),

                Filter::make('teknisi')
                    ->form([
                        Select::make('teknisi')
                            ->label('Teknisi')
                            ->searchable()
                            ->options(
                                LaporanPerbaikan::query()
                                    ->whereNotNull('nama_teknisi')
                                    ->orderBy('nama_teknisi')
                                    ->pluck(
                                        'nama_teknisi',
                                        'nama_teknisi'
                                    )
                                    ->toArray()
                            ),

                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->teknisi(
                            $data['teknisi'] ?? null
                        );
                    }),

                Filter::make('lokasi')
                    ->form([
                        Select::make('lokasi')
                            ->searchable()
                            ->options(
                                LaporanPerbaikan::query()
                                    ->orderBy('lokasi')
                                    ->pluck(
                                        'lokasi',
                                        'lokasi'
                                    )
                                    ->toArray()
                            ),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->lokasi(
                            $data['lokasi'] ?? null
                        );
                    }),

                Filter::make('kepemilikan')
                    ->form([
                        Select::make('kepemilikan')
                            ->options([
                                'Inventaris Kantor' => 'Inventaris Kantor',
                                'Pribadi' => 'Pribadi',
                                'Lainnya' => 'Lainnya',
                            ]),
                    ])

                    ->query(function (Builder $query, array $data): Builder {
                        return $query->kepemilikan(
                            $data['kepemilikan'] ?? null
                        );
                    }),

                Filter::make('kategori')
                    ->form([
                        Select::make('kategori')
                            ->options([
                                'Cepat' => 'Cepat',

                                'Normal' => 'Normal',

                                'Lama' => 'Lama',

                                'Belum Selesai' => 'Belum Selesai',
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->kategoriDurasi(
                            $data['kategori'] ?? null
                        );
                    }),
            ]);
    }
}
