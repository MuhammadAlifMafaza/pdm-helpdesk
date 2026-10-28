<?php

namespace App\Filament\Widgets\Admin;

use App\Models\Modules\Pengajuan\Models\PengajuanBarang;
use App\Models\Modules\Perbaikan\Models\TiketPerbaikan;
use Filament\Widgets\ChartWidget;

class AdminLayananTrendChart extends ChartWidget
{
    protected static bool $isLazy = false;
    protected ?string $heading = 'Tren Layanan Helpdesk';
    protected ?string $description = 'Perbandingan jumlah layanan Perbaikan dan Pengajuan Barang berdasarkan periode.';
    protected ?string $pollingInterval = '60s';
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'span';

    /**
     * Filter periode chart.
     */
    public ?string $filter = 'daily';

    protected function getFilters(): array
    {
        return [
            'daily' => 'Harian',
            'monthly' => 'Bulanan',
            'yearly' => 'Tahunan',
        ];
    }

    protected function getData(): array
    {
        return match ($this->filter) {
            'monthly' => $this->getMonthlyData(),
            'yearly' => $this->getYearlyData(),
            default => $this->getDailyData(),
        };
    }

    /**
     * Statistik harian:
     * 30 hari terakhir.
     */
    protected function getDailyData(): array
    {
        $startDate = now()->subDays(29)->startOfDay();
        $endDate = now()->endOfDay();

        $perbaikan = TiketPerbaikan::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as period, COUNT(*) as total')
            ->groupByRaw('DATE(created_at)')
            ->pluck('total', 'period');

        $pengajuan = PengajuanBarang::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as period, COUNT(*) as total')
            ->groupByRaw('DATE(created_at)')
            ->pluck('total', 'period');

        $labels = [];
        $perbaikanData = [];
        $pengajuanData = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);

            $key = $date->toDateString();

            $labels[] = $date->translatedFormat('d M');

            $perbaikanData[] = (int) ($perbaikan[$key] ?? 0);
            $pengajuanData[] = (int) ($pengajuan[$key] ?? 0);
        }

        return $this->buildChartData(
            $labels,
            $perbaikanData,
            $pengajuanData
        );
    }

    /**
     * Statistik bulanan:
     * 12 bulan terakhir.
     */
    protected function getMonthlyData(): array
    {
        $startDate = now()->subMonths(11)->startOfMonth();
        $endDate = now()->endOfMonth();

        $perbaikan = TiketPerbaikan::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw(
                "DATE_FORMAT(created_at, '%Y-%m') as period, COUNT(*) as total"
            )
            ->groupByRaw("DATE_FORMAT(created_at, '%Y-%m')")
            ->pluck('total', 'period');

        $pengajuan = PengajuanBarang::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw(
                "DATE_FORMAT(created_at, '%Y-%m') as period, COUNT(*) as total"
            )
            ->groupByRaw("DATE_FORMAT(created_at, '%Y-%m')")
            ->pluck('total', 'period');

        $labels = [];
        $perbaikanData = [];
        $pengajuanData = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);

            $key = $date->format('Y-m');

            $labels[] = $date->translatedFormat('M Y');

            $perbaikanData[] = (int) ($perbaikan[$key] ?? 0);
            $pengajuanData[] = (int) ($pengajuan[$key] ?? 0);
        }

        return $this->buildChartData(
            $labels,
            $perbaikanData,
            $pengajuanData
        );
    }

    /**
     * Statistik tahunan:
     * 5 tahun terakhir.
     */
    protected function getYearlyData(): array
    {
        $startDate = now()->subYears(4)->startOfYear();
        $endDate = now()->endOfYear();

        $perbaikan = TiketPerbaikan::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw(
                'YEAR(created_at) as period, COUNT(*) as total'
            )
            ->groupByRaw('YEAR(created_at)')
            ->pluck('total', 'period');

        $pengajuan = PengajuanBarang::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw(
                'YEAR(created_at) as period, COUNT(*) as total'
            )
            ->groupByRaw('YEAR(created_at)')
            ->pluck('total', 'period');

        $labels = [];
        $perbaikanData = [];
        $pengajuanData = [];

        for ($i = 4; $i >= 0; $i--) {
            $date = now()->subYears($i);

            $key = $date->year;

            $labels[] = (string) $date->year;

            $perbaikanData[] = (int) ($perbaikan[$key] ?? 0);
            $pengajuanData[] = (int) ($pengajuan[$key] ?? 0);
        }

        return $this->buildChartData(
            $labels,
            $perbaikanData,
            $pengajuanData
        );
    }

    /**
     * Membentuk dataset Chart.js.
     */
    protected function buildChartData(
        array $labels,
        array $perbaikanData,
        array $pengajuanData
    ): array {
        return [
            'datasets' => [
                [
                    'label' => 'Perbaikan',
                    'data' => $perbaikanData,

                    'borderColor' => '#3B82F6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.15)',

                    'fill' => true,
                    'tension' => 0.3,

                    'pointRadius' => 3,
                    'pointHoverRadius' => 5,
                ],

                [
                    'label' => 'Pengajuan Barang',
                    'data' => $pengajuanData,

                    'borderColor' => '#10B981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.15)',

                    'fill' => true,
                    'tension' => 0.3,

                    'pointRadius' => 3,
                    'pointHoverRadius' => 5,
                ],
            ],

            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
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

            'scales' => [
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],

                    'ticks' => [
                        'maxTicksLimit' => 7,
                        'autoSkip' => true,
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
