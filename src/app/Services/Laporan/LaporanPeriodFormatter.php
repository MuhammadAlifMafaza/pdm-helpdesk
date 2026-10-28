<?php

namespace App\Services\Laporan;

use Carbon\Carbon;

class LaporanPeriodFormatter
{
    /** Resolve explicit date range first, otherwise month/year selections. */
    public static function range(?array $filter): array
    {
        $filter ??= [];
        $from = self::parse($filter['dari_tanggal'] ?? $filter['from'] ?? null);
        $until = self::parse($filter['sampai_tanggal'] ?? $filter['until'] ?? null);

        if ($from || $until) {
            return [
                'from' => $from?->toDateString(),
                'until' => $until?->toDateString(),
            ];
        }

        $year = filled($filter['tahun'] ?? null) ? (int) $filter['tahun'] : null;
        $month = filled($filter['bulan'] ?? null) ? (int) $filter['bulan'] : null;

        if ($year && $month && $month >= 1 && $month <= 12) {
            $start = Carbon::create($year, $month, 1)->startOfMonth();

            return ['from' => $start->toDateString(), 'until' => $start->copy()->endOfMonth()->toDateString()];
        }

        if ($year) {
            return [
                'from' => Carbon::create($year, 1, 1)->toDateString(),
                'until' => Carbon::create($year, 12, 31)->toDateString(),
            ];
        }

        if ($month && $month >= 1 && $month <= 12) {
            $start = Carbon::create(now()->year, $month, 1)->startOfMonth();

            return ['from' => $start->toDateString(), 'until' => $start->copy()->endOfMonth()->toDateString()];
        }

        return ['from' => null, 'until' => null];
    }

    public static function format(?array $filter): string
    {
        $filter ??= [];
        $range = self::range($filter);
        $from = self::parse($range['from']);
        $until = self::parse($range['until']);

        if (! $from && ! $until) {
            return 'Semua Periode';
        }

        // Explicit date filters should preserve the actual selected dates.
        $hasExplicitDates = filled($filter['dari_tanggal'] ?? $filter['from'] ?? null)
            || filled($filter['sampai_tanggal'] ?? $filter['until'] ?? null);

        if ($hasExplicitDates) {
            if ($from && $until) {
                return $from->translatedFormat('d F Y').' – '.$until->translatedFormat('d F Y');
            }

            return $from
                ? 'Mulai '.$from->translatedFormat('d F Y')
                : 'Sampai '.$until->translatedFormat('d F Y');
        }

        if ($from && $until && $from->isSameMonth($until)) {
            return self::month($from);
        }

        if ($from && $until && $from->isSameYear($until) && $from->month === 1 && $until->month === 12) {
            return $from->format('Y');
        }

        if ($from && $until) {
            return $from->translatedFormat('d F Y').' – '.$until->translatedFormat('d F Y');
        }

        return $from ? 'Mulai '.$from->translatedFormat('d F Y') : 'Sampai '.$until->translatedFormat('d F Y');
    }

    private static function parse(mixed $date): ?Carbon
    {
        return filled($date) ? Carbon::parse($date)->locale('id') : null;
    }

    private static function month(Carbon $date): string
    {
        return $date->translatedFormat('F Y');
    }
}
