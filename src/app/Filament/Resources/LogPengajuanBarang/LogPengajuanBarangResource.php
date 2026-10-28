<?php

namespace App\Filament\Resources\LogPengajuanBarang;

use App\Filament\Resources\LogPengajuanBarang\Pages\ListLogPengajuanBarang;
use App\Filament\Resources\LogPengajuanBarang\Schemas\LogPengajuanBarangForm;
use App\Filament\Resources\LogPengajuanBarang\Schemas\LogPengajuanBarangInfolist;
use App\Models\Modules\Pengajuan\Models\LogPengajuan;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class LogPengajuanBarangResource extends Resource
{
    protected static ?string $slug = 'log-pengajuan-barang';

    protected static ?string $model = LogPengajuan::class; // Model yang digunakan untuk resource ini

    protected static ?string $navigationLabel = 'Timeline Pengajuan Barang'; // Label yang muncul di navigasi

    protected static ?string $pluralLabel = 'Timeline Pengajuan Barang'; // Label jamak untuk resource ini

    protected static ?string $modelLabel = 'LogPengajuan'; // Label untuk model, digunakan dalam berbagai tempat di Filament

    // protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clock';
    protected static UnitEnum|string|null $navigationGroup = 'Monitoring';

    protected static ?string $recordTitleAttribute = 'LogPengajuan';

    public static function form(Schema $schema): Schema
    {
        return LogPengajuanBarangForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LogPengajuanBarangInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->defaultSort('created_at', 'desc')

            ->columns([

                TextColumn::make('index')
                    ->label('No')
                    ->rowIndex(),

                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y')
                    ->timezone('Asia/Jakarta')
                    ->description(
                        fn($record) => $record->created_at?->copy()->timezone('Asia/Jakarta')->format('H:i:s') ?? '-'
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
                    )
                    ->weight('bold'),

                TextColumn::make('pengajuan.nama_barang')
                    ->label('Nama Barang')
                    ->placeholder('-')
                    ->searchable(
                        query: fn(
                        Builder $query,
                        string $search
                    ) => $query->searchTimeline($search)
                    ),

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
                    ->label('Admin')
                    ->placeholder('-')
                    ->searchable(
                        query: fn(
                        Builder $query,
                        string $search
                    ) => $query->searchTimeline($search)
                    ),

                TextColumn::make('pengajuan.status')
                    ->label('Status')
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
                        query: fn(Builder $query, string $search) => $query->searchTimeline($search)
                    ),

                TextColumn::make('event_description')
                    ->label('Deskripsi')
                    ->wrap()
                    ->limit(70)
                    ->tooltip(fn($record) => $record->event_description)
                    ->searchable(
                        query: fn(Builder $query, string $search) => $query->searchTimeline($search)
                    ),

            ])

            ->filters([
                SelectFilter::make('pemohon')
                    ->label('Pemohon')
                    ->relationship(
                        'pengajuan.user',
                        'name'
                    )
                    ->searchable()
                    ->preload(),

                SelectFilter::make('user_id')
                    ->label('Admin')
                    ->relationship(
                        'user',
                        'name'
                    )
                    ->searchable()
                    ->preload(),

                SelectFilter::make('status')
                    ->label('Status Pengajuan')
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

                SelectFilter::make('aktivitas')
                    ->label('Aktivitas')
                    ->options([

                        LogPengajuan::EVENT_CREATE => 'Pengajuan Dibuat',

                        LogPengajuan::EVENT_PROCESS => 'Diproses',

                        LogPengajuan::EVENT_APPROVE => 'Disetujui',

                        LogPengajuan::EVENT_REJECT => 'Ditolak',

                        LogPengajuan::EVENT_REOPEN => 'Dibuka Kembali',


                        LogPengajuan::EVENT_CHAT => 'Pesan',

                        LogPengajuan::EVENT_UPDATE => 'Perubahan Data',

                        LogPengajuan::EVENT_DELETE => 'Hapus Data',

                    ])

                    ->query(function (Builder $query, array $data) {

                        if (blank($data['value'])) {
                            return $query;
                        }

                        return match ($data['value']) {

                            LogPengajuan::EVENT_CREATE => $query->created(),

                            LogPengajuan::EVENT_PROCESS => $query->process(),

                            LogPengajuan::EVENT_APPROVE => $query->approve(),

                            LogPengajuan::EVENT_REJECT => $query->reject(),

                            LogPengajuan::EVENT_REOPEN => $query->reopen(),

                            LogPengajuan::EVENT_CHAT => $query->chat(),

                            LogPengajuan::EVENT_UPDATE => $query->updateData(),

                            LogPengajuan::EVENT_DELETE => $query->deleteData(),

                            default => $query->whereRaw('1 = 0'),
                        };

                    }),

                Filter::make('tanggal')
                    ->label('Tanggal')
                    ->form([

                        DatePicker::make('from')
                            ->label('Mulai'),

                        DatePicker::make('until')
                            ->label('Sampai'),

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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLogPengajuanBarang::route('/'),
            // 'create' => CreateLogPengajuanBarang::route('/create'),
            // 'view' => ViewLogPengajuanBarang::route('/{record}'),
            // 'edit' => EditLogPengajuanBarang::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'user',
                'pengajuan',
                'pengajuan.user',
            ]);
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasAnyRole([
            'admin',
            'super_admin',
        ]);
    }
}
