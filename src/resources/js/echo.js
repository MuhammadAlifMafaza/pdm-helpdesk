// resources/js/echo.js
import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

if (!window.helpdeskRealtimeInitialized) {
    window.helpdeskRealtimeInitialized = true;

    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST,
        wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
        wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
        enabledTransports: ['ws', 'wss'],
    });

    // Beritahu komponen Filament (drawer notifikasi) bahwa Echo sudah siap
    // dan mereka boleh subscribe ke channel realtime.
    window.dispatchEvent(new CustomEvent('EchoLoaded'));

    window.Echo.connector.pusher.connection.bind('error', (error) => {
        console.error('[Helpdesk realtime] Connection error', error);
    });

    window.Echo.connector.pusher.connection.bind('state_change', (states) => {
        console.debug('[Helpdesk realtime] Connection state', states);
    });

    if (window.helpdeskUserId) {
        const notificationChannel = window.Echo.private(`users.${window.helpdeskUserId}`);

        notificationChannel.error((error) => {
            console.error('[Helpdesk realtime] Channel authorization error', error);
        });

        notificationChannel
            .notification((notification) => {
                if (notification.id && window.helpdeskNotificationIds?.has(notification.id)) {
                    return;
                }

                window.helpdeskNotificationIds ??= new Set();
                if (notification.id) {
                    window.helpdeskNotificationIds.add(notification.id);
                }

                console.debug('[Helpdesk realtime] Notification received', notification);

                window.Livewire?.dispatch('helpdesk-realtime-update', {
                    module: notification.data?.module ?? notification.type?.split('.')[0],
                    reference_id: notification.reference_id,
                    activity: notification.type?.split('.')[1],
                });

                window.Livewire?.all().forEach((component) => {
                    component.$wire.$refresh();
                });

                document.dispatchEvent(
                    new CustomEvent('helpdesk-notification', {
                        detail: notification,
                    }),
                );
            });
    }
}
