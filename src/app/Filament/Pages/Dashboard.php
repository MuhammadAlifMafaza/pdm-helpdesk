<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\Admin\AdminOverview;
use App\Filament\Widgets\Admin\AdminPengajuanStatusChart;
use App\Filament\Widgets\Admin\AdminPengajuanTerbaru;
use App\Filament\Widgets\Admin\AdminTiketStatusChart;
use App\Filament\Widgets\Admin\AdminTiketTerbaru;
use App\Filament\Widgets\Admin\AdminTiketTrendChart;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Dashboard';

    protected static bool $shouldRegisterNavigation = false;

    // protected string $view = 'filament.pages.dashboard';
    protected function getHeaderWidgets(): array
    {
        return [
            // AdminOverview::class,
            // AdminTiketTrendChart::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            // AdminTiketStatusChart::class,
            // AdminPengajuanStatusChart::class,
            // AdminTiketTerbaru::class,
            // AdminPengajuanTerbaru::class,
        ];
    }
}
