<?php

namespace App\Filament\Resources\Laporan\LaporanKegiatans;

use App\Filament\Resources\Laporan\LaporanKegiatans\Pages\ListLaporanKegiatans;
use App\Filament\Resources\Laporan\LaporanKegiatans\Schemas\LaporanKegiatanForm;
use App\Filament\Resources\Laporan\LaporanKegiatans\Schemas\LaporanKegiatanInfolist;
use App\Filament\Resources\Laporan\LaporanKegiatans\Tables\LaporanKegiatansTable;
use App\Models\Modules\Laporan\Models\LaporanKegiatan;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class LaporanKegiatanResource extends Resource
{
    protected static ?string $model = LaporanKegiatan::class;

    protected static ?string $slug = 'laporan-kegiatan';

    // Navigation
    protected static UnitEnum|string|null $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Laporan Kegiatan Teknisi';

    protected static ?string $pluralLabel = 'Laporan Kegiatan Teknisi';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'LaporanKegiatan';

    public static function form(Schema $schema): Schema
    {
        return LaporanKegiatanForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LaporanKegiatanInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LaporanKegiatansTable::configure($table);
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
            'index' => ListLaporanKegiatans::route('/'),
        ];
    }
}
