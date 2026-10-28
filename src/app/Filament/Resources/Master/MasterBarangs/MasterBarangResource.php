<?php

namespace App\Filament\Resources\Master\MasterBarangs;

use App\Filament\Resources\Master\MasterBarangs\Pages\CreateMasterBarang;
use App\Filament\Resources\Master\MasterBarangs\Pages\EditMasterBarang;
use App\Filament\Resources\Master\MasterBarangs\Pages\ListMasterBarangs;
use App\Filament\Resources\Master\MasterBarangs\Pages\ViewMasterBarang;
use App\Filament\Resources\Master\MasterBarangs\Schemas\MasterBarangForm;
use App\Filament\Resources\Master\MasterBarangs\Schemas\MasterBarangInfolist;
use App\Filament\Resources\Master\MasterBarangs\Tables\MasterBarangsTable;
use App\Models\Modules\Master\Barang\Models\MasterBarang;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class MasterBarangResource extends Resource
{
    protected static ?string $model = MasterBarang::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static UnitEnum|string|null $navigationGroup = 'Master Data';

    protected static ?string $slug = 'master-barang';

    protected static ?string $navigationLabel = 'Master Barang';

    protected static ?string $modelLabel = 'Master Barang';

    protected static ?string $pluralModelLabel = 'Master Barang';

    protected static ?string $recordTitleAttribute = 'nama_barang';

    public static function form(Schema $schema): Schema
    {
        return MasterBarangForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MasterBarangInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MasterBarangsTable::configure($table);
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
            'index' => ListMasterBarangs::route('/'),
            'create' => CreateMasterBarang::route('/create'),
            'view' => ViewMasterBarang::route('/{record}'),
            'edit' => EditMasterBarang::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
