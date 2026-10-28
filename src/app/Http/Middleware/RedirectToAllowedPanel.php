<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Arahkan user ke panel yang berhak ia buka ketika mengakses panel lain,
 * alih-alih melempar 403.
 *
 * Middleware ini didaftarkan pada `middleware()` panel (bukan `authMiddleware()`)
 * agar berjalan sebelum `Filament\Http\Middleware\Authenticate` yang akan
 * `abort(403)` ketika `canAccessPanel()` bernilai false.
 */
class RedirectToAllowedPanel
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return $next($request);
        }

        $path = trim($request->path(), '/');
        $isAdminPanel = $path === 'admin' || str_starts_with($path, 'admin/');
        $isPemohonPanel = $path === 'pemohon' || str_starts_with($path, 'pemohon/');

        if ($isAdminPanel && ! $user->hasAnyRole(['admin', 'teknisi', 'super_admin'])) {
            return $this->redirectToHome($request, $user, 'panel Admin');
        }

        if ($isPemohonPanel && ! $user->hasRole('pemohon')) {
            return $this->redirectToHome($request, $user, 'panel Pemohon');
        }

        return $next($request);
    }

    private function redirectToHome(Request $request, User $user, string $panel): Response
    {
        $homeUrl = $user->homeUrl();

        // User tanpa peran tidak punya halaman tujuan; biarkan penangan 403
        // bawaan yang menampilkan pesan akses ditolak.
        if ($homeUrl === null) {
            abort(403, "Akun Anda tidak memiliki akses ke {$panel}.");
        }

        // Hindari loop redirect apabila halaman tujuan sendiri yang ditolak.
        if ($request->is(trim($homeUrl, '/'))) {
            abort(403, "Akun Anda tidak memiliki akses ke {$panel}.");
        }

        return redirect()->to($homeUrl);
    }
}
