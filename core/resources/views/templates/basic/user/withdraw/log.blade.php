@extends($activeTemplate.'layouts.master_mobile')
@section('main-content')

<!-- Page Header -->
<div class="page-header">
    <h2><i class="fas fa-arrow-up me-2"></i>উত্তোলন ইতিহাস</h2>
    <button class="filter-toggle" id="filterToggle">
        <i class="fas fa-search"></i>
    </button>
</div>

<!-- Search Section -->
<div class="filter-section" id="filterSection">
    <form action="">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" name="search" value="{{ request()->search }}" placeholder="ট্রানজেকশন খুঁজুন...">
            <button type="submit">খুঁজুন</button>
        </div>
    </form>
</div>

<!-- Withdraw List -->
<div class="withdraw-list">
    @forelse($withdraws as $withdraw)
    <div class="withdraw-item" onclick="showDetails(this)" 
        data-info="{{ json_encode($withdraw->withdraw_information) }}"
        @if($withdraw->status == 3) data-feedback="{{ $withdraw->admin_feedback }}" @endif
        data-amount="{{ showAmount($withdraw->amount) }}"
        data-charge="{{ showAmount($withdraw->charge) }}"
        data-receivable="{{ showAmount($withdraw->amount - $withdraw->charge) }}"
        data-rate="{{ showAmount($withdraw->rate) }}"
        data-currency="{{ $withdraw->currency }}"
        data-final="{{ showAmount($withdraw->final_amount) }}"
        data-verify-path="{{ asset(getFilePath('verify')) }}">
        data-verify-path="{{ asset(getFilePath('verify')) }}">
        <div class="wd-icon {{ $withdraw->status == 1 ? 'success' : ($withdraw->status == 2 ? 'pending' : 'failed') }}">
            <i class="fas {{ $withdraw->status == 1 ? 'fa-check' : ($withdraw->status == 2 ? 'fa-clock' : 'fa-times') }}"></i>
        </div>
        <div class="wd-info">
            <h4>{{ @$withdraw->method->name ?? 'Unknown' }}</h4>
            <p>{{ $withdraw->trx }} • {{ diffForHumans($withdraw->created_at) }}</p>
        </div>
        <div class="wd-amount">
            <span class="amount">৳{{ showAmount($withdraw->amount - $withdraw->charge) }}</span>
            <span class="status {{ $withdraw->status == 1 ? 'success' : ($withdraw->status == 2 ? 'pending' : 'failed') }}">
                {{ $withdraw->status == 1 ? 'সফল' : ($withdraw->status == 2 ? 'পেন্ডিং' : 'বাতিল') }}
            </span>
        </div>
    </div>
    @empty
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-arrow-up"></i></div>
        <h4>কোনো উত্তোলন নেই</h4>
        <p>{{ __($emptyMessage) }}</p>
        <a href="{{ route('user.withdraw') }}" class="action-btn">
            <i class="fas fa-plus me-2"></i>উত্তোলন করুন
        </a>
    </div>
    @endforelse
</div>

@if($withdraws->hasPages())
<div class="pagination-wrap">
    {{ $withdraws->links() }}
</div>
@endif

<div style="height: 20px;"></div>

<!-- Detail Modal -->
<div class="modal-overlay" id="detailModal">
    <div class="modal-box">
        <div class="modal-head">
            <h3>বিস্তারিত তথ্য</h3>
            <button onclick="closeModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body" id="modalContent"></div>
    </div>
</div>
@endsection

@push('style')
<style>
.page-header{background:linear-gradient(135deg,#16a34a 0%,#059669 100%);padding:20px;display:flex;justify-content:space-between;align-items:center}
.page-header h2{color:var(--white);font-size:18px;font-weight:700;margin:0;display:flex;align-items:center}
.filter-toggle{width:40px;height:40px;background:rgba(255,255,255,0.2);border:none;border-radius:12px;color:var(--white);font-size:16px;cursor:pointer}

.filter-section{display:none;background:var(--white);margin:16px;border-radius:16px;padding:12px;box-shadow:0 2px 10px rgba(0,0,0,0.06)}
.filter-section.active{display:block}
.search-box{display:flex;align-items:center;gap:10px;background:#f8f9fa;padding:10px 14px;border-radius:12px}
.search-box i{color:var(--gray);font-size:14px}
.search-box input{flex:1;border:none;background:none;font-size:14px;font-family:inherit}
.search-box input:focus{outline:none}
.search-box button{background:#16a34a;color:var(--white);border:none;padding:8px 16px;border-radius:10px;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer}

.withdraw-list{padding:0 16px}
.withdraw-item{display:flex;align-items:center;gap:14px;background:var(--white);padding:14px;border-radius:14px;margin-bottom:10px;box-shadow:0 2px 8px rgba(0,0,0,0.04);cursor:pointer}
.wd-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0}
.wd-icon.success{background:#dcfce7;color:#16a34a}
.wd-icon.pending{background:#fef9c3;color:var(--gold)}
.wd-icon.failed{background:#fce7f3;color:var(--crimson)}
.wd-info{flex:1;min-width:0}
.wd-info h4{font-size:14px;font-weight:600;color:var(--dark-text);margin-bottom:4px}
.wd-info p{font-size:11px;color:var(--gray);margin:0}
.wd-amount{text-align:right}
.wd-amount .amount{display:block;font-size:15px;font-weight:700;color:#16a34a}
.wd-amount .status{font-size:10px;font-weight:600;padding:3px 8px;border-radius:6px}
.wd-amount .status.success{background:#dcfce7;color:#16a34a}
.wd-amount .status.pending{background:#fef9c3;color:#ca8a04}
.wd-amount .status.failed{background:#fce7f3;color:var(--crimson)}

.empty-state{text-align:center;padding:50px 20px;background:var(--white);margin:0;border-radius:16px}
.empty-icon{width:70px;height:70px;background:#dcfce7;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:28px;color:#16a34a}
.empty-state h4{font-size:16px;font-weight:700;color:var(--dark-text);margin-bottom:6px}
.empty-state p{font-size:13px;color:var(--gray);margin-bottom:16px}
.action-btn{display:inline-flex;align-items:center;padding:12px 20px;background:#16a34a;color:var(--white);border-radius:12px;text-decoration:none;font-size:14px;font-weight:600}

.pagination-wrap{padding:0 16px;display:flex;justify-content:center}

.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.5);display:none;align-items:flex-end;justify-content:center;z-index:1000}
.modal-overlay.active{display:flex}
.modal-box{background:var(--white);width:100%;max-width:480px;border-radius:20px 20px 0 0;padding:20px;max-height:70vh;overflow-y:auto}
.modal-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px}
.modal-head h3{font-size:16px;font-weight:700;color:var(--dark-text);margin:0}
.modal-head button{width:32px;height:32px;background:#f8f9fa;border:none;border-radius:10px;color:var(--gray);font-size:14px;cursor:pointer}
.modal-body .detail-row{display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f0f0f0}
.modal-body .detail-row:last-child{border-bottom:none}
.modal-body .summary-section{background:#f8f9fa;border-radius:12px;padding:12px;margin-bottom:16px}
.modal-body .summary-row{display:flex;justify-content:space-between;padding:6px 0;font-size:13px}
.modal-body .summary-row span{color:var(--gray)}
.modal-body .summary-row strong{color:var(--dark-text)}
.modal-body .summary-row.highlight{margin-top:8px;padding-top:10px;border-top:1px dashed #e5e7eb}
.modal-body .summary-row.highlight strong{color:#16a34a;font-size:15px}
.modal-body .feedback{margin-top:16px;padding:12px;background:#fce7f3;border-radius:12px;font-size:12px;color:var(--crimson)}
.modal-body .image-box{margin:12px 0;padding:12px;background:#f8f9fa;border-radius:12px;text-align:center}
.modal-body .image-box img{max-width:100%;height:auto;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.1)}
</style>
@endpush

@push('script')
<script>
document.getElementById('filterToggle').addEventListener('click', function(){
    document.getElementById('filterSection').classList.toggle('active');
});

function showDetails(el) {
    var info = JSON.parse(el.dataset.info || '[]');
    var feedback = el.dataset.feedback || '';
    var amount = el.dataset.amount;
    var charge = el.dataset.charge;
    var receivable = el.dataset.receivable;
    var rate = el.dataset.rate;
    var currency = el.dataset.currency;
    var final = el.dataset.final;
    var verifyPath = el.dataset.verifyPath;
    
    var html = '<div class="summary-section">';
    html += '<div class="summary-row"><span>উত্তোলন</span><strong>৳' + amount + '</strong></div>';
    html += '<div class="summary-row"><span>চার্জ</span><strong style="color:var(--orange)">৳' + charge + '</strong></div>';
    html += '<div class="summary-row highlight"><span>প্রাপ্য</span><strong>৳' + receivable + '</strong></div>';
    if(rate != '1.00') {
        html += '<div class="summary-row"><span>' + currency + ' এ</span><strong>' + final + ' ' + currency + '</strong></div>';
    }
    html += '</div>';

    if(info.length > 0) {
        html += '<div style="font-weight:600;margin-bottom:10px;font-size:13px;">প্রদত্ত তথ্য</div>';
        info.forEach(function(item){
            if(item.type == 'file') {
                html += '<div class="detail-row"><span>' + item.name + '</span></div>';
                html += '<div class="image-box"><img src="' + verifyPath + '/' + item.value + '" alt="' + item.name + '" /></div>';
            } else {
                html += '<div class="detail-row"><span>' + item.name + '</span><strong>' + item.value + '</strong></div>';
            }
        });
    }

    if(feedback) {
        html += '<div class="feedback"><strong>অ্যাডমিন মন্তব্য:</strong><br>' + feedback + '</div>';
    }

    document.getElementById('modalContent').innerHTML = html;
    document.getElementById('detailModal').classList.add('active');
}

function closeModal() {
    document.getElementById('detailModal').classList.remove('active');
}

document.getElementById('detailModal').addEventListener('click', function(e){
    if(e.target === this) closeModal();
});
</script>
@endpush
