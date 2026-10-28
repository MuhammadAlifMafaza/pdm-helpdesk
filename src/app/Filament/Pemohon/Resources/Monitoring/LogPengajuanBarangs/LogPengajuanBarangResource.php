<?php

namespace App\Filament\Pemohon\Resources\Monitoring\LogPengajuanBarangs;

use App\Filament\Pemohon\Resources\Monitoring\LogPengajuanBarangs\Pages\CreateLogPengajuanBarang;
use App\Filament\Pemohon\Resources\Monitoring\LogPengajuanBarangs\Pages\EditLogPengajuanBarang;
use App\Filament\Pemohon\Resources\Monitoring\LogPengajuanBarangs\Pages\ListLogPengajuanBarangs;
use App\Filament\Pemohon\Resources\Monitoring\LogPengajuanBarangs\Schemas\LogPengajuanBarangForm;
use App\Filament\Pemohon\Resources\Monitoring\LogPengajuanBarangs\Tables\LogPengajuanBarangsTable;
use App\Models\Modules\Pengajuan\Models\LogPengajuan;
use BackedEnum;
use UnitEnum;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LogPengajuanBarangResource extends Resource
{
    protected static ?string $model = LogPengajuan::class;
    protected static ?string $slug = 'log-pengajuan';
    protected static ?string $pluralLabel = 'Timeline Pengajuan';
    protected static UnitEnum|string|null $navigationGroup = 'Monitoring';
    protected static ?string $navigationLabel = 'Timeline Pengajuan';
    protected static ?string $recordTitleAttribute = 'LogPengajuan';

    public static function form(Schema $schema): Schema
    {
        return LogPengajuanBarangForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LogPengajuanBarangsTable::configure($table);
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
            'index' => ListLogPengajuanBarangs::route('/'),
            // 'create' => CreateLogPengajuanBarang::route('/create'),
            // 'edit' => EditLogPengajuanBarang::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['user', 'pengajuan' => fn($query) => $query->withTrashed(), 'pengajuan.user'])
            // Tampilkan seluruh riwayat untuk pengajuan milik pemohon,
            // termasuk aktivitas yang dilakukan petugas/admin.
            ->whereHas('pengajuan', fn(Builder $query) => $query
                ->withTrashed()
                ->where('user_id', auth()->id()));
    }
}
