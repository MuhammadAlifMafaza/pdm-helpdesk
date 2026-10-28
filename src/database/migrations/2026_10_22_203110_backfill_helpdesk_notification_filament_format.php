<?php

use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Notifikasi Helpdesk yang dibuat setelah migrasi adaptasi 2026_10_13
     * tidak memiliki kunci `format` sehingga tidak pernah tampil di drawer
     * Filament (query drawer memfilter `data->format = 'filament'`).
     *
     * Migrasi ini mengisi format, body, dan duration pada baris yang belum
     * memiliki format, tanpa menghapus metadata Helpdesk yang sudah ada.
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

                    if (! is_array($data)) {
                        continue;
                    }

                    if (($data['format'] ?? null) === 'filament') {
                        continue;
                    }

                    // Hanya adaptasi notifikasi Helpdesk; notifikasi lain
                    // dari framework tetap dibiarkan apa adanya.
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

                    // Metadata Helpdesk dipertahankan; kunci Filament
                    // diberi prioritas agar lolos filter drawer.
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
     * Tidak ada rollback destruktif: baris notifikasi adalah data yang
     * dilihat pengguna dan tidak boleh dikembalikan ke format lama.
     */
    public function down(): void
    {
        // No-op by design.
    }
};
