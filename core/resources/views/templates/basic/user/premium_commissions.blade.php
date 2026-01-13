@extends($activeTemplate.'layouts.master_mobile')
@section('main-content')

<!-- Page Header -->
<div class="page-header-premium">
    <div class="ph-icon"><i class="fas fa-crown"></i></div>
    <h2>প্রিমিয়াম কমিশন</h2>
    <p>আপনার প্রিমিয়াম রেফারেল কমিশনের তালিকা</p>
</div>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card total">
        <div class="stat-icon"><i class="fas fa-coins"></i></div>
        <div class="stat-info">
            <span class="stat-label">মোট কমিশন</span>
            <strong class="stat-value">৳{{ showAmount($stats['total_amount']) }}</strong>
        </div>
    </div>
    <div class="stat-card approved">
        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
        <div class="stat-info">
            <span class="stat-label">এপ্রুভড</span>
            <strong class="stat-value">৳{{ showAmount($stats['approved_amount']) }}</strong>
        </div>
    </div>
    <div class="stat-card pending">
        <div class="stat-icon"><i class="fas fa-clock"></i></div>
        <div class="stat-info">
            <span class="stat-label">পেন্ডিং</span>
            <strong class="stat-value">৳{{ showAmount($stats['pending_amount']) }}</strong>
        </div>
    </div>
</div>

<!-- Status Summary -->
<div class="status-summary">
    <div class="summary-item">
        <span class="count">{{ $stats['total'] }}</span>
        <span class="label">মোট</span>
    </div>
    <div class="summary-divider"></div>
    <div class="summary-item pending">
        <span class="count">{{ $stats['pending'] }}</span>
        <span class="label">পেন্ডিং</span>
    </div>
    <div class="summary-divider"></div>
    <div class="summary-item approved">
        <span class="count">{{ $stats['approved'] }}</span>
        <span class="label">এপ্রুভড</span>
    </div>
    <div class="summary-divider"></div>
    <div class="summary-item locked">
        <span class="count">{{ $stats['locked'] }}</span>
        <span class="label">লকড</span>
    </div>
    <div class="summary-divider"></div>
    <div class="summary-item reversed">
        <span class="count">{{ $stats['reversed'] }}</span>
        <span class="label">রিভার্সড</span>
    </div>
</div>

@if(!$isPremium)
<!-- Premium Upgrade Banner -->
<div class="upgrade-banner">
    <div class="ub-icon"><i class="fas fa-crown"></i></div>
    <div class="ub-content">
        <strong>আপনি এখনও প্রিমিয়াম সদস্য নন</strong>
        <p>প্রিমিয়াম প্ল্যান নিলে আপনার রেফারদের প্রিমিয়াম কমিশন সরাসরি আপনার অ্যাকাউন্টে যোগ হবে!</p>
    </div>
    <a href="{{ route('plans') }}" class="ub-btn">
        <i class="fas fa-arrow-up"></i> আপগ্রেড
    </a>
</div>
@endif

<!-- Info Note -->
<div class="info-note-box">
    <i class="fas fa-info-circle"></i>
    <div>
        <strong>প্রিমিয়াম কমিশন কিভাবে কাজ করে?</strong>
        <p>আপনি যখন কোনো ইউজারকে রেফার করেন এবং সেই ইউজার প্রিমিয়াম প্ল্যান কিনে, তখন আপনি ৳{{ showAmount($premiumBaseValue) }} কমিশন পাবেন।
        @if(!$isPremium)
        তবে আপনি নিজে প্রিমিয়াম না হলে কমিশন "পেন্ডিং" থাকবে এবং অ্যাডমিন অনুমোদনের পর যোগ হবে।
        @else
        আপনি প্রিমিয়াম হওয়ায় কমিশন সরাসরি যোগ হয়!
        @endif
        </p>
    </div>
</div>

<!-- Commissions List -->
<div class="section-block">
    <div class="section-head">
        <h3><i class="fas fa-list"></i> কমিশন তালিকা</h3>
        <span class="count-badge">{{ $commissions->total() }}</span>
    </div>

    @forelse($commissions as $commission)
    <div class="commission-item {{ $commission->status }}">
        <div class="ci-left">
            <div class="ci-avatar">
                {{ strtoupper(substr($commission->referredUser->username ?? 'U', 0, 1)) }}
            </div>
            <div class="ci-info">
                <strong class="ci-user">{{ $commission->referredUser->fullname ?? $commission->referredUser->username ?? 'Unknown' }}</strong>
                <span class="ci-plan">{{ $commission->plan->name ?? 'N/A' }} প্ল্যান</span>
                <span class="ci-date"><i class="fas fa-calendar-alt"></i> {{ $commission->created_at->format('d M, Y h:i A') }}</span>
            </div>
        </div>
        <div class="ci-right">
            <span class="ci-amount">+৳{{ showAmount($commission->amount) }}</span>
            <span class="ci-status {{ $commission->status }}">
                @if($commission->status == 'pending')
                    <i class="fas fa-clock"></i> পেন্ডিং
                @elseif($commission->status == 'approved')
                    <i class="fas fa-check-circle"></i> এপ্রুভড
                @elseif($commission->status == 'locked')
                    <i class="fas fa-lock"></i> লকড
                @elseif($commission->status == 'reversed')
                    <i class="fas fa-undo"></i> রিভার্সড
                @endif
            </span>
            @if($commission->notes)
            <span class="ci-notes" title="{{ $commission->notes }}"><i class="fas fa-sticky-note"></i></span>
            @endif
        </div>
    </div>
    @empty
    <div class="empty-state">
        <div class="es-icon"><i class="fas fa-gift"></i></div>
        <h4>এখনো কোনো প্রিমিয়াম কমিশন নেই</h4>
        <p>আপনার রেফারেল লিংক শেয়ার করুন এবং প্রিমিয়াম কমিশন আয় করুন!</p>
        <a href="{{ route('user.referred') }}" class="es-btn">
            <i class="fas fa-share-alt"></i> রেফারেল লিংক দেখুন
        </a>
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
.page-header-premium{background:linear-gradient(135deg,#F99E2B 0%,#e88a1a 100%);padding:24px 20px;text-align:center}
.page-header-premium .ph-icon{width:50px;height:50px;background:rgba(255,255,255,0.2);border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto 8px;font-size:20px;color:#fff}
.page-header-premium h2{color:#fff;font-size:20px;font-weight:600;margin-bottom:4px}
.page-header-premium p{color:rgba(255,255,255,0.9);font-size:12px;margin:0}

.stats-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;padding:16px}
.stat-card{background:#fff;border-radius:12px;padding:12px;box-shadow:0 2px 6px rgba(0,0,0,0.06);display:flex;align-items:center;gap:10px}
.stat-icon{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:14px;color:#fff;flex-shrink:0}
.stat-card.total .stat-icon{background:linear-gradient(135deg,#3b82f6 0%,#2563eb 100%)}
.stat-card.approved .stat-icon{background:linear-gradient(135deg,#0F743C 0%,#0a5229 100%)}
.stat-card.pending .stat-icon{background:linear-gradient(135deg,#F99E2B 0%,#e88a1a 100%)}
.stat-label{display:block;font-size:9px;color:#666;margin-bottom:2px}
.stat-value{font-size:13px;font-weight:700;color:#1a1a1a}

.status-summary{display:flex;justify-content:space-around;align-items:center;background:#fff;margin:0 16px 12px;padding:12px;border-radius:12px;box-shadow:0 2px 6px rgba(0,0,0,0.06)}
.summary-item{text-align:center}
.summary-item .count{display:block;font-size:18px;font-weight:800;color:#1a1a1a}
.summary-item .label{font-size:10px;color:#666}
.summary-item.pending .count{color:#F99E2B}
.summary-item.approved .count{color:#0F743C}
.summary-item.locked .count{color:#ef4444}
.summary-item.reversed .count{color:#6b7280}
.summary-divider{width:1px;height:30px;background:#e5e7eb}

.upgrade-banner{display:flex;align-items:center;gap:12px;background:linear-gradient(135deg,#fef3c7 0%,#fde68a 100%);border:1px solid #fbbf24;margin:0 16px 12px;padding:14px;border-radius:12px}
.ub-icon{width:40px;height:40px;background:linear-gradient(135deg,#F99E2B 0%,#e88a1a 100%);border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:18px;flex-shrink:0}
.ub-content{flex:1}
.ub-content strong{display:block;font-size:12px;color:#92400e;margin-bottom:2px}
.ub-content p{font-size:10px;color:#a16207;margin:0}
.ub-btn{background:linear-gradient(135deg,#F99E2B 0%,#e88a1a 100%);color:#fff;padding:8px 14px;border-radius:8px;font-size:11px;font-weight:700;text-decoration:none;white-space:nowrap;display:flex;align-items:center;gap:4px}

.info-note-box{display:flex;gap:12px;background:#eff6ff;border:1px solid #3b82f6;margin:0 16px 12px;padding:14px;border-radius:12px}
.info-note-box>i{color:#3b82f6;font-size:18px;flex-shrink:0;margin-top:2px}
.info-note-box strong{display:block;font-size:12px;color:#1e40af;margin-bottom:4px}
.info-note-box p{font-size:11px;color:#3b82f6;margin:0;line-height:1.5}

.section-block{background:#fff;margin:0 16px 12px;border-radius:12px;padding:16px;box-shadow:0 2px 6px rgba(0,0,0,0.06)}
.section-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid #f0f0f0}
.section-head h3{font-size:14px;font-weight:700;color:#1a1a1a;display:flex;align-items:center;gap:6px;margin:0}
.section-head h3 i{color:#F99E2B;font-size:15px}
.count-badge{background:#F99E2B;color:#fff;padding:4px 10px;border-radius:10px;font-size:11px;font-weight:700}

.commission-item{display:flex;justify-content:space-between;align-items:center;padding:12px;background:#f9fafb;border-radius:10px;margin-bottom:10px;border-left:3px solid #e5e7eb}
.commission-item.pending{border-left-color:#F99E2B;background:linear-gradient(90deg,#fffbeb 0%,#f9fafb 20%)}
.commission-item.approved{border-left-color:#0F743C;background:linear-gradient(90deg,#f0fdf4 0%,#f9fafb 20%)}
.commission-item.locked{border-left-color:#ef4444;background:linear-gradient(90deg,#fef2f2 0%,#f9fafb 20%)}
.commission-item.reversed{border-left-color:#6b7280;background:linear-gradient(90deg,#f3f4f6 0%,#f9fafb 20%)}
.ci-left{display:flex;align-items:center;gap:10px;flex:1;min-width:0}
.ci-avatar{width:40px;height:40px;background:linear-gradient(135deg,#F99E2B 0%,#e88a1a 100%);border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:16px;font-weight:700;flex-shrink:0}
.ci-info{min-width:0}
.ci-user{display:block;font-size:13px;font-weight:700;color:#1a1a1a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.ci-plan{display:block;font-size:10px;color:#666;margin:2px 0}
.ci-date{font-size:9px;color:#999;display:flex;align-items:center;gap:4px}
.ci-right{text-align:right;flex-shrink:0}
.ci-amount{display:block;font-size:15px;font-weight:800;color:#0F743C;margin-bottom:4px}
.ci-status{display:inline-flex;align-items:center;gap:4px;font-size:9px;font-weight:700;padding:3px 8px;border-radius:6px}
.ci-status.pending{background:#fef3c7;color:#d97706}
.ci-status.approved{background:#dcfce7;color:#0F743C}
.ci-status.locked{background:#fee2e2;color:#dc2626}
.ci-status.reversed{background:#f3f4f6;color:#6b7280}
.ci-notes{color:#999;font-size:12px;margin-left:6px;cursor:help}

.empty-state{text-align:center;padding:30px 20px}
.es-icon{width:60px;height:60px;background:linear-gradient(135deg,#fef3c7 0%,#fde68a 100%);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-size:24px;color:#F99E2B}
.empty-state h4{font-size:14px;color:#1a1a1a;margin-bottom:6px}
.empty-state p{font-size:11px;color:#666;margin-bottom:16px}
.es-btn{display:inline-flex;align-items:center;gap:6px;background:linear-gradient(135deg,#F99E2B 0%,#e88a1a 100%);color:#fff;padding:10px 20px;border-radius:8px;font-size:12px;font-weight:700;text-decoration:none}

.pagination-wrap{padding:0 16px}
</style>
@endpush
