@extends($activeTemplate.'layouts.master_mobile')
@section('main-content')

<!-- Page Header -->
<div class="page-header">
    <h2><i class="fas fa-plus-circle me-2"></i>ডিপোজিট ইতিহাস</h2>
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

<!-- Deposit List -->
<div class="deposit-list">
    @forelse($deposits as $deposit)
    <div class="deposit-item" @if($deposit->method_code >= 1000) onclick="showDetails(this)" data-info="{{ ($deposit->detail != null) ? json_encode($deposit->detail) : '[]' }}" @if($deposit->status == 3) data-feedback="{{ $deposit->admin_feedback }}" @endif @endif>
        <div class="dep-icon {{ $deposit->status == 1 ? 'success' : ($deposit->status == 2 ? 'pending' : 'failed') }}">
            <i class="fas {{ $deposit->status == 1 ? 'fa-check' : ($deposit->status == 2 ? 'fa-clock' : 'fa-times') }}"></i>
        </div>
        <div class="dep-info">
            <h4>{{ $deposit->gateway?->name ?? 'Unknown' }}</h4>
            <p>{{ $deposit->trx }} • {{ diffForHumans($deposit->created_at) }}</p>
        </div>
        <div class="dep-amount">
            <span class="amount">৳{{ showAmount($deposit->amount) }}</span>
            <span class="status {{ $deposit->status == 1 ? 'success' : ($deposit->status == 2 ? 'pending' : 'failed') }}">
                {{ $deposit->status == 1 ? 'সফল' : ($deposit->status == 2 ? 'পেন্ডিং' : 'বাতিল') }}
            </span>
        </div>
    </div>
    @empty
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-plus-circle"></i></div>
        <h4>কোনো ডিপোজিট নেই</h4>
        <p>{{ __($emptyMessage) }}</p>
        <a href="{{ route('user.deposit') }}" class="action-btn">
            <i class="fas fa-plus me-2"></i>ডিপোজিট করুন
        </a>
    </div>
    @endforelse
</div>

@if($deposits->hasPages())
<div class="pagination-wrap">
    {{ $deposits->links() }}
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
.page-header{background:var(--purple);padding:20px;display:flex;justify-content:space-between;align-items:center}
.page-header h2{color:var(--white);font-size:18px;font-weight:700;margin:0;display:flex;align-items:center}
.filter-toggle{width:40px;height:40px;background:var(--crimson);border:none;border-radius:12px;color:var(--white);font-size:16px;cursor:pointer}

.filter-section{display:none;background:var(--white);margin:16px;border-radius:16px;padding:12px;box-shadow:0 2px 10px rgba(0,0,0,0.06)}
.filter-section.active{display:block}
.search-box{display:flex;align-items:center;gap:10px;background:#f8f9fa;padding:10px 14px;border-radius:12px}
.search-box i{color:var(--gray);font-size:14px}
.search-box input{flex:1;border:none;background:none;font-size:14px;font-family:inherit}
.search-box input:focus{outline:none}
.search-box button{background:var(--purple);color:var(--white);border:none;padding:8px 16px;border-radius:10px;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer}

.deposit-list{padding:0 16px}
.deposit-item{display:flex;align-items:center;gap:14px;background:var(--white);padding:14px;border-radius:14px;margin-bottom:10px;box-shadow:0 2px 8px rgba(0,0,0,0.04);cursor:pointer}
.dep-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0}
.dep-icon.success{background:#dcfce7;color:#16a34a}
.dep-icon.pending{background:#fef9c3;color:var(--gold)}
.dep-icon.failed{background:#fce7f3;color:var(--crimson)}
.dep-info{flex:1;min-width:0}
.dep-info h4{font-size:14px;font-weight:600;color:var(--dark-text);margin-bottom:4px}
.dep-info p{font-size:11px;color:var(--gray);margin:0}
.dep-amount{text-align:right}
.dep-amount .amount{display:block;font-size:15px;font-weight:700;color:var(--dark-text)}
.dep-amount .status{font-size:10px;font-weight:600;padding:3px 8px;border-radius:6px}
.dep-amount .status.success{background:#dcfce7;color:#16a34a}
.dep-amount .status.pending{background:#fef9c3;color:#ca8a04}
.dep-amount .status.failed{background:#fce7f3;color:var(--crimson)}

.empty-state{text-align:center;padding:50px 20px;background:var(--white);margin:0;border-radius:16px}
.empty-icon{width:70px;height:70px;background:#f3e8ff;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:28px;color:var(--purple)}
.empty-state h4{font-size:16px;font-weight:700;color:var(--dark-text);margin-bottom:6px}
.empty-state p{font-size:13px;color:var(--gray);margin-bottom:16px}
.action-btn{display:inline-flex;align-items:center;padding:12px 20px;background:var(--purple);color:var(--white);border-radius:12px;text-decoration:none;font-size:14px;font-weight:600}

.pagination-wrap{padding:0 16px;display:flex;justify-content:center}

.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.5);display:none;align-items:flex-end;justify-content:center;z-index:1000}
.modal-overlay.active{display:flex}
.modal-box{background:var(--white);width:100%;max-width:480px;border-radius:20px 20px 0 0;padding:20px;max-height:70vh;overflow-y:auto}
.modal-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px}
.modal-head h3{font-size:16px;font-weight:700;color:var(--dark-text);margin:0}
.modal-head button{width:32px;height:32px;background:#f8f9fa;border:none;border-radius:10px;color:var(--gray);font-size:14px;cursor:pointer}
.modal-body .detail-row{display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f0f0f0}
.modal-body .detail-row:last-child{border-bottom:none}
.modal-body .feedback{margin-top:16px;padding:12px;background:#fce7f3;border-radius:12px;font-size:12px;color:var(--crimson)}
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
    var html = '';

    if(info.length > 0) {
        info.forEach(function(item){
            if(item.type != 'file') {
                html += '<div class="detail-row"><span>' + item.name + '</span><strong>' + item.value + '</strong></div>';
            }
        });
    }

    if(feedback) {
        html += '<div class="feedback"><strong>অ্যাডমিন মন্তব্য:</strong><br>' + feedback + '</div>';
    }

    if(html) {
        document.getElementById('modalContent').innerHTML = html;
        document.getElementById('detailModal').classList.add('active');
    }
}

function closeModal() {
    document.getElementById('detailModal').classList.remove('active');
}

document.getElementById('detailModal').addEventListener('click', function(e){
    if(e.target === this) closeModal();
});
</script>
@endpush
