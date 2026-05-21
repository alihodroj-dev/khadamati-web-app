@extends('layouts.app')

@section('content')
<style>
    /* ===== Chat-specific styles ===== */
    .chat-shell {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 2px rgba(16, 24, 40, 0.04), 0 4px 16px rgba(16, 24, 40, 0.04);
        overflow: hidden;
    }

    .chat-header {
        padding: 20px 24px;
        background: linear-gradient(180deg, #fbfcfe 0%, #ffffff 100%);
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
    }

    .chat-header__left { display: flex; align-items: center; gap: 14px; }

    .avatar {
        width: 44px; height: 44px;
        border-radius: 50%;
        background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
        color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-weight: 600; font-size: 16px;
        flex-shrink: 0;
        box-shadow: 0 2px 6px rgba(30, 58, 138, 0.25);
    }

    .chat-title { font-size: 17px; font-weight: 700; color: #111827; line-height: 1.2; }
    .chat-subtitle {
        font-size: 12px; color: #6b7280; margin-top: 4px;
        display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
    }
    .chat-ref {
        background: #f3f4f6; padding: 2px 8px; border-radius: 6px;
        font-family: ui-monospace, "SF Mono", Menlo, monospace;
        font-size: 11px; color: #374151;
    }

    .status-dot {
        display: inline-flex; align-items: center; gap: 6px;
        font-size: 12px; font-weight: 500;
    }
    .status-dot::before {
        content: ""; width: 8px; height: 8px; border-radius: 50%;
    }
    .status-active { color: #047857; }
    .status-active::before {
        background: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.18);
        animation: pulse 2s infinite;
    }
    .status-closed { color: #6b7280; }
    .status-closed::before { background: #9ca3af; }

    @keyframes pulse {
        0%, 100% { box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.18); }
        50%      { box-shadow: 0 0 0 6px rgba(16, 185, 129, 0.05); }
    }

    .btn-close {
        padding: 8px 14px;
        background: #fff; color: #dc2626;
        border: 1px solid #fecaca;
        border-radius: 8px; font-size: 13px; font-weight: 500;
        cursor: pointer; transition: all 0.15s ease;
        display: inline-flex; align-items: center; gap: 6px;
    }
    .btn-close:hover { background: #fef2f2; border-color: #fca5a5; }

    .badge-closed {
        padding: 6px 12px; background: #f3f4f6; color: #6b7280;
        border-radius: 8px; font-size: 12px; font-weight: 500;
    }

    /* ===== Messages area ===== */
    .messages-area {
        height: 520px;
        overflow-y: auto;
        padding: 24px;
        background:
            radial-gradient(circle at 20% 10%, rgba(59, 130, 246, 0.04) 0, transparent 40%),
            radial-gradient(circle at 80% 90%, rgba(30, 58, 138, 0.04) 0, transparent 40%),
            #fafbfc;
        scroll-behavior: smooth;
    }
    .messages-area::-webkit-scrollbar { width: 6px; }
    .messages-area::-webkit-scrollbar-track { background: transparent; }
    .messages-area::-webkit-scrollbar-thumb {
        background: #d1d5db; border-radius: 3px;
    }
    .messages-area::-webkit-scrollbar-thumb:hover { background: #9ca3af; }

    /* ===== Message bubbles ===== */
    .msg-row { display: flex; margin-bottom: 14px; animation: msgIn 0.25s ease-out; }
    .msg-row.staff { justify-content: flex-end; }
    .msg-row.citizen { justify-content: flex-start; }

    @keyframes msgIn {
        from { opacity: 0; transform: translateY(6px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .msg-bubble {
        max-width: 70%;
        padding: 10px 14px;
        border-radius: 16px;
        font-size: 14px; line-height: 1.45;
        word-wrap: break-word;
        position: relative;
        box-shadow: 0 1px 2px rgba(16, 24, 40, 0.06);
    }
    .msg-bubble.staff {
        background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
        color: #fff;
        border-bottom-right-radius: 4px;
    }
    .msg-bubble.citizen {
        background: #fff;
        color: #1f2937;
        border: 1px solid #e5e7eb;
        border-bottom-left-radius: 4px;
    }

    .msg-text { white-space: pre-wrap; }

    .msg-meta {
        font-size: 11px;
        margin-top: 5px;
        display: flex; align-items: center; gap: 5px;
        opacity: 0.85;
    }
    .msg-bubble.staff   .msg-meta { color: #bfdbfe; justify-content: flex-end; }
    .msg-bubble.citizen .msg-meta { color: #9ca3af; }

    /* ===== Day separator ===== */
    .day-divider {
        display: flex; align-items: center; gap: 12px;
        margin: 18px 0 14px;
    }
    .day-divider::before, .day-divider::after {
        content: ""; flex: 1; height: 1px; background: #e5e7eb;
    }
    .day-divider span {
        font-size: 11px; font-weight: 500; color: #6b7280;
        text-transform: uppercase; letter-spacing: 0.5px;
        background: #fff; padding: 4px 10px;
        border-radius: 999px; border: 1px solid #e5e7eb;
    }

    /* ===== Empty state ===== */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #9ca3af;
    }
    .empty-state__icon {
        width: 64px; height: 64px;
        margin: 0 auto 14px;
        border-radius: 50%;
        background: linear-gradient(135deg, #e0e7ff 0%, #f3f4f6 100%);
        display: flex; align-items: center; justify-content: center;
        color: #6366f1; font-size: 28px;
    }
    .empty-state__title { color: #374151; font-weight: 600; margin-bottom: 4px; }
    .empty-state__sub { font-size: 13px; }

    /* ===== Typing indicator ===== */
    .typing-row { display: none; margin-bottom: 10px; }
    .typing-row.show { display: flex; }
    .typing-bubble {
        background: #fff; border: 1px solid #e5e7eb;
        padding: 10px 14px; border-radius: 16px;
        border-bottom-left-radius: 4px;
        display: flex; gap: 4px; align-items: center;
    }
    .typing-bubble span {
        width: 6px; height: 6px; border-radius: 50%;
        background: #9ca3af; display: inline-block;
        animation: typingDot 1.2s infinite ease-in-out;
    }
    .typing-bubble span:nth-child(2) { animation-delay: 0.15s; }
    .typing-bubble span:nth-child(3) { animation-delay: 0.3s; }
    @keyframes typingDot {
        0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
        30%           { transform: translateY(-4px); opacity: 1; }
    }

    /* ===== Composer ===== */
    .composer {
        padding: 16px 20px;
        background: #fff;
        border-top: 1px solid #e5e7eb;
    }
    .composer__row {
        display: flex; gap: 10px; align-items: flex-end;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 8px;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .composer__row:focus-within {
        border-color: #3b82f6;
        background: #fff;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.10);
    }
    .composer__input {
        flex: 1;
        border: none; outline: none; resize: none;
        background: transparent;
        padding: 8px 10px;
        font-size: 14px; line-height: 1.45;
        font-family: inherit;
        max-height: 140px; min-height: 24px;
        color: #1f2937;
    }
    .composer__input::placeholder { color: #9ca3af; }

    .btn-send {
        height: 40px;
        padding: 0 18px;
        background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
        color: #fff;
        border: none; border-radius: 10px;
        font-size: 14px; font-weight: 500;
        cursor: pointer;
        display: inline-flex; align-items: center; gap: 6px;
        transition: transform 0.1s ease, box-shadow 0.15s ease, opacity 0.15s ease;
        box-shadow: 0 2px 6px rgba(30, 58, 138, 0.3);
        flex-shrink: 0;
    }
    .btn-send:hover  { transform: translateY(-1px); box-shadow: 0 4px 10px rgba(30, 58, 138, 0.4); }
    .btn-send:active { transform: translateY(0); }
    .btn-send:disabled {
        opacity: 0.6; cursor: not-allowed; transform: none;
        box-shadow: 0 2px 6px rgba(30, 58, 138, 0.2);
    }

    .composer-hint {
        font-size: 11px; color: #9ca3af; margin-top: 8px; padding: 0 4px;
    }

    .back-link {
        color: #1e40af; font-size: 14px; font-weight: 500;
        display: inline-flex; align-items: center; gap: 6px;
        text-decoration: none;
        transition: gap 0.15s ease;
    }
    .back-link:hover { gap: 10px; }

    .btn-reopen {
        padding: 8px 14px;
        background: #fff; color: #047857;
        border: 1px solid #a7f3d0;
        border-radius: 8px; font-size: 13px; font-weight: 500;
        cursor: pointer; transition: all 0.15s ease;
        display: inline-flex; align-items: center; gap: 6px;
    }
    .btn-reopen:hover { background: #ecfdf5; border-color: #6ee7b7; }

    @media (max-width: 640px) {
        .msg-bubble { max-width: 85%; }
        .messages-area { height: 60vh; padding: 16px; }
        .chat-header { padding: 14px 16px; }
        .composer { padding: 12px; }
    }
</style>

<div class="container mx-auto px-4">
    <div class="mb-4">
        <a href="{{ route('staff.conversations.index') }}" class="back-link">
            <i class="ti ti-arrow-left"></i> Back to conversations
        </a>
    </div>

    <div class="chat-shell">
        {{-- Header --}}
        <div class="chat-header">
            <div class="chat-header__left">
                @php
                    $name = $conversation->citizen->name ?? $conversation->citizen->email;
                    $initials = collect(explode(' ', $name))->map(fn($p) => mb_substr($p, 0, 1))->take(2)->implode('');
                    $initials = mb_strtoupper($initials ?: 'U');
                @endphp
                <div class="avatar">{{ $initials }}</div>
                <div>
                    <div class="chat-title">{{ $name }}</div>
                    <div class="chat-subtitle">
                        <span class="chat-ref">#{{ $conversation->serviceRequest->reference_number }}</span>
                        @if($conversation->status === 'active')
                            <span class="status-dot status-active">Active</span>
                        @else
                            <form method="POST" action="{{ route('staff.conversations.reopen', $conversation->id) }}"
                                onsubmit="return confirm('Reopen this conversation? The citizen will be able to send messages again.')">
                                @csrf
                                <button type="submit" class="btn-reopen">
                                    <i class="ti ti-lock-open"></i> Reopen conversation
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            @if($conversation->status === 'active')
                <form method="POST" action="{{ route('staff.conversations.close', $conversation->id) }}"
                      onsubmit="return confirm('Close this conversation? The citizen will not be able to send new messages.')">
                    @csrf
                    <button type="submit" class="btn-close">
                        <i class="ti ti-lock"></i> Close conversation
                    </button>
                </form>
            @else
                <span class="badge-closed">
                    <i class="ti ti-lock"></i> Closed
                </span>
            @endif
        </div>

        {{-- Messages Area --}}
        <div id="messages-container" class="messages-area">
            @if($messages->isEmpty())
                <div class="empty-state" id="empty-state">
                    <div class="empty-state__icon"><i class="ti ti-messages"></i></div>
                    <div class="empty-state__title">No messages yet</div>
                    <div class="empty-state__sub">Send a message to start the conversation.</div>
                </div>
            @else
                @include('staff.conversations.partials.messages', ['messages' => $messages])
            @endif

            {{-- Typing indicator (hidden by default) --}}
            <div class="typing-row" id="typing-indicator">
                <div class="typing-bubble">
                    <span></span><span></span><span></span>
                </div>
            </div>
        </div>

        {{-- Send Message Form --}}
        @if($conversation->status === 'active')
        <div class="composer">
            <form method="POST" action="{{ route('staff.conversations.send', $conversation->id) }}" id="message-form">
                @csrf
                <div class="composer__row">
                    <textarea
                        name="message"
                        id="message-input"
                        rows="1"
                        class="composer__input"
                        placeholder="Type your message here…"
                        required
                    ></textarea>
                    <button type="submit" id="send-btn" class="btn-send">
                        <i class="ti ti-send"></i> Send
                    </button>
                </div>
            </form>
            <div class="composer-hint">
                Press <strong>Enter</strong> to send · <strong>Shift + Enter</strong> for a new line
            </div>
        </div>
        @endif
    </div>
</div>

<script>
    // Configuration
    const conversationId = {{ $conversation->id }};
    let lastMessageId = {{ $messages->isNotEmpty() ? $messages->last()->id : 0 }};
    let isPolling = true;
    let isSending = false;
    let typingTimeout = null;
    const textarea = document.getElementById('message-input');
    const emptyStateEl = document.getElementById('empty-state');

    // Track which message IDs we already have (to prevent duplicates)
    let existingMessageIds = new Set();

    @foreach($messages as $message)
        existingMessageIds.add({{ $message->id }});
    @endforeach

    function scrollToBottom() {
        const container = document.getElementById('messages-container');
        if (container) container.scrollTop = container.scrollHeight;
    }

    function formatTime(timestamp) {
        const date = new Date(timestamp);
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    function removeEmptyState() {
        const el = document.getElementById('empty-state');
        if (el) el.remove();
    }

    function addMessage(message, isStaff) {
        if (existingMessageIds.has(message.id)) {
            console.log('Skipping duplicate message:', message.id);
            return false;
        }

        const container = document.getElementById('messages-container');
        if (!container) return false;

        removeEmptyState();

        const messageDiv = document.createElement('div');
        messageDiv.className = `msg-row ${isStaff ? 'staff' : 'citizen'}`;
        messageDiv.id = `message-${message.id}`;

        const readMark = (message.is_read && isStaff)
            ? '<i class="ti ti-checks"></i> Read'
            : (isStaff ? '<i class="ti ti-check"></i>' : '');

        messageDiv.innerHTML = `
            <div class="msg-bubble ${isStaff ? 'staff' : 'citizen'}">
                <div class="msg-text">${escapeHtml(message.message)}</div>
                <div class="msg-meta">
                    <span>${formatTime(message.created_at)}</span>
                    ${readMark ? `<span>${readMark}</span>` : ''}
                </div>
            </div>
        `;

        // Insert before typing indicator if present
        const typing = document.getElementById('typing-indicator');
        if (typing) container.insertBefore(messageDiv, typing);
        else container.appendChild(messageDiv);

        existingMessageIds.add(message.id);
        if (message.id > lastMessageId) lastMessageId = message.id;

        scrollToBottom();
        return true;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function showTyping(show) {
        const el = document.getElementById('typing-indicator');
        if (!el) return;
        if (show) {
            el.classList.add('show');
            scrollToBottom();
        } else {
            el.classList.remove('show');
        }
    }

    async function pollNewMessages() {
        if (!isPolling) return;

        try {
            const response = await fetch(`/staff/conversations/${conversationId}/poll?last_message_id=${lastMessageId}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (!response.ok) return;
            const result = await response.json();

            if (result.success && result.data && result.data.messages && result.data.messages.length > 0) {
                result.data.messages.forEach(message => {
                    if (message.sender_type !== 'staff') {
                        addMessage(message, false);
                    } else {
                        if (!existingMessageIds.has(message.id)) existingMessageIds.add(message.id);
                        if (message.id > lastMessageId) lastMessageId = message.id;
                    }
                });
            }

            // Optional: handle a `typing` flag if the backend returns one
            if (result.data && typeof result.data.citizen_typing === 'boolean') {
                showTyping(result.data.citizen_typing);
            }
        } catch (error) {
            console.error('Polling error:', error);
        }
    }

    async function sendMessage(message) {
        if (isSending || !message.trim()) return false;

        isSending = true;
        const sendBtn = document.getElementById('send-btn');
        const originalHtml = sendBtn.innerHTML;
        sendBtn.innerHTML = '<i class="ti ti-loader-2"></i> Sending…';
        sendBtn.disabled = true;

        try {
            const response = await fetch(`/staff/conversations/${conversationId}/messages`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ message: message })
            });

            const result = await response.json();

            if (response.status === 201 && result.success) {
                addMessage(result.data, true);
                if (textarea) {
                    textarea.value = '';
                    autoResize();
                }
                return true;
            } else {
                alert('Failed to send message: ' + (result.message || 'Unknown error'));
                return false;
            }
        } catch (error) {
            console.error('Send error:', error);
            alert('Failed to send message. Please try again.');
            return false;
        } finally {
            isSending = false;
            if (sendBtn) { sendBtn.innerHTML = originalHtml; sendBtn.disabled = false; }
        }
    }

    async function sendTypingIndicator(isTyping) {
        try {
            await fetch(`/staff/conversations/${conversationId}/typing`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ is_typing: isTyping })
            });
        } catch (error) { /* silent */ }
    }

    // Auto-resize textarea
    function autoResize() {
        if (!textarea) return;
        textarea.style.height = 'auto';
        textarea.style.height = Math.min(textarea.scrollHeight, 140) + 'px';
    }

    // Init
    scrollToBottom();
    setInterval(() => pollNewMessages(), 3000);

    const form = document.getElementById('message-form');
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const message = textarea ? textarea.value.trim() : '';
            if (message) await sendMessage(message);
        });
    }

    if (textarea) {
        let isCurrentlyTyping = false;

        textarea.addEventListener('input', () => {
            autoResize();
            if (!isCurrentlyTyping) {
                isCurrentlyTyping = true;
                sendTypingIndicator(true);
            }
            clearTimeout(typingTimeout);
            typingTimeout = setTimeout(() => {
                isCurrentlyTyping = false;
                sendTypingIndicator(false);
            }, 1000);
        });

        // Enter to send, Shift+Enter for newline
        textarea.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                const message = textarea.value.trim();
                if (message) sendMessage(message);
            }
        });
    }
</script>
@endsection