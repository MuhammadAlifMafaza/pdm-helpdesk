<?php

namespace App\Filament\Resources\Laporan\LaporanPerbaikans\Pages;

use App\Filament\Resources\Laporan\LaporanPerbaikans\LaporanPerbaikanResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditLaporanPerbaikan extends EditRecord
{
    protected static string $resource = LaporanPerbaikanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
