<?php

namespace App\Filament\Pemohon\Widgets;

use App\Models\Modules\Pengajuan\Models\PengajuanBarang;
use App\Models\Modules\Perbaikan\Models\TiketPerbaikan;
use Filament\Widgets\ChartWidget;

class AktivitasLayananChart extends ChartWidget
{
    protected static bool $isLazy = false;
    protected ?string $heading = 'Aktivitas Layanan';
    protected ?string $description = 'Perbandingan jumlah layanan Perbaikan dan Pengajuan Barang berdasarkan periode.';
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'full';
    protected ?string $pollingInterval = '60s';
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

    protected function getType(): string
    {
        return 'line';
    }

    /*
    |--------------------------------------------------------------------------
    | Chart Options
    |--------------------------------------------------------------------------
    */
    protected function getOptions(): array
    {
        return [
            'responsive' => true,

            /*
            |--------------------------------------------------------------------------
            | Interaction
            |--------------------------------------------------------------------------
            */

            'interaction' => [
                'mode' => 'index',
                'intersect' => false,
            ],

            /*
            |--------------------------------------------------------------------------
            | Plugins
            |--------------------------------------------------------------------------
            */

            'plugins' => [

                'legend' => [
                    'display' => true,
                    'position' => 'top',

                    'labels' => [
                        'usePointStyle' => true,
                        'pointStyle' => 'circle',
                        'padding' => 20,
                    ],
                ],

                'tooltip' => [
                    'enabled' => true,
                    'mode' => 'index',
                    'intersect' => false,

                    'displayColors' => true,

                    'padding' => 12,

                    'titleMarginBottom' => 8,

                    'bodySpacing' => 6,
                ],
            ],

            /*
            |--------------------------------------------------------------------------
            | Scales
            |--------------------------------------------------------------------------
            */

            'scales' => [

                'x' => [
                    'grid' => [
                        'display' => false,
                    ],

                    'ticks' => [
                        'maxRotation' => 0,
                        'autoSkip' => true,
                    ],
                ],

                'y' => [
                    'beginAtZero' => true,

                    'ticks' => [
                        'precision' => 0,
                        'stepSize' => 1,
                    ],

                    'grid' => [
                        'drawTicks' => false,
                    ],
                ],
            ],

            /*
            |--------------------------------------------------------------------------
            | Elements
            |--------------------------------------------------------------------------
            */

            'elements' => [

                'line' => [
                    'tension' => 0.35,
                    'borderWidth' => 2,
                ],

                'point' => [
                    'radius' => 3,
                    'hoverRadius' => 6,
                    'hoverBorderWidth' => 2,
                ],
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Dataset Configuration
    |--------------------------------------------------------------------------
    */
    protected function getDatasets(
        array $tiketData,
        array $pengajuanData
    ): array {
        return [

            /*
            |--------------------------------------------------------------------------
            | Tiket Perbaikan
            |--------------------------------------------------------------------------
            */

            [
                'label' => 'Tiket Perbaikan',

                'data' => $tiketData,

                'borderColor' => '#3B82F6',

                'backgroundColor' => '#3B82F6',

                'borderWidth' => 2,

                'fill' => false,

                'tension' => 0.35,

                'pointRadius' => 3,

                'pointHoverRadius' => 6,

                'pointHoverBorderWidth' => 2,
            ],

            /*
            |--------------------------------------------------------------------------
            | Pengajuan Barang
            |--------------------------------------------------------------------------
            */

            [
                'label' => 'Pengajuan Barang',

                'data' => $pengajuanData,

                'borderColor' => '#F59E0B',

                'backgroundColor' => '#F59E0B',

                'borderWidth' => 2,

                'fill' => false,

                'tension' => 0.35,

                'pointRadius' => 3,

                'pointHoverRadius' => 6,

                'pointHoverBorderWidth' => 2,
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Hari
    |--------------------------------------------------------------------------
    */
    protected function getDailyData(): array
    {
        $start = now()
            ->startOfDay()
            ->subDays(6);

        $end = now()->endOfDay();

        /*
        |--------------------------------------------------------------------------
        | Labels
        |--------------------------------------------------------------------------
        */

        $labels = [];

        for (
            $date = $start->copy();
            $date <= $end;
            $date->addDay()
        ) {
            $labels[] = $date->translatedFormat('d M');
        }

        /*
        |--------------------------------------------------------------------------
        | Tiket Perbaikan
        |--------------------------------------------------------------------------
        */

        $tiket = TiketPerbaikan::query()
            ->where('user_id', auth()->id())
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw(
                'DATE(created_at) as tanggal,
                 COUNT(*) as total'
            )
            ->groupBy('tanggal')
            ->pluck('total', 'tanggal');

        /*
        |--------------------------------------------------------------------------
        | Pengajuan Barang
        |--------------------------------------------------------------------------
        */

        $pengajuan = PengajuanBarang::query()
            ->where('user_id', auth()->id())
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw(
                'DATE(created_at) as tanggal,
                 COUNT(*) as total'
            )
            ->groupBy('tanggal')
            ->pluck('total', 'tanggal');

        /*
        |--------------------------------------------------------------------------
        | Dataset
        |--------------------------------------------------------------------------
        */

        $tiketData = [];
        $pengajuanData = [];

        for (
            $date = $start->copy();
            $date <= $end;
            $date->addDay()
        ) {
            $key = $date->format('Y-m-d');

            $tiketData[] = (int) ($tiket[$key] ?? 0);

            $pengajuanData[] = (int) ($pengajuan[$key] ?? 0);
        }

        return [
            'datasets' => $this->getDatasets(
                $tiketData,
                $pengajuanData
            ),

            'labels' => $labels,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Bulan
    |--------------------------------------------------------------------------
    */
    protected function getMonthlyData(): array
    {
        $start = now()
            ->startOfMonth()
            ->subMonths(11);

        $end = now()->endOfMonth();

        /*
        |--------------------------------------------------------------------------
        | Period & Labels
        |--------------------------------------------------------------------------
        */

        $labels = [];
        $periods = [];

        for (
            $date = $start->copy();
            $date <= $end;
            $date->addMonth()
        ) {
            $periods[] = $date->copy();

            $labels[] = $date->translatedFormat('M Y');
        }

        /*
        |--------------------------------------------------------------------------
        | Tiket Perbaikan
        |--------------------------------------------------------------------------
        */

        $tiket = TiketPerbaikan::query()
            ->where('user_id', auth()->id())
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw(
                'YEAR(created_at) as tahun,
                 MONTH(created_at) as bulan,
                 COUNT(*) as total'
            )
            ->groupBy('tahun', 'bulan')
            ->get()
            ->keyBy(
                fn($item) => sprintf(
                    '%04d-%02d',
                    $item->tahun,
                    $item->bulan
                )
            );

        /*
        |--------------------------------------------------------------------------
        | Pengajuan Barang
        |--------------------------------------------------------------------------
        */

        $pengajuan = PengajuanBarang::query()
            ->where('user_id', auth()->id())
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw(
                'YEAR(created_at) as tahun,
                 MONTH(created_at) as bulan,
                 COUNT(*) as total'
            )
            ->groupBy('tahun', 'bulan')
            ->get()
            ->keyBy(
                fn($item) => sprintf(
                    '%04d-%02d',
                    $item->tahun,
                    $item->bulan
                )
            );

        /*
        |--------------------------------------------------------------------------
        | Dataset
        |--------------------------------------------------------------------------
        */

        $tiketData = [];
        $pengajuanData = [];

        foreach ($periods as $date) {
            $key = $date->format('Y-m');

            $tiketData[] = (int) (
                $tiket[$key]->total ?? 0
            );

            $pengajuanData[] = (int) (
                $pengajuan[$key]->total ?? 0
            );
        }

        return [
            'datasets' => $this->getDatasets(
                $tiketData,
                $pengajuanData
            ),

            'labels' => $labels,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Tahun
    |--------------------------------------------------------------------------
    */
    protected function getYearlyData(): array
    {
        $start = now()
            ->startOfYear()
            ->subYears(4);

        $end = now()->endOfYear();

        /*
        |--------------------------------------------------------------------------
        | Years & Labels
        |--------------------------------------------------------------------------
        */

        $years = [];
        $labels = [];

        for (
            $year = $start->copy();
            $year <= $end;
            $year->addYear()
        ) {
            $years[] = $year->year;

            $labels[] = (string) $year->year;
        }

        /*
        |--------------------------------------------------------------------------
        | Tiket Perbaikan
        |--------------------------------------------------------------------------
        */

        $tiket = TiketPerbaikan::query()
            ->where('user_id', auth()->id())
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw(
                'YEAR(created_at) as tahun,
                 COUNT(*) as total'
            )
            ->groupBy('tahun')
            ->pluck('total', 'tahun');

        /*
        |--------------------------------------------------------------------------
        | Pengajuan Barang
        |--------------------------------------------------------------------------
        */

        $pengajuan = PengajuanBarang::query()
            ->where('user_id', auth()->id())
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw(
                'YEAR(created_at) as tahun,
                 COUNT(*) as total'
            )
            ->groupBy('tahun')
            ->pluck('total', 'tahun');

        /*
        |--------------------------------------------------------------------------
        | Dataset
        |--------------------------------------------------------------------------
        */

        $tiketData = [];
        $pengajuanData = [];

        foreach ($years as $year) {
            $tiketData[] = (int) (
                $tiket[$year] ?? 0
            );

            $pengajuanData[] = (int) (
                $pengajuan[$year] ?? 0
            );
        }

        return [
            'datasets' => $this->getDatasets(
                $tiketData,
                $pengajuanData
            ),

            'labels' => $labels,
        ];
    }
}
