@extends($activeTemplate.'layouts.master_mobile')
@section('main-content')

<!-- Page Header -->
<div class="page-header">
    <h2><i class="fas fa-coins me-2"></i>কমিশন ইতিহাস</h2>
    <button class="filter-toggle" id="filterToggle">
        <i class="fas fa-filter"></i>
    </button>
</div>

<!-- Filter Section -->
<div class="filter-section" id="filterSection">
    <form action="" class="filter-form">
        <div class="filter-row">
            <input type="text" name="search" value="{{ request()->search }}" class="filter-input" placeholder="TRX/ইউজারনেম...">
        </div>
        <div class="filter-row">
            <select name="remark" class="filter-select">
                <option value="">সব ধরণ</option>
                <option value="deposit_commission">ডিপোজিট কমিশন</option>
                <option value="plan_subscribe_commission">প্ল্যান কমিশন</option>
                <option value="ptc_view_commission">বিজ্ঞাপন কমিশন</option>
            </select>
            <select name="level" class="filter-select">
                <option value="">সব লেভেল</option>
                @for($i = 1; $i <= $levels; $i++)
                    <option value="{{ $i }}">লেভেল {{ $i }}</option>
                @endfor
            </select>
        </div>
        <button type="submit" class="filter-btn">
            <i class="fas fa-search me-2"></i>ফিল্টার করুন
        </button>
    </form>
</div>

<!-- Commission List -->
<div class="commission-list">
    @forelse($commissions as $log)
    <div class="commission-item">
        <div class="comm-icon {{ $log->type == 'deposit_commission' ? 'green' : ($log->type == 'plan_subscribe_commission' ? 'purple' : 'orange') }}">
            <i class="fas {{ $log->type == 'deposit_commission' ? 'fa-arrow-down' : ($log->type == 'plan_subscribe_commission' ? 'fa-crown' : 'fa-ad') }}"></i>
        </div>
        <div class="comm-info">
            <h4>
                @if($log->type == 'deposit_commission')
                    ডিপোজিট কমিশন
                @elseif($log->type == 'plan_subscribe_commission')
                    প্ল্যান কমিশন
                @else
                    বিজ্ঞাপন কমিশন
                @endif
            </h4>
            <p>{{ $log->userFrom->username }} • লেভেল {{ $log->level }}</p>
        </div>
        <div class="comm-amount">+৳{{ showAmount($log->amount) }}</div>
    </div>
    @empty
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-coins"></i></div>
        <h4>কোনো কমিশন নেই</h4>
        <p>{{ __($emptyMessage) }}</p>
    </div>
    @endforelse
</div>

@if($commissions->hasPages())
<div class="pagination-wrap">
    {{ $commissions->links() }}
</div>
@endif

<div style="height: 20px;"></div>
@endsection

@push('style')
<style>
.page-header{background:var(--purple);padding:20px;display:flex;justify-content:space-between;align-items:center}
.page-header h2{color:var(--white);font-size:18px;font-weight:700;margin:0;display:flex;align-items:center}
.filter-toggle{width:40px;height:40px;background:var(--crimson);border:none;border-radius:12px;color:var(--white);font-size:16px;cursor:pointer}

.filter-section{display:none;background:var(--white);margin:16px;border-radius:16px;padding:16px;box-shadow:0 2px 10px rgba(0,0,0,0.06)}
.filter-section.active{display:block}
.filter-row{display:flex;gap:10px;margin-bottom:12px}
.filter-input,.filter-select{flex:1;padding:12px 14px;border:1.5px solid #e5e7eb;border-radius:12px;font-size:13px;font-family:inherit;background:var(--white)}
.filter-input:focus,.filter-select:focus{outline:none;border-color:var(--purple)}
.filter-btn{width:100%;padding:12px;background:var(--purple);color:var(--white);border:none;border-radius:12px;font-size:14px;font-weight:600;font-family:inherit;cursor:pointer;display:flex;align-items:center;justify-content:center}

.commission-list{padding:0 16px}
.commission-item{display:flex;align-items:center;gap:14px;background:var(--white);padding:14px;border-radius:14px;margin-bottom:10px;box-shadow:0 2px 8px rgba(0,0,0,0.04)}
.comm-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0}
.comm-icon.green{background:#dcfce7;color:#16a34a}
.comm-icon.purple{background:#f3e8ff;color:var(--purple)}
.comm-icon.orange{background:#fff7ed;color:var(--orange)}
.comm-info{flex:1;min-width:0}
.comm-info h4{font-size:14px;font-weight:600;color:var(--dark-text);margin-bottom:4px}
.comm-info p{font-size:11px;color:var(--gray);margin:0}
.comm-amount{font-size:15px;font-weight:700;color:#16a34a;flex-shrink:0}

.empty-state{text-align:center;padding:50px 20px;background:var(--white);margin:0;border-radius:16px}
.empty-icon{width:70px;height:70px;background:#f3e8ff;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:28px;color:var(--purple)}
.empty-state h4{font-size:16px;font-weight:700;color:var(--dark-text);margin-bottom:6px}
.empty-state p{font-size:13px;color:var(--gray);margin:0}

.pagination-wrap{padding:0 16px;display:flex;justify-content:center}
</style>
@endpush

@push('script')
<script>
document.getElementById('filterToggle').addEventListener('click', function(){
    document.getElementById('filterSection').classList.toggle('active');
});
(function($){
    "use strict"
    $('[name=remark]').val('{{ request()->remark }}');
    $('[name=level]').val('{{ request()->level }}');
})(jQuery);
</script>
@endpush
