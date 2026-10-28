@auth
            @vite('resources/js/app.js')

    <script>
        window.helpdeskUserId = @json(auth()->id());
    </script>

    <script>
    if (!window.helpdeskToastHandlerRegistered) {
    window.helpdeskToastHandlerRegistered = true;

    const escapeHelpdeskHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (character) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;',
    })[character]);

    document.addEventListener('helpdesk-notification', (event) => {
    const notification = event.detail ?? {};
    const bodyParts = [];

    if (notification.kode) {
    bodyParts.push(`<strong>${escapeHelpdeskHtml(notification.kode)}</strong>`);
    }

    if (notification.data?.sender_name) {
    bodyParts.push(escapeHelpdeskHtml(notification.data.sender_name));
    }

    if (notification.message) {
    bodyParts.push(escapeHelpdeskHtml(notification.message));
    }

    const toast = new window.FilamentNotification();
    toast
    .title(notification.title ?? 'Notifikasi Helpdesk')
    .body(bodyParts.join('<br>'));

    if (notification.color) {
    toast.color(notification.color);
    }

    if (notification.icon) {
    toast.icon(notification.icon);
    }

    toast.send();
    });
    }
    </script>
@endauth
