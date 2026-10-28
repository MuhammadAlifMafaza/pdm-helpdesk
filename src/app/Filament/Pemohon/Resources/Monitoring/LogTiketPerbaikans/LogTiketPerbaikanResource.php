<?php

namespace App\Filament\Pemohon\Resources\Monitoring\LogTiketPerbaikans;

// use App\Filament\Pemohon\Resources\Monitoring\LogTiketPerbaikans\Pages\CreateLogTiketPerbaikan;
// use App\Filament\Pemohon\Resources\Monitoring\LogTiketPerbaikans\Pages\EditLogTiketPerbaikan;
use App\Filament\Pemohon\Resources\Monitoring\LogTiketPerbaikans\Pages\ListLogTiketPerbaikans;
use App\Filament\Pemohon\Resources\Monitoring\LogTiketPerbaikans\Schemas\LogTiketPerbaikanForm;
use App\Filament\Pemohon\Resources\Monitoring\LogTiketPerbaikans\Tables\LogTiketPerbaikansTable;
use App\Models\Modules\Perbaikan\Models\LogPerbaikan;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LogTiketPerbaikanResource extends Resource
{
    protected static ?string $model = LogPerbaikan::class;
    protected static ?string $slug = 'log-perbaikan';
    protected static ?string $pluralLabel = 'Timeline Perbaikan';
    protected static UnitEnum|string|null $navigationGroup = 'Monitoring';
    protected static ?string $navigationLabel = 'Timeline Perbaikan';
    protected static ?string $recordTitleAttribute = 'LogPerbaikan';

    public static function form(Schema $schema): Schema
    {
        return LogTiketPerbaikanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LogTiketPerbaikansTable::configure($table);
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
            'index' => ListLogTiketPerbaikans::route('/'),
            // 'create' => CreateLogTiketPerbaikan::route('/create'),
            // 'edit' => EditLogTiketPerbaikan::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['user', 'tiket' => fn($query) => $query->withTrashed(), 'tiket.user', 'tiket.ruangan'])
            // Tampilkan seluruh riwayat tiket milik pemohon,
            // termasuk aktivitas yang dilakukan teknisi/admin.
            ->whereHas('tiket', fn(Builder $query) => $query
                ->withTrashed()
                ->where('user_id', auth()->id()));
    }
}
