<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectToAuthorizedHelpdeskPanel
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        $path = trim($request->path(), '/');
        $isAdminPath = $path === 'admin' || str_starts_with($path, 'admin/');
        $isPemohonPath = $path === 'pemohon' || str_starts_with($path, 'pemohon/');

        if ($isAdminPath && $user->hasRole('pemohon')) {
            return redirect()->to('/pemohon');
        }

        if ($isPemohonPath && $user->hasAnyRole(['admin', 'teknisi', 'super_admin'])) {
            return redirect()->to('/admin');
        }

        return $next($request);
    }
}
