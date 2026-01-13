@extends($activeTemplate.'layouts.master_mobile')
@section('main-content')

<!-- Clean Hero Section -->
<div class="ref-hero">
    <div class="hero-bg"></div>
    <div class="hero-content">
        <div class="hero-stats">
            <div class="stat-circle main-stat">
                <span class="stat-value">{{ $refUsers->total() }}</span>
                <span class="stat-label">মোট রেফার</span>
            </div>
            <div class="stat-circle secondary-stat">
                <span class="stat-value">৳{{ number_format($totalReferralEarnings ?? 0, 0) }}</span>
                <span class="stat-label">মোট আয়</span>
            </div>
        </div>
        @if($isPremium)
        <span class="hero-badge premium"><i class="fas fa-crown"></i> প্রিমিয়াম</span>
        @else
        <a href="{{ route('plans') }}" class="hero-badge free"><i class="fas fa-rocket"></i> আপগ্রেড</a>
        @endif
    </div>
</div>

<!-- Referral Link Card -->
<div class="ref-card share-card">
    <div class="card-header">
        <div class="card-icon"><i class="fas fa-share-alt"></i></div>
        <h3>লিংক শেয়ার করুন</h3>
    </div>
    <div class="ref-link-wrap">
        <input type="text" value="{{ route('home') }}?reference={{ $user->referral_code }}" id="referralURL" readonly>
        <button class="copy-btn" id="copyBoard"><i class="fas fa-copy"></i></button>
    </div>
    <div class="share-icons">
        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('home').'?reference='.$user->referral_code) }}" target="_blank" class="share-icon fb"><i class="fab fa-facebook-f"></i></a>
        <a href="https://wa.me/?text={{ urlencode('Join AGCO! '.route('home').'?reference='.$user->referral_code) }}" target="_blank" class="share-icon wa"><i class="fab fa-whatsapp"></i></a>
        <a href="https://telegram.me/share/url?url={{ urlencode(route('home').'?reference='.$user->referral_code) }}" target="_blank" class="share-icon tg"><i class="fab fa-telegram-plane"></i></a>
    </div>
</div>

<!-- Your Current Plan Status -->
<div class="ref-card plan-status-card">
    @if($isPremium)
    <div class="plan-status premium">
        <div class="plan-icon"><i class="fas fa-crown"></i></div>
        <div class="plan-info">
            <span class="plan-type">প্রিমিয়াম মেম্বার</span>
            <span class="plan-name">{{ $user->plan->name ?? 'প্রিমিয়াম প্ল্যান' }}</span>
        </div>
        <div class="plan-badge"><i class="fas fa-check"></i> সক্রিয়</div>
    </div>
    @else
    <div class="plan-status free">
        <div class="plan-icon"><i class="fas fa-user"></i></div>
        <div class="plan-info">
            <span class="plan-type">ফ্রি মেম্বার</span>
            <span class="plan-name">স্টেপ কমিশন সিস্টেম</span>
        </div>
        <a href="{{ route('plans') }}" class="plan-upgrade-btn">আপগ্রেড <i class="fas fa-arrow-up"></i></a>
    </div>
    @endif
</div>

<!-- How to Earn - Simple Visual Guide -->
@if($signupCommissionEnabled || $hasAnyCommission)
<div class="ref-card how-earn-card">
    <div class="card-header">
        <h3><i class="fas fa-gift"></i> আপনার রেফারেল বোনাস</h3>
    </div>

    @if($isPremium)
    <!-- Premium User Commission Display -->
    <div class="premium-commission-box">
        <div class="commission-header">
            <i class="fas fa-crown"></i> আপনি প্রতি রেফারের কার্যক্রম থেকে কমিশন পাবেন
        </div>
        <div class="commission-grid">
            @if($taskCommissions->count() > 0)
            <div class="commission-item">
                <div class="comm-icon"><i class="fas fa-tasks"></i></div>
                <div class="comm-info">
                    <span class="comm-label">টাস্ক কমিশন</span>
                    <span class="comm-value">{{ showAmount($taskCommissions->first()->percent ?? 0) }}%</span>
                </div>
            </div>
            @endif
            @if($planCommissions->count() > 0)
            <div class="commission-item">
                <div class="comm-icon"><i class="fas fa-box"></i></div>
                <div class="comm-info">
                    <span class="comm-label">প্ল্যান কমিশন</span>
                    <span class="comm-value">{{ showAmount($planCommissions->first()->percent ?? 0) }}%</span>
                </div>
            </div>
            @endif
            @if($depositCommissions->count() > 0)
            <div class="commission-item">
                <div class="comm-icon"><i class="fas fa-coins"></i></div>
                <div class="comm-info">
                    <span class="comm-label">ডিপোজিট কমিশন</span>
                    <span class="comm-value">{{ showAmount($depositCommissions->first()->percent ?? 0) }}%</span>
                </div>
            </div>
            @endif
        </div>
        <div class="commission-note">
            <i class="fas fa-info-circle"></i> আপনার রেফার করা ব্যক্তি যখন টাস্ক করবে, প্ল্যান কিনবে বা ডিপোজিট করবে, তখন আপনি উপরের হারে কমিশন পাবেন।
        </div>
    </div>
    @else
    <!-- Free User Step Commission Display -->
    <div class="free-commission-box">
        @if($stepCommissionEnabled ?? false)
        <div class="step-system-display">
            <div class="step-header">
                <i class="fas fa-layer-group"></i> স্টেপ কমিশন সিস্টেম
            </div>
            <div class="step-explanation">
                প্রতিটি নতুন রেফারে আপনার কমিশন বাড়বে!
            </div>

            <div class="step-progress-box">
                <div class="current-step">
                    <span class="step-count">{{ $userReferralCount }}</span>
                    <span class="step-text">সম্পন্ন রেফার</span>
                </div>
                <div class="next-step">
                    <span class="next-label">পরবর্তী রেফারে পাবেন</span>
                    <span class="next-amount">৳{{ showAmount($nextStepCommission) }}</span>
                </div>
            </div>

            <div class="step-ladder-full">
                @php
                    $baseAmt = $freeUserSettings['step_base_amount'] ?? 100;
                    $incAmt = $freeUserSettings['step_increment'] ?? 100;
                    $maxSteps = min($freeUserSettings['step_max'] ?? 10, 5);
                @endphp
                @for($i = 1; $i <= $maxSteps; $i++)
                <div class="ladder-step {{ $userReferralCount >= $i ? 'completed' : ($nextStepNumber == $i ? 'current' : '') }}">
                    <span class="ladder-num">{{ $i }}</span>
                    <span class="ladder-amt">৳{{ showAmount($baseAmt + (($i - 1) * $incAmt)) }}</span>
                </div>
                @endfor
            </div>
        </div>
        @else
        <div class="flat-commission-display">
            <div class="flat-amount">৳{{ showAmount($signupReferrerAmount ?? 0) }}</div>
            <div class="flat-label">প্রতি রেফারে আপনি পাবেন</div>
        </div>
        @endif

        <div class="upgrade-prompt">
            <i class="fas fa-arrow-up"></i>
            <span>প্রিমিয়াম হলে আরও বেশি কমিশন পাবেন!</span>
            <a href="{{ route('plans') }}">আপগ্রেড</a>
        </div>
    </div>
    @endif
</div>
@endif

<!-- Quick Stats Grid -->
<div class="stats-grid">
    <div class="stat-box green">
        <i class="fas fa-wallet"></i>
        <span class="stat-val">৳{{ showAmount($balanceBreakdown['main_balance']) }}</span>
        <span class="stat-lbl">ব্যালেন্স</span>
    </div>
    <div class="stat-box orange">
        <i class="fas fa-hand-holding-usd"></i>
        <span class="stat-val">৳{{ showAmount($totalReferralEarnings) }}</span>
        <span class="stat-lbl">রেফারেল আয়</span>
    </div>
    @if($balanceBreakdown['total_hold'] > 0)
    <div class="stat-box red">
        <i class="fas fa-lock"></i>
        <span class="stat-val">৳{{ showAmount($balanceBreakdown['total_hold']) }}</span>
        <span class="stat-lbl">হোল্ড</span>
    </div>
    @endif
</div>

<!-- Signup Bonus Notice -->
@if($userGotSignupBonus && $referrer)
<div class="notice-card success">
    <i class="fas fa-gift"></i>
    <span>আপনি <strong>৳{{ showAmount($signupReferredAmount) }}</strong> বোনাস পেয়েছেন!</span>
</div>
@endif

<!-- My Referrals Section -->
<div class="ref-card">
    <div class="card-header">
        <h3><i class="fas fa-users"></i> আমার রেফারেলস</h3>
        <span class="count-pill">{{ $refUsers->total() }}</span>
    </div>

    @if($refUsers->count() > 0)
    <div class="ref-list">
        @foreach($refUsers as $log)
        <div class="ref-item">
            <div class="ref-avatar">{{ strtoupper(substr($log->username, 0, 1)) }}</div>
            <div class="ref-info">
                <span class="ref-name">{{ $log->fullname ?: $log->username }}</span>
                <span class="ref-date">{{ $log->created_at->format('d M, Y') }}</span>
            </div>
            <div class="ref-earn">
                <span class="earn-amt">+৳{{ showAmount($signupReferrerAmount ?? 0) }}</span>
                @if($log->status == 1)
                <span class="earn-status ok"><i class="fas fa-check"></i></span>
                @else
                <span class="earn-status wait"><i class="fas fa-clock"></i></span>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="empty-state">
        <i class="fas fa-user-friends"></i>
        <p>এখনো কেউ জয়েন করেনি</p>
        <small>লিংক শেয়ার করে আয় শুরু করুন!</small>
    </div>
    @endif
</div>

@if($refUsers->hasPages())
<div class="pagination-wrap">
    {{ $refUsers->links() }}
</div>
@endif

<!-- Recent Earnings -->
@if($signupCommissions->count() > 0)
<div class="ref-card">
    <div class="card-header">
        <h3><i class="fas fa-coins"></i> সাম্প্রতিক আয়</h3>
    </div>
    <div class="earnings-list">
        @foreach($signupCommissions->take(5) as $commission)
        <div class="earn-item">
            <div class="earn-icon"><i class="fas fa-plus"></i></div>
            <div class="earn-details">
                <span class="earn-title">{{ Str::limit($commission->details, 25) }}</span>
                <span class="earn-time">{{ $commission->created_at->diffForHumans() }}</span>
            </div>
            <span class="earn-amount">+৳{{ showAmount($commission->amount) }}</span>
        </div>
        @endforeach
    </div>
    @if($signupCommissions->count() > 5)
    <a href="{{ route('user.transactions') }}?type=referral" class="view-all">সব দেখুন <i class="fas fa-arrow-right"></i></a>
    @endif
</div>
@endif

<div style="height:80px"></div>
@endsection

@push('style')
<style>
:root {
    --primary: #0F743C;
    --primary-dark: #0a5229;
    --secondary: #C7662B;
    --warning: #F99E2B;
    --error: #DA3E2F;
    --success: #2ECC71;
    --info: #4A90E2;
}

/* Hero Section */
.ref-hero {
    position: relative;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    padding: 30px 20px;
    margin-bottom: 16px;
    overflow: hidden;
}
.hero-bg {
    position: absolute;
    top: -50%;
    right: -30%;
    width: 200px;
    height: 200px;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
}
.hero-content {
    position: relative;
    z-index: 1;
    text-align: center;
}
.hero-stats {
    display: flex;
    justify-content: center;
    gap: 24px;
    margin-bottom: 16px;
}
.stat-circle {
    text-align: center;
}
.stat-circle .stat-value {
    display: block;
    font-size: 28px;
    font-weight: 800;
    color: #fff;
    line-height: 1.2;
}
.stat-circle .stat-label {
    font-size: 12px;
    color: rgba(255,255,255,0.8);
    font-weight: 500;
}
.hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 18px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
}
.hero-badge.premium {
    background: linear-gradient(135deg, var(--warning) 0%, #e88a1a 100%);
    color: #fff;
}
.hero-badge.free {
    background: #fff;
    color: var(--primary);
}

/* Cards */
.ref-card {
    background: #fff;
    margin: 0 16px 16px;
    border-radius: 16px;
    padding: 18px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
}
.share-card {
    text-align: center;
    background: linear-gradient(135deg, #f8faf9 0%, #e8f5e9 100%);
    border: 1px solid rgba(15,116,60,0.15);
}
.share-card .card-header {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    margin-bottom: 16px;
}
.share-card .card-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 22px;
}
.share-card h3 {
    font-size: 16px;
    font-weight: 700;
    color: #1a1a1a;
    margin: 0;
}
.card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
}
.card-header h3 {
    font-size: 14px;
    font-weight: 700;
    color: #1a1a1a;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}
.card-header h3 i {
    color: var(--primary);
}
.count-pill {
    background: var(--primary);
    color: #fff;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 700;
}

/* Referral Link */
.ref-link-wrap {
    display: flex;
    gap: 8px;
    margin-bottom: 16px;
}
.ref-link-wrap input {
    flex: 1;
    padding: 14px 16px;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    font-size: 13px;
    background: #fff;
    color: #333;
    font-weight: 500;
}
.copy-btn {
    width: 52px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    border: none;
    border-radius: 12px;
    color: #fff;
    font-size: 18px;
    cursor: pointer;
    transition: transform 0.2s;
}
.copy-btn:active {
    transform: scale(0.95);
}

/* Share Icons */
.share-icons {
    display: flex;
    justify-content: center;
    gap: 12px;
}
.share-icon {
    width: 46px;
    height: 46px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 20px;
    text-decoration: none;
    transition: transform 0.2s;
}
.share-icon:hover {
    transform: translateY(-2px);
}
.share-icon.fb { background: #1877f2; }
.share-icon.wa { background: #25d366; }
.share-icon.tg { background: #0088cc; }

/* How to Earn Card */
.how-earn-card {
    background: linear-gradient(135deg, #fefefe 0%, #f8faf9 100%);
    border: 1px solid rgba(15,116,60,0.1);
}
.earn-steps {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-bottom: 20px;
    padding: 16px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    border-radius: 12px;
}
.step-item {
    text-align: center;
}
.step-num {
    width: 36px;
    height: 36px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    font-weight: 800;
    color: #fff;
    margin: 0 auto 6px;
}
.step-text {
    font-size: 11px;
    color: rgba(255,255,255,0.9);
    font-weight: 600;
}
.step-arrow {
    color: rgba(255,255,255,0.5);
    font-size: 12px;
}

/* Bonus Compare */
.bonus-compare {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 16px;
}
.bonus-card {
    background: #f8f9fa;
    border-radius: 14px;
    padding: 16px 12px;
    text-align: center;
    border: 2px solid transparent;
    transition: all 0.2s;
}
.bonus-card.active {
    border-color: var(--primary);
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
}
.bonus-card.premium.active {
    border-color: var(--warning);
    background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
}
.bonus-tag {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: 700;
    margin-bottom: 10px;
}
.bonus-tag.free {
    background: #e5e7eb;
    color: #666;
}
.bonus-tag.premium {
    background: linear-gradient(135deg, var(--warning) 0%, #e88a1a 100%);
    color: #fff;
}
.bonus-amount {
    font-size: 24px;
    font-weight: 800;
    color: var(--primary);
    margin-bottom: 4px;
}
.bonus-label {
    font-size: 11px;
    color: #666;
    margin-bottom: 8px;
}
.bonus-features {
    text-align: left;
    margin-bottom: 10px;
}
.feature-row {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 0;
    border-bottom: 1px solid rgba(0,0,0,0.05);
}
.feature-row:last-child {
    border-bottom: none;
}
.feature-icon {
    width: 22px;
    height: 22px;
    background: var(--warning);
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    color: #fff;
}
.feature-text {
    flex: 1;
    font-size: 11px;
    color: #555;
}
.feature-val {
    font-size: 12px;
    font-weight: 700;
    color: var(--warning);
}
.your-plan {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 10px;
    color: var(--primary);
    font-weight: 700;
}
.bonus-card.premium .your-plan {
    color: var(--warning);
}
.upgrade-link {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    color: var(--warning);
    font-weight: 700;
    text-decoration: none;
}

/* Step Progress Mini */
.step-progress-mini {
    text-align: center;
}
.step-current {
    margin-bottom: 10px;
}
.step-label {
    display: block;
    font-size: 11px;
    color: #666;
    margin-bottom: 2px;
}
.step-amount {
    display: block;
    font-size: 22px;
    font-weight: 800;
    color: var(--primary);
}
.step-ladder {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    justify-content: center;
}
.ladder-item {
    font-size: 9px;
    padding: 3px 6px;
    border-radius: 8px;
    background: #f0f0f0;
    color: #999;
}
.ladder-item.done {
    background: var(--primary);
    color: #fff;
}
.ladder-item.next {
    background: linear-gradient(135deg, var(--warning) 0%, #e88a1a 100%);
    color: #fff;
    animation: pulse 1.5s infinite;
}
@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

/* Plan Status Card */
.plan-status-card {
    padding: 0;
    overflow: hidden;
}
.plan-status {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
}
.plan-status.premium {
    background: linear-gradient(135deg, var(--warning) 0%, #e88a1a 100%);
}
.plan-status.free {
    background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
}
.plan-icon {
    width: 48px;
    height: 48px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: #fff;
}
.plan-info {
    flex: 1;
}
.plan-type {
    display: block;
    font-size: 11px;
    color: rgba(255,255,255,0.8);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.plan-name {
    display: block;
    font-size: 16px;
    font-weight: 700;
    color: #fff;
}
.plan-badge {
    background: rgba(255,255,255,0.25);
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    color: #fff;
}
.plan-upgrade-btn {
    background: var(--primary);
    color: #fff;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 6px;
}

/* Premium Commission Box */
.premium-commission-box {
    padding: 16px;
    background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
    border-radius: 12px;
}
.commission-header {
    font-size: 13px;
    color: #92400e;
    font-weight: 600;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.commission-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
    gap: 10px;
    margin-bottom: 12px;
}
.commission-item {
    background: #fff;
    border-radius: 10px;
    padding: 12px;
    text-align: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}
.comm-icon {
    width: 36px;
    height: 36px;
    background: var(--warning);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 8px;
    color: #fff;
    font-size: 14px;
}
.comm-label {
    display: block;
    font-size: 10px;
    color: #666;
    margin-bottom: 4px;
}
.comm-value {
    display: block;
    font-size: 18px;
    font-weight: 800;
    color: var(--warning);
}
.commission-note {
    font-size: 11px;
    color: #78350f;
    background: rgba(146,64,14,0.1);
    padding: 10px;
    border-radius: 8px;
}

/* Free Commission Box */
.free-commission-box {
    padding: 16px;
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    border-radius: 12px;
}
.step-system-display {
    margin-bottom: 16px;
}
.step-header {
    font-size: 14px;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.step-explanation {
    font-size: 12px;
    color: #166534;
    margin-bottom: 16px;
}
.step-progress-box {
    display: flex;
    gap: 12px;
    margin-bottom: 16px;
}
.current-step, .next-step {
    flex: 1;
    background: #fff;
    border-radius: 12px;
    padding: 14px;
    text-align: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}
.step-count {
    display: block;
    font-size: 28px;
    font-weight: 800;
    color: var(--primary);
}
.step-text {
    display: block;
    font-size: 10px;
    color: #666;
}
.next-label {
    display: block;
    font-size: 10px;
    color: #666;
    margin-bottom: 4px;
}
.next-amount {
    display: block;
    font-size: 24px;
    font-weight: 800;
    color: var(--warning);
}
.step-ladder-full {
    display: flex;
    gap: 6px;
    overflow-x: auto;
    padding-bottom: 4px;
}
.ladder-step {
    flex: 1;
    min-width: 50px;
    background: #e5e7eb;
    border-radius: 8px;
    padding: 8px 4px;
    text-align: center;
}
.ladder-step.completed {
    background: var(--primary);
}
.ladder-step.current {
    background: var(--warning);
    animation: pulse 1.5s infinite;
}
.ladder-num {
    display: block;
    font-size: 12px;
    font-weight: 700;
    color: #666;
}
.ladder-step.completed .ladder-num,
.ladder-step.current .ladder-num {
    color: #fff;
}
.ladder-amt {
    display: block;
    font-size: 10px;
    color: #888;
}
.ladder-step.completed .ladder-amt,
.ladder-step.current .ladder-amt {
    color: rgba(255,255,255,0.9);
}
.flat-commission-display {
    text-align: center;
    padding: 20px;
}
.flat-amount {
    font-size: 32px;
    font-weight: 800;
    color: var(--primary);
}
.flat-label {
    font-size: 12px;
    color: #666;
}
.upgrade-prompt {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px;
    background: rgba(15,116,60,0.1);
    border-radius: 8px;
    font-size: 11px;
    color: var(--primary);
}
.upgrade-prompt a {
    margin-left: auto;
    background: var(--primary);
    color: #fff;
    padding: 6px 12px;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
}

/* Earn Tip */
.earn-tip {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px;
    background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
    border-radius: 10px;
    font-size: 12px;
    color: #2e7d32;
}
.earn-tip i {
    font-size: 18px;
    color: var(--warning);
}

/* Stats Grid */
.stats-grid {
    display: flex;
    gap: 10px;
    margin: 0 16px 16px;
    flex-wrap: wrap;
}
.stat-box {
    flex: 1;
    min-width: calc(50% - 10px);
    background: #fff;
    border-radius: 14px;
    padding: 14px;
    display: flex;
    align-items: center;
    gap: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    border-left: 4px solid;
}
.stat-box.green { border-color: var(--primary); }
.stat-box.orange { border-color: var(--warning); }
.stat-box.red { border-color: var(--error); }
.stat-box i {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    color: #fff;
}
.stat-box.green i { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); }
.stat-box.orange i { background: linear-gradient(135deg, var(--warning) 0%, #e88a1a 100%); }
.stat-box.red i { background: linear-gradient(135deg, var(--error) 0%, #c23520 100%); }
.stat-val {
    display: block;
    font-size: 16px;
    font-weight: 800;
    color: #1a1a1a;
}
.stat-lbl {
    font-size: 11px;
    color: #666;
}

/* Notice Card */
.notice-card {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 0 16px 16px;
    padding: 14px 16px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 600;
}
.notice-card.success {
    background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
    color: #2e7d32;
}
.notice-card i {
    font-size: 18px;
}

/* Referral List */
.ref-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.ref-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 12px;
    transition: background 0.2s;
}
.ref-item:hover {
    background: #f0f0f0;
}
.ref-avatar {
    width: 42px;
    height: 42px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 16px;
    font-weight: 700;
    flex-shrink: 0;
}
.ref-info {
    flex: 1;
    min-width: 0;
}
.ref-name {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #1a1a1a;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.ref-date {
    font-size: 11px;
    color: #999;
}
.ref-earn {
    text-align: right;
    flex-shrink: 0;
}
.earn-amt {
    display: block;
    font-size: 14px;
    font-weight: 700;
    color: var(--primary);
}
.earn-status {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    font-size: 10px;
}
.earn-status.ok {
    background: var(--success);
    color: #fff;
}
.earn-status.wait {
    background: var(--warning);
    color: #fff;
}

/* Earnings List */
.earnings-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.earn-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    background: #f8f9fa;
    border-radius: 10px;
}
.earn-icon {
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg, var(--success) 0%, #27ae60 100%);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 12px;
}
.earn-details {
    flex: 1;
    min-width: 0;
}
.earn-title {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: #333;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.earn-time {
    font-size: 10px;
    color: #999;
}
.earn-amount {
    font-size: 14px;
    font-weight: 700;
    color: var(--primary);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 30px 20px;
    color: #999;
}
.empty-state i {
    font-size: 40px;
    color: #e5e7eb;
    margin-bottom: 12px;
}
.empty-state p {
    font-size: 14px;
    margin: 0 0 4px;
    color: #666;
}
.empty-state small {
    font-size: 12px;
}

/* View All */
.view-all {
    display: block;
    text-align: center;
    padding: 12px;
    margin-top: 12px;
    border-top: 1px solid #eee;
    color: var(--primary);
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
}

/* Pagination */
.pagination-wrap {
    padding: 0 16px;
    margin-bottom: 16px;
}

@media (max-width: 400px) {
    .hero-stats {
        gap: 16px;
    }
    .stat-circle .stat-value {
        font-size: 24px;
    }
    .stat-box {
        min-width: 100%;
    }
}
</style>
@endpush

@push('script')
<script>
document.getElementById('copyBoard').addEventListener('click', function(){
    var copyText = document.getElementById("referralURL");
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    document.execCommand("copy");
    this.innerHTML = '<i class="fas fa-check"></i>';
    setTimeout(() => { this.innerHTML = '<i class="fas fa-copy"></i>'; }, 2000);
    if(typeof iziToast !== 'undefined') {
        iziToast.success({message: "কপি হয়েছে!", position: "topRight"});
    }
});
</script>
@endpush
