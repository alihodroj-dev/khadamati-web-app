@foreach($messages as $message)
    @php
        $isStaff = $message->sender_type === 'staff';
    @endphp
    <div class="msg-row {{ $isStaff ? 'staff' : 'citizen' }}" id="message-{{ $message->id }}">
        <div class="msg-bubble {{ $isStaff ? 'staff' : 'citizen' }}">
            <div class="msg-text">{{ $message->message }}</div>
            <div class="msg-meta">
                <span>{{ $message->created_at->format('H:i') }}</span>
                @if($isStaff)
                    @if($message->is_read)
                        <span><i class="ti ti-checks"></i> Read</span>
                    @else
                        <span><i class="ti ti-check"></i></span>
                    @endif
                @endif
            </div>
        </div>
    </div>
@endforeach