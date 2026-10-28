<?php

namespace App\Filament\Widgets\Admin;

use App\Models\Modules\Pengajuan\Models\PengajuanBarang;
use App\Models\Modules\Perbaikan\Models\TiketPerbaikan;
use Filament\Widgets\ChartWidget;

class AdminStatusLayananChart extends ChartWidget
{
    protected static bool $isLazy = true;
    protected ?string $heading = 'Status Layanan';
    protected ?string $description = 'Perbandingan status layanan Perbaikan dan Pengajuan Barang.';
    protected ?string $pollingInterval = '60s';
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = '1';

    protected function getData(): array
    {
        $tiketOpen = TiketPerbaikan::query()
            ->where('status', 'Open')
            ->count();

        $tiketProgress = TiketPerbaikan::query()
            ->where('status', 'In Progress')
            ->count();

        $tiketClose = TiketPerbaikan::query()
            ->where('status', 'Close')
            ->count();

        $pengajuanOpen = PengajuanBarang::query()
            ->where('status', 'Open')
            ->count();

        $pengajuanProgress = PengajuanBarang::query()
            ->where('status', 'In Progress')
            ->count();

        $pengajuanClose = PengajuanBarang::query()
            ->where('status', 'Close')
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Perbaikan',

                    'data' => [
                        $tiketOpen,
                        $tiketProgress,
                        $tiketClose,
                    ],

                    'backgroundColor' => '#3B82F6',
                    'borderColor' => '#2563EB',
                    'borderWidth' => 1,

                    'borderRadius' => 4,

                    'barPercentage' => 0.75,
                    'categoryPercentage' => 0.70,
                ],

                [
                    'label' => 'Pengajuan Barang',

                    'data' => [
                        $pengajuanOpen,
                        $pengajuanProgress,
                        $pengajuanClose,
                    ],

                    'backgroundColor' => '#8B5CF6',
                    'borderColor' => '#7C3AED',
                    'borderWidth' => 1,

                    'borderRadius' => 4,

                    'barPercentage' => 0.75,
                    'categoryPercentage' => 0.70,
                ],
            ],

            'labels' => [
                'Open',
                'In Progress',
                'Close',
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,

            'maintainAspectRatio' => false,

            'interaction' => [
                'mode' => 'index',
                'intersect' => false,
            ],

            'plugins' => [
                'legend' => [
                    'position' => 'top',

                    'labels' => [
                        'usePointStyle' => true,
                        'pointStyle' => 'rectRounded',
                        'padding' => 18,
                    ],
                ],

                'tooltip' => [
                    'enabled' => true,
                ],
            ],

            'scales' => [
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],

                'y' => [
                    'beginAtZero' => true,

                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole([
            'admin',
            'admin_super',
            'super_admin',
        ]) ?? false;
    }
}
