<?php

namespace App\Services\Notifications;

use App\Models\User;
use App\Notifications\HelpdeskNotification;
use Filament\Notifications\Events\DatabaseNotificationsSent;

class HelpdeskNotificationService
{
    /**
     * Kirim notification secara manual.
     *
     * Digunakan untuk kebutuhan non-domain-event.
     *
     * Domain notification utama menggunakan:
     * HelpdeskActivityCreated
     * ->
     * SendHelpdeskNotification
     */
    public static function sendNotification(
        User $recipient,
        string $type,
        string $title,
        string $message,
        ?string $kode = null,
        ?string $url = null,
        ?string $icon = null,
        ?string $color = null,
        ?string $referenceId = null,
        array $data = [],
    ): void {

        $recipient->notify(
            new HelpdeskNotification(
                type: $type,
                title: $title,
                message: $message,
                kode: $kode,
                url: $url,
                icon: $icon,
                color: $color,
                referenceId: $referenceId,
                data: $data,
            ),
        );

        // Refresh Filament's built-in notification drawer and unread counter.
        DatabaseNotificationsSent::dispatch($recipient);
    }
}
