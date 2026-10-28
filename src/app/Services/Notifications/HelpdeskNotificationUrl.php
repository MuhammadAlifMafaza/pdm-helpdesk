<?php

namespace App\Services\Notifications;

use App\Models\User;
use Illuminate\Support\Facades\Route;

class HelpdeskNotificationUrl
{
    public static function for(
        string $module,
        int|string $referenceId,
        User $recipient,
    ): ?string {
        $panel = self::resolvePanel($recipient);

        if (!$panel) {
            return null;
        }

        $routeName = match ([$module, $panel]) {
            ['perbaikan', 'pemohon'] => 'filament.pemohon.resources.ticket-perbaikan.view',
            ['perbaikan', 'admin'] => 'filament.admin.resources.ticket-services.view',
            ['pengajuan', 'pemohon'] => 'filament.pemohon.resources.pengajuan-barang.view',
            ['pengajuan', 'admin'] => 'filament.admin.resources.pengajuan-barang.view',
            default => null,
        };

        // A missing/renamed route must not break delivery of the notification.
        if (!$routeName || !Route::has($routeName)) {
            return null;
        }

        try {
            // Store a relative path so new notifications remain valid when the
            // application host changes between local/staging/production.
            return route($routeName, ['record' => $referenceId], false);
        } catch (\Throwable) {
            return null;
        }
    }

    protected static function resolvePanel(User $recipient): ?string
    {
        if ($recipient->hasRole('pemohon')) {
            return 'pemohon';
        }

        if ($recipient->hasAnyRole(['admin', 'teknisi', 'super_admin'])) {
            return 'admin';
        }

        return null;
    }
}
