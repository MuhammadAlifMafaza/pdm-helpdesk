<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Helpdesk Notifications
    |--------------------------------------------------------------------------
    |
    | broadcast_enabled menambahkan channel `broadcast` pada notifikasi
    | Helpdesk sehingga panel menerima update realtime melalui Reverb.
    | Database tetap selalu digunakan sebagai sumber notifikasi utama,
    | jadi layanan tetap berjalan walau server WebSocket mati.
    |
    */

    'notifications' => [
        'broadcast_enabled' => (bool) env('HELPDESK_BROADCAST_ENABLED', false),
    ],

];
