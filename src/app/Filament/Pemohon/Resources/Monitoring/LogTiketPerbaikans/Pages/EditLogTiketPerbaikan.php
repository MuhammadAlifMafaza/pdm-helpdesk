<?php

namespace App\Filament\Pemohon\Resources\Monitoring\LogTiketPerbaikans\Pages;

use App\Filament\Pemohon\Resources\Monitoring\LogTiketPerbaikans\LogTiketPerbaikanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLogTiketPerbaikan extends EditRecord
{
    protected static string $resource = LogTiketPerbaikanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
