<?php

namespace App\Filament\Concerns;

use App\Models\User;
use App\Services\Notifications\HelpdeskNotificationReadService;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

trait InteractsWithNotificationCenter
{
    use WithPagination;

    public string $filter = 'all';

    public function setFilter(string $filter): void
    {
        $this->filter = in_array($filter, ['all', 'unread', 'read'], true)
            ? $filter
            : 'all';

        $this->resetPage();
    }

    /**
     * Menandai satu notifikasi sebagai terbaca.
     * Kepemilikan divalidasi oleh service melalui relasi aktor.
     */
    public function markAsRead(
        string $notificationId,
        HelpdeskNotificationReadService $notificationReader,
    ): void {
        $user = Auth::user();

        abort_unless($user instanceof User, 401);

        $notificationReader->markAsRead($user, $notificationId);
    }

    /**
     * Menandai semua unread milik aktor, tidak hanya item yang terlihat.
     */
    public function markAllAsRead(
        HelpdeskNotificationReadService $notificationReader,
    ): void {
        $user = Auth::user();

        abort_unless($user instanceof User, 401);

        $notificationReader->markAllAsRead($user);

        // getViewData() menghitung unread dari database pada render berikutnya.
        // Jika komponen memiliki state/counter tersimpan, reset state tersebut
        // atau panggil mekanisme refresh yang memang digunakan komponen.
        $this->dispatch('$refresh');
    }

    /**
     * Tandai terbaca sebelum navigasi dan pertahankan URL payload Helpdesk.
     */
    public function openNotification(
        string $notificationId,
        HelpdeskNotificationReadService $notificationReader,
    ) {
        $user = Auth::user();

        abort_unless($user instanceof User, 401);

        /** @var DatabaseNotification $notification */
        $notification = $user->notifications()
            ->whereKey($notificationId)
            ->firstOrFail();

        $notificationReader->markAsRead($user, $notificationId);

        $url = data_get($notification->data, 'url');

        if (!is_string($url) || trim($url) === '') {
            $this->dispatch('$refresh');

            return null;
        }

        // Hindari open redirect: navigasi hanya memakai path dan query
        // dari URL tersimpan, bukan hostname eksternal.
        $path = parse_url($url, PHP_URL_PATH);
        $query = parse_url($url, PHP_URL_QUERY);

        if (
            !is_string($path)
            || $path === ''
            || !str_starts_with($path, '/')
            || str_starts_with($path, '//')
        ) {
            $this->dispatch('$refresh');

            return null;
        }

        $target = $path . ($query ? '?' . $query : '');

        return redirect()->to($target);
    }

    protected function getViewData(): array
    {
        $user = Auth::user();

        abort_unless($user instanceof User, 401);

        $query = $user->notifications()->latest();

        if ($this->filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($this->filter === 'read') {
            $query->whereNotNull('read_at');
        }

        return [
            'notifications' => $query->paginate(15),
            'unreadCount' => $user->unreadNotifications()->count(),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Auth::user()?->unreadNotifications()->count() ?? 0;

        return $count > 0 ? (string) min($count, 99) : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
}
