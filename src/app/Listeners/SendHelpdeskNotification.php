<?php

namespace App\Listeners;

use App\Events\HelpdeskActivityCreated;
use App\Models\User;
use App\Notifications\HelpdeskNotification;
use App\Services\Notifications\HelpdeskNotificationUrl;
use App\Services\Notifications\HelpdeskRecipientResolver;
use Filament\Notifications\Events\DatabaseNotificationsSent;

class SendHelpdeskNotification
{
    public function __construct(
        protected HelpdeskRecipientResolver $recipientResolver,
    ) {}

    /* =======================================================================
     * HANDLE EVENT
     * ========================================================================
     */
    public function handle(
        HelpdeskActivityCreated $event,
    ): void {
        /* =======================================================================
         * Jika referenceId null, maka tidak ada yang perlu dikirimkan.
         * ========================================================================
         */
        if ($event->referenceId === null) {
            return;
        }

        // Resolve the actor at event handling time and persist identity in payload.
        // Do not use auth() here: listeners/notifications may execute asynchronously.
        $actor = $event->actorId ? User::query()->find($event->actorId) : null;
        $event->data = array_merge($event->data, [
            'actor_id' => $actor?->id ?? $event->actorId,
            'actor_name' => $actor?->name ?? 'Sistem',
        ]);

        /* =======================================================================
         * Resolve recipients.
         * ========================================================================
         */
        $recipients = $this->recipientResolver->resolve(
            module: $event->module,
            referenceId: $event->referenceId,
            actorId: $event->actorId,
            activity: $event->activity,
            data: $event->data,
        );

        /* =======================================================================
         * Jika tidak ada recipient, maka tidak ada yang perlu dikirimkan.
         * ========================================================================
         */
        if ($recipients->isEmpty()) {
            return;
        }

        /* =======================================================================
         * Kirim notification ke setiap recipient.
         * ========================================================================
         */
        foreach ($recipients as $recipient) {

            $url = HelpdeskNotificationUrl::for(
                module: $event->module,
                referenceId: $event->referenceId,
                recipient: $recipient,
            );

            $notification = $this->buildNotification(
                event: $event,
                url: $url,
            );

            if (! $notification) {
                continue;
            }

            // Preserve the actor in metadata and make it visible in the native drawer body.
            $recipient->notify($notification);

            // Perbarui drawer notifikasi Filament secara realtime
            // (membutuhkan server Reverb yang berjalan).
            if (config('helpdesk.notifications.broadcast_enabled', false)) {
                DatabaseNotificationsSent::dispatch($recipient);
            }
        }
    }

    /* =======================================================================
     * BUILD NOTIFICATION (Berdasarkan activity/aktivitas)
     * ========================================================================
     */
    protected function buildNotification(
        HelpdeskActivityCreated $event,
        ?string $url,
    ): ?HelpdeskNotification {

        return match ($event->activity) {
            'created' => $this->createdNotification($event, $url),
            'chat' => $this->chatNotification($event, $url),
            'status' => $this->statusNotification($event, $url),
            'updated' => $this->updatedNotification($event, $url),
            'cancelled' => $this->cancelledNotification($event, $url),
            'rejected' => $this->rejectedNotification($event, $url),
            'confirmation_requested' => $this->confirmationRequestedNotification($event, $url),
            'confirmed' => $this->confirmedNotification($event, $url),
            'confirmation_rejected' => $this->confirmationRejectedNotification($event, $url),
            default => null,
        };
    }

    /* =======================================================================
     * NOTIFICATION BUILDERS (Berdasarkan activity/aktivitas)
     * ========================================================================
     */

    // Notification Created tiket/pengajuan baru.
    protected function createdNotification(
        HelpdeskActivityCreated $event,
        ?string $url,
    ): HelpdeskNotification {

        $isTicket = $event->module === 'perbaikan';

        return new HelpdeskNotification(
            type: "{$event->module}.created",

            title: $isTicket
            ? 'Tiket Perbaikan Baru'
            : 'Pengajuan Barang Baru',

            message: $this->messageWithActor($event, $event->data['message']
            ?? (
                $isTicket
                ? "Tiket {$event->kode} baru telah dibuat."
                : "Pengajuan {$event->kode} baru telah dibuat."
            )),

            kode: $event->kode,
            url: $url,

            icon: $isTicket
            ? 'heroicon-o-wrench-screwdriver'
            : 'heroicon-o-cube',

            color: 'info',
            referenceId: (string) $event->referenceId,
            data: $event->data,
        );
    }

    // Notification Chat tiket/pengajuan masuk.
    protected function chatNotification(
        HelpdeskActivityCreated $event,
        ?string $url,
    ): HelpdeskNotification {

        return new HelpdeskNotification(
            type: "{$event->module}.chat",
            title: "Pesan Baru · {$event->kode}",

            message: $this->messageWithActor($event, $event->data['message']
            ?? 'Pesan baru pada layanan Anda.'),

            kode: $event->kode,
            url: $url,
            icon: 'heroicon-o-chat-bubble-left-right',
            color: 'info',
            referenceId: (string) $event->referenceId,
            data: $event->data,
        );
    }

    // Notification Status tiket/pengajuan diperbarui.
    protected function statusNotification(
        HelpdeskActivityCreated $event,
        ?string $url,
    ): HelpdeskNotification {

        return new HelpdeskNotification(
            type: "{$event->module}.status",
            title: "Status {$event->kode} Diperbarui",

            message: $this->messageWithActor($event, $event->data['message']
            ?? 'Status layanan telah diperbarui.'),

            kode: $event->kode,
            url: $url,
            icon: 'heroicon-o-arrow-path',
            color: 'warning',
            referenceId: (string) $event->referenceId,
            data: $event->data,
        );
    }

    // Notification Perubahan Data tiket/pengajuan.
    protected function updatedNotification(
        HelpdeskActivityCreated $event,
        ?string $url,
    ): HelpdeskNotification {

        return new HelpdeskNotification(
            type: "{$event->module}.updated",
            title: "Data {$event->kode} Diperbarui",

            message: $this->messageWithActor($event, $event->data['message']
            ?? 'Data layanan telah diperbarui.'),

            kode: $event->kode,
            url: $url,
            icon: 'heroicon-o-pencil-square',
            color: 'warning',
            referenceId: (string) $event->referenceId,
            data: $event->data,
        );
    }

    // Notification Pembatalan layanan (pemberitahuan ke staf).
    protected function cancelledNotification(
        HelpdeskActivityCreated $event,
        ?string $url,
    ): HelpdeskNotification {

        $isTicket = $event->module === 'perbaikan';

        return new HelpdeskNotification(
            type: "{$event->module}.cancelled",

            title: $isTicket
            ? "Tiket {$event->kode} Dibatalkan"
            : "Pengajuan {$event->kode} Dibatalkan",

            message: $this->messageWithActor($event, $event->data['message']
            ?? 'Layanan telah dibatalkan.'),

            kode: $event->kode,
            url: $url,

            icon: 'heroicon-o-x-circle',

            color: 'danger',
            referenceId: (string) $event->referenceId,
            data: $event->data,
        );
    }

    // Notification Penolakan layanan oleh staf (ke pemilik layanan).
    protected function rejectedNotification(
        HelpdeskActivityCreated $event,
        ?string $url,
    ): HelpdeskNotification {

        $isTicket = $event->module === 'perbaikan';

        return new HelpdeskNotification(
            type: "{$event->module}.rejected",

            title: $isTicket
            ? "Tiket {$event->kode} Ditolak"
            : "Pengajuan {$event->kode} Ditolak",

            message: $this->messageWithActor($event, $event->data['message']
            ?? 'Layanan Anda ditolak. Silakan hubungi staf untuk keterangan lebih lanjut.'),

            kode: $event->kode,
            url: $url,

            icon: 'heroicon-o-no-symbol',

            color: 'danger',
            referenceId: (string) $event->referenceId,
            data: $event->data,
        );
    }

    // Notification Permintaan konfirmasi penyelesaian (staf → pemilik layanan).
    protected function confirmationRequestedNotification(
        HelpdeskActivityCreated $event,
        ?string $url,
    ): HelpdeskNotification {

        return new HelpdeskNotification(
            type: "{$event->module}.confirmation_requested",
            title: "Konfirmasi Penyelesaian · {$event->kode}",

            message: $this->messageWithActor($event, $event->data['message']
            ?? 'Pekerjaan telah selesai dikerjakan. Mohon konfirmasi penyelesaian.'),

            kode: $event->kode,
            url: $url,
            icon: 'heroicon-o-hand-raised',
            color: 'info',
            referenceId: (string) $event->referenceId,
            data: $event->data,
        );
    }

    // Notification Konfirmasi diterima (pemilik layanan → staf).
    protected function confirmedNotification(
        HelpdeskActivityCreated $event,
        ?string $url,
    ): HelpdeskNotification {

        return new HelpdeskNotification(
            type: "{$event->module}.confirmed",
            title: "Penyelesaian {$event->kode} Dikonfirmasi",

            message: $this->messageWithActor($event, $event->data['message']
            ?? 'Pemohon mengonfirmasi pekerjaan telah selesai.'),

            kode: $event->kode,
            url: $url,
            icon: 'heroicon-o-check-badge',
            color: 'success',
            referenceId: (string) $event->referenceId,
            data: $event->data,
        );
    }

    // Notification Konfirmasi ditolak / minta revisi (pemilik layanan → staf).
    protected function confirmationRejectedNotification(
        HelpdeskActivityCreated $event,
        ?string $url,
    ): HelpdeskNotification {

        return new HelpdeskNotification(
            type: "{$event->module}.confirmation_rejected",
            title: "Konfirmasi Ditolak · {$event->kode}",

            message: $this->messageWithActor($event, $event->data['message']
            ?? 'Pemohon meminta revisi sebelum penyelesaian dikonfirmasi.'),

            kode: $event->kode,
            url: $url,
            icon: 'heroicon-o-arrow-path',
            color: 'warning',
            referenceId: (string) $event->referenceId,
            data: $event->data,
        );
    }

    protected function messageWithActor(HelpdeskActivityCreated $event, string $message): string
    {
        $actorName = trim((string) ($event->data['actor_name'] ?? ''));
        if ($actorName === '') {
            return $message;
        }

        // Avoid duplicate prefixes when upstream already supplied actor information.
        if (str_starts_with(mb_strtolower($message), mb_strtolower($actorName.':'))) {
            return $message;
        }

        return $actorName.': '.$message;
    }
}
