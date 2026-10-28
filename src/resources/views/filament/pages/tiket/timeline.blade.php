{{-- Timeline bersama untuk aktivitas transaksi modul utama. --}}
@php
    $record = $getRecord();
    $logs = $record->timeline()->with('user')->orderByDesc('created_at')->orderByDesc('id')->get();

    // Daftar event_type yang diperlakukan sebagai "status event"
    // (menampilkan pil status + kotak perubahan nilai + deskripsi tunggal).
    $statusEventTypes = ['CREATE', 'PROCESS', 'ASSIGN', 'APPROVE', 'REJECT', 'REOPEN', 'PENDING', 'COMPLETE', 'STATUS'];

    // Bersihkan nilai lama/baru: trim + ganti underscore jadi spasi, kembalikan null bila kosong.
    $cleanValue = static function ($value): ?string {
        if ($value === null) {
            return null;
        }
        $value = trim(str_replace('_', ' ', (string) $value));

        return $value === '' ? null : $value;
    };

    // Buang tag internal ([SELESAI]/[DITOLAK]/[REOPEN]) yang dipakai model untuk
    // mendeteksi event_type, karena sudah terwakili oleh pil status berwarna —
    // tidak perlu bocor ke catatan yang ditampilkan ke user.
    $stripStatusTag = static function (?string $text): ?string {
        if ($text === null) {
            return null;
        }
        $text = trim(preg_replace('/^\[(SELESAI|DITOLAK|REOPEN)\]\s*/i', '', $text));

        return $text === '' ? null : $text;
    };
@endphp

@once
    <style>
        /* =========================================================
                    TICKET TIMELINE
                    CSS dibuat khusus agar tidak bergantung pada Tailwind
                    ========================================================= */

        .ticket-timeline {
            width: 100%;
            padding: 28px 24px 32px;
            box-sizing: border-box;
        }

        .ticket-timeline-list {
            --ticket-timeline-max-height: 520px;

            width: 100%;
            max-height: var(--ticket-timeline-max-height);
            margin: 0;
            padding: 0 10px 0 0;
            overflow-y: auto;
            overflow-x: hidden;

            /* Firefox */
            scrollbar-width: thin;
            scrollbar-color: #d1d5db transparent;
        }

        /* Chrome / Safari / Edge */
        .ticket-timeline-list::-webkit-scrollbar {
            width: 6px;
        }

        .ticket-timeline-list::-webkit-scrollbar-track {
            background: transparent;
        }

        .ticket-timeline-list::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 9999px;
        }

        .ticket-timeline-list:hover::-webkit-scrollbar-thumb {
            background: #9ca3af;
        }

        .dark .ticket-timeline-list {
            scrollbar-color: #4b5563 transparent;
        }

        .dark .ticket-timeline-list::-webkit-scrollbar-thumb {
            background: #4b5563;
        }

        .dark .ticket-timeline-list:hover::-webkit-scrollbar-thumb {
            background: #6b7280;
        }

        /* ---------------------------------------------------------
                    ITEM
                    --------------------------------------------------------- */

        .ticket-timeline-item {
            position: relative;
            display: grid;
            grid-template-columns: 28px minmax(0, 1fr);
            column-gap: 14px;
            width: 100%;
            box-sizing: border-box;
        }

        /* ---------------------------------------------------------
                    TIMELINE LEFT COLUMN
                    --------------------------------------------------------- */

        .ticket-timeline-marker {
            position: relative;
            width: 28px;
            min-height: 100%;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        /* garis vertikal */

        .ticket-timeline-line {
            position: absolute;
            top: 13px;
            bottom: -10px;
            left: 50%;
            width: 2px;
            transform: translateX(-50%);
            background: #d1d5db;
            z-index: 1;
        }

        /* titik */

        .ticket-timeline-dot {
            position: relative;
            z-index: 3;
            width: 13px;
            height: 13px;
            margin-top: 3px;
            border-radius: 50%;
            background: #6b7280;
            border: 3px solid #ffffff;
            box-sizing: content-box;
            box-shadow: 0 0 0 1px #d1d5db;
        }

        /* ---------------------------------------------------------
                    CONTENT
                    --------------------------------------------------------- */

        .ticket-timeline-content {
            min-width: 0;
            padding: 0 0 34px;
        }

        .ticket-timeline-date {
            margin: 0;
            padding: 0;
            font-size: 13px;
            line-height: 20px;
            font-weight: 500;
            color: #6b7280;
        }

        .ticket-timeline-user {
            margin-top: 2px;
            font-size: 14px;
            line-height: 21px;
            font-weight: 600;
            color: #111827;
        }

        .ticket-timeline-category {
            display: inline-flex;
            align-items: center;
            width: fit-content;
            margin-top: 5px;
            padding: 2px 8px;
            border-radius: 5px;
            border: 1px solid #d1d5db;
            background: #f9fafb;
            color: #4b5563;
            font-size: 11px;
            line-height: 16px;
            font-weight: 500;
        }

        /* ---------------------------------------------------------
                    TITLE / CHAT MESSAGE
                    --------------------------------------------------------- */

        .ticket-timeline-message {
            width: min(100%, 600px);
            margin-top: 9px;
            padding: 10px 13px;
            box-sizing: border-box;

            border: 1px solid #e5e7eb;
            border-radius: 8px;

            background: #f9fafb;

            color: #374151;
            font-size: 13px;
            line-height: 20px;

            word-break: break-word;
        }

        /* ---------------------------------------------------------
                    STATUS
                    --------------------------------------------------------- */

        .ticket-timeline-status {
            display: inline-flex;
            align-items: center;
            margin-top: 8px;
            padding: 3px 9px;

            border-radius: 999px;

            font-size: 11px;
            line-height: 16px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .02em;
        }

        .ticket-timeline-status-open,
        .ticket-timeline-status-success {
            background: #ecfdf3;
            color: #047857;
            border: 1px solid #a7f3d0;
        }

        .ticket-timeline-status-deleted {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        .ticket-timeline-status-progress {
            background: #fffbeb;
            color: #b45309;
            border: 1px solid #fde68a;
        }

        .ticket-timeline-status-pending {
            background: #f3f4f6;
            color: #4b5563;
            border: 1px solid #d1d5db;
        }

        .ticket-timeline-status-closed {
            background: #f5f3ff;
            color: #6d28d9;
            border: 1px solid #ddd6fe;
        }

        .ticket-timeline-status-default {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
        }

        /* ---------------------------------------------------------
                    DATA CHANGE
                    --------------------------------------------------------- */

        .ticket-timeline-change {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;

            width: min(100%, 600px);
            margin-top: 9px;
            padding: 10px 13px;
            box-sizing: border-box;

            border: 1px solid #e5e7eb;
            border-radius: 8px;

            background: #f9fafb;

            font-size: 13px;
        }

        .ticket-timeline-old {
            padding: 3px 7px;
            border-radius: 5px;

            background: #fef2f2;
            color: #dc2626;

            text-decoration: line-through;
            text-decoration-color: #fca5a5;
        }

        .ticket-timeline-new {
            padding: 3px 7px;
            border-radius: 5px;

            background: #ecfdf5;
            color: #047857;

            font-weight: 600;
        }

        .ticket-timeline-arrow {
            color: #9ca3af;
            font-size: 16px;
            line-height: 16px;
        }

        /* ---------------------------------------------------------
                    DESCRIPTION
                    --------------------------------------------------------- */

        .ticket-timeline-description {
            margin-top: 8px;

            color: #6b7280;
            font-size: 13px;
            line-height: 20px;

            max-width: 600px;
        }

        /* ---------------------------------------------------------
                    CREATE EVENT
                    --------------------------------------------------------- */

        .ticket-timeline-create .ticket-timeline-dot {
            background: #10b981;
            box-shadow: 0 0 0 1px #6ee7b7;
        }

        /* ---------------------------------------------------------
                    ASSIGN / PROCESS / REOPEN EVENT
                    --------------------------------------------------------- */

        .ticket-timeline-assign .ticket-timeline-dot {
            background: #f59e0b;
            box-shadow: 0 0 0 1px #fde68a;
        }

        /* ---------------------------------------------------------
                    DELETE EVENT
                    --------------------------------------------------------- */

        .ticket-timeline-delete .ticket-timeline-dot {
            background: #ef4444;
            box-shadow: 0 0 0 1px #fca5a5;
        }

        /* ---------------------------------------------------------
                    STATUS EVENT
                    --------------------------------------------------------- */

        .ticket-timeline-status-event .ticket-timeline-dot {
            background: #3b82f6;
            box-shadow: 0 0 0 1px #93c5fd;
        }

        /* ---------------------------------------------------------
                    CHAT EVENT
                    --------------------------------------------------------- */

        .ticket-timeline-chat .ticket-timeline-dot {
            background: #6b7280;
            box-shadow: 0 0 0 1px #d1d5db;
        }

        /* ---------------------------------------------------------
                    DARK MODE
                    --------------------------------------------------------- */

        .dark .ticket-timeline-line {
            background: #374151;
        }

        .dark .ticket-timeline-dot {
            border-color: #111827;
            box-shadow: 0 0 0 1px #4b5563;
        }

        .dark .ticket-timeline-date {
            color: #9ca3af;
        }

        .dark .ticket-timeline-user {
            color: #f9fafb;
        }

        .dark .ticket-timeline-category {
            background: #1f2937;
            border-color: #374151;
            color: #d1d5db;
        }

        .dark .ticket-timeline-message,
        .dark .ticket-timeline-change {
            background: #1f2937;
            border-color: #374151;
            color: #d1d5db;
        }

        .dark .ticket-timeline-description {
            color: #9ca3af;
        }

        .dark .ticket-timeline-dot {
            background-color: #9ca3af;
        }

        .dark .ticket-timeline-create .ticket-timeline-dot {
            background: #34d399;
        }

        .dark .ticket-timeline-assign .ticket-timeline-dot {
            background: #fbbf24;
        }

        .dark .ticket-timeline-delete .ticket-timeline-dot {
            background: #f87171;
        }

        .dark .ticket-timeline-status-event .ticket-timeline-dot {
            background: #60a5fa;
        }

        /* ---------------------------------------------------------
                    EMPTY
                    --------------------------------------------------------- */

        .ticket-timeline-empty {
            padding: 40px 20px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
        }
    </style>
@endonce

<div class="ticket-timeline">

    @if ($logs->count())

        <div class="ticket-timeline-list">

            @foreach ($logs as $log)
                @php
                    // Normalisasi agar pencocokan match() tidak case-sensitive terhadap data di DB.
                    $eventType = strtoupper((string) ($log->event_type ?? ''));
                    $eventDescription = $stripStatusTag($log->event_description ?: $log->keterangan);
                    $keterangan = $stripStatusTag($log->keterangan);

                    /*
                     * Tentukan tipe visual berdasarkan kategori.
                     */
                    $eventClass = match ($eventType) {
                        'CREATE' => 'ticket-timeline-create',
                        'DELETE' => 'ticket-timeline-delete',
                        'CHAT' => 'ticket-timeline-chat',
                        'UPDATE' => 'ticket-timeline-update',
                        'ASSIGN', 'PROCESS', 'REOPEN' => 'ticket-timeline-assign',
                        // COMPLETE/APPROVE = "Close - Selesai/Disetujui" -> ikut warna hijau CREATE.
                        'COMPLETE', 'APPROVE' => 'ticket-timeline-create',
                        // REJECT = "Close - Ditolak" -> ikut warna merah DELETE.
                        'REJECT' => 'ticket-timeline-delete',
                        default => 'ticket-timeline-status-event',
                    };

                    $isStatusEvent = in_array($eventType, $statusEventTypes, true);
                    $isDeleteEvent = $eventType === 'DELETE';
                    $isUpdateEvent = $eventType === 'UPDATE';

                    $oldValue = $cleanValue($log->data_lama ?? null);
                    $newValue = $cleanValue($log->data_baru ?? null);

                    // Kotak "nilai lama → nilai baru": dipakai bersama oleh STATUS, DELETE, dan UPDATE
                    // agar markup-nya tidak ditulis ulang tiga kali.
                    $showChangeBox =
                        ($isStatusEvent && $oldValue && $newValue) ||
                        (($isDeleteEvent || $isUpdateEvent) && ($oldValue || $newValue));

                    /*
                     * Tentukan class & label pil status berdasarkan event_type (bukan teks data_baru).
                     * Ini penting karena COMPLETE/APPROVE ("Selesai"/"Disetujui") dan REJECT ("Ditolak")
                     * sama-sama menyimpan data_baru = "Close" — hanya event_type yang membedakannya
                     * (lihat getEventTypeAttribute() di LogPerbaikan / LogPengajuan).
                     */
                    $statusClass = match (true) {
                        $eventType === 'CREATE' => 'ticket-timeline-status-open',
                        in_array($eventType, ['ASSIGN', 'PROCESS', 'REOPEN'], true)
                            => 'ticket-timeline-status-progress',
                        $eventType === 'PENDING' => 'ticket-timeline-status-pending',
                        in_array($eventType, ['COMPLETE', 'APPROVE'], true) => 'ticket-timeline-status-success',
                        $eventType === 'REJECT' => 'ticket-timeline-status-deleted',
                        // Fallback: event "STATUS" generik dengan data_baru "Close" (tanpa tag [SELESAI]/[DITOLAK]).
                        $eventType === 'STATUS' && strtolower($newValue ?? '') === 'close'
                            => 'ticket-timeline-status-closed',
                        default => 'ticket-timeline-status-default',
                    };

                    $statusLabel = match ($eventType) {
                        'COMPLETE' => 'SELESAI',
                        'APPROVE' => 'DISETUJUI',
                        'REJECT' => 'DITOLAK',
                        default => $newValue ? strtoupper($newValue) : null,
                    };
                @endphp

                <div class="ticket-timeline-item {{ $eventClass }}">

                    {{-- =========================================
                MARKER
                ========================================== --}}
                    <div class="ticket-timeline-marker">
                        <div class="ticket-timeline-dot"></div>

                        @if (!$loop->last)
                            <div class="ticket-timeline-line"></div>
                        @endif
                    </div>

                    {{-- =========================================
                CONTENT
                ========================================== --}}
                    <div class="ticket-timeline-content">

                        {{-- DATE --}}
                        <div class="ticket-timeline-date">
                            {{ $log->created_at?->translatedFormat('d M Y, H:i') ?? 'Waktu tidak tersedia' }}
                        </div>

                        {{-- USER --}}
                        <div class="ticket-timeline-user">
                            {{ $log->user?->name ?? 'System' }}
                        </div>

                        {{-- CATEGORY --}}
                        @if ($log->kategori_log)
                            <div class="ticket-timeline-category">
                                {{ $log->kategori_log }}
                            </div>
                        @endif

                        {{-- =====================================
                    CHAT / NORMAL ACTIVITY (judul)
                    ====================================== --}}
                        @if ($log->timeline_title && !$isStatusEvent && !$isDeleteEvent)
                            <div class="ticket-timeline-message">
                                {{ $log->timeline_title }}
                            </div>
                        @endif

                        {{-- =====================================
                    PIL STATUS (hanya event status)
                    ====================================== --}}
                        @if ($isStatusEvent && $statusLabel)
                            <div class="ticket-timeline-status {{ $statusClass }}">
                                {{ $statusLabel }}
                            </div>
                        @endif

                        {{-- =====================================
                    PERUBAHAN NILAI (lama → baru)
                    Dipakai bersama oleh STATUS, DELETE, UPDATE
                    ====================================== --}}
                        @if ($showChangeBox)
                            <div class="ticket-timeline-change">
                                @if ($oldValue)
                                    <span class="ticket-timeline-old">{{ $oldValue }}</span>
                                @endif
                                @if ($oldValue && $newValue)
                                    <span class="ticket-timeline-arrow">→</span>
                                @endif
                                @if ($newValue)
                                    <span class="ticket-timeline-new">{{ $newValue }}</span>
                                @endif
                            </div>
                        @endif

                        {{-- =====================================
                    DESKRIPSI per tipe event
                    ====================================== --}}
                        @if ($isStatusEvent)
                            @if ($eventDescription)
                                <div class="ticket-timeline-description">
                                    {{ $eventDescription }}
                                </div>
                            @endif
                        @elseif ($isDeleteEvent)
                            @if ($eventDescription)
                                <div class="ticket-timeline-description">
                                    {{ $eventDescription }}
                                </div>
                            @endif
                            @if ($keterangan && $keterangan !== $eventDescription)
                                <div class="ticket-timeline-description">
                                    {{ $keterangan }}
                                </div>
                            @endif
                        @elseif ($isUpdateEvent)
                            @if ($eventDescription)
                                <div class="ticket-timeline-message">
                                    {{ $eventDescription }}
                                </div>
                            @endif
                            @if ($keterangan && $keterangan !== $eventDescription)
                                <div class="ticket-timeline-description">
                                    {{ $keterangan }}
                                </div>
                            @endif
                        @elseif ($keterangan)
                            {{-- KETERANGAN UMUM: event lain di luar status/delete/update (mis. CHAT) --}}
                            <div class="ticket-timeline-message">
                                {{ $keterangan }}
                            </div>
                        @endif

                    </div>

                </div>
            @endforeach

        </div>
    @else
        <div class="ticket-timeline-empty">
            Belum ada aktivitas.
        </div>
    @endif

</div>
