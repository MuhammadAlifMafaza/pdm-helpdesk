<?php

namespace App\Filament\Widgets\Teknisi;

use App\Models\Modules\Perbaikan\Models\TiketPerbaikan;
use Filament\Widgets\ChartWidget;

class TeknisiStatusChart extends ChartWidget
{
    protected ?string $heading = 'Status Pekerjaan Saya';
    protected ?string $description = 'Distribusi tiket yang pernah ditangani oleh teknisi.';
    protected ?string $pollingInterval = '60s';
    protected static bool $isLazy = false;
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $userId = auth()->id();

        $query = TiketPerbaikan::query()
            ->whereHas('logs', function ($query) use ($userId) {
                $query
                    ->where('user_id', $userId)
                    ->where('kategori_log', 'Status')
                    ->where('data_lama', 'Open')
                    ->where('data_baru', 'In Progress');
            });

        return [
            'datasets' => [
                [
                    'label' => 'Tiket Saya',

                    'data' => [
                        (clone $query)
                            ->where('status', 'Open')
                            ->count(),

                        (clone $query)
                            ->where('status', 'In Progress')
                            ->count(),

                        (clone $query)
                            ->where('status', 'Close')
                            ->count(),
                    ],

                    'backgroundColor' => [
                        '#3B82F6',
                        '#F59E0B',
                        '#10B981',
                    ],

                    'borderWidth' => 2,

                    'hoverOffset' => 10,
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
                ],
            ],
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('teknisi') ?? false;
    }
}
