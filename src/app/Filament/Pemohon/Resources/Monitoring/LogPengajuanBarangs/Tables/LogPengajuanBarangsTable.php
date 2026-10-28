<?php

namespace App\Filament\Pemohon\Resources\Monitoring\LogPengajuanBarangs\Tables;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LogPengajuanBarangsTable
{
    public static function configure(Table $table): Table
    {
        return $table

            ->defaultSort('created_at', 'desc')
            ->striped()
            ->columns([

                TextColumn::make('index')
                    ->label('No')
                    ->rowIndex(),

                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y')
                    ->timezone('Asia/Jakarta')
                    ->description(
                        fn($record) => $record->created_at?->timezone('Asia/Jakarta')->format('H:i:s') ?? '-'
                    )
                    ->sortable(),

                TextColumn::make('pengajuan.kode_pengajuan')
                    ->label('Kode Pengajuan')
                    ->copyable()
                    ->searchable(
                        query: fn(
                        Builder $query,
                        string $search
                    ) => $query->searchTimeline($search)
                    )->weight('bold'),

                TextColumn::make('pengajuan.nama_barang')
                    ->label('Nama Barang')
                    ->searchable(),

                TextColumn::make('pengajuan.jumlah')
                    ->label('Jumlah')
                    ->sortable(),

                TextColumn::make('pengajuan.user.name')
                    ->label('Pemohon')
                    ->placeholder('-')
                    ->searchable(
                        query: fn(
                        Builder $query,
                        string $search
                    ) => $query->searchTimeline($search)
                    ),

                TextColumn::make('user.name')
                    ->label('Dilakukan Oleh')
                    ->placeholder('-')
                    ->searchable(
                        query: fn(
                        Builder $query,
                        string $search
                    ) => $query->searchTimeline($search)
                    ),

                TextColumn::make('pengajuan.status')
                    ->label('Status Saat Ini')
                    ->tooltip('Status terbaru dari pengajuan, bukan status pada saat aktivitas log dibuat.')
                    ->badge()
                    ->colors([
                        'primary' => 'Open',
                        'warning' => 'In Progress',
                        'success' => 'Close',
                    ]),

                TextColumn::make('event_name')
                    ->label('Aktivitas')
                    ->badge()
                    ->icon(fn($record) => $record->event_icon)
                    ->color(fn($record) => $record->event_color)
                    ->searchable(
                        query: fn(
                        Builder $query,
                        string $search
                    ) => $query->searchTimeline($search)
                    ),

                TextColumn::make('event_description')
                    ->label('Deskripsi')
                    ->wrap()
                    ->limit(70)
                    ->tooltip(fn($record) => $record->event_description)
                    ->searchable(
                        query: fn(
                        Builder $query,
                        string $search
                    ) => $query->searchTimeline($search)
                    ),

            ])

            ->filters([

                SelectFilter::make('aktivitas')
                    ->options([
                        'CREATE' => 'Pengajuan Dibuat',
                        'ASSIGN' => 'Pengajuan Diambil',
                        'CHAT' => 'Pesan',
                        'UPDATE' => 'Perubahan Data',
                        'COMPLETE' => 'Selesai',
                        'REJECT' => 'Ditolak',
                        'REOPEN' => 'Reopen',
                        'DELETE' => 'Hapus Data',
                    ])
                    ->query(function (Builder $query, array $data) {

                        if (blank($data['value'])) {
                            return $query;
                        }

                        return match ($data['value']) {

                            'CREATE' => $query
                                ->where('kategori_log', 'Status')
                                ->whereNull('data_lama')
                                ->where('data_baru', 'Open'),

                            'ASSIGN' => $query
                                ->where('kategori_log', 'Status')
                                ->where('data_lama', 'Open')
                                ->where('data_baru', 'In Progress'),

                            'CHAT' => $query
                                ->where('kategori_log', 'Chat'),

                            'UPDATE' => $query
                                ->where('kategori_log', 'Update Data'),

                            'COMPLETE' => $query
                                ->where('kategori_log', 'Status')
                                ->where('data_lama', 'In Progress')
                                ->where('data_baru', 'Close')
                                ->where('keterangan', 'like', '[SELESAI]%'),

                            'REJECT' => $query
                                ->where('kategori_log', 'Status')
                                ->where('data_lama', 'In Progress')
                                ->where('data_baru', 'Close')
                                ->where('keterangan', 'like', '[DITOLAK]%'),

                            'REOPEN' => $query
                                ->where('kategori_log', 'Status')
                                ->where('data_lama', 'Close')
                                ->where('data_baru', 'In Progress')
                                ->where('keterangan', 'like', '[REOPEN]%'),

                            'DELETE' => $query
                                ->where('kategori_log', 'Delete Data'),

                            default => $query->whereRaw('1 = 0'),
                        };
                    }),

                SelectFilter::make('status')
                    ->options([
                        'Open' => 'Open',

                        'In Progress' => 'In Progress',

                        'Close' => 'Close',
                    ])
                    ->query(function (Builder $query, array $data) {

                        return $query->when(
                            filled($data['value']),
                            fn($q) => $q->whereHas(
                                'pengajuan',
                                fn($q2) => $q2->where(
                                    'status',
                                    $data['value']
                                )
                            )
                        );

                    }),

                SelectFilter::make('tanggal')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('from')
                                    ->label('Dari Tanggal')
                                    ->native(false),
                                DatePicker::make('until')
                                    ->label('Sampai Tanggal')
                                    ->native(false),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data) {

                        return $query
                            ->when(
                                $data['from'],
                                fn($q, $date) => $q->whereDate(
                                    'created_at',
                                    '>=',
                                    $date
                                )
                            )
                            ->when(
                                $data['until'],
                                fn($q, $date) => $q->whereDate(
                                    'created_at',
                                    '<=',
                                    $date
                                )
                            );

                    }),

            ]);
    }
}
