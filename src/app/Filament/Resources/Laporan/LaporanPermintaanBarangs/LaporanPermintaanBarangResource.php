<?php

namespace App\Filament\Resources\Laporan\LaporanPermintaanBarangs;

use App\Filament\Resources\Laporan\LaporanPermintaanBarangs\Pages\ListLaporanPermintaanBarangs;
use App\Filament\Resources\Laporan\LaporanPermintaanBarangs\Schemas\LaporanPermintaanBarangInfolist;
use App\Filament\Resources\Laporan\LaporanPermintaanBarangs\Tables\LaporanPermintaanBarangsTable;
use App\Models\Modules\Laporan\Models\LaporanPermintaanBarang;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class LaporanPermintaanBarangResource extends Resource
{
    protected static ?string $model = LaporanPermintaanBarang::class;

    protected static ?string $slug = 'Laporan-Permintaan-Barang';

    // navigation
    protected static UnitEnum|string|null $navigationGroup = 'Laporan'; // Navigation Group

    protected static ?string $navigationLabel = 'Laporan Permintaan Barang'; // Navigation Label

    protected static ?string $pluralLabel = 'Laporan Permintaan Barang';

    protected static ?string $recordTitleAttribute = 'LaporanPermintaanBarang';

    public static function infolist(Schema $schema): Schema
    {
        return LaporanPermintaanBarangInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LaporanPermintaanBarangsTable::configure($table);
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
            'index' => ListLaporanPermintaanBarangs::route('/'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasAnyRole([
            'admin',
            'super_admin',
        ]);
    }
}
