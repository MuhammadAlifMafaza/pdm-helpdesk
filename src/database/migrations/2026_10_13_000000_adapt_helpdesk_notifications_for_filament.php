<?php

use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Convert existing Helpdesk notification payloads to Filament's native
     * database notification format without deleting notification records or
     * their existing metadata.
     */
    public function up(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('notifications')) {
            return;
        }

        DB::table('notifications')
            ->orderBy('id')
            ->chunk(100, function ($notifications): void {
                foreach ($notifications as $notification) {
                    $data = json_decode((string) $notification->data, true);

                    if (! is_array($data) || ($data['format'] ?? null) === 'filament') {
                        continue;
                    }

                    // Only adapt Helpdesk notifications; leave unrelated
                    // Laravel notifications untouched.
                    if (! isset($data['type']) || ! Str::contains((string) $data['type'], ['perbaikan.', 'pengajuan.'])) {
                        continue;
                    }

                    $title = (string) ($data['title'] ?? 'Notifikasi Helpdesk');
                    $body = (string) ($data['message'] ?? $data['body'] ?? 'Ada pembaruan pada layanan Helpdesk.');
                    $url = $data['url'] ?? null;
                    $icon = $data['icon'] ?? null;
                    $color = match ($data['color'] ?? null) {
                        'success', 'warning', 'danger', 'info', 'gray' => $data['color'],
                        default => 'info',
                    };

                    $filamentNotification = FilamentNotification::make()
                        ->title($title)
                        ->body($body)
                        ->color($color);

                    if (filled($icon)) {
                        $filamentNotification->icon($icon);
                    }

                    if (filled($url)) {
                        $filamentNotification->actions([
                            Action::make('open')
                                ->label('Buka notifikasi')
                                ->button()
                                ->url($url)
                                ->markAsRead(),
                        ]);
                    }

                    $nativeData = $filamentNotification->getDatabaseMessage();

                    // Preserve all existing Helpdesk metadata while ensuring
                    // Filament's required fields take precedence.
                    $data = array_merge($data, $nativeData, [
                        'format' => 'filament',
                        'title' => $title,
                        'body' => $body,
                        'type' => $data['type'],
                    ]);

                    DB::table('notifications')
                        ->where('id', $notification->id)
                        ->update([
                            'data' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        ]);
                }
            });
    }

    /**
     * Deliberately no destructive rollback: historical notification payloads
     * are user-visible records and should not be rewritten back or removed.
     */
    public function down(): void
    {
        // No-op by design. The conversion is additive and preserves old keys.
    }
};
