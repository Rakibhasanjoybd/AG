@extends($activeTemplate.'layouts.master_mobile')
@section('main-content')

<!-- Page Header -->
<div class="page-header">
    <h2><i class="fas fa-headset me-2"></i>সাপোর্ট টিকেট</h2>
    <a href="{{ route('ticket.open') }}" class="new-ticket-btn">
        <i class="fas fa-plus"></i>
    </a>
</div>

<!-- Tickets List -->
<div class="tickets-container">
    @forelse($supports as $support)
    <a href="{{ route('ticket.view', $support->ticket) }}" class="ticket-card">
        <div class="ticket-icon {{ $support->status == 0 ? 'open' : ($support->status == 1 ? 'answered' : ($support->status == 2 ? 'replied' : 'closed')) }}">
            <i class="fas {{ $support->status == 0 ? 'fa-clock' : ($support->status == 1 ? 'fa-check' : ($support->status == 2 ? 'fa-reply' : 'fa-times')) }}"></i>
        </div>
        <div class="ticket-info">
            <h4>#{{ $support->ticket }} - {{ Str::limit($support->subject, 25) }}</h4>
            <p>
                <span class="priority {{ $support->priority == 3 ? 'high' : ($support->priority == 2 ? 'medium' : 'low') }}">
                    {{ $support->priority == 3 ? 'উচ্চ' : ($support->priority == 2 ? 'মাঝারি' : 'নিম্ন') }}
                </span>
                • {{ \Carbon\Carbon::parse($support->last_reply)->diffForHumans() }}
            </p>
        </div>
        <div class="ticket-status {{ $support->status == 0 ? 'open' : ($support->status == 1 ? 'answered' : ($support->status == 2 ? 'replied' : 'closed')) }}">
            {{ $support->status == 0 ? 'খোলা' : ($support->status == 1 ? 'উত্তর দেওয়া' : ($support->status == 2 ? 'রিপ্লাই' : 'বন্ধ')) }}
        </div>
    </a>
    @empty
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-headset"></i></div>
        <h4>কোনো টিকেট নেই</h4>
        <p>{{ __($emptyMessage) }}</p>
        <a href="{{ route('ticket.open') }}" class="action-btn">
            <i class="fas fa-plus me-2"></i>নতুন টিকেট খুলুন
        </a>
    </div>
    @endforelse
</div>

@if($supports->hasPages())
<div class="pagination-wrap">
    {{ $supports->links() }}
</div>
@endif

<div style="height: 20px;"></div>
@endsection

@push('style')
<style>
.page-header{background:var(--purple);padding:20px;display:flex;justify-content:space-between;align-items:center}
.page-header h2{color:var(--white);font-size:18px;font-weight:700;margin:0;display:flex;align-items:center}
.new-ticket-btn{width:40px;height:40px;background:var(--gold);border:none;border-radius:12px;color:var(--purple);font-size:16px;display:flex;align-items:center;justify-content:center;text-decoration:none}

.tickets-container{padding:16px}
.ticket-card{display:flex;align-items:center;gap:14px;background:var(--white);padding:14px;border-radius:14px;margin-bottom:10px;text-decoration:none;box-shadow:0 2px 8px rgba(0,0,0,0.04)}
.ticket-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0}
.ticket-icon.open{background:#fef9c3;color:#ca8a04}
.ticket-icon.answered{background:#dcfce7;color:#16a34a}
.ticket-icon.replied{background:#dbeafe;color:#2563eb}
.ticket-icon.closed{background:#f3f4f6;color:#6b7280}
.ticket-info{flex:1;min-width:0}
.ticket-info h4{font-size:14px;font-weight:600;color:var(--dark-text);margin-bottom:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.ticket-info p{font-size:11px;color:var(--gray);margin:0;display:flex;align-items:center;gap:6px}
.priority{font-weight:600}
.priority.high{color:var(--crimson)}
.priority.medium{color:var(--orange)}
.priority.low{color:var(--gray)}
.ticket-status{padding:6px 10px;border-radius:8px;font-size:10px;font-weight:600;flex-shrink:0}
.ticket-status.open{background:#fef9c3;color:#ca8a04}
.ticket-status.answered{background:#dcfce7;color:#16a34a}
.ticket-status.replied{background:#dbeafe;color:#2563eb}
.ticket-status.closed{background:#f3f4f6;color:#6b7280}

.empty-state{text-align:center;padding:50px 20px;background:var(--white);border-radius:16px}
.empty-icon{width:70px;height:70px;background:#f3e8ff;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:28px;color:var(--purple)}
.empty-state h4{font-size:16px;font-weight:700;color:var(--dark-text);margin-bottom:6px}
.empty-state p{font-size:13px;color:var(--gray);margin-bottom:16px}
.action-btn{display:inline-flex;align-items:center;padding:12px 20px;background:var(--purple);color:var(--white);border-radius:12px;text-decoration:none;font-size:14px;font-weight:600}

.pagination-wrap{padding:0 16px;display:flex;justify-content:center}
</style>
@endpush
