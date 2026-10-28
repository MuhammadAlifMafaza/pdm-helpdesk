<?php

namespace App\Filament\Resources\TicketServices;

use App\Filament\Resources\TicketServices\Pages\CreateTicketService;
use App\Filament\Resources\TicketServices\Pages\EditTicketService;
use App\Filament\Resources\TicketServices\Pages\ListTicketServices;
use App\Filament\Resources\TicketServices\Pages\ViewTicketService;
use App\Models\Modules\Perbaikan\models\TiketPerbaikan as TicketService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
/* FILAMENT IMPORT */

// Filament Actions imports
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
// Filament Forms imports
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
// Filament Resources imports
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
// Filament Table import
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class TicketServiceResource extends Resource
{
    protected static ?string $model = TicketService::class;

    // url slug
    protected static ?string $slug = 'ticket-services';

    // navigation
    protected static UnitEnum|string|null $navigationGroup = 'Service Desk'; // Navigation Group

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-wrench-screwdriver'; // Navigation Icon

    protected static ?string $navigationLabel = 'Tiket Perbaikan'; // Navigation Label

    protected static ?string $pluralLabel = 'Tiket Perbaikan';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'keluhan';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([

                Select::make('user_id')
                    ->relationship(
                        'user',
                        'name'
                    )
                    ->label('Nama Pemohon')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('ruangan_id')
                    ->relationship(
                        'ruangan',
                        'nama_ruangan'
                    )
                    ->label('Nama Ruangan')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('keluhan')
                    ->label('Keluhan')
                    ->required()
                    ->maxLength(255),

                Select::make('kepemilikan')
                    ->options([
                        'Inventaris Kantor' => 'Inventaris Kantor',
                        'Pribadi' => 'Pribadi',
                        'Lainnya' => 'Lainnya',
                    ])
                    ->label('Kepemilikan')
                    ->required(),

                Textarea::make('deskripsi')
                    ->label('Deskripsi')
                    ->rows(5)
                    ->required(),

                Select::make('status')
                    ->label('Status Tiket')
                    ->default('Open')
                    ->options([
                        'Open' => 'Open',
                        'In Progress' => 'In Progress',
                        'Close' => 'Closed',
                    ])
                    ->required()
                    ->disabled(),

            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->schema([

                Section::make('Informasi Tiket')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([

                        TextEntry::make('kode_tiket')
                            ->label('Nomor Tiket'),

                        TextEntry::make('user.name')
                            ->label('Nama Pemohon'),

                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(
                                fn (?string $state): string => match ($state) {
                                    'Open' => 'info',
                                    'In Progress' => 'warning',
                                    'Close' => 'success',
                                    default => 'gray',
                                }
                            ),
                        TextEntry::make('keluhan'),

                        TextEntry::make('status_outcome')
                            ->label('Hasil Tiket')
                            ->badge()
                            ->placeholder('-')
                            ->color(
                                fn (?string $state): string => match ($state) {
                                    'Completed' => 'success',
                                    'Rejected' => 'danger',
                                    default => 'gray',
                                }
                            ),

                        TextEntry::make('ruangan.nama_ruangan')
                            ->label('Ruangan'),

                        TextEntry::make('created_at')
                            ->label('Dibuat')
                            ->dateTime('d M Y H:i')
                            ->timezone('Asia/Jakarta'),

                        TextEntry::make('kepemilikan'),

                        TextEntry::make('updated_at')
                            ->label('Terakhir Diperbarui')
                            ->dateTime('d M Y H:i')
                            ->timezone('Asia/Jakarta'),

                    ]),

                Section::make('Deskripsi Kerusakan')
                    ->columnSpanFull()
                    ->schema([

                        TextEntry::make('deskripsi')
                            ->hiddenLabel()
                            ->columnSpanFull(),

                    ]),

                Section::make('Timeline Aktivitas')
                    ->columnSpan(1)
                    ->schema([

                        ViewEntry::make('id')
                            ->hiddenLabel()
                            ->view('filament.pages.tiket.timeline'),

                    ]),

                Section::make('Diskusi')
                    ->columnSpan(1)
                    ->schema([

                        ViewEntry::make('id')
                            ->view(
                                'filament.pages.tiket.chat'
                            ),

                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('Created_at', 'desc')
            ->columns([
                TextColumn::make('index')
                    ->label('No')
                    ->rowIndex(),

                TextColumn::make('kode_tiket')
                    ->label('Kode Tiket')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('user.name')
                    ->label('Pemohon'),

                TextColumn::make('ruangan.nama_ruangan')
                    ->label('Ruangan'),

                TextColumn::make('keluhan')
                    ->label('Keluhan')
                    ->wrap()
                    ->searchable(),

                TextColumn::make('status')
                    ->badge()
                    ->icon(fn (string $state) => match ($state) {

                        'Open' => 'heroicon-o-exclamation-circle',

                        'In Progress' => 'heroicon-o-wrench-screwdriver',

                        'Close' => 'heroicon-o-check-badge',

                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->color(fn (string $state) => match ($state) {
                        'Open' => 'info',
                        'In Progress' => 'warning',
                        'Close' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('status_outcome')
                    ->badge()
                    ->icon(fn (?string $state) => match ($state) {
                        'Completed' => 'heroicon-o-check-circle',
                        'Rejected' => 'heroicon-o-x-circle',
                        'Reopen' => 'heroicon-o-arrow-path',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'Completed' => 'success',
                        'Rejected' => 'danger',
                        'Reopen' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->dateTime('d M Y')
                    ->timezone('Asia/Jakarta')
                    ->description(
                        fn ($record) => $record->created_at->format('H:i:s')
                    ),

                TextColumn::make('waktu_mulai')
                    ->dateTime('d M Y')
                    ->timezone('Asia/Jakarta')
                    ->description(
                        fn ($record) => $record->created_at->format('H:i:s')
                    ),

                TextColumn::make('waktu_selesai')
                    ->dateTime('d M Y')
                    ->timezone('Asia/Jakarta')
                    ->description(
                        fn ($record) => $record->waktu_selesai->format('H:i:s')
                    ),

                TextColumn::make('durasi_pengerjaan')
                    ->timezone(''),

            ])

            ->filters([
                TrashedFilter::make(),

            ])

            ->actions([

                Action::make('ambil_tiket')
                    ->label('')
                    ->tooltip('Ambil Tiket')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->visible(
                        fn ($record) => $record->status === 'Open'
                    )
                    ->action(function ($record) {

                        $record->updateStatus(
                            'In Progress',
                            'Tiket mulai dikerjakan oleh '
                            .auth()->user()->name
                        );

                        $record->sendMessage(
                            'Teknisi '
                            .auth()->user()->name
                            .' mengambil tiket ini.'
                        );
                    }),

                Action::make('selesai')
                    ->label('')
                    ->tooltip('Selesai')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(
                        fn ($record) => $record->status === 'In Progress'
                        && ! $record->isMenungguKonfirmasi()
                    )
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('catatan')
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {

                        $record->mintaKonfirmasiSelesai(
                            $data['catatan']
                        );

                    })
                    ->modalHeading('Konfirmasi Penyelesaian')
                    ->modalDescription(
                        'Pemohon akan diminta mengonfirmasi penyelesaian pekerjaan ini.'
                    ),

                Action::make('tolak')
                    ->label('')
                    ->tooltip('Tolak')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->visible(
                        fn ($record) => $record->status === 'In Progress'
                    )
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('catatan')
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {

                        $record->closeAsRejected(
                            $data['catatan']
                        );

                    })
                    ->modalHeading('Konfirmasi Penolakan')
                    ->modalDescription(
                        'Tiket akan ditutup sebagai ditolak dan pemohon menerima notifikasi penolakan.'
                    ),

                Action::make('reopen')
                    ->label('')
                    ->tooltip('Reopen Ticket')
                    ->color('primary')
                    ->icon('heroicon-o-arrow-path')
                    ->visible(
                        fn ($record) => $record->isClosed()
                        &&
                        (
                            auth()->user()->hasRole('admin')
                            ||
                            auth()->user()->hasRole('super_admin')
                        )
                    )
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('catatan')
                            ->label('Alasan Reopen')
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {

                        $record->reopen(
                            $data['catatan']
                        );

                    }),

                ViewAction::make()
                    ->label('')
                    ->tooltip('View'),

                RestoreAction::make()
                    ->label('')
                    ->tooltip('Restore Ticket')
                    ->visible(
                        fn ($record) => $record->trashed()
                        &&
                        (
                            auth()->user()->hasRole('admin')
                            ||
                            auth()->user()->hasRole('super_admin')
                        )

                    ),

                EditAction::make()
                    ->label('')
                    ->tooltip('Edit')
                    ->visible(
                        fn ($record) => $record->canStaffEdit()
                    ),

                DeleteAction::make()
                    ->label('')
                    ->tooltip('Soft Delete')
                    ->visible(
                        fn ($record) => $record->isClosed()
                        &&
                        (
                            auth()->user()->hasRole('admin')
                            ||
                            auth()->user()->hasRole('super_admin')
                        )
                    )
                    ->requiresConfirmation(),

                ForceDeleteAction::make()
                    ->label('')
                    ->tooltip('Force Delete')
                    ->visible(
                        fn () => auth()->user()->hasRole('super_admin')
                    ),

            ])
            ->actionsColumnLabel('Action Button');

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
            'index' => ListTicketServices::route('/'),
            'create' => CreateTicketService::route('/create'),
            'view' => ViewTicketService::route('/{record}'),
            'edit' => EditTicketService::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasAnyRole([
            'admin',
            'teknisi',
            'super_admin',
        ]);
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasAnyRole([
            'admin',
            'teknisi',
            'super_admin',
        ]);
    }

    public static function canEdit($record): bool
    {
        return $record->canStaffEdit();
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->hasAnyRole([
            'super_admin',
            'admin',
        ]);
    }
}
