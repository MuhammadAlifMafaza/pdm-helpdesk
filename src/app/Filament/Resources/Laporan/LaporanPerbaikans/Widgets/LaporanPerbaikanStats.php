<?php

namespace App\Filament\Resources\Laporan\LaporanPerbaikans\Widgets;

use App\Models\Modules\Laporan\Models\LaporanPerbaikan;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LaporanPerbaikanStats extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';
    protected function getColumns(): int
    {
        return 5;
    }
    protected function getStats(): array
    {
        return [

            Stat::make(
                'Total Tiket',
                LaporanPerbaikan::count()
            )
                ->icon('heroicon-o-document-text')
                ->color('primary'),

            Stat::make(
                'Open',
                LaporanPerbaikan::open()->count()
            )
                ->label('Belum Di Kerjakan')
                ->description('Tiket Yang Belum Di Kerjakan')
                ->icon('heroicon-o-clock')
                ->color('info'),

            Stat::make(
                'Diproses',
                LaporanPerbaikan::progress()->count()
            )
                ->description('Tiket Yang Sedang Di Kerjakan')
                ->icon('heroicon-o-cog-6-tooth')
                ->color('warning'),

            Stat::make(
                'Selesai',
                LaporanPerbaikan::closed()->count()
            )
                ->description('Tiket Yang Selesai Di Kerjakan')
                ->icon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make(
                'Rata-rata Durasi (Jam)',
                LaporanPerbaikan::getAverageDuration() . ' Jam'
            )
                ->description(
                    LaporanPerbaikan::getAverageDurationDescription()
                )
                ->icon('heroicon-o-clock')
                ->color('Green'),

        ];
    }
}
