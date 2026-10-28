<?php

namespace App\Filament\Pemohon\Resources\Service\TiketPerbaikans;

use App\Filament\Pemohon\Resources\Service\TiketPerbaikans\Pages\CreateTiketPerbaikan;
use App\Filament\Pemohon\Resources\Service\TiketPerbaikans\Pages\EditTiketPerbaikan;
use App\Filament\Pemohon\Resources\Service\TiketPerbaikans\Pages\ListTiketPerbaikans;
use App\Filament\Pemohon\Resources\Service\TiketPerbaikans\Pages\ViewTiketPerbaikan;
use App\Filament\Pemohon\Resources\Service\TiketPerbaikans\Schemas\TiketPerbaikanForm;
use App\Filament\Pemohon\Resources\Service\TiketPerbaikans\Schemas\TiketPerbaikanInfolist;
use App\Filament\Pemohon\Resources\Service\TiketPerbaikans\Tables\TiketPerbaikansTable;
use App\Models\Modules\Perbaikan\models\TiketPerbaikan;
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
use Filament\Resources\Resource;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Section;

// Filament Resources imports
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class TiketPerbaikanResource extends Resource
{
    protected static ?string $model = TiketPerbaikan::class;
    // url slug
    protected static ?string $slug = 'ticket-perbaikan';

    // navigation
    protected static UnitEnum|string|null $navigationGroup = 'Pelayanan'; // Navigation Group
    // protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationLabel = 'Tiket Perbaikan'; // Navigation Label
    protected static ?string $pluralLabel = 'Tiket Perbaikan';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'Tiket Perbaikan';

    public static function form(Schema $schema): Schema
    {
        return TiketPerbaikanForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TiketPerbaikanInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TiketPerbaikansTable::configure($table);
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
            'index' => ListTiketPerbaikans::route('/'),
            'create' => CreateTiketPerbaikan::route('/create'),
            'view' => ViewTiketPerbaikan::route('/{record}'),
            'edit' => EditTiketPerbaikan::route('/{record}/edit'),
        ];
    }

    public static function canEdit($record): bool
    {
        return $record->canPemohonEdit();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', auth()->id());
    }
}
