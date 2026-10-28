<?php

namespace App\Filament\Widgets\Admin;

use App\Models\Modules\Pengajuan\Models\PengajuanBarang;
use App\Models\Modules\Perbaikan\Models\TiketPerbaikan;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminOverview extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '30s';
    protected int|string|array $columnSpan = 'full';
    protected function getColumns(): int
    {
        return 5;
    }

    /**
     * Widget hanya dapat dilihat oleh Admin.
     */
    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole([
            'admin',
            'admin_super',
            'super_admin',
        ]) ?? false;
    }

    protected function getStats(): array
    {
        /*
        |--------------------------------------------------------------------------
        | Statistik Perbaikan
        |--------------------------------------------------------------------------
        */

        $totalTiket = TiketPerbaikan::getTotalTiket();

        $tiketOpen = TiketPerbaikan::getTotalOpen();

        $tiketProgress = TiketPerbaikan::getTotalInProgress();

        $tiketClose = TiketPerbaikan::getTotalClose();

        $averageTiket = TiketPerbaikan::getAverageDurationHuman();


        /*
        |--------------------------------------------------------------------------
        | Statistik Pengajuan Barang
        |--------------------------------------------------------------------------
        */

        $totalPengajuan = PengajuanBarang::getTotalPengajuan();

        $pengajuanOpen = PengajuanBarang::getTotalOpen();

        $pengajuanProgress = PengajuanBarang::getTotalInProgress();

        $pengajuanClose = PengajuanBarang::getTotalClose();

        $averagePengajuan = PengajuanBarang::getAverageDurationHuman();


        /*
        |--------------------------------------------------------------------------
        | Total Seluruh Layanan
        |--------------------------------------------------------------------------
        */

        $totalLayanan = $totalTiket + $totalPengajuan;


        return [

            /*
            |--------------------------------------------------------------------------
            | Total Layanan
            |--------------------------------------------------------------------------
            */

            Stat::make(
                'Total Permintaan Layanan',
                number_format($totalLayanan)
            )
                ->description('Total seluruh layanan Helpdesk')
                ->descriptionIcon('heroicon-o-chart-bar')
                ->icon('heroicon-o-squares-2x2')
                ->color('primary'),


            /*
            |--------------------------------------------------------------------------
            | Layanan Perbaikan
            |--------------------------------------------------------------------------
            */

            Stat::make(
                'Tiket Perbaikan',
                number_format($totalTiket)
            )
                ->description(
                    "{$tiketOpen} Open • {$tiketProgress} Proses • {$tiketClose} Selesai"
                )
                ->descriptionIcon('heroicon-o-wrench-screwdriver')
                ->icon('heroicon-o-wrench-screwdriver')
                ->color('warning'),


            /*
            |--------------------------------------------------------------------------
            | Layanan Pengajuan Barang
            |--------------------------------------------------------------------------
            */

            Stat::make(
                'Pengajuan Barang',
                number_format($totalPengajuan)
            )
                ->description(
                    "{$pengajuanOpen} Open • {$pengajuanProgress} Proses • {$pengajuanClose} Selesai"
                )
                ->descriptionIcon('heroicon-o-cube')
                ->icon('heroicon-o-cube')
                ->color('success'),


            /*
            |--------------------------------------------------------------------------
            | Rata-rata Durasi Perbaikan
            |--------------------------------------------------------------------------
            */

            Stat::make(
                'Rata-rata Durasi Perbaikan ',
                $averageTiket
            )
                ->description('Rata-rata waktu penyelesaian tiket')
                ->descriptionIcon('heroicon-o-clock')
                ->icon('heroicon-o-clock')
                ->color('info'),


            /*
            |--------------------------------------------------------------------------
            | Rata-rata Durasi Pengajuan
            |--------------------------------------------------------------------------
            */

            Stat::make(
                'Rata-rata Durasi Pengajuan Barang',
                $averagePengajuan
            )
                ->description('Rata-rata waktu penyelesaian pengajuan')
                ->descriptionIcon('heroicon-o-clock')
                ->icon('heroicon-o-clock')
                ->color('info'),

        ];
    }
}
