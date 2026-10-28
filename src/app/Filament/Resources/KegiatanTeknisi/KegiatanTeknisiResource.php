<?php

namespace App\Filament\Resources\KegiatanTeknisi;

use App\Filament\Resources\KegiatanTeknisi\Pages\CreateKegiatanTeknisi;
use App\Filament\Resources\KegiatanTeknisi\Pages\EditKegiatanTeknisi;
use App\Filament\Resources\KegiatanTeknisi\Pages\ListKegiatanTeknisi;
use App\Filament\Resources\KegiatanTeknisi\Pages\ViewKegiatanTeknisi;
use App\Filament\Resources\KegiatanTeknisi\Schemas\KegiatanTeknisiForm;
use App\Filament\Resources\KegiatanTeknisi\Schemas\KegiatanTeknisiInfolist;
use App\Filament\Resources\KegiatanTeknisi\Tables\KegiatanTeknisiTable;
use App\Models\Modules\Teknisi\Models\KegiatanTeknisi;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class KegiatanTeknisiResource extends Resource
{
    protected static ?string $model = KegiatanTeknisi::class;

    protected static ?string $slug = 'kegiatan-teknisi';

    protected static ?string $navigationLabel = 'Catatan Kegiatan';

    protected static ?string $pluralLabel = 'Catatan Kegiatan Teknisi';

    protected static UnitEnum|string|null $navigationGroup = 'Teknisi';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $recordTitleAttribute = 'deskripsi_kegiatan';

    public static function form(
        Schema $schema
    ): Schema {

        return KegiatanTeknisiForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return KegiatanTeknisiInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KegiatanTeknisiTable::configure($table);
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
            'index' => ListKegiatanTeknisi::route('/'),
            'create' => CreateKegiatanTeknisi::route('/create'),
            'view' => ViewKegiatanTeknisi::route('/{record}'),
            'edit' => EditKegiatanTeknisi::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasAnyRole([
            'super_admin',
            'admin',
            'teknisi',
        ]);
    }

    public static function canView($record): bool
    {
        return auth()->user()->hasAnyRole([
            'super_admin',
            'admin',
            'teknisi',
        ]);
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasAnyRole([
            'super_admin',
            'admin',
            'teknisi',
        ]);
    }

    public static function canEdit($record): bool
    {
        if ($record->trashed()) {
            return false;
        }

        $user = auth()->user();

        if (
            $user->hasRole('super_admin') ||
            $user->hasRole('admin')
        ) {
            return true;
        }

        return
            $user->hasRole('teknisi')
            &&
            $record->teknisi_id == $user->id;
    }

    public static function canDelete($record): bool
    {
        $user = auth()->user();

        if (
            $user->hasRole('super_admin') ||
            $user->hasRole('admin')
        ) {
            return true;
        }

        return
            $user->hasRole('teknisi')
            &&
            $record->teknisi_id == $user->id;
    }

    public static function canRestore($record): bool
    {
        return auth()->user()->hasAnyRole([
            'super_admin',
            'admin',
        ]);
    }

    public static function canForceDelete($record): bool
    {
        return false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasAnyRole([
            'super_admin',
            'admin',
            'teknisi',
        ]);
    }
}
