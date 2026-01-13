@extends($activeTemplate.'layouts.master_mobile')
@section('main-content')
@php
    $user = auth()->user();
@endphp

<!-- Ultra Modern Header with Gradient -->
<div class="withdraw-header-ultra">
    <div class="header-bg-pattern-animated"></div>
    <div class="header-content-ultra">
        <div class="header-row-top">
            <a href="{{ route('user.home') }}" class="back-btn-ultra">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="header-center-content">
                <div class="header-icon-ultra">
                    <i class="fas fa-arrow-up"></i>
                </div>
                <h2 class="header-title-ultra">উত্তোলন করুন</h2>
                <p class="header-subtitle-ultra">আপনার আয় নিজের অ্যাকাউন্টে নিন</p>
            </div>
            <div class="header-spacer-ultra"></div>
        </div>

        <!-- Balance Card with Glassmorphism -->
        <div class="balance-card-glass-ultra">
            <div class="balance-gradient-bg"></div>
            <div class="balance-content-glass">
                <div class="balance-icon-glass">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="balance-info-glass">
                    <span class="balance-label-glass">উত্তোলনযোগ্য ব্যালেন্স</span>
                    <span class="balance-amount-glass">৳{{ showAmount($user->balance) }}</span>
                </div>
            </div>
        </div>

    </div>
</div>

@if($isPremiumPackage && $withdrawStatus['can_withdraw'])
<!-- Premium User Withdrawal Status -->
<div class="premium-withdraw-status">
    <div class="pws-header">
        <div class="pws-icon"><i class="fas fa-crown"></i></div>
        <span class="pws-badge">প্রিমিয়াম</span>
    </div>
    <div class="pws-content">
        @if(isset($withdrawStatus['remaining']))
        <div class="pws-stat">
            <span class="pws-label">যেকোনো সময় উত্তোলন বাকি</span>
            <span class="pws-value">{{ $withdrawStatus['remaining'] }}/{{ $withdrawStatus['total'] ?? 5 }}</span>
        </div>
        <div class="pws-progress">
            <div class="pws-progress-bar" style="width: {{ (($withdrawStatus['total'] ?? 5) - ($withdrawStatus['remaining'] ?? 0)) / ($withdrawStatus['total'] ?? 5) * 100 }}%"></div>
        </div>
        @endif
        <div class="pws-message">
            <i class="fas fa-check-circle"></i>
            {{ $withdrawStatus['reason'] ?? 'উত্তোলন করতে পারবেন' }}
        </div>
    </div>
</div>
@endif

@if(!$isPremiumPackage && $nonPremiumLimitInfo)
@php
    $general = gs();
    $freeUserMinWithdraw = $general->free_user_min_withdraw ?? 50;
    $freeUserMaxWithdraw = $general->free_user_max_withdraw ?? 1000;
    $freeUserDailyLimit = $general->free_user_daily_withdraw_limit ?? 100;

    // Calculate today's withdrawals
    $todayWithdrawals = \App\Models\Withdrawal::where('user_id', $user->id)
        ->whereDate('created_at', now()->toDateString())
        ->whereIn('status', [1, 2])
        ->sum('amount');
    $dailyRemaining = max(0, $freeUserDailyLimit - $todayWithdrawals);
@endphp

<!-- Free User Withdrawal Limits Card -->
<div class="free-user-limits-card">
    <div class="ful-header">
        <div class="ful-icon"><i class="fas fa-user"></i></div>
        <div class="ful-title">
            <strong>ফ্রি মেম্বার উত্তোলন সীমা</strong>
            <span class="ful-badge">ফ্রি সদস্য</span>
        </div>
    </div>
    <div class="ful-limits-grid">
        <div class="ful-limit-item">
            <div class="ful-limit-icon min"><i class="fas fa-arrow-down"></i></div>
            <div class="ful-limit-info">
                <span class="ful-limit-label">সর্বনিম্ন</span>
                <span class="ful-limit-value">৳{{ showAmount($freeUserMinWithdraw) }}</span>
            </div>
        </div>
        <div class="ful-limit-item">
            <div class="ful-limit-icon max"><i class="fas fa-arrow-up"></i></div>
            <div class="ful-limit-info">
                <span class="ful-limit-label">প্রতিবার সর্বোচ্চ</span>
                <span class="ful-limit-value">৳{{ showAmount($freeUserMaxWithdraw) }}</span>
            </div>
        </div>
        <div class="ful-limit-item daily">
            <div class="ful-limit-icon daily"><i class="fas fa-calendar-day"></i></div>
            <div class="ful-limit-info">
                <span class="ful-limit-label">দৈনিক লিমিট</span>
                <span class="ful-limit-value">৳{{ showAmount($freeUserDailyLimit) }}</span>
            </div>
            <div class="ful-daily-remaining">
                <span class="ful-remaining-label">আজ বাকি:</span>
                <span class="ful-remaining-value {{ $dailyRemaining <= 0 ? 'exhausted' : '' }}">৳{{ showAmount($dailyRemaining) }}</span>
            </div>
        </div>
    </div>
</div>

<!-- Non-Premium Lifetime Limits Banner -->
<div class="lifetime-limit-banner {{ $nonPremiumLimitInfo['remaining'] <= 0 ? 'exhausted' : ($nonPremiumLimitInfo['percent_used'] >= 80 ? 'warning' : '') }}">
    <div class="llb-header">
        <div class="llb-icon">
            <i class="fas {{ $nonPremiumLimitInfo['remaining'] <= 0 ? 'fa-ban' : 'fa-hourglass-half' }}"></i>
        </div>
        <div class="llb-title">
            <strong>লাইফটাইম উত্তোলন লিমিট</strong>
            <span class="llb-badge free">ফ্রি মেম্বার</span>
        </div>
    </div>
    <div class="llb-progress">
        <div class="llb-progress-bar" style="width: {{ min(100, $nonPremiumLimitInfo['percent_used']) }}%"></div>
    </div>
    <div class="llb-stats">
        <div class="llb-stat">
            <span class="llb-stat-label">ব্যবহৃত</span>
            <span class="llb-stat-value used">৳{{ showAmount($nonPremiumLimitInfo['used']) }}</span>
        </div>
        <div class="llb-stat">
            <span class="llb-stat-label">বাকি আছে</span>
            <span class="llb-stat-value remaining">৳{{ showAmount($nonPremiumLimitInfo['remaining']) }}</span>
        </div>
        <div class="llb-stat">
            <span class="llb-stat-label">মোট লিমিট</span>
            <span class="llb-stat-value total">৳{{ showAmount($nonPremiumLimitInfo['limit']) }}</span>
        </div>
    </div>
    @if($nonPremiumLimitInfo['remaining'] <= 0)
    <div class="llb-action exhausted">
        <i class="fas fa-exclamation-triangle"></i>
        <span>আপনার লাইফটাইম লিমিট শেষ। প্রিমিয়াম প্ল্যান নিলে আনলিমিটেড উত্তোলন করতে পারবেন।</span>
    </div>
    @elseif($nonPremiumLimitInfo['percent_used'] >= 80)
    <div class="llb-action warning">
        <i class="fas fa-exclamation-circle"></i>
        <span>লিমিট প্রায় শেষ! প্রিমিয়াম প্ল্যান নিয়ে আনলিমিটেড উত্তোলন করুন।</span>
    </div>
    @endif
    <a href="{{ route('plans') }}" class="llb-upgrade-btn">
        <i class="fas fa-crown"></i> প্রিমিয়াম আপগ্রেড
    </a>
</div>
@endif

@if(!$withdrawStatus['can_withdraw'])
<!-- Status Alert -->
<div class="simple-alert info">
    <i class="fas fa-clock"></i>
    <div>
        <strong>{{ $withdrawStatus['reason'] }}</strong>
        @if($withdrawStatus['type'] === 'weekly' && isset($withdrawStatus['next_date']))
            <p>পরবর্তী তারিখ: {{ $withdrawStatus['next_date'] }}</p>
        @endif
    </div>
</div>
@endif

@if($withdrawStatus['can_withdraw'])
<!-- Withdraw Form -->
<div class="form-section">
    <form action="{{route('user.withdraw.money')}}" method="post" id="withdrawForm">
        @csrf

        <!-- Method Selection with Visual Cards -->
        <div class="form-group">
            <label class="form-lbl"><i class="fas fa-university"></i> উত্তোলন পদ্ধতি</label>
            <div class="method-cards-container">
                @foreach($withdrawMethods as $method)
                <label class="method-card" data-method-id="{{ $method->id }}">
                    <input type="radio" name="method_id" value="{{ $method->id }}"
                        data-min="{{ $method->min_limit }}"
                        data-max="{{ $method->max_limit }}"
                        data-fixed="{{ $method->fixed_charge }}"
                        data-percent="{{ $method->percent_charge }}"
                        {{ old('method_id') == $method->id ? 'checked' : '' }} required>
                    <div class="method-card-inner">
                        @if($method->image)
                        <img src="{{ getImage(getFilePath('withdraw').'/'.$method->image) }}" alt="{{ $method->name }}" class="method-img">
                        @else
                        <div class="method-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        @endif
                        <span class="method-name">{{ $method->name }}</span>
                    </div>
                </label>
                @endforeach
            </div>
        </div>

        <!-- Wallet Number Input -->
        <div class="form-group">
            <label class="form-lbl"><i class="fas fa-mobile-alt"></i> ওয়ালেট নম্বর</label>
            <input type="text" name="wallet_number" value="{{ old('wallet_number') }}" class="form-input" placeholder="যেমন: 01XXXXXXXXX" required minlength="11" maxlength="20" inputmode="numeric">
            <small class="input-hint">আপনার বিকাশ/নগদ/রকেট নম্বর দিন</small>
        </div>

        <!-- Amount Input -->
        <div class="form-group">
            <label class="form-lbl"><i class="fas fa-coins"></i> পরিমাণ</label>
            <div class="input-group">
                <span class="input-prefix">৳</span>
                <input type="number" step="any" name="amount" value="{{ old('amount') }}" class="form-input" placeholder="0" required>
            </div>
            <div class="limits-hint">সীমা: <span class="min">0</span> - <span class="max">0</span> টাকা</div>
        </div>

        <!-- Summary -->
        <div class="summary-box">
            <div class="summary-row">
                <span>চার্জ</span>
                <strong class="charge-text">৳<span class="charge">0</span></strong>
            </div>
            <div class="summary-row total">
                <span>আপনি পাবেন</span>
                <strong>৳<span class="receivable">0</span></strong>
            </div>
        </div>

        <!-- Withdrawal PIN -->
        <div class="form-group pin-section">
            <label class="form-lbl"><i class="fas fa-lock"></i> উত্তোলন পিন</label>
            <input type="password" name="withdrawal_pin" class="form-input pin-input" placeholder="● ● ● ●" maxlength="6" minlength="4" required pattern="[0-9]{4,6}" inputmode="numeric" autocomplete="off">
            <small class="pin-hint">রেজিস্ট্রেশনের সময় দেওয়া ৪-৬ ডিজিট পিন</small>
        </div>

        @if(auth()->user()->ts)
        <!-- 2FA Code -->
        <div class="form-group">
            <label class="form-lbl"><i class="fas fa-key"></i> 2FA কোড</label>
            <input type="text" name="authenticator_code" class="form-input" placeholder="২ফ্যাক্টর কোড লিখুন" required>
        </div>
        @endif

        <button type="submit" class="btn-submit withdraw-btn">
            <i class="fas fa-paper-plane me-2"></i>উত্তোলন করুন
        </button>
    </form>
</div>
@else
<!-- Cannot Withdraw Message -->
<div class="cannot-withdraw-section">
    <div class="cw-icon">
        <i class="fas fa-hourglass-half"></i>
    </div>
    <h4>এখন উত্তোলন করতে পারবেন না</h4>
    <p>{{ $withdrawStatus['reason'] }}</p>

    @if(!$user->runningPlan)
        <a href="{{ route('plans') }}" class="cw-btn">
            <i class="fas fa-crown"></i> প্ল্যান কিনুন
        </a>
    @endif

    @if(isset($withdrawStatus['next_date']))
        <div class="cw-countdown">
            <i class="fas fa-calendar-check"></i>
            <span>পরবর্তী উত্তোলন: <strong>{{ $withdrawStatus['next_date'] }}</strong></span>
        </div>
    @endif
</div>
@endif

<!-- Quick Info -->
<div class="info-card warning">
    <div class="info-icon"><i class="fas fa-info-circle"></i></div>
    <div class="info-content">
        <strong>উত্তোলন নিয়ম</strong>
        @if($user->plan)
            <p>• প্ল্যান কেনার পর {{ $user->plan->anytime_withdraw_limit ?? 5 }}টি যেকোনো সময় উত্তোলন<br>
            • এরপর প্রতি সপ্তাহে ১টি নির্দিষ্ট দিনে উত্তোলন</p>
        @else
            <p>উত্তোলনের জন্য প্ল্যান কেনা আবশ্যক</p>
        @endif
    </div>
</div>

<div style="height: 20px;"></div>
@endsection

@push('style')
<style>
@import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700;800&display=swap');

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Hind Siliguri', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    background: #0a0e27;
    overflow-x: hidden;
}

/* Premium Withdraw Status */
.premium-withdraw-status {
    background: linear-gradient(135deg, #fff9e6 0%, #fff3cd 100%);
    margin: 16px;
    border-radius: 16px;
    padding: 16px;
    border: 2px solid #ffc107;
    box-shadow: 0 4px 15px rgba(255, 193, 7, 0.2);
}
.pws-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
}
.pws-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #ffc107, #ffdb4d);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #856404;
    font-size: 18px;
}
.pws-badge {
    background: linear-gradient(135deg, #ffc107, #ffdb4d);
    color: #856404;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
}
.pws-content {
    background: white;
    border-radius: 12px;
    padding: 14px;
}
.pws-stat {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}
.pws-label {
    font-size: 13px;
    color: #6c757d;
}
.pws-value {
    font-size: 18px;
    font-weight: 800;
    color: #0F743C;
}
.pws-progress {
    height: 8px;
    background: #e9ecef;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 12px;
}
.pws-progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #0F743C, #2ecc71);
    border-radius: 4px;
    transition: width 0.3s;
}
.pws-message {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #0F743C;
    font-weight: 600;
}
.pws-message i {
    font-size: 16px;
}

/* Ultra Modern Header */
.withdraw-header-ultra {
    background: linear-gradient(135deg, #DA3E2F 0%, #C7662B 50%, #DA3E2F 100%);
    padding: 30px 20px 40px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(218, 62, 47, 0.3);
}

.header-bg-pattern-animated {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image:
        repeating-linear-gradient(45deg, transparent, transparent 10px, rgba(255,255,255,0.03) 10px, rgba(255,255,255,0.03) 20px),
        repeating-linear-gradient(-45deg, transparent, transparent 10px, rgba(255,255,255,0.03) 10px, rgba(255,255,255,0.03) 20px);
    animation: patternMove 20s linear infinite;
}

@keyframes patternMove {
    0% { transform: translate(0, 0); }
    100% { transform: translate(20px, 20px); }
}

.header-content-ultra {
    position: relative;
    z-index: 2;
}

.header-row-top {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 20px;
}

.back-btn-ultra {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,0.15);
    border-radius: 12px;
    color: #fff;
    text-decoration: none;
    font-size: 18px;
    backdrop-filter: blur(10px);
    transition: all 0.3s;
    flex-shrink: 0;
}

.back-btn-ultra:hover {
    background: rgba(255,255,255,0.25);
    transform: translateX(-3px);
}

.header-center-content {
    flex: 1;
    text-align: center;
}

.header-icon-ultra {
    width: 64px;
    height: 64px;
    background: rgba(255,255,255,0.15);
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 12px;
    font-size: 28px;
    color: #fff;
    backdrop-filter: blur(10px);
    animation: float 3s ease-in-out infinite;
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-8px); }
}

.header-title-ultra {
    font-size: 24px;
    font-weight: 800;
    color: #fff;
    margin: 0 0 6px;
    text-shadow: 0 2px 15px rgba(0,0,0,0.2);
}

.header-subtitle-ultra {
    font-size: 13px;
    color: rgba(255,255,255,0.85);
    margin: 0;
}

.header-spacer-ultra {
    width: 44px;
}

/* Balance Card with Glassmorphism */
.balance-card-glass-ultra {
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 20px;
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(255,255,255,0.2);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.balance-gradient-bg {
    position: absolute;
    top: -50%;
    right: -30%;
    width: 150px;
    height: 150px;
    background: radial-gradient(circle, rgba(255,255,255,0.2), transparent);
    border-radius: 50%;
    filter: blur(30px);
    animation: pulse 4s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); opacity: 0.2; }
    50% { transform: scale(1.2); opacity: 0.3; }
}

.balance-content-glass {
    display: flex;
    align-items: center;
    gap: 16px;
    position: relative;
    z-index: 2;
}

.balance-icon-glass {
    width: 56px;
    height: 56px;
    background: rgba(255,255,255,0.2);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    color: #fff;
    flex-shrink: 0;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.balance-info-glass {
    flex: 1;
}

.balance-label-glass {
    display: block;
    font-size: 12px;
    color: rgba(255,255,255,0.8);
    margin-bottom: 6px;
    font-weight: 500;
}

.balance-amount-glass {
    display: block;
    font-size: 28px;
    font-weight: 900;
    color: #fff;
    text-shadow: 0 2px 10px rgba(0,0,0,0.2);
}

/* Modern Alerts */
.simple-alert {
    display: flex;
    gap: 14px;
    background: rgba(249, 158, 43, 0.1);
    margin: 20px 16px 16px;
    padding: 16px;
    border-radius: 16px;
    border: 1px solid rgba(249, 158, 43, 0.3);
}

.simple-alert.warning {
    background: rgba(249, 158, 43, 0.1);
    border-color: rgba(249, 158, 43, 0.3);
}

.simple-alert.info {
    background: rgba(59, 130, 246, 0.1);
    border-color: rgba(59, 130, 246, 0.3);
}

.simple-alert i {
    width: 40px;
    height: 40px;
    background: rgba(249, 158, 43, 0.2);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}

.simple-alert.warning i {
    color: #F99E2B;
    background: rgba(249, 158, 43, 0.2);
}

.simple-alert.info i {
    color: #3b82f6;
    background: rgba(59, 130, 246, 0.2);
}

.simple-alert strong {
    display: block;
    font-size: 14px;
    color: rgba(255,255,255,0.95);
    margin-bottom: 4px;
    font-weight: 700;
}

.simple-alert p {
    font-size: 12px;
    color: rgba(255,255,255,0.7);
    margin: 0 0 8px;
    line-height: 1.5;
}

.alert-link {
    font-size: 13px;
    color: #0F743C;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.cannot-withdraw-section{background:#fff;margin:0 16px 16px;border-radius:12px;padding:30px 20px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,0.1)}
.cw-icon{width:60px;height:60px;background:#fef3c7;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-size:28px;color:#f59e0b}
.cannot-withdraw-section h4{font-size:16px;color:#1f2937;margin-bottom:6px}
.cannot-withdraw-section p{font-size:12px;color:#6b7280;margin-bottom:16px}
.cw-btn{display:inline-flex;align-items:center;gap:6px;background:#8b5cf6;color:#fff;padding:10px 20px;border-radius:8px;font-weight:600;text-decoration:none;font-size:13px}
.cw-countdown{margin-top:12px;padding:8px 12px;background:#fef3c7;border-radius:8px;color:#f59e0b;font-size:12px}

/* Modern Form Section */
.form-section {
    background: #1a1f3a;
    margin: 0 16px 20px;
    border-radius: 20px;
    padding: 24px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.3);
    border: 2px solid rgba(255,255,255,0.05);
}

.form-group {
    margin-bottom: 20px;
}

.label-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.form-lbl {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: rgba(255,255,255,0.9);
    font-weight: 700;
    margin: 0;
}

.form-lbl i {
    font-size: 16px;
    color: #0F743C;
}

.manage-link {
    font-size: 12px;
    color: #0F743C;
    text-decoration: none;
    font-weight: 700;
}

/* Method Cards Container */
.method-cards-container {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin-top: 8px;
}

.method-card {
    cursor: pointer;
    position: relative;
}

.method-card input[type="radio"] {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

.method-card-inner {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 16px 8px;
    background: rgba(255,255,255,0.05);
    border: 2px solid rgba(255,255,255,0.1);
    border-radius: 14px;
    transition: all 0.3s ease;
    min-height: 90px;
}

.method-card input[type="radio"]:checked + .method-card-inner {
    background: linear-gradient(135deg, rgba(15, 116, 60, 0.3) 0%, rgba(15, 116, 60, 0.15) 100%);
    border-color: #0F743C;
    box-shadow: 0 4px 15px rgba(15, 116, 60, 0.3);
    transform: scale(1.02);
}

.method-card:hover .method-card-inner {
    background: rgba(15, 116, 60, 0.1);
    border-color: rgba(15, 116, 60, 0.5);
}

.method-img {
    width: 40px;
    height: 40px;
    object-fit: contain;
    border-radius: 8px;
}

.method-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #0F743C, #15803d);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    color: #fff;
}

.method-name {
    font-size: 12px;
    font-weight: 700;
    color: rgba(255,255,255,0.9);
    text-align: center;
    line-height: 1.2;
}

.input-group {
    position: relative;
    display: flex;
    align-items: center;
}

.input-prefix {
    position: absolute;
    left: 14px;
    color: rgba(255,255,255,0.6);
    font-size: 18px;
    font-weight: 700;
    z-index: 2;
}

.form-input {
    width: 100%;
    padding: 14px 14px 14px 40px;
    border: 2px solid rgba(255,255,255,0.1);
    border-radius: 12px;
    font-size: 18px;
    font-weight: 700;
    background: rgba(255,255,255,0.05);
    color: rgba(255,255,255,0.95);
    transition: all 0.3s;
}

.form-input:focus {
    outline: none;
    border-color: #0F743C;
    background: rgba(255,255,255,0.08);
    box-shadow: 0 0 0 4px rgba(15, 116, 60, 0.1);
}

.limits-hint {
    font-size: 11px;
    color: rgba(255,255,255,0.5);
    margin-top: 8px;
    font-weight: 500;
}

.summary-box {
    background: rgba(255,255,255,0.05);
    border-radius: 16px;
    padding: 16px;
    margin-top: 16px;
    border: 1px solid rgba(255,255,255,0.1);
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    font-size: 14px;
}

.summary-row span {
    color: rgba(255,255,255,0.6);
    font-weight: 600;
}

.summary-row strong {
    color: rgba(255,255,255,0.95);
    font-size: 15px;
    font-weight: 800;
}

.summary-row.total {
    border-top: 1px dashed rgba(255,255,255,0.2);
    padding-top: 12px;
    margin-top: 6px;
}

.summary-row.total strong {
    color: #0F743C;
    font-size: 18px;
}

.charge-text {
    color: #F99E2B !important;
}

.btn-submit {
    width: 100%;
    padding: 16px;
    background: linear-gradient(135deg, #0F743C 0%, #16a085 100%);
    color: #fff;
    border: none;
    border-radius: 16px;
    font-size: 16px;
    font-weight: 800;
    cursor: pointer;
    margin-top: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    box-shadow: 0 8px 25px rgba(15, 116, 60, 0.4);
    transition: all 0.3s;
    position: relative;
    overflow: hidden;
}

.btn-submit::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 200%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.5s;
}

.btn-submit:hover::before {
    left: 100%;
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 35px rgba(15, 116, 60, 0.5);
}

.btn-submit:active {
    transform: scale(0.98);
}

.info-card{display:flex;gap:10px;background:#fff;margin:0 16px;padding:12px;border-radius:8px;border-left:3px solid #f59e0b}
.info-card.warning{border-color:#f59e0b}
.info-icon{width:36px;height:36px;background:#fef3c7;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#f59e0b;font-size:14px;flex-shrink:0}
.info-content{font-size:11px;color:#6b7280}
.info-content strong{display:block;color:#1f2937;margin-bottom:2px;font-size:12px}
.info-content p{margin:0}

/* PIN Input Section */
.pin-section {
    background: rgba(218, 62, 47, 0.08);
    border-radius: 16px;
    padding: 16px;
    margin-top: 16px;
    border: 1px solid rgba(218, 62, 47, 0.2);
}

.pin-input {
    text-align: center;
    font-weight: 700;
    font-size: 22px;
    letter-spacing: 8px;
    padding: 16px !important;
    background: rgba(255,255,255,0.1) !important;
    border: 2px solid rgba(218, 62, 47, 0.3) !important;
}

.pin-input:focus {
    border-color: #DA3E2F !important;
    box-shadow: 0 0 0 4px rgba(218, 62, 47, 0.15) !important;
}

.pin-input::placeholder {
    color: rgba(255,255,255,0.3);
    letter-spacing: 12px;
}

.pin-hint {
    display: block;
    font-size: 11px;
    color: rgba(255,255,255,0.5);
    margin-top: 8px;
    text-align: center;
}

/* Lifetime Limit Banner */
.lifetime-limit-banner{background:#fff;margin:12px 16px;border-radius:12px;padding:16px;box-shadow:0 2px 8px rgba(0,0,0,0.06);border:1px solid #e5e7eb}
.lifetime-limit-banner.warning{border-color:#f59e0b;background:linear-gradient(135deg,#fffbeb 0%,#fef3c7 100%)}
.lifetime-limit-banner.exhausted{border-color:#ef4444;background:linear-gradient(135deg,#fef2f2 0%,#fee2e2 100%)}
.llb-header{display:flex;align-items:center;gap:12px;margin-bottom:12px}
.llb-icon{width:40px;height:40px;background:linear-gradient(135deg,#F99E2B 0%,#e88a1a 100%);border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:18px;flex-shrink:0}
.lifetime-limit-banner.exhausted .llb-icon{background:linear-gradient(135deg,#ef4444 0%,#dc2626 100%)}
.llb-title{flex:1}
.llb-title strong{display:block;font-size:13px;color:#1f2937;margin-bottom:2px}
.llb-badge{display:inline-block;font-size:9px;padding:2px 8px;border-radius:6px;font-weight:700;background:#fef3c7;color:#d97706}
.llb-badge.free{background:#fee2e2;color:#dc2626}
.llb-progress{height:8px;background:#e5e7eb;border-radius:4px;overflow:hidden;margin-bottom:12px}
.llb-progress-bar{height:100%;background:linear-gradient(90deg,#16a34a 0%,#22c55e 100%);border-radius:4px;transition:width 0.3s ease}
.lifetime-limit-banner.warning .llb-progress-bar{background:linear-gradient(90deg,#f59e0b 0%,#fbbf24 100%)}
.lifetime-limit-banner.exhausted .llb-progress-bar{background:linear-gradient(90deg,#ef4444 0%,#f87171 100%)}
.llb-stats{display:flex;justify-content:space-between;gap:8px;margin-bottom:12px}
.llb-stat{flex:1;text-align:center;padding:8px;background:#f9fafb;border-radius:8px}
.llb-stat-label{display:block;font-size:9px;color:#6b7280;margin-bottom:2px}
.llb-stat-value{font-size:13px;font-weight:700}
.llb-stat-value.used{color:#6b7280}
.llb-stat-value.remaining{color:#16a34a}
.lifetime-limit-banner.warning .llb-stat-value.remaining{color:#f59e0b}
.lifetime-limit-banner.exhausted .llb-stat-value.remaining{color:#ef4444}
.llb-stat-value.total{color:#1f2937}
.llb-stat-value.per-withdraw{color:#8b5cf6;font-weight:800}
.llb-action{display:flex;align-items:center;gap:8px;padding:10px;background:rgba(245,158,11,0.1);border-radius:8px;margin-bottom:12px}
.llb-action.warning{background:rgba(245,158,11,0.15)}
.llb-action.exhausted{background:rgba(239,68,68,0.15)}
.llb-action i{font-size:16px;color:#f59e0b;flex-shrink:0}
.llb-action.exhausted i{color:#ef4444}
.llb-action span{font-size:11px;color:#92400e;font-weight:600}
.llb-action.exhausted span{color:#991b1b}
.llb-upgrade-btn{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:12px;background:linear-gradient(135deg,#F99E2B 0%,#e88a1a 100%);color:#fff;border-radius:8px;font-size:13px;font-weight:700;text-decoration:none;box-shadow:0 2px 6px rgba(249,158,43,0.3)}
.llb-upgrade-btn:hover{transform:translateY(-1px);box-shadow:0 4px 10px rgba(249,158,43,0.4)}

/* Free User Limits Card */
.free-user-limits-card{background:#fff;margin:12px 16px;border-radius:12px;padding:16px;box-shadow:0 2px 8px rgba(0,0,0,0.06);border:1px solid #e5e7eb}
.ful-header{display:flex;align-items:center;gap:10px;margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid #f0f0f0}
.ful-icon{width:36px;height:36px;background:linear-gradient(135deg,#78909c 0%,#546e7a 100%);border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:16px;flex-shrink:0}
.ful-title{flex:1}
.ful-title strong{display:block;font-size:13px;color:#1f2937;margin-bottom:2px}
.ful-badge{display:inline-block;font-size:9px;padding:2px 8px;border-radius:6px;font-weight:700;background:#f3f4f6;color:#6b7280}
.ful-limits-grid{display:flex;flex-wrap:wrap;gap:8px}
.ful-limit-item{flex:1;min-width:calc(50% - 4px);display:flex;align-items:center;gap:10px;padding:10px;background:#f9fafb;border-radius:10px;border:1px solid #e5e7eb}
.ful-limit-item.daily{flex:1 0 100%}
.ful-limit-icon{width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:14px;color:#fff;flex-shrink:0}
.ful-limit-icon.min{background:linear-gradient(135deg,#ef4444 0%,#dc2626 100%)}
.ful-limit-icon.max{background:linear-gradient(135deg,#22c55e 0%,#16a34a 100%)}
.ful-limit-icon.daily{background:linear-gradient(135deg,#3b82f6 0%,#2563eb 100%)}
.ful-limit-info{flex:1}
.ful-limit-label{display:block;font-size:10px;color:#6b7280;margin-bottom:1px}
.ful-limit-value{font-size:14px;font-weight:700;color:#1f2937}
.ful-daily-remaining{text-align:right;padding:4px 10px;background:#fff;border-radius:6px;border:1px solid #e5e7eb}
.ful-remaining-label{display:block;font-size:9px;color:#6b7280}
.ful-remaining-value{font-size:13px;font-weight:700;color:#16a34a}
.ful-remaining-value.exhausted{color:#ef4444}
</style>
@endpush

@push('script')
<script type="text/javascript">
(function ($) {
    "use strict";

    function updatePreview() {
        var $selectedRadio = $('input[name=method_id]:checked');
        var amount = parseFloat($('input[name=amount]').val()) || 0;

        if (!$selectedRadio.length) {
            $('.min').text('0');
            $('.max').text('0');
            $('.charge').text('0');
            $('.receivable').text('0');
            return;
        }

        var minLimit = parseFloat($selectedRadio.data('min')) || 0;
        var maxLimit = parseFloat($selectedRadio.data('max')) || 0;
        var fixedCharge = parseFloat($selectedRadio.data('fixed')) || 0;
        var percentCharge = parseFloat($selectedRadio.data('percent')) || 0;

        $('.min').text(minLimit.toFixed(0));
        $('.max').text(maxLimit.toFixed(0));

        if (amount > 0) {
            var charge = fixedCharge + (amount * percentCharge / 100);
            $('.charge').text(charge.toFixed(0));

            var receivable = amount - charge;
            $('.receivable').text(Math.max(0, receivable).toFixed(0));
        } else {
            $('.charge').text('0');
            $('.receivable').text('0');
        }
    }

    $('input[name=method_id]').change(updatePreview);
    $('input[name=amount]').on('input', updatePreview);
    updatePreview();

    // Only allow numbers in PIN input
    $('.pin-input').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    // Only allow numbers in wallet number input
    $('input[name=wallet_number]').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

})(jQuery);
</script>
@endpush
