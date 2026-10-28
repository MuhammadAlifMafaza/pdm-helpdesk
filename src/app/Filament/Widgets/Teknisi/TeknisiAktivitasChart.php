<?php

namespace App\Filament\Widgets\Teknisi;

use App\Models\Modules\Perbaikan\Models\LogPerbaikan;
use Filament\Widgets\ChartWidget;

class TeknisiAktivitasChart extends ChartWidget
{
    protected ?string $heading = 'Aktivitas Pekerjaan';
    protected ?string $description = 'Rekap aktivitas pelayanan yang dilakukan teknisi.';
    protected ?string $pollingInterval = '60s';
    protected static bool $isLazy = false;
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $userId = auth()->id();

        $logs = LogPerbaikan::query()
            ->where('user_id', $userId)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Aktivitas',

                    'data' => [
                        $logs->where('kategori_log', 'Status')
                            ->where('data_lama', 'Open')
                            ->where('data_baru', 'In Progress')
                            ->count(),

                        $logs->where('kategori_log', 'Update Data')
                            ->count(),

                        $logs->where('kategori_log', 'Chat')
                            ->count(),

                        $logs->where('kategori_log', 'Pending')
                            ->count(),

                        $logs->filter(function ($log) {
                            return $log->kategori_log === 'Status'
                                && $log->data_lama === 'In Progress'
                                && $log->data_baru === 'Close'
                                && str_contains(
                                    $log->keterangan ?? '',
                                    '[SELESAI]'
                                );
                        })->count(),

                        $logs->filter(function ($log) {
                            return $log->kategori_log === 'Status'
                                && $log->data_lama === 'In Progress'
                                && $log->data_baru === 'Close'
                                && str_contains(
                                    $log->keterangan ?? '',
                                    '[DITOLAK]'
                                );
                        })->count(),
                    ],

                    'backgroundColor' => [
                        '#3B82F6',
                        '#8B5CF6',
                        '#06B6D4',
                        '#F59E0B',
                        '#10B981',
                        '#EF4444',
                    ],

                    'borderRadius' => 5,
                ],
            ],

            'labels' => [
                'Ambil Tiket',
                'Update Data',
                'Chat',
                'Pending',
                'Selesai',
                'Ditolak',
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

            'plugins' => [
                'legend' => [
                    'display' => false,
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
        return auth()->user()?->hasRole('teknisi') ?? false;
    }
}
