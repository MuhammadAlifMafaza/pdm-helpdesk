<?php

namespace App\Filament\Widgets\Teknisi;

use App\Models\Modules\Perbaikan\Models\TiketPerbaikan;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TeknisiOverview extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '30s';

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $userId = auth()->id();

        $tiketSaya = TiketPerbaikan::query()
            ->handledByTechnician($userId);

        $tiketAktif = TiketPerbaikan::query()
            ->currentlyHandledByTechnician($userId)
            ->where('status', 'In Progress');

        $tiketSelesai = (clone $tiketSaya)
            ->where('status', 'Close');

        $totalSaya = (clone $tiketSaya)->count();

        $totalAktif = (clone $tiketAktif)->count();

        $totalSelesai = (clone $tiketSelesai)->count();

        $averageDuration = TiketPerbaikan::getAverageDurationHuman(
            $tiketSelesai
        );

        return [

            Stat::make(
                'Tiket Saya',
                number_format($totalSaya)
            )
                ->description(
                    'Tiket yang pernah tangani'
                )
                ->icon('heroicon-o-wrench-screwdriver')
                ->color('primary'),

            Stat::make(
                'Sedang Dikerjakan',
                number_format($totalAktif)
            )
                ->description(
                    'Pekerjaan yang sedang di kerjakan'
                )
                ->icon('heroicon-o-cog-6-tooth')
                ->color('warning'),

            Stat::make(
                'Selesai',
                number_format($totalSelesai)
            )
                ->description(
                    'Tiket yang telah di selesaikan'
                )
                ->icon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make(
                'Rata-rata Durasi Perbaikan',
                $averageDuration
            )
                ->description(
                    'Rata-rata waktu penyelesaian tiket'
                )
                ->icon('heroicon-o-clock')
                ->color('info'),
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('teknisi') ?? false;
    }
}
