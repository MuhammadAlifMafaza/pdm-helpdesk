<?php

namespace App\Filament\Pemohon\Widgets\Statistik;

use App\Models\Modules\Perbaikan\Models\TiketPerbaikan;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TiketPerbaikanStats extends StatsOverviewWidget
{
    protected ?string $heading = 'Tiket Perbaikan';

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $query = TiketPerbaikan::query()
            ->where('user_id', auth()->id());

        return [

            Stat::make(
                'Total',
                number_format(
                    TiketPerbaikan::getTotalTiket($query)
                )
            )
                ->description('Seluruh tiket perbaikan Anda')
                ->icon('heroicon-o-wrench-screwdriver')
                ->color('primary'),

            Stat::make(
                'Open',
                number_format(
                    TiketPerbaikan::getTotalOpen($query)
                )
            )
                ->description('Menunggu penanganan')
                ->icon('heroicon-o-clock')
                ->color('warning'),

            Stat::make(
                'In Progress',
                number_format(
                    TiketPerbaikan::getTotalInProgress($query)
                )
            )
                ->description('Sedang ditangani')
                ->icon('heroicon-o-arrow-path')
                ->color('info'),

            Stat::make(
                'Close',
                number_format(
                    TiketPerbaikan::getTotalClose($query)
                )
            )
                ->description('Tiket telah diselesaikan')
                ->icon('heroicon-o-check-circle')
                ->color('success'),
        ];
    }
}
