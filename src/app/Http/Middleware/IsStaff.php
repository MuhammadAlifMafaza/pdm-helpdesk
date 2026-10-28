<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsStaff
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return redirect('/admin/login');
        }

        if (! $user->hasAnyRole(['admin', 'teknisi', 'super_admin'])) {
            $homeUrl = $user->homeUrl();

            if ($homeUrl === null) {
                abort(403);
            }

            return redirect()->to($homeUrl);
        }

        return $next($request);
    }
}
