<?php

namespace App\Filament\Resources\Master\MasterRuangan\Pages;

use App\Filament\Resources\Master\MasterRuangan\MasterRuanganResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMasterRuangan extends EditRecord
{
    protected static string $resource = MasterRuanganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
