@extends($activeTemplate . 'layouts.app_mobile')
@section('content')
@php
    $user = auth()->user();
    $currentPlan = $user->plan;
    $isActive = $user->runningPlan;
@endphp

<div class="profile-v2-container">
    <!-- Profile Header -->
    <div class="pv2-profile-header">
        <div class="pv2ph-bg">
            <svg class="pv2ph-pattern" viewBox="0 0 400 200">
                <defs>
                    <linearGradient id="headerGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#0F743C;stop-opacity:1" />
                        <stop offset="100%" style="stop-color:#1a9e52;stop-opacity:1" />
                    </linearGradient>
                </defs>
                <rect width="400" height="200" fill="url(#headerGrad)"/>
                <circle cx="80" cy="50" r="30" fill="rgba(255,255,255,0.1)"/>
                <circle cx="320" cy="150" r="40" fill="rgba(255,255,255,0.08)"/>
                <path d="M0,120 Q100,80 200,120 T400,120 L400,200 L0,200 Z" fill="rgba(255,255,255,0.05)"/>
            </svg>
        </div>

        <div class="pv2ph-content">
            <div class="pv2ph-logo">
                <img src="{{ getImage(getFilePath('logoIcon') . '/logo.png') }}" alt="Logo" class="pv2ph-logo-img">
            </div>

            @if($isActive && $currentPlan)
            <div class="pv2ph-plan-badge">
                <div class="pv2phb-icon">
                    <i class="fas fa-crown"></i>
                </div>
                <div class="pv2phb-info">
                    <span class="pv2phb-label">স্বাগতম, প্রিয় {{ $currentPlan->display_code ?: $currentPlan->name }} সদস্য!</span>
                    <span class="pv2phb-id">{{ $user->username }}</span>
                </div>
                <div class="pv2phb-date">
                    {{ \Carbon\Carbon::parse($user->expire_date)->format('d/m/Y') }}
                </div>
            </div>
            @else
            <div class="pv2ph-welcome">
                <h2 class="pv2phw-title">স্বাগতম</h2>
                <p class="pv2phw-username">{{ $user->username }}</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Balance Cards -->
    <div class="pv2-balance-section">
        <div class="pv2bs-card main-balance">
            <div class="pv2bs-icon">
                <i class="fas fa-wallet"></i>
            </div>
            <div class="pv2bs-info">
                <div class="pv2bs-label">মোট নাড</div>
                <div class="pv2bs-amount">{{ $general->cur_sym }} {{ showAmount($user->balance) }}</div>
            </div>
            <div class="pv2bs-arrow">
                <i class="fas fa-chevron-right"></i>
            </div>
        </div>

        <div class="pv2bs-row">
            <a href="{{ route('user.deposit') }}" class="pv2bs-card mini-card">
                <div class="pv2bs-mini-info">
                    <div class="pv2bs-label">আকাউন্ট আপডেট</div>
                    <div class="pv2bs-amount">{{ $general->cur_sym }} {{ showAmount($user->total_deposit) }}</div>
                </div>
                <div class="pv2bs-mini-arrow">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </a>

            <a href="{{ route('user.withdraw') }}" class="pv2bs-card mini-card">
                <div class="pv2bs-mini-info">
                    <div class="pv2bs-label">নিশ্চিত পরিমাণ</div>
                    <div class="pv2bs-amount">{{ $general->cur_sym }} {{ showAmount($user->total_withdraw) }}</div>
                </div>
                <div class="pv2bs-mini-arrow">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </a>
        </div>
    </div>

    <!-- Quick Actions Grid -->
    <div class="pv2-actions-grid">
        <a href="{{ route('user.referral.users') }}" class="pv2ag-item">
            <div class="pv2ag-icon" style="background: linear-gradient(135deg, #10b981, #34d399);">
                <i class="fas fa-users"></i>
            </div>
            <div class="pv2ag-label">আমার অবদান</div>
        </a>

        <a href="{{ route('user.ptc.ads') }}" class="pv2ag-item">
            <div class="pv2ag-icon" style="background: linear-gradient(135deg, #f59e0b, #fbbf24);">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <div class="pv2ag-label">চাকরির আবেদন</div>
        </a>

        <a href="{{ route('user.transactions') }}" class="pv2ag-item">
            <div class="pv2ag-icon" style="background: linear-gradient(135deg, #8b5cf6, #a78bfa);">
                <i class="fas fa-file-invoice"></i>
            </div>
            <div class="pv2ag-label">চাকরির বিবরণী</div>
        </a>

        <a href="{{ route('plans') }}" class="pv2ag-item">
            <div class="pv2ag-icon" style="background: linear-gradient(135deg, #0F743C, #1a9e52);">
                <i class="fas fa-rocket"></i>
            </div>
            <div class="pv2ag-label">কোম্পানির প্রোমোশন</div>
        </a>

        <a href="{{ route('user.deposit.history') }}" class="pv2ag-item">
            <div class="pv2ag-icon" style="background: linear-gradient(135deg, #06b6d4, #22d3ee);">
                <i class="fas fa-receipt"></i>
            </div>
            <div class="pv2ag-label">কর্মচারী বিতর্কি</div>
        </a>

        <a href="{{ route('ticket') }}" class="pv2ag-item">
            <div class="pv2ag-icon" style="background: linear-gradient(135deg, #ec4899, #f472b6);">
                <i class="fas fa-headset"></i>
            </div>
            <div class="pv2ag-label">গ্রাস সেবা</div>
        </a>

        <a href="{{ route('user.deposit') }}" class="pv2ag-item">
            <div class="pv2ag-icon" style="background: linear-gradient(135deg, #14b8a6, #2dd4bf);">
                <i class="fas fa-plus-circle"></i>
            </div>
            <div class="pv2ag-label">টপ আপ</div>
        </a>

        <a href="{{ route('user.commissions') }}" class="pv2ag-item">
            <div class="pv2ag-icon" style="background: linear-gradient(135deg, #f97316, #fb923c);">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="pv2ag-label">প্রভাবের কেন্দ্র</div>
        </a>

        <a href="{{ route('user.withdraw.history') }}" class="pv2ag-item">
            <div class="pv2ag-icon" style="background: linear-gradient(135deg, #ef4444, #f87171);">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="pv2ag-label">তরলীন</div>
        </a>
    </div>

    <!-- User Stats -->
    <div class="pv2-stats-section">
        <div class="pv2ss-title">
            <i class="fas fa-chart-pie"></i>
            <span>পরিসংখ্যান</span>
        </div>

        <div class="pv2ss-grid">
            <div class="pv2ss-item">
                <div class="pv2ss-icon">
                    <i class="fas fa-eye"></i>
                </div>
                <div class="pv2ss-info">
                    <div class="pv2ss-value">{{ $user->total_ptc_view ?? 0 }}</div>
                    <div class="pv2ss-label">মোট বিজ্ঞাপন দেখা</div>
                </div>
            </div>

            <div class="pv2ss-item">
                <div class="pv2ss-icon">
                    <i class="fas fa-gift"></i>
                </div>
                <div class="pv2ss-info">
                    <div class="pv2ss-value">{{ $general->cur_sym }}{{ showAmount($user->total_ref_com ?? 0) }}</div>
                    <div class="pv2ss-label">রেফারেল কমিশন</div>
                </div>
            </div>

            <div class="pv2ss-item">
                <div class="pv2ss-icon">
                    <i class="fas fa-crown"></i>
                </div>
                <div class="pv2ss-info">
                    <div class="pv2ss-value">{{ $user->expire_date ? \Carbon\Carbon::parse($user->expire_date)->diffInDays(now()) : 0 }}</div>
                    <div class="pv2ss-label">প্ল্যান মেয়াদ বাকি</div>
                </div>
            </div>

            <div class="pv2ss-item">
                <div class="pv2ss-icon">
                    <i class="fas fa-network-wired"></i>
                </div>
                <div class="pv2ss-info">
                    <div class="pv2ss-value">{{ $user->allReferrals->count() }}</div>
                    <div class="pv2ss-label">মোট রেফারেল</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="pv2-quick-links">
        <a href="{{ route('user.profile.setting') }}" class="pv2ql-item">
            <i class="fas fa-user-cog"></i>
            <span>প্রোফাইল সেটিংস</span>
            <i class="fas fa-chevron-right"></i>
        </a>

        <a href="{{ route('user.change.password') }}" class="pv2ql-item">
            <i class="fas fa-lock"></i>
            <span>পাসওয়ার্ড পরিবর্তন</span>
            <i class="fas fa-chevron-right"></i>
        </a>

        @if($general->kyc_verification)
        <a href="{{ route('user.kyc.form') }}" class="pv2ql-item">
            <i class="fas fa-id-card"></i>
            <span>কেওয়াইসি যাচাইকরণ</span>
            @if($user->kv == 1)
            <span class="pv2ql-badge verified">যাচাইকৃত</span>
            @elseif($user->kv == 2)
            <span class="pv2ql-badge pending">পর্যালোচনায়</span>
            @else
            <i class="fas fa-chevron-right"></i>
            @endif
        </a>
        @endif

        <a href="{{ route('user.twofactor') }}" class="pv2ql-item">
            <i class="fas fa-shield-alt"></i>
            <span>টু-ফ্যাক্টর নিরাপত্তা</span>
            @if($user->ts)
            <span class="pv2ql-badge active">সক্রিয়</span>
            @else
            <i class="fas fa-chevron-right"></i>
            @endif
        </a>
    </div>

    <!-- Logout Button -->
    <div class="pv2-logout-section">
        <button onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="pv2-logout-btn">
            <i class="fas fa-sign-out-alt"></i>
            <span>লগ আউট</span>
        </button>
        <form id="logout-form" action="{{ route('user.logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </div>
</div>
@endsection

@push('style')
<style>
:root {
    --primary: #0F743C;
    --primary-light: #1a9e52;
    --secondary: #C7662B;
    --warning: #F99E2B;
    --error: #DA3E2F;
    --bg-light: #f8faf8;
}

.profile-v2-container {
    min-height: 100vh;
    background: linear-gradient(180deg, #f0fff4 0%, #fffef9 100%);
    padding-bottom: 100px;
}

/* Profile Header */
.pv2-profile-header {
    position: relative;
    height: 180px;
    overflow: hidden;
}

.pv2ph-bg {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
}

.pv2ph-pattern {
    width: 100%;
    height: 100%;
}

.pv2ph-content {
    position: relative;
    z-index: 2;
    padding: 20px;
}

.pv2ph-logo {
    width: 60px;
    height: 60px;
    background: rgba(255,255,255,0.2);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 16px;
    backdrop-filter: blur(10px);
}

.pv2ph-logo-img {
    width: 40px;
    height: 40px;
    object-fit: contain;
}

.pv2ph-plan-badge {
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(15px);
    border-radius: 16px;
    padding: 14px 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    border: 1px solid rgba(255,255,255,0.3);
}

.pv2phb-icon {
    width: 44px;
    height: 44px;
    background: linear-gradient(135deg, #fbbf24, #f59e0b);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 20px;
    flex-shrink: 0;
}

.pv2phb-info {
    flex: 1;
}

.pv2phb-label {
    display: block;
    font-size: 12px;
    color: rgba(255,255,255,0.95);
    font-weight: 600;
    margin-bottom: 2px;
}

.pv2phb-id {
    display: block;
    font-size: 16px;
    color: #fff;
    font-weight: 800;
}

.pv2phb-date {
    font-size: 13px;
    color: rgba(255,255,255,0.9);
    font-weight: 600;
}

.pv2ph-welcome {
    text-align: center;
    padding-top: 10px;
}

.pv2phw-title {
    font-size: 24px;
    font-weight: 800;
    color: #fff;
    margin: 0 0 6px;
}

.pv2phw-username {
    font-size: 16px;
    color: rgba(255,255,255,0.95);
    font-weight: 600;
    margin: 0;
}

/* Balance Section */
.pv2-balance-section {
    padding: 0 16px;
    margin-top: -30px;
    position: relative;
    z-index: 3;
}

.pv2bs-card {
    background: #fff;
    border-radius: 16px;
    padding: 18px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    margin-bottom: 12px;
    transition: all 0.3s;
}

.pv2bs-card.main-balance {
    display: flex;
    align-items: center;
    gap: 14px;
    background: linear-gradient(135deg, #fff 0%, #f9fafb 100%);
    border: 2px solid var(--primary);
}

.pv2bs-icon {
    width: 54px;
    height: 54px;
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 24px;
    flex-shrink: 0;
}

.pv2bs-info {
    flex: 1;
}

.pv2bs-label {
    font-size: 13px;
    color: #6b7280;
    font-weight: 600;
    margin-bottom: 4px;
}

.pv2bs-amount {
    font-size: 24px;
    font-weight: 900;
    color: var(--primary);
}

.pv2bs-arrow {
    color: var(--primary);
    font-size: 20px;
}

.pv2bs-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.pv2bs-card.mini-card {
    padding: 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    text-decoration: none;
    border: 1px solid #e5e7eb;
}

.pv2bs-card.mini-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 25px rgba(0,0,0,0.12);
}

.pv2bs-mini-info .pv2bs-label {
    font-size: 11px;
}

.pv2bs-mini-info .pv2bs-amount {
    font-size: 16px;
}

.pv2bs-mini-arrow {
    color: #9ca3af;
    font-size: 14px;
}

/* Actions Grid */
.pv2-actions-grid {
    padding: 20px 16px;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}

.pv2ag-item {
    text-align: center;
    text-decoration: none;
    transition: all 0.3s;
}

.pv2ag-item:hover {
    transform: translateY(-4px);
}

.pv2ag-icon {
    width: 64px;
    height: 64px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 10px;
    color: #fff;
    font-size: 26px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
    position: relative;
}

.pv2ag-icon::after {
    content: '';
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 18px;
    background: inherit;
    opacity: 0.3;
    filter: blur(12px);
    z-index: -1;
}

.pv2ag-label {
    font-size: 12px;
    font-weight: 600;
    color: #1a1a2e;
    line-height: 1.3;
}

/* Stats Section */
.pv2-stats-section {
    margin: 20px 16px;
    background: #fff;
    border-radius: 20px;
    padding: 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.06);
}

.pv2ss-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 18px;
    font-weight: 800;
    color: #1a1a2e;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 2px solid var(--primary);
}

.pv2ss-title i {
    color: var(--primary);
}

.pv2ss-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}

.pv2ss-item {
    background: linear-gradient(135deg, #f9fafb, #fff);
    border-radius: 14px;
    padding: 14px;
    display: flex;
    align-items: center;
    gap: 12px;
    border: 1px solid #e5e7eb;
}

.pv2ss-icon {
    width: 44px;
    height: 44px;
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 18px;
    flex-shrink: 0;
}

.pv2ss-item:nth-child(2) .pv2ss-icon {
    background: linear-gradient(135deg, #f59e0b, #fbbf24);
}

.pv2ss-item:nth-child(3) .pv2ss-icon {
    background: linear-gradient(135deg, #8b5cf6, #a78bfa);
}

.pv2ss-item:nth-child(4) .pv2ss-icon {
    background: linear-gradient(135deg, #ef4444, #f87171);
}

.pv2ss-info {
    flex: 1;
}

.pv2ss-value {
    font-size: 18px;
    font-weight: 800;
    color: #1a1a2e;
    margin-bottom: 2px;
}

.pv2ss-label {
    font-size: 11px;
    color: #6b7280;
    font-weight: 600;
}

/* Quick Links */
.pv2-quick-links {
    margin: 20px 16px;
    background: #fff;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.06);
}

.pv2ql-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px 18px;
    text-decoration: none;
    color: #1a1a2e;
    font-weight: 600;
    font-size: 15px;
    border-bottom: 1px solid #f3f4f6;
    transition: all 0.3s;
}

.pv2ql-item:last-child {
    border-bottom: none;
}

.pv2ql-item:hover {
    background: #f9fafb;
}

.pv2ql-item > i:first-child {
    width: 44px;
    height: 44px;
    background: linear-gradient(135deg, rgba(15, 116, 60, 0.1), rgba(26, 158, 82, 0.05));
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary);
    font-size: 18px;
}

.pv2ql-item > span:nth-child(2) {
    flex: 1;
}

.pv2ql-item > i:last-child {
    color: #9ca3af;
    font-size: 16px;
}

.pv2ql-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
}

.pv2ql-badge.verified {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(52, 211, 153, 0.1));
    color: #10b981;
}

.pv2ql-badge.pending {
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(251, 191, 36, 0.1));
    color: #f59e0b;
}

.pv2ql-badge.active {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(96, 165, 250, 0.1));
    color: #3b82f6;
}

/* Logout Section */
.pv2-logout-section {
    padding: 20px 16px 30px;
}

.pv2-logout-btn {
    width: 100%;
    padding: 16px;
    background: linear-gradient(135deg, #ef4444, #f87171);
    border: none;
    border-radius: 14px;
    color: #fff;
    font-size: 16px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    cursor: pointer;
    box-shadow: 0 4px 20px rgba(239, 68, 68, 0.3);
    transition: all 0.3s;
}

.pv2-logout-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 25px rgba(239, 68, 68, 0.4);
}

.pv2-logout-btn i {
    font-size: 20px;
}

@media (max-width: 380px) {
    .pv2-actions-grid {
        gap: 12px;
    }
    .pv2ag-icon {
        width: 56px;
        height: 56px;
        font-size: 22px;
    }
    .pv2ag-label {
        font-size: 11px;
    }
    .pv2ss-grid {
        gap: 10px;
    }
}
</style>
@endpush

@push('script')
<script>
(function($) {
    "use strict";

    // Add any interactive features here
    console.log('Profile V2 loaded');

})(jQuery);
</script>
@endpush
