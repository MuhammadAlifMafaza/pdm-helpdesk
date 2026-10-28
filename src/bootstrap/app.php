<?php

use App\Http\Middleware\IsPemohon;
use App\Http\Middleware\IsStaff;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        channels: __DIR__.'/../routes/channels.php',
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'pemohon' => IsPemohon::class,
            'staff' => IsStaff::class,
        ]);
    })
    ->withExceptions(
        function (Exceptions $exceptions): void {
            /*
            |----------------------------------------------------------------------
            | 403 → halaman utama sesuai peran
            |----------------------------------------------------------------------
            | Menangkap seluruh sumber 403 (akses panel, policy/Shield,
            | abort(403) pada controller) lalu mengarahkan user terautentikasi
            | ke halamannya sendiri, alih-alih menampilkan halaman error.
            |
            | Dibatasi hanya navigasi halaman (GET/HEAD, bukan JSON, bukan
            | request Livewire) agar aksi Livewire yang ditolak tetap menerima
            | 403 dan tidak merusak siklus update komponen.
            */
            $exceptions->render(function (HttpException $e, Request $request) {
                if ($e->getStatusCode() !== 403) {
                    return null;
                }

                if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
                    return null;
                }

                if ($request->expectsJson() || $request->hasHeader('X-Livewire')) {
                    return null;
                }

                $user = $request->user();

                if (! $user instanceof User) {
                    return null;
                }

                $homeUrl = $user->homeUrl();

                if ($homeUrl === null) {
                    return null;
                }

                // Berhenti redirect ketika halaman tujuan sendiri yang ditolak,
                // agar tidak terjadi loop antara homeUrl dan 403.
                if ($request->is(trim($homeUrl, '/'))) {
                    return null;
                }

                if ($request->hasSession()) {
                    Notification::make()
                        ->warning()
                        ->title('Akses ditolak')
                        ->body('Anda tidak memiliki akses ke halaman tersebut.')
                        ->send();
                }

                return redirect()->to($homeUrl);
            });
        }
    )
    ->create();
