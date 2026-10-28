<?php

namespace App\Providers;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /*
        |----------------------------------------------------------------------
        | Locale Carbon → bahasa Indonesia
        |----------------------------------------------------------------------
        | Menyinkronkan locale Carbon dengan APP_LOCALE sehingga format
        | tanggal berbasis terjemahan (translatedFormat, diffForHumans)
        | menampilkan nama bulan & waktu relatif dalam bahasa Indonesia
        | ("24 Okt 2026", "3 jam yang lalu") alih-alih bahasa Inggris.
        |
        | Catatan: token format PHP polos (format('d M Y')) tidak
        | terpengaruh locale — gunakan translatedFormat() untuk teks bulan.
        */
        $locale = config('app.locale');

        Carbon::setLocale($locale);
        CarbonImmutable::setLocale($locale);
    }
}
