<?php

namespace App\Filament\Resources\Master\MasterRuangan;

use App\Filament\Resources\Master\MasterRuangan\Pages\CreateMasterRuangan;
use App\Filament\Resources\Master\MasterRuangan\Pages\EditMasterRuangan;
use App\Filament\Resources\Master\MasterRuangan\Pages\ListMasterRuangan;
use App\Filament\Resources\Master\MasterRuangan\Pages\ViewMasterRuangan;
use App\Filament\Resources\Master\MasterRuangan\Schemas\MasterRuanganInfolist;
use App\Filament\Resources\Master\MasterRuangan\Tables\MasterRuanganTable;
use App\Models\Modules\Master\Ruangan\Models\MasterRuangan;
// import untuk enum data and form
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class MasterRuanganResource extends Resource
{
    protected static ?string $slug = 'master-ruangan';

    protected static ?string $model = MasterRuangan::class;

    protected static ?string $navigationLabel = 'Master Ruangan';

    protected static ?string $recordTitleAttribute = 'Master Ruangan';

    protected static UnitEnum|string|null $navigationGroup = 'Master Data';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([

                TextInput::make('nama_ruangan')
                    ->required()
                    ->maxLength(255),

                TextInput::make('nama_gedung')
                    ->required()
                    ->maxLength(255),

                DateTimePicker::make('created_at')
                    ->disabled()
                    ->label('Tanggal Dibuat')
                    ->displayFormat('d M Y H:i:s')
                    ->default(now()),
            ]);

    }

    public static function infolist(Schema $schema): Schema
    {
        return MasterRuanganInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        // return MasterRuanganTable::configure($table);
        return $table
            ->defaultSort('nama_ruangan', 'asc')
            ->columns([
                TextColumn::make('index')
                    ->label('#')
                    ->rowIndex()
                    ->sortable(),

                TextColumn::make('nama_ruangan')
                    ->searchable(),

                TextColumn::make('nama_gedung')
                    ->searchable(),

                TextColumn::make('created_at')
                    ->dateTime('d M Y H:i'),
            ])
            ->actions([
                ViewAction::make()
                    ->label('')
                    ->tooltip('View'),
                EditAction::make()
                    ->label('')
                    ->tooltip('Edit'),
                DeleteAction::make()
                    ->label('')
                    ->tooltip('Delete'),
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
            'index' => ListMasterRuangan::route('/'),
            'create' => CreateMasterRuangan::route('/create'),
            'view' => ViewMasterRuangan::route('/{record}'),
            'edit' => EditMasterRuangan::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->check() &&
            auth()->user()->hasAnyRole([
                'admin',
                'teknisi',
                'super_admin',
            ]);
    }
}
