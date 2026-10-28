<?php

namespace App\Filament\Resources\Master\MasterKategoriBarang;

use App\Filament\Resources\Master\MasterKategoriBarang\Pages\CreateMasterKategoriBarang;
use App\Filament\Resources\Master\MasterKategoriBarang\Pages\EditMasterKategoriBarang;
use App\Filament\Resources\Master\MasterKategoriBarang\Pages\ListMasterKategoriBarangs;
use App\Filament\Resources\Master\MasterKategoriBarang\Pages\ViewMasterKategoriBarang;
use App\Filament\Resources\Master\MasterKategoriBarang\Schemas\MasterKategoriBarangForm;
use App\Filament\Resources\Master\MasterKategoriBarang\Schemas\MasterKategoriBarangInfolist;
use App\Filament\Resources\Master\MasterKategoriBarang\Tables\MasterKategoriBarangsTable;
use App\Models\Modules\Master\Barang\Models\MasterKategoriBarang;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class MasterKategoriBarangResource extends Resource
{
    protected static ?string $model = MasterKategoriBarang::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static UnitEnum|string|null $navigationGroup = 'Master Data';

    protected static ?string $slug = 'master-kategori-barang';

    protected static ?string $navigationLabel = 'Master Kategori Barang';

    protected static ?string $modelLabel = 'Master Kategori Barang';

    protected static ?string $pluralModelLabel = 'Master Kategori Barang';

    protected static ?string $recordTitleAttribute = 'nama_kategori';

    public static function form(Schema $schema): Schema
    {
        return MasterKategoriBarangForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MasterKategoriBarangInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MasterKategoriBarangsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMasterKategoriBarangs::route('/'),
            'create' => CreateMasterKategoriBarang::route('/create'),
            'view' => ViewMasterKategoriBarang::route('/{record}'),
            'edit' => EditMasterKategoriBarang::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes();
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
