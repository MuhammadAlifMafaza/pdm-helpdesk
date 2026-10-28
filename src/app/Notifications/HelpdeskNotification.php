<?php

namespace App\Notifications;

use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class HelpdeskNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $type,
        public string $title,
        public string $message,
        public ?string $kode = null,
        public ?string $url = null,
        public ?string $icon = null,
        public ?string $color = null,
        public ?string $referenceId = null,
        public array $data = [],
    ) {}

    /* ========================================================================
     * Notification channels.
     * ========================================================================
     */
    public function via(object $notifiable): array
    {
        // Database is the durable source of truth for the Helpdesk notification center.
        // Broadcasting is optional because it requires a running Reverb/Pusher service.
        // Keep database notifications available even when WebSocket service is offline.
        $channels = ['database'];

        if (config('helpdesk.notifications.broadcast_enabled', false)) {
            $channels[] = 'broadcast';
        }

        return $channels;
    }

    /* ========================================================================
     * Notification payloads.
     *
     * Filament hanya menampilkan notifikasi database yang memiliki
     * `format: filament` (lihat Filament\Notifications\Livewire\DatabaseNotifications
     * ->getNotificationsQuery()). Karena itu payload dibangun melalui
     * FilamentNotification::getDatabaseMessage(), lalu metadata Helpdesk
     * (type, kode, url, reference_id, data) ditambahkan di atasnya.
     * ========================================================================
     */
    public function toDatabase(
        object $notifiable
    ): array {
        return array_merge(
            $this->filamentPayload(),
            $this->helpdeskMetadata(),
        );
    }

    /* ========================================================================
     * Broadcast payloads.
     * ========================================================================
     */
    public function toBroadcast(
        object $notifiable,
    ): BroadcastMessage {

        return new BroadcastMessage(array_merge(
            $this->filamentPayload(),
            $this->helpdeskMetadata(),
            [
                'id' => $this->id,
                'data' => array_merge($this->data, [
                    'module' => explode('.', $this->type)[0] ?? null,
                    'activity' => explode('.', $this->type)[1] ?? null,
                ]),
            ],
        ));
    }

    /* ========================================================================
     * Broadcast type.
     * ========================================================================
     */
    public function broadcastType(): string
    {
        return 'helpdesk.notification';
    }

    /* =======================================================================
     * FILAMENT PAYLOAD (format, title, body, color, icon, actions, duration)
     * ========================================================================
     */
    protected function filamentPayload(): array
    {
        $filamentNotification = FilamentNotification::make()
            ->title($this->title)
            ->body($this->message)
            ->color($this->resolveColor());

        if (filled($this->icon)) {
            $filamentNotification->icon($this->icon);
        }

        if (filled($this->url)) {
            $filamentNotification->actions([
                Action::make('open')
                    ->label('Buka notifikasi')
                    ->button()
                    ->url($this->url)
                    ->markAsRead(),
            ]);
        }

        return $filamentNotification->getDatabaseMessage();
    }

    /* =======================================================================
     * HELPDESK METADATA (dipertahankan untuk kebutuhan toast & deep link)
     * ========================================================================
     *
     * @return array<string, mixed>
     */
    protected function helpdeskMetadata(): array
    {
        return [
            'type' => $this->type,
            'message' => $this->message,
            'kode' => $this->kode,
            'url' => $this->url,
            'icon' => $this->icon,
            'color' => $this->color,
            'reference_id' => $this->referenceId,
            'data' => array_merge($this->data, [
                'module' => explode('.', $this->type)[0] ?? null,
            ]),
        ];
    }

    /* =======================================================================
     * COLOR
     * ========================================================================
     */
    protected function resolveColor(): string
    {
        return match ($this->color) {
            'success', 'warning', 'danger', 'info', 'gray' => $this->color,
            default => 'info',
        };
    }
}
