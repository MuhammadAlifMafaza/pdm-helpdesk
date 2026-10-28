<?php

namespace App\Filament\Pemohon\Widgets\Statistik;

use App\Models\Modules\Pengajuan\Models\PengajuanBarang;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PengajuanBarangStats extends StatsOverviewWidget
{
    protected ?string $heading = 'Pengajuan Barang';
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $query = PengajuanBarang::query()
            ->where('user_id', auth()->id());

        return [

            Stat::make(
                'Total',
                number_format(
                    PengajuanBarang::getTotalPengajuan($query)
                )
            )
                ->description('Seluruh pengajuan barang Anda')
                ->icon('heroicon-o-clipboard-document-list')
                ->color('primary'),

            Stat::make(
                'Open',
                number_format(
                    PengajuanBarang::getTotalOpen($query)
                )
            )
                ->description('Menunggu diproses')
                ->icon('heroicon-o-clock')
                ->color('warning'),

            Stat::make(
                'In Progress',
                number_format(
                    PengajuanBarang::getTotalInProgress($query)
                )
            )
                ->description('Sedang diproses')
                ->icon('heroicon-o-arrow-path')
                ->color('info'),

            Stat::make(
                'Close',
                number_format(
                    PengajuanBarang::getTotalClose($query)
                )
            )
                ->description('Pengajuan telah diselesaikan')
                ->icon('heroicon-o-check-circle')
                ->color('success'),
        ];
    }
}
