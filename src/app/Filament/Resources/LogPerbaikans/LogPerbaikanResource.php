<?php

namespace App\Filament\Resources\LogPerbaikans;

use App\Filament\Resources\LogPerbaikans\Pages\ListLogPerbaikans;
use App\Filament\Resources\LogPerbaikans\Schemas\LogPerbaikanInfolist;
use App\Models\Modules\Perbaikan\Models\LogPerbaikan;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class LogPerbaikanResource extends Resource
{
    protected static ?string $model = LogPerbaikan::class; // Model yang digunakan untuk resource ini

    protected static ?string $slug = 'log-perbaikan'; // slug atau URL path untuk resource ini

    protected static ?string $pluralLabel = 'Timeline Perbaikan';

    protected static UnitEnum|string|null $navigationGroup = 'Monitoring';

    protected static ?string $navigationLabel = 'Timeline Perbaikan';

    protected static ?string $recordTitleAttribute = 'LogPerbaikan';

    public static function table(Table $table): Table
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
                        fn($record) => $record->created_at->format('H:i:s')
                    )
                    ->sortable(),

                TextColumn::make('tiket.kode_tiket')
                    ->label('Kode Tiket')
                    ->copyable()
                    ->searchable(
                        query: fn(
                        Builder $query,
                        string $search
                    ) => $query->searchTimeline($search)
                    )->weight('bold'),

                TextColumn::make('tiket.keluhan')
                    ->label('Keluhan Kerusakan'),

                TextColumn::make('tiket.ruangan.nama_ruangan')
                    ->label('Ruangan')
                    ->placeholder('-')
                    ->searchable(
                        query: fn(
                        Builder $query,
                        string $search
                    ) => $query->searchTimeline($search)
                    ),

                TextColumn::make('tiket.user.name')
                    ->label('Pemohon')
                    ->placeholder('-')
                    ->searchable(
                        query: fn(
                        Builder $query,
                        string $search
                    ) => $query->searchTimeline($search)
                    ),

                TextColumn::make('user.name')
                    ->label('Teknisi')
                    ->placeholder('-')
                    ->searchable(
                        query: fn(
                        Builder $query,
                        string $search
                    ) => $query->searchTimeline($search)
                    ),

                TextColumn::make('tiket.status')
                    ->label('Status Tiket')
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
                    ->searchable(
                        query: fn(
                        Builder $query,
                        string $search
                    ) => $query->searchTimeline($search)
                    ),

            ])

            ->filters([

                SelectFilter::make('ruangan')
                    ->label('Ruangan')
                    ->relationship(
                        'tiket.ruangan',
                        'nama_ruangan'
                    )
                    ->searchable()
                    ->preload(),

                SelectFilter::make('aktivitas')
                    ->options([
                        'CREATE' => 'Tiket Dibuat',
                        'ASSIGN' => 'Tiket Diambil',
                        'PENDING' => 'Pending',
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

                            'CHAT' => $query
                                ->where('kategori_log', 'Chat'),

                            'UPDATE' => $query
                                ->where('kategori_log', 'Update Data'),

                            'DELETE' => $query
                                ->where('kategori_log', 'Delete Data'),

                            default => $query,
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
                                'tiket',
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

    public static function infolist(Schema $schema): Schema
    {
        return LogPerbaikanInfolist::configure($schema);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLogPerbaikans::route('/'),
        ];
    }
}
