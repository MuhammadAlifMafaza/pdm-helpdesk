<?php

namespace App\Filament\Pemohon\Resources\Monitoring\LogTiketPerbaikans\Pages;

use App\Filament\Pemohon\Resources\Monitoring\LogTiketPerbaikans\LogTiketPerbaikanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLogTiketPerbaikans extends ListRecords
{
    protected static string $resource = LogTiketPerbaikanResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
