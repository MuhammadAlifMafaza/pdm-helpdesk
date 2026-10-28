@php
    $record = $getRecord();

    $messages = $record
        ->chatLogs()
        ->with('user')
        ->oldest('created_at')
        ->get();

    $currentUser = auth()->id();
@endphp

<style>
    /* ==============================
       HEADER
    ============================== */

    .ticket-chat-header {
        margin-bottom: 16px;
    }

    .ticket-chat-title {
        font-size: 15px;
        font-weight: 600;
        line-height: 1.4;
        color: #111827;
    }

    .ticket-chat-description {
        margin-top: 3px;
        font-size: 13px;
        color: #6b7280;
    }

    /* ============================================================
       CHAT CONTAINER
    ============================================================ */

    .ticket-chat {
        width: 100%;
        font-size: 13px;
    }


    /* ============================================================
       MESSAGE LIST
    ============================================================ */

    .ticket-chat-messages {
        display: flex;
        flex-direction: column;
        gap: 10px;

        max-height: 500px;
        overflow-y: auto;

        padding: 4px 4px 8px;
    }


    /* ============================================================
       MESSAGE ROW
    ============================================================ */

    .ticket-message {
        display: flex;
        width: 100%;
    }

    .ticket-message.mine {
        justify-content: flex-end;
    }

    .ticket-message.other {
        justify-content: flex-start;
    }


    /* ============================================================
       MESSAGE CARD / BUBBLE
    ============================================================ */

    .ticket-message-card {
        width: fit-content;
        max-width: min(78%, 620px);

        padding: 10px 12px;

        border: 1px solid #e5e7eb;
        border-radius: 12px;

        background: #ffffff;

        box-shadow:
            0 1px 2px rgba(0, 0, 0, 0.04);
    }

    .ticket-message.mine .ticket-message-card {
        background: #eff6ff;
        border-color: #bfdbfe;
    }


    /* ============================================================
       MESSAGE HEADER
    ============================================================ */

    .ticket-message-header {
        display: flex;
        align-items: center;

        gap: 8px;

        margin-bottom: 6px;
    }


    /* ============================================================
       USER BADGE
    ============================================================ */

    .ticket-user-badge {
        display: inline-flex;

        align-items: center;
        justify-content: center;

        width: 28px;
        height: 28px;

        flex: 0 0 28px;

        border-radius: 50%;

        background: #e5e7eb;
        color: #374151;

        font-size: 11px;
        font-weight: 600;
    }

    .ticket-message.mine .ticket-user-badge {
        background: #dbeafe;
        color: #1d4ed8;
    }


    /* ============================================================
       USER INFORMATION
    ============================================================ */

    .ticket-user-name {
        font-size: 12px;
        font-weight: 600;

        line-height: 1.2;

        color: #374151;
    }

    .ticket-user-role {
        margin-top: 2px;

        font-size: 10px;

        line-height: 1.2;

        color: #9ca3af;
    }


    /* ============================================================
       MESSAGE TIME
    ============================================================ */

    .ticket-message-time {
        margin-left: auto;

        align-self: flex-start;

        white-space: nowrap;

        font-size: 9px;

        color: #9ca3af;
    }


    /* ============================================================
       MESSAGE CONTENT
    ============================================================ */

    .ticket-message-content {
        width: 100%;

        font-size: 13px;
        line-height: 1.55;

        color: #374151;

        text-align: left;

        /* white-space: pre-wrap; */

        overflow-wrap: anywhere;
        word-break: break-word;
    }

    .ticket-message.mine .ticket-message-content {
        color: #1e3a8a;
    }


    /* ============================================================
       EMPTY STATE
    ============================================================ */

    .ticket-chat-empty {
        padding: 28px 20px;

        border: 1px dashed #d1d5db;
        border-radius: 12px;

        text-align: center;

        background: #fafafa;
    }

    .ticket-chat-empty-title {
        font-size: 13px;
        font-weight: 600;

        color: #6b7280;
    }

    .ticket-chat-empty-description {
        margin-top: 4px;

        font-size: 11px;

        color: #9ca3af;
    }


    /* ============================================================
       COMPOSER
    ============================================================ */

    .ticket-chat-composer {
        margin-top: 14px;

        padding-top: 14px;

        border-top: 1px solid #e5e7eb;
    }

    .ticket-chat-form {
        display: flex;

        align-items: flex-end;

        gap: 8px;
    }


    /* ============================================================
       TEXT INPUT
    ============================================================ */

    .ticket-chat-input {
        flex: 1;

        min-height: 42px;
        max-height: 120px;

        padding: 10px 12px;

        border: 1px solid #d1d5db;
        border-radius: 10px;

        background: #ffffff;
        color: #111827;

        font-family: inherit;
        font-size: 13px;
        line-height: 1.45;

        resize: vertical;

        outline: none;

        transition:
            border-color 0.15s ease,
            box-shadow 0.15s ease;
    }

    .ticket-chat-input::placeholder {
        color: #9ca3af;
    }

    .ticket-chat-input:focus {
        border-color: #2563eb;

        box-shadow:
            0 0 0 2px rgba(37, 99, 235, 0.10);
    }


    /* ============================================================
       SEND BUTTON
    ============================================================ */

    .ticket-chat-send {
        display: inline-flex;

        align-items: center;
        justify-content: center;

        width: 42px;
        height: 42px;

        flex: 0 0 42px;

        border: none;
        border-radius: 10px;

        background: #2563eb;
        color: #ffffff;

        cursor: pointer;

        transition:
            background-color 0.15s ease,
            transform 0.1s ease;
    }

    .ticket-chat-send:hover {
        background: #1d4ed8;
    }

    .ticket-chat-send:active {
        transform: scale(0.96);
    }

    .ticket-chat-send:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }


    /* ============================================================
       SEND ICON
    ============================================================ */

    .ticket-chat-send-icon {
        width: 16px;
        height: 16px;

        display: block;

        flex: 0 0 16px;
    }


    /* ============================================================
       HELPER TEXT
    ============================================================ */

    .ticket-chat-helper {
        margin-top: 6px;

        font-size: 10px;

        color: #9ca3af;
    }


    /* ============================================================
       CLOSED
    ============================================================ */

    .ticket-chat-closed {
        margin-top: 14px;

        padding: 12px;

        border: 1px dashed #d1d5db;
        border-radius: 10px;

        text-align: center;

        font-size: 12px;

        color: #6b7280;

        background: #fafafa;
    }


    /* ============================================================
       SCROLLBAR
    ============================================================ */

    .ticket-chat-messages::-webkit-scrollbar {
        width: 5px;
    }

    .ticket-chat-messages::-webkit-scrollbar-track {
        background: transparent;
    }

    .ticket-chat-messages::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 999px;
    }


    /* ============================================================
       DARK MODE
    ============================================================ */

    @media (prefers-color-scheme: dark) {

        .ticket-message-card {
            background: #1f2937;
            border-color: #374151;

            box-shadow:
                0 1px 2px rgba(0, 0, 0, 0.20);
        }

        .ticket-message.mine .ticket-message-card {
            background: #172554;
            border-color: #1e40af;
        }


        .ticket-user-badge {
            background: #374151;
            color: #e5e7eb;
        }

        .ticket-message.mine .ticket-user-badge {
            background: #1e3a8a;
            color: #dbeafe;
        }


        .ticket-user-name {
            color: #e5e7eb;
        }

        .ticket-user-role {
            color: #9ca3af;
        }

        .ticket-message-time {
            color: #9ca3af;
        }


        .ticket-message-content {
            color: #e5e7eb;
        }

        .ticket-message.mine .ticket-message-content {
            color: #dbeafe;
        }


        .ticket-chat-empty {
            background: #111827;
            border-color: #374151;
        }

        .ticket-chat-empty-title {
            color: #d1d5db;
        }

        .ticket-chat-empty-description {
            color: #6b7280;
        }


        .ticket-chat-composer {
            border-color: #374151;
        }


        .ticket-chat-input {
            background: #111827;
            border-color: #374151;
            color: #f9fafb;
        }

        .ticket-chat-input::placeholder {
            color: #6b7280;
        }

        .ticket-chat-input:focus {
            border-color: #3b82f6;

            box-shadow:
                0 0 0 2px rgba(59, 130, 246, 0.15);
        }


        .ticket-chat-closed {
            background: #111827;
            border-color: #374151;
            color: #9ca3af;
        }


        .ticket-chat-messages::-webkit-scrollbar-thumb {
            background: #4b5563;
        }
    }


    /* ============================================================
       MOBILE
    ============================================================ */

    @media (max-width: 640px) {

        .ticket-message-card {
            max-width: 88%;
        }

        .ticket-message-content {
            font-size: 12px;
        }

        .ticket-message-time {
            font-size: 8px;
        }
    }
</style>


<div class="ticket-chat">

    {{-- ============================================================
    HEADER
    ============================================================= --}}

    {{-- <div class="ticket-chat-header">

        <div class="ticket-chat-title">
            Diskusi Tiket
        </div>

        <div class="ticket-chat-description">
            Komunikasi terkait penanganan tiket
        </div>

    </div> --}}


    {{-- ============================================================
    MESSAGE LIST
    ============================================================= --}}

    <div class="ticket-chat-messages">

        @forelse($messages as $message)

            @php
                $isMine = $message->user_id === $currentUser;

                $userName = $message->user?->name ?? 'Unknown User';

                $initial = strtoupper(
                    mb_substr($userName, 0, 1)
                );
            @endphp


            <div class="ticket-message {{ $isMine ? 'mine' : 'other' }}">

                <div class="ticket-message-card">

                    {{-- User --}}
                    <div class="ticket-message-header">

                        <span class="ticket-user-badge">
                            {{ $initial }}
                        </span>

                        <div>

                            <div class="ticket-user-name">
                                {{ $userName }}
                            </div>

                            <div class="ticket-user-role">
                                {{ $message->user?->roles?->first()?->name ?? 'User' }}
                            </div>

                        </div>

                        <span class="ticket-message-time">
                            {{ $message->created_at->translatedFormat('d M Y H:i') }}
                        </span>

                    </div>


                    {{-- Message --}}
                    <div class="ticket-message-content">
                        {{ $message->keterangan }}
                    </div>

                </div>

            </div>

        @empty

            <div class="ticket-chat-empty">

                <div class="ticket-chat-empty-title">
                    Belum ada diskusi
                </div>

                <div class="ticket-chat-empty-description">
                    Belum ada pesan pada tiket ini.
                </div>

            </div>

        @endforelse

    </div>


    {{-- ============================================================
    COMPOSER
    ============================================================= --}}

    @if($record->status !== 'Close')

        <div class="ticket-chat-composer">

            <form wire:submit="sendChatMessage" class="ticket-chat-form">

                <textarea wire:model="chatMessage" class="ticket-chat-input" rows="1"
                    placeholder="Tulis komentar atau informasi terkait tiket..." x-on:keydown.enter="
                        if (!$event.shiftKey) {
                            $event.preventDefault();
                            $el.form.requestSubmit();
                        }">
                    </textarea>


                {{-- SEND BUTTON --}}
                <button type="submit" class="ticket-chat-send" wire:loading.attr="disabled" wire:target="sendChatMessage"
                    title="Kirim pesan">

                    {{-- SVG INLINE, BUKAN COMPONENT ICON --}}
                    <svg class="ticket-chat-send-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                        aria-hidden="true">
                        <path d="M22 2L11 13" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />

                        <path d="M22 2L15 22L11 13L2 9L22 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>

                </button>

            </form>


            <div class="ticket-chat-helper">
                Gunakan diskusi untuk menyampaikan informasi terkait tiket.
            </div>

        </div>

    @else

        <div class="ticket-chat-closed">
            Tiket telah ditutup. Diskusi baru tidak dapat dikirim.
        </div>

    @endif

</div>
