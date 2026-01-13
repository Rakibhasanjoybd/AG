@extends($activeTemplate.'layouts.master_mobile')
@section('main-content')

<!-- Ticket Header -->
<div class="ticket-header">
    <div class="ticket-header-info">
        <span class="ticket-status-badge {{ $myTicket->status == 0 ? 'open' : ($myTicket->status == 1 ? 'answered' : ($myTicket->status == 2 ? 'replied' : 'closed')) }}">
            {{ $myTicket->status == 0 ? 'খোলা' : ($myTicket->status == 1 ? 'উত্তর দেওয়া' : ($myTicket->status == 2 ? 'রিপ্লাই' : 'বন্ধ')) }}
        </span>
        <h3>#{{ $myTicket->ticket }}</h3>
        <p>{{ Str::limit($myTicket->subject, 40) }}</p>
    </div>
    @if ($myTicket->status != 3 && $myTicket->user)
    <button class="close-ticket-btn confirmationBtn" data-question="@lang('Are you sure to close this ticket?')" data-action="{{ route('ticket.close', $myTicket->id) }}">
        <i class="fas fa-times"></i>
    </button>
    @endif
</div>

<!-- Reply Form -->
@if ($myTicket->status != 4)
<div class="section-block">
    <form method="post" action="{{ route('ticket.reply', $myTicket->id) }}" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <textarea name="message" class="form-input" rows="3" placeholder="আপনার উত্তর লিখুন...">{{ old('message') }}</textarea>
        </div>

        <div class="form-group">
            <div class="attach-header">
                <span class="form-lbl">সংযুক্তি</span>
                <button type="button" class="add-file-btn addFile">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
            <input type="file" name="attachments[]" class="form-input file-input">
            <div id="fileUploadsContainer"></div>
        </div>

        <button type="submit" class="btn-submit">
            <i class="fas fa-reply me-2"></i>উত্তর দিন
        </button>
    </form>
</div>
@endif

<!-- Messages -->
<div class="messages-container">
    @foreach ($messages as $message)
    <div class="message-bubble {{ $message->admin_id == 0 ? 'user' : 'admin' }}">
        <div class="msg-header">
            <span class="msg-author">{{ $message->admin_id == 0 ? $message->ticket->name : $message->admin->name }}</span>
            @if($message->admin_id != 0)
            <span class="staff-badge">স্টাফ</span>
            @endif
        </div>
        <div class="msg-content">{{ $message->message }}</div>
        @if ($message->attachments->count() > 0)
        <div class="msg-attachments">
            @foreach ($message->attachments as $k => $image)
            <a href="{{ route('ticket.download', encrypt($image->id)) }}" class="attachment-link">
                <i class="fas fa-paperclip"></i> ফাইল {{ ++$k }}
            </a>
            @endforeach
        </div>
        @endif
        <div class="msg-time">{{ $message->created_at->diffForHumans() }}</div>
    </div>
    @endforeach
</div>

<div style="height: 20px;"></div>
<x-confirmation-modal></x-confirmation-modal>
@endsection

@push('style')
<style>
.ticket-header{background:var(--purple);padding:20px;display:flex;justify-content:space-between;align-items:flex-start}
.ticket-header-info h3{color:var(--white);font-size:18px;font-weight:700;margin:8px 0 4px}
.ticket-header-info p{color:rgba(255,255,255,0.7);font-size:13px;margin:0}
.ticket-status-badge{display:inline-block;padding:4px 10px;border-radius:8px;font-size:10px;font-weight:600}
.ticket-status-badge.open{background:#fef9c3;color:#ca8a04}
.ticket-status-badge.answered{background:#dcfce7;color:#16a34a}
.ticket-status-badge.replied{background:#dbeafe;color:#2563eb}
.ticket-status-badge.closed{background:#f3f4f6;color:#6b7280}
.close-ticket-btn{width:36px;height:36px;background:var(--crimson);border:none;border-radius:10px;color:var(--white);font-size:14px;cursor:pointer}

.section-block{background:var(--white);margin:16px;border-radius:20px;padding:16px;box-shadow:0 2px 10px rgba(0,0,0,0.04)}
.form-group{margin-bottom:12px}
.form-lbl{font-size:12px;color:var(--gray);font-weight:500}
.form-input{width:100%;padding:12px;border:1.5px solid #e5e7eb;border-radius:12px;font-size:14px;font-family:inherit;background:var(--white)}
.form-input:focus{outline:none;border-color:var(--purple)}
textarea.form-input{resize:vertical;min-height:80px}

.attach-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px}
.add-file-btn{width:32px;height:32px;background:var(--gold);color:var(--purple);border:none;border-radius:8px;font-size:12px;cursor:pointer}
.file-input{font-size:12px;padding:10px}

.btn-submit{width:100%;padding:12px;background:var(--purple);color:var(--white);border:none;border-radius:12px;font-size:14px;font-weight:700;font-family:inherit;cursor:pointer;display:flex;align-items:center;justify-content:center}

.messages-container{padding:0 16px}
.message-bubble{padding:14px;border-radius:16px;margin-bottom:12px}
.message-bubble.user{background:var(--white);border:1px solid #e5e7eb;margin-left:20px}
.message-bubble.admin{background:#fef9c3;margin-right:20px}
.msg-header{display:flex;align-items:center;gap:8px;margin-bottom:8px}
.msg-author{font-size:13px;font-weight:600;color:var(--dark-text)}
.staff-badge{background:var(--gold);color:var(--purple);padding:2px 8px;border-radius:6px;font-size:10px;font-weight:600}
.msg-content{font-size:14px;color:var(--dark-text);line-height:1.6;margin-bottom:8px}
.msg-attachments{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:8px}
.attachment-link{display:inline-flex;align-items:center;gap:4px;padding:6px 10px;background:#f3e8ff;color:var(--purple);border-radius:8px;font-size:11px;text-decoration:none;font-weight:500}
.msg-time{font-size:11px;color:var(--gray)}

.file-remove-wrap{display:flex;gap:8px;margin-top:8px}
.file-remove-wrap input{flex:1}
.remove-btn{width:38px;background:var(--crimson);border:none;border-radius:10px;color:var(--white);cursor:pointer}
</style>
@endpush

@push('script')
<script>
(function($) {
    "use strict";
    var fileAdded = 0;
    $('.addFile').on('click', function() {
        if (fileAdded >= 4) {
            if(typeof notify === 'function') notify('error', 'সর্বোচ্চ ৫টি ফাইল');
            return false;
        }
        fileAdded++;
        $("#fileUploadsContainer").append(`
            <div class="file-remove-wrap">
                <input type="file" name="attachments[]" class="form-input file-input" required />
                <button type="button" class="remove-btn"><i class="fas fa-times"></i></button>
            </div>
        `);
    });
    $(document).on('click', '.remove-btn', function() {
        fileAdded--;
        $(this).closest('.file-remove-wrap').remove();
    });
})(jQuery);
</script>
@endpush
