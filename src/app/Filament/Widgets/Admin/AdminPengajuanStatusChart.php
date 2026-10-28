<?php

namespace App\Filament\Widgets\Admin;

use App\Models\Modules\Pengajuan\Models\PengajuanBarang;
use Filament\Widgets\ChartWidget;

class AdminPengajuanStatusChart extends ChartWidget
{
    protected ?string $heading = 'Status Pengajuan Barang';
    protected ?string $description = 'Distribusi status pengajuan barang saat ini.';
    protected ?string $pollingInterval = '60s';
    protected static bool $isLazy = true;
    protected static ?int $sort = 5;
    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Pengajuan Barang',

                    'data' => [
                        PengajuanBarang::query()
                            ->where('status', 'Open')
                            ->count(),

                        PengajuanBarang::query()
                            ->where('status', 'In Progress')
                            ->count(),

                        PengajuanBarang::query()
                            ->where('status', 'Close')
                            ->count(),
                    ],

                    'backgroundColor' => [
                        '#3B82F6',
                        '#F59E0B',
                        '#10B981',
                    ],

                    'borderColor' => [
                        '#2563EB',
                        '#D97706',
                        '#059669',
                    ],

                    'borderWidth' => 2,

                    'hoverOffset' => 10,

                    'hoverBorderWidth' => 3,
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
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,

            'maintainAspectRatio' => false,

            'cutout' => '64%',

            'interaction' => [
                'mode' => 'nearest',
                'intersect' => true,
            ],

            'animation' => [
                'animateRotate' => true,
                'animateScale' => true,
            ],

            'plugins' => [
                'legend' => [
                    'position' => 'bottom',

                    'labels' => [
                        'usePointStyle' => true,
                        'pointStyle' => 'circle',
                        'padding' => 16,
                    ],
                ],

                'tooltip' => [
                    'enabled' => true,

                    'displayColors' => true,
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
