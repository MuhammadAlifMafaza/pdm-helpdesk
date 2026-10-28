<?php

namespace App\Filament\Widgets\Admin;

use App\Models\Modules\Perbaikan\Models\TiketPerbaikan;
use Filament\Widgets\ChartWidget;

class AdminTiketStatusChart extends ChartWidget
{
    protected ?string $heading = 'Status Perbaikan';
    protected ?string $description = 'Distribusi status tiket perbaikan saat ini.';
    protected ?string $pollingInterval = '60s';
    protected static bool $isLazy = true;
    protected static ?int $sort = 4;
    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Tiket Perbaikan',

                    'data' => [
                        TiketPerbaikan::query()
                            ->where('status', 'Open')
                            ->count(),

                        TiketPerbaikan::query()
                            ->where('status', 'In Progress')
                            ->count(),

                        TiketPerbaikan::query()
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
