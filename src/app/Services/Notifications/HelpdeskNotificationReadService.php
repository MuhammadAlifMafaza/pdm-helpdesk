<?php

namespace App\Services\Notifications;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;

class HelpdeskNotificationReadService
{
    /**
     * Tandai satu notifikasi milik aktor sebagai terbaca.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function markAsRead(
        User $user,
        string $notificationId,
    ): bool {
        /** @var DatabaseNotification $notification */
        $notification = $user->notifications()
            ->whereKey($notificationId)
            ->firstOrFail();

        if ($notification->read_at !== null) {
            return false;
        }

        $notification->markAsRead();

        return true;
    }

    /**
     * Tandai seluruh notifikasi belum terbaca milik aktor.
     *
     * Mencakup semua unread di database, termasuk yang tidak
     * sedang ditampilkan pada panel atau halaman saat ini.
     */
    public function markAllAsRead(User $user): int
    {
        return $user->unreadNotifications()->update([
            'read_at' => now(),
        ]);
    }
}
