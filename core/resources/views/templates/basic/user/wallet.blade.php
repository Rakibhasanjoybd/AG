@extends($activeTemplate.'layouts.master_mobile')
@section('main-content')
@php
    $achievementProgress = $user->getAchievementProgress();
    $transferStatus = $user->canTransferFromHoldWallet();
    $achievementConfig = \App\Models\HoldWalletSetting::getAchievementConfig();
    $availableBalance = $user->getAvailableHoldBalanceByLevel();
    $pendingBalance = $user->pendingHoldBalance();
    $totalHoldBalance = $user->totalHoldBalance;
    $feeConfig = \App\Models\HoldWalletSetting::getTransferFeeConfig();
    $estimatedFee = \App\Models\HoldWalletSetting::calculateTransferFee($availableBalance);
    $netAmount = max(0, $availableBalance - $estimatedFee);
    $totalBalance = $user->balance + $totalHoldBalance;
    $mainPercentage = $totalBalance > 0 ? round(($user->balance / $totalBalance) * 100, 1) : 0;
    $holdPercentage = $totalBalance > 0 ? round(($totalHoldBalance / $totalBalance) * 100, 1) : 0;
@endphp

<!-- Flat Wallet Hero -->
<div class="wallet-hero-flat">
    <div class="balance-main">
        <span class="balance-label">মূল ব্যালেন্স</span>
        <h1 class="balance-amount">৳ {{ showAmount($user->balance) }}</h1>
        <div class="balance-percent">মোট সম্পদের {{ $mainPercentage }}%</div>
    </div>
    <div class="action-buttons">
        <a href="{{ route('user.deposit') }}" class="btn-action btn-deposit">
            <i class="fas fa-plus-circle"></i>
            <span>ডিপোজিট</span>
        </a>
        <a href="{{ route('user.withdraw') }}" class="btn-action btn-withdraw">
            <i class="fas fa-arrow-circle-up"></i>
            <span>উত্তোলন</span>
        </a>
    </div>
</div>

<!-- Wallet Stats -->
<div class="wallet-stats-flat">
    <div class="stat-card">
        <div class="stat-icon stat-total">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-content">
            <span class="stat-amount">৳{{ showAmount($totalBalance) }}</span>
            <span class="stat-title">মোট সম্পদ</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-hold">
            <i class="fas fa-lock"></i>
        </div>
        <div class="stat-content">
            <span class="stat-amount">৳{{ showAmount($totalHoldBalance) }}</span>
            <span class="stat-title">হোল্ড ({{ $holdPercentage }}%)</span>
        </div>
    </div>
</div>

<!-- Flat Achievement Section -->
<div class="flat-section">
    <div class="section-header" onclick="toggleSection('achievement')">
        <div class="header-info">
            <div class="section-icon level-{{ $achievementProgress['current_level'] }}">
                <i class="{{ $achievementProgress['current_config']['icon'] }}"></i>
            </div>
            <div class="header-text">
                <h3>{{ $achievementProgress['current_config']['name'] }}</h3>
                <p>{{ $achievementProgress['referral_count'] }} জন রেফার করেছেন</p>
            </div>
        </div>
        <i class="fas fa-chevron-down toggle-icon"></i>
    </div>
    <div class="section-content" id="achievement-content">
        @if(!$achievementProgress['is_max_level'])
        <div class="progress-info">
            <div class="progress-text">
                <span class="progress-current">বর্তমান অগ্রগতি</span>
                <span class="progress-next">পরবর্তী: {{ $achievementProgress['next_config']['name'] }}</span>
            </div>
            <div class="progress-bar-flat">
                <div class="progress-fill-flat" style="width: {{ $achievementProgress['progress_percent'] }}%"></div>
            </div>
            <span class="progress-remaining">আরও {{ $achievementProgress['referrals_needed'] }} জন রেফার প্রয়োজন</span>
        </div>
        @else
        <div class="achievement-max">
            <i class="fas fa-trophy"></i>
            <div>
                <h4>অভিনন্দন!</h4>
                <p>সর্বোচ্চ লেভেল অর্জন করেছেন</p>
            </div>
        </div>
        @endif

        <div class="level-list">
            <div class="level-item {{ $achievementProgress['current_level'] >= 1 ? 'active' : '' }}">
                <i class="fas fa-seedling"></i>
                <div>
                    <strong>স্টার্টার (১-৪৯ জন)</strong>
                    <span>৩০ দিন অপেক্ষা করতে হবে</span>
                </div>
            </div>
            <div class="level-item {{ $achievementProgress['current_level'] >= 2 ? 'active' : '' }}">
                <i class="fas fa-medal"></i>
                <div>
                    <strong>সিলভার (৫০-১৯৯ জন)</strong>
                    <span>১৫ দিন অপেক্ষা করতে হবে</span>
                </div>
            </div>
            <div class="level-item {{ $achievementProgress['current_level'] >= 3 ? 'active' : '' }}">
                <i class="fas fa-crown"></i>
                <div>
                    <strong>গোল্ড (২০০+ জন)</strong>
                    <span>তাৎক্ষণিক ট্রান্সফার</span>
                </div>
            </div>
        </div>
        <a href="{{ route('user.hold.wallet') }}" class="btn-link">বিস্তারিত দেখুন →</a>
    </div>
</div>

<!-- Flat Hold Wallet -->
<div class="flat-section">
    <div class="section-header" onclick="toggleSection('holdwallet')">
        <div class="header-info">
            <div class="section-icon hold-icon">
                <i class="fas fa-lock"></i>
            </div>
            <div class="header-text">
                <h3>হোল্ড ওয়ালেট</h3>
                <p>৳{{ showAmount($totalHoldBalance) }}</p>
            </div>
        </div>
        <div class="header-actions">
            <span class="badge {{ $transferStatus['can_transfer'] ? 'badge-success' : 'badge-warning' }}">
                {{ $transferStatus['can_transfer'] ? 'সক্রিয়' : 'অপেক্ষারত' }}
            </span>
            <i class="fas fa-chevron-down toggle-icon"></i>
        </div>
    </div>
    <div class="section-content" id="holdwallet-content">
        <div class="hold-items">
            <div class="hold-row">
                <div class="hold-info">
                    <i class="fas fa-user-friends"></i>
                    <span>রেফারেল কমিশন</span>
                </div>
                <strong>৳{{ showAmount($user->referral_commission_hold ?? 0) }}</strong>
            </div>
            <div class="hold-row">
                <div class="hold-info">
                    <i class="fas fa-level-up-alt"></i>
                    <span>আপগ্রেড কমিশন</span>
                </div>
                <strong>৳{{ showAmount($user->upgrade_commission_hold ?? 0) }}</strong>
            </div>
            <div class="hold-row">
                <div class="hold-info">
                    <i class="fas fa-ad"></i>
                    <span>PTC কমিশন</span>
                </div>
                <strong>৳{{ showAmount($user->ptc_commission_hold ?? 0) }}</strong>
            </div>
        </div>

        <div class="transfer-info">
            <div class="transfer-box">
                <div class="transfer-head">
                    <span>ট্রান্সফারযোগ্য ব্যালেন্স</span>
                    <h3>৳{{ showAmount($availableBalance) }}</h3>
                </div>
                @if($availableBalance > 0)
                <div class="transfer-calc">
                    <div class="calc-row">
                        <span>ট্রান্সফার ফি</span>
                        <span>-৳{{ showAmount($estimatedFee) }}</span>
                    </div>
                    <div class="calc-row total">
                        <span>নেট পাবেন</span>
                        <strong>৳{{ showAmount($netAmount) }}</strong>
                    </div>
                </div>
                @endif
                <div class="transfer-status-flat {{ $transferStatus['can_transfer'] ? 'ready' : 'waiting' }}">
                    <i class="fas {{ $transferStatus['can_transfer'] ? 'fa-check-circle' : 'fa-clock' }}"></i>
                    <span>{{ $transferStatus['reason'] }}</span>
                </div>
            </div>

            @if($transferStatus['can_transfer'] && $availableBalance > 0)
            <form action="{{ route('user.hold.wallet.transfer') }}" method="POST">
                @csrf
                <button type="submit" class="btn-primary-flat">
                    <i class="fas fa-exchange-alt"></i>
                    <span>মূল ব্যালেন্সে ট্রান্সফার করুন</span>
                </button>
            </form>
            @else
            <button class="btn-disabled-flat" disabled>
                <i class="fas fa-{{ $availableBalance <= 0 ? 'lock' : 'hourglass' }}"></i>
                <span>{{ $availableBalance <= 0 ? 'ট্রান্সফারযোগ্য ব্যালেন্স নেই' : $transferStatus['wait_days'].' দিন অপেক্ষা করুন' }}</span>
            </button>
            @endif
        </div>
        <a href="{{ route('user.hold.wallet') }}" class="btn-link">বিস্তারিত দেখুন →</a>
    </div>
</div>

<!-- Flat Quick Menu -->
<div class="flat-section">
    <div class="section-header" onclick="toggleSection('quicklinks')">
        <div class="header-info">
            <div class="section-icon menu-icon-bg">
                <i class="fas fa-th"></i>
            </div>
            <div class="header-text">
                <h3>দ্রুত মেনু</h3>
                <p>লেনদেন ও রিপোর্ট</p>
            </div>
        </div>
        <i class="fas fa-chevron-down toggle-icon"></i>
    </div>
    <div class="section-content" id="quicklinks-content">
        <div class="quick-menu-list">
            <a href="{{ route('user.referred') }}" class="menu-item">
                <i class="fas fa-user-friends"></i>
                <span>রেফারেল তালিকা</span>
                <div class="menu-badge">{{ $achievementProgress['referral_count'] }}</div>
                <i class="fas fa-chevron-right"></i>
            </a>
            <a href="{{ route('user.deposit.history') }}" class="menu-item">
                <i class="fas fa-download"></i>
                <span>ডিপোজিট ইতিহাস</span>
                <i class="fas fa-chevron-right"></i>
            </a>
            <a href="{{ route('user.withdraw.history') }}" class="menu-item">
                <i class="fas fa-upload"></i>
                <span>উত্তোলন ইতিহাস</span>
                <i class="fas fa-chevron-right"></i>
            </a>
            <a href="{{ route('user.transactions') }}" class="menu-item">
                <i class="fas fa-exchange-alt"></i>
                <span>সব লেনদেন</span>
                <i class="fas fa-chevron-right"></i>
            </a>
            <a href="{{ route('user.commissions') }}" class="menu-item">
                <i class="fas fa-coins"></i>
                <span>কমিশন ইতিহাস</span>
                <i class="fas fa-chevron-right"></i>
            </a>
        </div>
    </div>
</div>

<div style="height: 20px;"></div>
@endsection

@push('style')
<style>
/* Bangla Font Support */
@import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap');

/* Global Bangla Typography */
body,
.wallet-hero-flat,
.flat-section,
.header-text h3,
.header-text p,
.level-item strong,
.level-item span,
.hold-info span,
.menu-item span,
.btn-primary-flat,
.btn-action span,
.balance-label,
.balance-percent,
.stat-title,
.progress-current,
.progress-next,
.progress-remaining,
.transfer-head span,
.calc-row,
.badge {
    font-family: 'Hind Siliguri', sans-serif !important;
}

/* Flat Design - Hero Section */
.wallet-hero-flat{background:#9333ea;padding:24px 16px;margin:-20px -16px 0}
.balance-main{text-align:center;margin-bottom:16px}
.balance-label{color:rgba(255,255,255,0.85);font-size:13px;display:block;margin-bottom:6px;font-weight:500}
.balance-amount{color:#fff;font-size:36px;font-weight:800;margin:0 0 6px;line-height:1.1}
.balance-percent{color:rgba(255,255,255,0.75);font-size:13px;font-weight:500}
.action-buttons{display:flex;gap:10px;justify-content:center}
.btn-action{display:flex;align-items:center;gap:8px;padding:12px 20px;border-radius:8px;text-decoration:none;font-size:14px;font-weight:600;flex:1;justify-content:center;max-width:160px}
.btn-action i{font-size:18px}
.btn-deposit{background:#fbbf24;color:#1f2937}
.btn-withdraw{background:rgba(255,255,255,0.2);color:#fff;border:1px solid rgba(255,255,255,0.3)}

/* Flat Stats Cards */
.wallet-stats-flat{display:flex;gap:12px;padding:0 16px;margin:16px 0}
.stat-card{flex:1;background:#fff;padding:14px;border-radius:12px;display:flex;align-items:center;gap:12px;box-shadow:0 1px 3px rgba(0,0,0,0.08)}
.stat-icon{width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0}
.stat-total{background:#e0e7ff;color:#4f46e5}
.stat-hold{background:#fef3c7;color:#f59e0b}
.stat-content{flex:1}
.stat-amount{display:block;font-size:16px;font-weight:700;color:#1f2937;line-height:1.2;margin-bottom:2px}
.stat-title{font-size:12px;color:#6b7280;font-weight:500}

/* Flat Sections */
.flat-section{background:#fff;margin:12px 16px;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.08);overflow:hidden}
.section-header{display:flex;justify-content:space-between;align-items:center;padding:16px;cursor:pointer;user-select:none;background:#fff}
.section-header:active{background:#f9fafb}
.header-info{display:flex;align-items:center;gap:12px;flex:1}
.section-icon{width:48px;height:48px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0}
.section-icon.level-1{background:#9333ea;color:#fff}
.section-icon.level-2{background:#6b7280;color:#fff}
.section-icon.level-3{background:#f59e0b;color:#fff}
.hold-icon{background:#9333ea;color:#fff}
.menu-icon-bg{background:#f59e0b;color:#fff}
.header-text h3{margin:0 0 2px;font-size:16px;font-weight:700;color:#1f2937;line-height:1.3}
.header-text p{margin:0;font-size:14px;color:#6b7280;font-weight:500}
.header-actions{display:flex;align-items:center;gap:10px}
.badge{padding:5px 10px;border-radius:6px;font-size:11px;font-weight:600}
.badge-success{background:#d1fae5;color:#065f46}
.badge-warning{background:#fef3c7;color:#92400e}
.toggle-icon{font-size:18px;color:#9ca3af;transition:transform 0.3s}
.flat-section.active .toggle-icon{transform:rotate(180deg)}
.section-content{max-height:0;overflow:hidden;transition:max-height 0.4s ease;padding:0 16px}
.flat-section.active .section-content{max-height:2000px;padding:0 16px 16px}

/* Progress Info - Flat */
.progress-info{margin-bottom:16px}
.progress-text{display:flex;justify-content:space-between;margin-bottom:8px}
.progress-current{font-size:13px;color:#6b7280;font-weight:500}
.progress-next{font-size:13px;color:#9333ea;font-weight:600}
.progress-bar-flat{height:10px;background:#e5e7eb;border-radius:5px;overflow:hidden;margin-bottom:6px}
.progress-fill-flat{height:100%;background:#9333ea;border-radius:5px;transition:width 0.5s ease}
.progress-remaining{font-size:12px;color:#6b7280;display:block}

/* Achievement Max */
.achievement-max{display:flex;align-items:center;gap:14px;padding:16px;background:#fef3c7;border-radius:10px;margin-bottom:16px}
.achievement-max i{font-size:32px;color:#f59e0b}
.achievement-max h4{margin:0 0 2px;font-size:16px;color:#78350f;font-weight:700}
.achievement-max p{margin:0;font-size:13px;color:#92400e}

/* Level List - Flat */
.level-list{margin-bottom:12px}
.level-item{display:flex;align-items:center;gap:12px;padding:12px;background:#f9fafb;border-radius:8px;margin-bottom:8px;opacity:0.5}
.level-item.active{background:#f3e8ff;opacity:1}
.level-item i{width:36px;height:36px;border-radius:8px;background:#e5e7eb;display:flex;align-items:center;justify-content:center;font-size:16px;color:#9ca3af;flex-shrink:0}
.level-item.active i{background:#9333ea;color:#fff}
.level-item div{flex:1}
.level-item strong{display:block;font-size:14px;color:#1f2937;margin-bottom:2px;font-weight:600}
.level-item span{font-size:13px;color:#6b7280}

/* Hold Items - Flat */
.hold-items{margin-bottom:16px}
.hold-row{display:flex;justify-content:space-between;align-items:center;padding:12px;background:#f9fafb;border-radius:8px;margin-bottom:8px}
.hold-info{display:flex;align-items:center;gap:10px}
.hold-info i{width:32px;height:32px;border-radius:8px;background:#e0e7ff;color:#4f46e5;display:flex;align-items:center;justify-content:center;font-size:14px}
.hold-row:nth-child(2) .hold-info i{background:#fff7ed;color:#f59e0b}
.hold-row:nth-child(3) .hold-info i{background:#fef3c7;color:#f59e0b}
.hold-info span{font-size:14px;color:#1f2937;font-weight:500}
.hold-row strong{font-size:16px;color:#1f2937;font-weight:700}

/* Transfer Info - Flat */
.transfer-info{margin-bottom:12px}
.transfer-box{background:#faf5ff;padding:14px;border-radius:10px;margin-bottom:12px}
.transfer-head{margin-bottom:10px}
.transfer-head span{font-size:12px;color:#6b7280;display:block;margin-bottom:4px}
.transfer-head h3{margin:0;font-size:28px;font-weight:800;color:#9333ea}
.transfer-calc{border-top:1px solid #e5e7eb;padding-top:10px;margin-top:10px}
.calc-row{display:flex;justify-content:space-between;padding:6px 0;font-size:13px;color:#6b7280}
.calc-row.total{border-top:1px solid #e5e7eb;margin-top:6px;padding-top:10px}
.calc-row.total span{font-size:14px;color:#1f2937;font-weight:600}
.calc-row.total strong{font-size:18px;color:#16a34a;font-weight:700}
.transfer-status-flat{display:flex;align-items:center;gap:10px;padding:10px;border-radius:8px;margin-top:12px}
.transfer-status-flat.ready{background:#d1fae5}
.transfer-status-flat.waiting{background:#fef3c7}
.transfer-status-flat i{font-size:18px}
.transfer-status-flat.ready i{color:#16a34a}
.transfer-status-flat.waiting i{color:#f59e0b}
.transfer-status-flat span{font-size:13px;color:#1f2937;font-weight:500}

/* Buttons - Flat */
.btn-primary-flat{width:100%;padding:14px;background:#9333ea;color:#fff;border:none;border-radius:8px;font-size:15px;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:10px;font-family:inherit;margin-top:12px}
.btn-primary-flat:active{background:#7c3aed}
.btn-disabled-flat{width:100%;padding:14px;background:#e5e7eb;color:#9ca3af;border:none;border-radius:8px;font-size:15px;font-weight:600;cursor:not-allowed;display:flex;align-items:center;justify-content:center;gap:10px;font-family:inherit;margin-top:12px}
.btn-link{display:block;text-align:center;color:#9333ea;text-decoration:none;font-size:14px;font-weight:600;padding:10px;margin-top:10px}

/* Quick Menu List - Flat */
.quick-menu-list{padding:0}
.menu-item{display:flex;align-items:center;gap:12px;padding:14px;background:#f9fafb;border-radius:8px;text-decoration:none;margin-bottom:8px;position:relative}
.menu-item:active{background:#f3f4f6}
.menu-item i:first-child{width:36px;height:36px;border-radius:8px;background:#e0e7ff;color:#4f46e5;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0}
.menu-item:nth-child(2) i:first-child{background:#fff7ed;color:#f59e0b}
.menu-item:nth-child(3) i:first-child{background:#fef3c7;color:#f59e0b}
.menu-item:nth-child(4) i:first-child{background:#fce7f3;color:#dc2626}
.menu-item:nth-child(5) i:first-child{background:#fef9c3;color:#f59e0b}
.menu-item span{flex:1;font-size:15px;font-weight:600;color:#1f2937}
.menu-badge{background:#dc2626;color:#fff;font-size:11px;font-weight:700;padding:3px 8px;border-radius:10px;min-width:24px;text-align:center}
.menu-item i:last-child{color:#9ca3af;font-size:14px}
</style>
@endpush

@push('script')
<script>
function toggleSection(id) {
    const section = event.currentTarget.closest('.flat-section');
    section.classList.toggle('active');
}

// Auto-open first section on load
document.addEventListener('DOMContentLoaded', function() {
    const firstSection = document.querySelector('.flat-section');
    if (firstSection) {
        firstSection.classList.add('active');
    }
});
</script>
@endpush
