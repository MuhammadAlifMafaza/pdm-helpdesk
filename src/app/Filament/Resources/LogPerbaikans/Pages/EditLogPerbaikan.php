<?php

namespace App\Filament\Resources\LogPerbaikans\Pages;

use App\Filament\Resources\LogPerbaikans\LogPerbaikanResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditLogPerbaikan extends EditRecord
{
    protected static string $resource = LogPerbaikanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
