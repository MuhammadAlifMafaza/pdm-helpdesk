<?php

namespace App\Filament\Resources\Laporan\LaporanPerbaikans;

use App\Filament\Resources\Laporan\LaporanPerbaikans\Pages\ListLaporanPerbaikans;
use App\Filament\Resources\Laporan\LaporanPerbaikans\Schemas\LaporanPerbaikanForm;
use App\Filament\Resources\Laporan\LaporanPerbaikans\Schemas\LaporanPerbaikanInfolist;
use App\Filament\Resources\Laporan\LaporanPerbaikans\Tables\LaporanPerbaikansTable;
use App\Models\Modules\Laporan\Models\LaporanPerbaikan;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class LaporanPerbaikanResource extends Resource
{
    protected static ?string $model = LaporanPerbaikan::class;

    protected static ?string $slug = 'Laporan-Perbaikan';

    // navigation
    protected static UnitEnum|string|null $navigationGroup = 'Laporan'; // Navigation Group

    protected static ?string $navigationLabel = 'Laporan Perbaikan'; // Navigation Label

    protected static ?string $pluralLabel = 'Laporan Perbaikan';

    protected static ?string $recordTitleAttribute = 'LaporanPerbaikan';

    public static function form(Schema $schema): Schema
    {
        return LaporanPerbaikanForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LaporanPerbaikanInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LaporanPerbaikansTable::configure($table);
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
            'index' => ListLaporanPerbaikans::route('/'),
        ];
    }
}
