<?php

namespace App\Filament\Pemohon\Pages;

use App\Filament\Pemohon\Widgets\AktivitasLayananChart;
use App\Filament\Pemohon\Widgets\PengajuanBarangTerbaru;
use App\Filament\Pemohon\Widgets\TiketPerbaikanTerbaru;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Dashboard';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $slug = 'dashboard';

    protected function getFooterWidgets(): array
    {
        return [
            // AktivitasLayananChart::class,
            // TiketPerbaikanTerbaru::class,
            // PengajuanBarangTerbaru::class,
        ];
    }
}
