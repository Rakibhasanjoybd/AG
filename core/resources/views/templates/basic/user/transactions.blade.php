@extends($activeTemplate.'layouts.master_mobile')
@section('main-content')

<!-- Page Header -->
<div class="trx-page-header">
    <div class="trx-header-content">
        <div class="trx-header-icon">
            <i class="fas fa-exchange-alt"></i>
        </div>
        <div class="trx-header-text">
            <h2>লেনদেনের ইতিহাস</h2>
            <p>আপনার সকল লেনদেন দেখুন</p>
        </div>
    </div>
    <button class="trx-filter-toggle" id="filterToggle">
        <i class="fas fa-sliders-h"></i>
    </button>
</div>

<!-- Summary Cards -->
<div class="trx-summary-section">
    <div class="trx-summary-card credit-card">
        <div class="trx-summary-icon">
            <i class="fas fa-arrow-down"></i>
        </div>
        <div class="trx-summary-info">
            <span class="trx-summary-label">মোট জমা</span>
            <span class="trx-summary-value">৳{{ showAmount($transactions->where('trx_type', '+')->sum('amount')) }}</span>
        </div>
    </div>
    <div class="trx-summary-card debit-card">
        <div class="trx-summary-icon">
            <i class="fas fa-arrow-up"></i>
        </div>
        <div class="trx-summary-info">
            <span class="trx-summary-label">মোট খরচ</span>
            <span class="trx-summary-value">৳{{ showAmount($transactions->where('trx_type', '-')->sum('amount')) }}</span>
        </div>
    </div>
</div>

<!-- Filter Section -->
<div class="trx-filter-section" id="filterSection">
    <div class="trx-filter-header">
        <i class="fas fa-filter"></i>
        <span>ফিল্টার অপশন</span>
    </div>
    <form action="" class="trx-filter-form">
        <div class="trx-filter-group">
            <label class="trx-filter-label">ট্রানজেকশন নম্বর</label>
            <div class="trx-input-wrap">
                <i class="fas fa-search"></i>
                <input type="text" name="search" value="{{ request()->search }}" class="trx-filter-input" placeholder="খুঁজুন...">
            </div>
        </div>
        <div class="trx-filter-row">
            <div class="trx-filter-group">
                <label class="trx-filter-label">ধরণ</label>
                <select name="type" class="trx-filter-select">
                    <option value="">সব ধরণ</option>
                    <option value="+" @selected(request()->type == '+')>জমা (+)</option>
                    <option value="-" @selected(request()->type == '-')>খরচ (-)</option>
                </select>
            </div>
            <div class="trx-filter-group">
                <label class="trx-filter-label">রিমার্ক</label>
                <select name="remark" class="trx-filter-select">
                    <option value="">সব রিমার্ক</option>
                    @foreach ($remarks as $remark)
                        <option value="{{ $remark->remark }}" @selected(request()->remark == $remark->remark)>{{ __(keyToTitle($remark->remark)) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <button type="submit" class="trx-filter-btn">
            <i class="fas fa-search me-2"></i>ফিল্টার করুন
        </button>
    </form>
</div>

<!-- Transactions List -->
<div class="trx-list-section">
    <div class="trx-list-header">
        <span class="trx-list-title">সাম্প্রতিক লেনদেন</span>
        <span class="trx-list-count">{{ $transactions->total() }} টি</span>
    </div>

    <div class="trx-list-container">
        @forelse($transactions as $trx)
        <div class="trx-card">
            <div class="trx-card-left">
                <div class="trx-card-icon {{ $trx->trx_type == '+' ? 'credit' : 'debit' }}">
                    <i class="fas {{ $trx->trx_type == '+' ? 'fa-arrow-down' : 'fa-arrow-up' }}"></i>
                </div>
                <div class="trx-card-details">
                    <h4 class="trx-card-title">{{ Str::limit(__($trx->details), 28) }}</h4>
                    <div class="trx-card-meta">
                        <span class="trx-card-id"><i class="fas fa-hashtag"></i>{{ $trx->trx }}</span>
                        <span class="trx-card-date"><i class="fas fa-clock"></i>{{ diffForHumans($trx->created_at) }}</span>
                    </div>
                </div>
            </div>
            <div class="trx-card-right">
                <span class="trx-card-amount {{ $trx->trx_type == '+' ? 'credit' : 'debit' }}">
                    {{ $trx->trx_type }}৳{{ showAmount($trx->amount) }}
                </span>
                <span class="trx-card-balance">ব্যালেন্স: ৳{{ showAmount($trx->post_balance) }}</span>
            </div>
        </div>
        @empty
        <div class="trx-empty-state">
            <div class="trx-empty-icon">
                <i class="fas fa-receipt"></i>
            </div>
            <h4>কোনো লেনদেন নেই</h4>
            <p>{{ __($emptyMessage) }}</p>
        </div>
        @endforelse
    </div>
</div>

@if($transactions->hasPages())
<div class="trx-pagination-wrap">
    {{ $transactions->links() }}
</div>
@endif

<div style="height: 80px;"></div>
@endsection

@push('style')
<style>
:root {
    --agco-primary: #0F743C;
    --agco-primary-light: #e8f5ed;
    --agco-error: #DA3E2F;
    --agco-error-light: #fdecea;
    --agco-warning: #F99E2B;
    --agco-warning-light: #fef7e8;
    --agco-secondary: #C7662B;
    --agco-secondary-light: #fdf1ea;
    --agco-bg: #f8faf9;
    --agco-card: #ffffff;
    --agco-text: #1a2e23;
    --agco-text-muted: #6b7c72;
    --agco-border: #e2e8e5;
}

/* Page Header */
.trx-page-header {
    background: linear-gradient(135deg, var(--agco-primary) 0%, #0a5a2e 100%);
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
    overflow: hidden;
}
.trx-page-header::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 200px;
    height: 200px;
    background: rgba(255,255,255,0.08);
    border-radius: 50%;
}
.trx-header-content {
    display: flex;
    align-items: center;
    gap: 14px;
    position: relative;
    z-index: 1;
}
.trx-header-icon {
    width: 48px;
    height: 48px;
    background: rgba(255,255,255,0.15);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: #fff;
}
.trx-header-text h2 {
    color: #fff;
    font-size: 18px;
    font-weight: 700;
    margin: 0 0 4px 0;
}
.trx-header-text p {
    color: rgba(255,255,255,0.8);
    font-size: 12px;
    margin: 0;
}
.trx-filter-toggle {
    width: 44px;
    height: 44px;
    background: rgba(255,255,255,0.15);
    border: 2px solid rgba(255,255,255,0.3);
    border-radius: 12px;
    color: #fff;
    font-size: 18px;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    z-index: 1;
}
.trx-filter-toggle:hover, .trx-filter-toggle.active {
    background: #fff;
    color: var(--agco-primary);
}

/* Summary Cards */
.trx-summary-section {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    padding: 16px;
    margin-top: -10px;
    position: relative;
    z-index: 2;
}
.trx-summary-card {
    background: var(--agco-card);
    border-radius: 16px;
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    box-shadow: 0 4px 15px rgba(15, 116, 60, 0.08);
    border: 1px solid var(--agco-border);
}
.trx-summary-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}
.credit-card .trx-summary-icon {
    background: var(--agco-primary-light);
    color: var(--agco-primary);
}
.debit-card .trx-summary-icon {
    background: var(--agco-error-light);
    color: var(--agco-error);
}
.trx-summary-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.trx-summary-label {
    font-size: 11px;
    color: var(--agco-text-muted);
    font-weight: 500;
}
.trx-summary-value {
    font-size: 16px;
    font-weight: 700;
    color: var(--agco-text);
}

/* Filter Section */
.trx-filter-section {
    display: none;
    background: var(--agco-card);
    margin: 0 16px 16px;
    border-radius: 16px;
    padding: 16px;
    box-shadow: 0 4px 15px rgba(15, 116, 60, 0.06);
    border: 1px solid var(--agco-border);
}
.trx-filter-section.active {
    display: block;
    animation: slideDown 0.3s ease;
}
@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
.trx-filter-header {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 600;
    color: var(--agco-primary);
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 1px dashed var(--agco-border);
}
.trx-filter-group {
    margin-bottom: 14px;
    flex: 1;
}
.trx-filter-label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: var(--agco-text);
    margin-bottom: 6px;
}
.trx-input-wrap {
    position: relative;
}
.trx-input-wrap i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--agco-text-muted);
    font-size: 14px;
}
.trx-filter-input {
    width: 100%;
    padding: 12px 14px 12px 40px;
    border: 1.5px solid var(--agco-border);
    border-radius: 12px;
    font-size: 14px;
    font-family: inherit;
    background: var(--agco-bg);
    color: var(--agco-text);
    transition: all 0.3s ease;
}
.trx-filter-input:focus {
    outline: none;
    border-color: var(--agco-primary);
    background: var(--agco-card);
    box-shadow: 0 0 0 3px var(--agco-primary-light);
}
.trx-filter-row {
    display: flex;
    gap: 12px;
}
.trx-filter-select {
    width: 100%;
    padding: 12px 14px;
    border: 1.5px solid var(--agco-border);
    border-radius: 12px;
    font-size: 14px;
    font-family: inherit;
    background: var(--agco-bg);
    color: var(--agco-text);
    cursor: pointer;
    transition: all 0.3s ease;
}
.trx-filter-select:focus {
    outline: none;
    border-color: var(--agco-primary);
    background: var(--agco-card);
    box-shadow: 0 0 0 3px var(--agco-primary-light);
}
.trx-filter-btn {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, var(--agco-primary) 0%, #0a5a2e 100%);
    color: #fff;
    border: none;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
    font-family: inherit;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.3s ease;
    margin-top: 4px;
}
.trx-filter-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(15, 116, 60, 0.3);
}

/* Transactions List */
.trx-list-section {
    padding: 0 16px;
}
.trx-list-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 14px;
}
.trx-list-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--agco-text);
}
.trx-list-count {
    font-size: 12px;
    font-weight: 600;
    color: var(--agco-primary);
    background: var(--agco-primary-light);
    padding: 4px 10px;
    border-radius: 20px;
}
.trx-list-container {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.trx-card {
    background: var(--agco-card);
    border-radius: 16px;
    padding: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 10px rgba(15, 116, 60, 0.05);
    border: 1px solid var(--agco-border);
    transition: all 0.3s ease;
}
.trx-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(15, 116, 60, 0.1);
}
.trx-card-left {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
    min-width: 0;
}
.trx-card-icon {
    width: 46px;
    height: 46px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.trx-card-icon.credit {
    background: var(--agco-primary-light);
    color: var(--agco-primary);
}
.trx-card-icon.debit {
    background: var(--agco-error-light);
    color: var(--agco-error);
}
.trx-card-details {
    flex: 1;
    min-width: 0;
}
.trx-card-title {
    font-size: 14px;
    font-weight: 600;
    color: var(--agco-text);
    margin: 0 0 6px 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.trx-card-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}
.trx-card-id, .trx-card-date {
    font-size: 11px;
    color: var(--agco-text-muted);
    display: flex;
    align-items: center;
    gap: 4px;
}
.trx-card-id i, .trx-card-date i {
    font-size: 10px;
    opacity: 0.7;
}
.trx-card-right {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 4px;
    flex-shrink: 0;
}
.trx-card-amount {
    font-size: 16px;
    font-weight: 700;
}
.trx-card-amount.credit {
    color: var(--agco-primary);
}
.trx-card-amount.debit {
    color: var(--agco-error);
}
.trx-card-balance {
    font-size: 10px;
    color: var(--agco-text-muted);
    background: var(--agco-bg);
    padding: 3px 8px;
    border-radius: 6px;
}

/* Empty State */
.trx-empty-state {
    text-align: center;
    padding: 50px 20px;
    background: var(--agco-card);
    border-radius: 16px;
    border: 1px solid var(--agco-border);
}
.trx-empty-icon {
    width: 80px;
    height: 80px;
    background: var(--agco-primary-light);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    font-size: 32px;
    color: var(--agco-primary);
}
.trx-empty-state h4 {
    font-size: 17px;
    font-weight: 700;
    color: var(--agco-text);
    margin: 0 0 8px 0;
}
.trx-empty-state p {
    font-size: 13px;
    color: var(--agco-text-muted);
    margin: 0;
}

/* Pagination */
.trx-pagination-wrap {
    padding: 20px 16px;
    display: flex;
    justify-content: center;
}
.trx-pagination-wrap .pagination {
    gap: 6px;
    flex-wrap: wrap;
    justify-content: center;
}
.trx-pagination-wrap .page-link {
    border-radius: 10px;
    padding: 10px 16px;
    font-size: 13px;
    font-weight: 600;
    border: 1px solid var(--agco-border);
    background: var(--agco-card);
    color: var(--agco-text);
    transition: all 0.3s ease;
}
.trx-pagination-wrap .page-link:hover {
    background: var(--agco-primary-light);
    color: var(--agco-primary);
    border-color: var(--agco-primary);
}
.trx-pagination-wrap .page-item.active .page-link {
    background: var(--agco-primary);
    color: #fff;
    border-color: var(--agco-primary);
}
.trx-pagination-wrap .page-item.disabled .page-link {
    background: var(--agco-bg);
    color: var(--agco-text-muted);
    opacity: 0.5;
}
</style>
@endpush

@push('script')
<script>
document.getElementById('filterToggle').addEventListener('click', function(){
    this.classList.toggle('active');
    document.getElementById('filterSection').classList.toggle('active');
});
</script>
@endpush
