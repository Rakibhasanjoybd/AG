@extends($activeTemplate . 'layouts.frontend')
@section('content')
@php
    $isCurrent = null;
    $userBalance = 0;
    if(auth()->check()) {
        $user = auth()->user();
        $isCurrent = @$user->runningPlan ? $user->plan_id : null;
        $userBalance = $user->balance;
    }

    // Get referral commissions for plans
    $referralCommissions = \App\Models\Referral::where('status', 1)->orderBy('level')->get();
@endphp

<!-- Plans Screen - Enhanced Premium Design -->
<div class="plans-screen">
    <!-- Header with Gradient & Shadow -->
    <div class="plans-header">
        <a href="{{ route('home') }}" class="header-back" aria-label="Go back to home">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div class="header-center">
            <h1 class="header-title">
                <i class="fas fa-gem"></i>
                প্যাকেজসমূহ
            </h1>
            <p class="header-subtitle">আপনার জন্য সেরা প্যাকেজ নির্বাচন করুন</p>
        </div>
        @auth
        <div class="header-balance">
            <div class="balance-icon">
                <i class="fas fa-wallet"></i>
            </div>
            <div class="balance-info">
                <span class="bal-label">আপনার ব্যালেন্স</span>
                <span class="bal-amount">{{ $general->cur_sym }}{{ showAmount($userBalance) }}</span>
            </div>
        </div>
        @else
        <div class="header-balance-placeholder"></div>
        @endauth
    </div>

    <!-- Benefits Banner -->
    <div class="benefits-banner">
        <div class="benefits-marquee">
            <div class="benefit-item">
                <i class="fas fa-bolt"></i>
                <span>তাৎক্ষণিক সক্রিয়করণ</span>
            </div>
            <div class="benefit-item">
                <i class="fas fa-shield-check"></i>
                <span>১০০% নিরাপদ</span>
            </div>
            <div class="benefit-item">
                <i class="fas fa-users"></i>
                <span>রেফারেল বোনাস</span>
            </div>
            <div class="benefit-item">
                <i class="fas fa-money-bill-wave"></i>
                <span>দৈনিক আয়</span>
            </div>
            <div class="benefit-item">
                <i class="fas fa-headset"></i>
                <span>২৪/৭ সাপোর্ট</span>
            </div>
            <!-- Duplicate for seamless loop -->
            <div class="benefit-item">
                <i class="fas fa-bolt"></i>
                <span>তাৎক্ষণিক সক্রিয়করণ</span>
            </div>
            <div class="benefit-item">
                <i class="fas fa-shield-check"></i>
                <span>১০০% নিরাপদ</span>
            </div>
            <div class="benefit-item">
                <i class="fas fa-users"></i>
                <span>রেফারেল বোনাস</span>
            </div>
            <div class="benefit-item">
                <i class="fas fa-money-bill-wave"></i>
                <span>দৈনিক আয়</span>
            </div>
            <div class="benefit-item">
                <i class="fas fa-headset"></i>
                <span>২৪/৭ সাপোর্ট</span>
            </div>
        </div>
    </div>

    <!-- Plans List -->
    <div class="plans-list">
        @foreach ($plans as $index => $plan)
            @php
                $isActive = auth()->check() && $isCurrent == $plan->id;
                $affordable = auth()->check() && $userBalance >= $plan->price;
                $colorSchemes = [
                    ['border' => '#E91E63', 'bg' => 'linear-gradient(135deg, #E91E63, #9C27B0, #2196F3, #4CAF50, #FF9800)'],
                    ['border' => '#9C27B0', 'bg' => 'linear-gradient(135deg, #9C27B0, #673AB7)'],
                    ['border' => '#FF9800', 'bg' => 'linear-gradient(135deg, #FF9800, #F44336)'],
                    ['border' => '#4CAF50', 'bg' => 'linear-gradient(135deg, #4CAF50, #009688)'],
                    ['border' => '#2196F3', 'bg' => 'linear-gradient(135deg, #2196F3, #3F51B5)'],
                ];
                $colorScheme = $colorSchemes[$index % count($colorSchemes)];

                // Calculate earnings
                $dailyEarning = $plan->daily_ad_rate ?? ($plan->price * 0.014);
                $monthlyEarning = $dailyEarning * 30;
                $yearlyEarning = $dailyEarning * $plan->validity;
            @endphp

            <div class="plan-card {{ $isActive ? 'active' : '' }} {{ !$affordable && auth()->check() ? 'not-affordable' : '' }}"
                 data-id="{{ $plan->id }}"
                 data-name="{{ $plan->name }}"
                 data-price="{{ $plan->price }}"
                 data-price-formatted="{{ showAmount($plan->price) }}"
                 data-daily="{{ $plan->daily_limit }}"
                 data-ref="{{ $plan->ref_level }}"
                 data-validity="{{ $plan->validity }}"
                 data-daily-earning="{{ number_format($dailyEarning, 2) }}"
                 data-monthly-earning="{{ number_format($monthlyEarning, 2) }}"
                 data-yearly-earning="{{ number_format($yearlyEarning, 2) }}"
                 data-affordable="{{ $affordable ? '1' : '0' }}"
                 data-active="{{ $isActive ? '1' : '0' }}"
                 style="--card-border: {{ $colorScheme['border'] }}; --card-bg: {{ $colorScheme['bg'] }}">

                <!-- Active Badge -->
                @if($isActive)
                <div class="active-ribbon">
                    <i class="fas fa-check-circle"></i>
                    <span>সক্রিয়</span>
                </div>
                @endif

                <!-- Card Glow Effect -->
                <div class="card-glow"></div>

                <div class="plan-icon-wrap">
                    <div class="plan-icon" style="background: {{ $colorScheme['bg'] }}">
                        <i class="fas fa-gem"></i>
                        <div class="icon-shine"></div>
                    </div>
                    <div class="icon-pulse"></div>
                </div>

                <div class="plan-info">
                    <h3 class="plan-name">{{ __($plan->name) }}</h3>
                    <div class="plan-price-wrap">
                        <div class="plan-price">
                            <span class="currency">{{ $general->cur_sym }}</span>
                            <span class="amount">{{ showAmount($plan->price) }}</span>
                        </div>
                        <div class="plan-roi">
                            <i class="fas fa-chart-line"></i>
                            <span>+{{ number_format(($yearlyEarning / $plan->price) * 100, 0) }}% ROI</span>
                        </div>
                    </div>
                    <div class="plan-meta">
                        <div class="meta-item">
                            <i class="far fa-calendar-alt"></i>
                            <span>{{ $plan->validity }} দিন</span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-coins"></i>
                            <span>দৈনিক: {{ $general->cur_sym }}{{ number_format($dailyEarning, 2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="plan-action">
                    @if($isActive)
                    <div class="status-badge active">
                        <i class="fas fa-check-double"></i>
                        <span>চলমান</span>
                    </div>
                    @else
                    <button class="btn-details" type="button" name="btnDetails{{ $plan->id }}" id="btnDetails{{ $plan->id }}">
                        <span>বিস্তারিত</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- Trust Indicators -->
    <div class="trust-section">
        <div class="trust-stats">
            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value">১০,০০০+</div>
                    <div class="stat-label">সক্রিয় ব্যবহারকারী</div>
                </div>
            </div>
            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value">৯৮.৫%</div>
                    <div class="stat-label">পেমেন্ট সফলতা</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Navigation Hint -->
    <div class="bottom-hint">
        <i class="fas fa-chevron-down bounce"></i>
        <p>আরও তথ্যের জন্য প্যাকেজে ক্লিক করুন</p>
    </div>
</div>

<!-- Package Detail Modal - Premium Professional Design -->
<div class="modal fade" id="PackageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen-sm-down modal-dialog-scrollable">
        <div class="modal-content pkg-modal-v3">
            <!-- Modal Header with Plan Info -->
            <div class="pkg-header-v3">
                <button type="button" class="pkg-close-v3" data-bs-dismiss="modal" aria-label="Close modal">
                    <i class="fas fa-times"></i>
                </button>
                
                <div class="header-glow"></div>
                
                <div class="pkg-icon-v3">
                    <i class="fas fa-gem"></i>
                    <div class="icon-particles"></div>
                </div>
                
                <h3 class="pkg-name-v3" aria-live="polite">প্যাকেজ বিস্তারিত</h3>
                <div class="pkg-price-v3">
                    <span class="price-sym">{{ $general->cur_sym }}</span>
                    <span class="price-val"></span>
                </div>
                
                <div class="pkg-badges">
                    <div class="badge-item">
                        <i class="fas fa-bolt"></i>
                        <span>তাৎক্ষণিক সক্রিয়</span>
                    </div>
                    <div class="badge-item">
                        <i class="fas fa-shield-check"></i>
                        <span>১০০% নিরাপদ</span>
                    </div>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="pkg-body-v3">
                <!-- Quick Stats Cards -->
                <div class="quick-stats-mobile">
                    <div class="stat-card-mobile">
                        <div class="stat-icon-mobile">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                        <div class="stat-info-mobile">
                            <div class="stat-label-mobile">মেয়াদকাল</div>
                            <div class="stat-value-mobile"><span class="pkg-validity">365</span> দিন</div>
                        </div>
                    </div>
                    <div class="stat-card-mobile">
                        <div class="stat-icon-mobile">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-info-mobile">
                            <div class="stat-label-mobile">ROI রিটার্ন</div>
                            <div class="stat-value-mobile"><span class="pkg-roi">0</span>%</div>
                        </div>
                    </div>
                </div>

                <!-- Withdrawal Speed - Compact -->
                <div class="pkg-section-mobile highlight-mobile">
                    <div class="section-header-mobile">
                        <i class="fas fa-rocket"></i>
                        <span>উত্তোলনের গতি</span>
                    </div>
                    <div class="withdrawal-info-mobile">
                        <div class="info-row-mobile">
                            <span class="info-label-mobile">প্রক্রিয়াকরণ সময়</span>
                            <span class="info-value-mobile">১০-৩০ মিনিট</span>
                        </div>
                        <div class="info-note-mobile">স্বয়ংক্রিয় দ্রুত প্রক্রিয়াকরণ</div>
                    </div>
                </div>

                <!-- Earnings Table -->
                <div class="pkg-section-mobile">
                    <div class="section-header-mobile">
                        <i class="fas fa-coins"></i>
                        <span>সময়ের একক</span>
                    </div>
                    <div class="earnings-table-mobile">
                        <table class="table-mobile">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>কাজের সংখ্যা</th>
                                    <th>মোট কমিশন</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="period-cell">দৈনিক</td>
                                    <td><span class="pkg-daily-ads-2">5</span> টি</td>
                                    <td class="amount-cell">৳<span class="pkg-daily-earn-2">0.00</span></td>
                                </tr>
                                <tr>
                                    <td class="period-cell">প্রতি মাসে</td>
                                    <td><span class="pkg-monthly-ads-2">150</span> টি</td>
                                    <td class="amount-cell">৳<span class="pkg-monthly-earn-2">0.00</span></td>
                                </tr>
                                <tr class="highlight-row">
                                    <td class="period-cell">প্রতি বছর</td>
                                    <td><span class="pkg-yearly-ads-2">1825</span> টি</td>
                                    <td class="amount-cell">৳<span class="pkg-yearly-earn-2">0.00</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Referral Commission -->
                @if($referralCommissions && count($referralCommissions) > 0)
                <div class="pkg-section-mobile">
                    <div class="section-header-mobile">
                        <i class="fas fa-user-friends"></i>
                        <span>আমন্ত্রণ কমিশন আয়ের অনুপাত</span>
                    </div>
                    <div class="commission-list-mobile">
                        @foreach($referralCommissions->take(3) as $ref)
                        <div class="commission-row-mobile">
                            <div class="commission-text-mobile">
                                @if($ref->level == 1)
                                <div class="comm-desc-mobile">A কে সদস্য হওয়ার জন্য আমন্ত্রণ জানান।</div>
                                @elseif($ref->level == 2)
                                <div class="comm-desc-mobile">A অথবা B কে সদস্য হওয়ার জন্য আমন্ত্রণ জানান।</div>
                                @else
                                <div class="comm-desc-mobile">B তার অথবা C কে সদস্য হওয়ার জন্য আমন্ত্রণ জানান।</div>
                                @endif
                            </div>
                            <div class="commission-values-mobile">
                                <div class="comm-percent-mobile">{{ $ref->percent }}%</div>
                                <div class="comm-amount-mobile">৳<span class="ref-amount-{{ $ref->level }}">0.00</span></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Task Commission -->
                <div class="pkg-section-mobile">
                    <div class="section-header-mobile">
                        <i class="fas fa-tasks"></i>
                        <span>টাস্ক কমিশন আয় অনুপাত</span>
                    </div>
                    <div class="task-commission-list-mobile">
                        @foreach($referralCommissions->take(3) as $ref)
                        <div class="task-row-mobile">
                            <div class="task-desc-mobile">
                                @if($ref->level == 1)
                                অথবা A কাজটি সম্পন্ন করে
                                @elseif($ref->level == 2)
                                অথবা B কাজটি সম্পন্ন করে
                                @else
                                অথবা C কাজটি সম্পন্ন করে
                                @endif
                            </div>
                            <div class="task-percent-mobile">{{ $ref->commission ?? $ref->percent }}%</div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Balance Info -->
                @auth
                <div class="pkg-balance-section">
                    <div class="balance-header">
                        <i class="fas fa-wallet"></i>
                        <span>আপনার ওয়ালেট</span>
                    </div>
                    <div class="balance-content">
                        <div class="balance-row">
                            <span class="balance-label">বর্তমান ব্যালেন্স</span>
                            <span class="balance-val">{{ $general->cur_sym }}{{ showAmount($userBalance) }}</span>
                        </div>
                        <div class="balance-row need-row" style="display:none;">
                            <span class="balance-label">অতিরিক্ত প্রয়োজন</span>
                            <span class="need-val text-danger"></span>
                        </div>
                    </div>
                </div>
                @endauth
            </div>

            <!-- Modal Footer -->
            <div class="pkg-footer-v3">
                @auth
                <form method="post" action="{{ route('user.buyPlan') }}" id="buyForm" style="display:none;">
                    @csrf
                    <input type="hidden" name="id" id="planIdInput">
                </form>

                <button type="button" class="pkg-btn-v3 btn-buy" id="btnBuyNow">
                    <div class="btn-content">
                        <i class="fas fa-rocket"></i>
                        <div>
                            <div class="btn-title">এখনই সক্রিয় করুন</div>
                            <div class="btn-subtitle">তাৎক্ষণিক অ্যাক্টিভেশন</div>
                        </div>
                    </div>
                    <div class="btn-shine"></div>
                </button>

                <a href="#" class="pkg-btn-v3 btn-deposit" id="btnDeposit" style="display:none;">
                    <div class="btn-content">
                        <i class="fas fa-wallet"></i>
                        <div>
                            <div class="btn-title">ব্যালেন্স যোগ করুন</div>
                            <div class="btn-subtitle">নিরাপদ পেমেন্ট গেটওয়ে</div>
                        </div>
                    </div>
                    <div class="btn-shine"></div>
                </a>

                <button type="button" class="pkg-btn-v3 btn-active" id="btnActive" disabled style="display:none;">
                    <i class="fas fa-check-circle"></i>
                    <span>এই প্যাকেজ সক্রিয়</span>
                </button>
                @else
                <a href="{{ route('user.login') }}" class="pkg-btn-v3 btn-login" id="btnLogin">
                    <div class="btn-content">
                        <i class="fas fa-sign-in-alt"></i>
                        <div>
                            <div class="btn-title">লগইন করুন</div>
                            <div class="btn-subtitle">আপনার অ্যাকাউন্টে প্রবেশ করুন</div>
                        </div>
                    </div>
                    <div class="btn-shine"></div>
                </a>
                @endauth
                
                <div class="footer-trust">
                    <i class="fas fa-shield-check"></i>
                    <span>SSL এনক্রিপ্টেড | নিরাপদ লেনদেন</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('style-lib')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @include('partials.preload-style', ['href' => 'https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700;800&display=swap', 'crossorigin' => true])
@endpush

@push('style')
<style>
:root {
    --primary: #0F743C;
    --primary-light: #1a9e52;
    --primary-dark: #0a5129;
    --secondary: #C7662B;
    --warning: #F99E2B;
    --error: #DA3E2F;
    --success: #10B981;
    --bg-light: #f5f7fa;
    --bg-white: #ffffff;
    --text-dark: #1a1a2e;
    --text-gray: #6b7280;
    --border-color: #e5e7eb;
    --shadow-sm: 0 2px 8px rgba(0,0,0,0.06);
    --shadow-md: 0 4px 16px rgba(0,0,0,0.1);
    --shadow-lg: 0 8px 30px rgba(0,0,0,0.15);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Hind Siliguri', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    background: var(--bg-light);
    overflow-x: hidden;
}

/* Plans Screen */
.plans-screen {
    min-height: 100vh;
    background: linear-gradient(180deg, #f8fafb 0%, #f0f4f8 50%, #f5f7fa 100%);
    padding-bottom: 80px;
    position: relative;
}

/* Header - Enhanced */
.plans-header {
    background: linear-gradient(135deg, #0F743C 0%, #16a085 50%, #1a9e52 100%);
    padding: 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    position: sticky;
    top: 0;
    z-index: 100;
    box-shadow: 0 4px 25px rgba(15, 116, 60, 0.4);
    background-size: 200% 200%;
    animation: gradientShift 8s ease infinite;
}

@keyframes gradientShift {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

.header-back {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,0.2);
    border-radius: 14px;
    color: #fff;
    text-decoration: none;
    font-size: 18px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    flex-shrink: 0;
    backdrop-filter: blur(10px);
}

.header-back:hover {
    background: rgba(255,255,255,0.3);
    transform: scale(1.05);
    color: #fff;
}

.header-center {
    flex: 1;
    text-align: center;
}

.header-title {
    font-size: 20px;
    font-weight: 800;
    color: #fff;
    margin: 0 0 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    text-shadow: 0 2px 10px rgba(0,0,0,0.2);
}

.header-title i {
    animation: gemRotate 3s linear infinite;
}

@keyframes gemRotate {
    0%, 100% { transform: rotateY(0deg); }
    50% { transform: rotateY(180deg); }
}

.header-subtitle {
    font-size: 11px;
    color: rgba(255,255,255,0.9);
    margin: 0;
    font-weight: 500;
}

.header-balance {
    background: rgba(255,255,255,0.2);
    padding: 8px 12px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
    backdrop-filter: blur(10px);
}

.balance-icon {
    width: 28px;
    height: 28px;
    background: rgba(255,255,255,0.3);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 13px;
}

.balance-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.bal-label {
    font-size: 9px;
    color: rgba(255,255,255,0.85);
    font-weight: 500;
    line-height: 1;
}

.bal-amount {
    font-size: 14px;
    font-weight: 800;
    color: #fff;
    line-height: 1;
}

.header-balance-placeholder {
    width: 100px;
    flex-shrink: 0;
}

/* Benefits Banner */
.benefits-banner {
    background: linear-gradient(135deg, rgba(15, 116, 60, 0.05), rgba(26, 158, 82, 0.03));
    border-bottom: 2px solid rgba(15, 116, 60, 0.1);
    padding: 12px 0;
    overflow: hidden;
    position: relative;
}

.benefits-marquee {
    display: flex;
    gap: 20px;
    animation: marqueeScroll 30s linear infinite;
    will-change: transform;
}

@keyframes marqueeScroll {
    0% { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}

.benefit-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: rgba(255,255,255,0.7);
    border-radius: 20px;
    white-space: nowrap;
    font-size: 12px;
    font-weight: 700;
    color: var(--text-dark);
    box-shadow: var(--shadow-sm);
}

.benefit-item i {
    font-size: 14px;
    color: var(--primary);
}

/* Plans List */
.plans-list {
    padding: 20px 16px;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

/* Plan Card - Premium Enhanced */
.plan-card {
    background: var(--bg-white);
    border-radius: 20px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: var(--shadow-sm);
    border: 2px solid transparent;
    cursor: pointer;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.plan-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, transparent 0%, var(--card-border, var(--primary)) 100%);
    opacity: 0;
    transition: opacity 0.4s;
    pointer-events: none;
}

.plan-card:hover::before {
    opacity: 0.03;
}

.plan-card:hover {
    transform: translateY(-4px) scale(1.01);
    box-shadow: var(--shadow-lg);
    border-color: var(--card-border, var(--primary));
}

.plan-card.active {
    border-color: var(--primary);
    background: linear-gradient(135deg, rgba(15, 116, 60, 0.04), rgba(26, 158, 82, 0.02));
}

.plan-card.active::before {
    opacity: 0.05;
}

.plan-card.not-affordable {
    opacity: 0.75;
}

/* Card Glow Effect */
.card-glow {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 200px;
    height: 200px;
    background: radial-gradient(circle, var(--card-border, var(--primary)) 0%, transparent 70%);
    opacity: 0;
    transform: translate(-50%, -50%);
    transition: opacity 0.4s;
    pointer-events: none;
}

.plan-card:hover .card-glow {
    opacity: 0.15;
}

/* Active Ribbon */
.active-ribbon {
    position: absolute;
    top: 12px;
    right: -32px;
    background: linear-gradient(135deg, var(--success), #059669);
    color: #fff;
    padding: 4px 40px;
    font-size: 11px;
    font-weight: 700;
    transform: rotate(45deg);
    box-shadow: 0 4px 10px rgba(16, 185, 129, 0.4);
    display: flex;
    align-items: center;
    gap: 4px;
    z-index: 10;
}

.active-ribbon i {
    font-size: 10px;
}

.plan-icon-wrap {
    position: relative;
    flex-shrink: 0;
}

.plan-icon {
    width: 64px;
    height: 64px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    color: #fff;
    box-shadow: var(--shadow-md);
    position: relative;
    overflow: hidden;
    transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.plan-card:hover .plan-icon {
    transform: scale(1.1) rotate(5deg);
}

.icon-shine {
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent 40%, rgba(255,255,255,0.4) 50%, transparent 60%);
    animation: iconShine 3s infinite;
}

@keyframes iconShine {
    0% { transform: translateX(-100%) rotate(45deg); }
    100% { transform: translateX(100%) rotate(45deg); }
}

.icon-pulse {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 100%;
    height: 100%;
    border-radius: 18px;
    border: 3px solid var(--card-border, var(--primary));
    transform: translate(-50%, -50%);
    animation: iconPulse 2s infinite;
    opacity: 0;
}

@keyframes iconPulse {
    0% {
        transform: translate(-50%, -50%) scale(1);
        opacity: 0.6;
    }
    100% {
        transform: translate(-50%, -50%) scale(1.4);
        opacity: 0;
    }
}

.plan-info {
    flex: 1;
    min-width: 0;
}

.plan-name {
    font-size: 17px;
    font-weight: 800;
    color: var(--text-dark);
    margin: 0 0 8px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.plan-price-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
    flex-wrap: wrap;
}

.plan-price {
    display: flex;
    align-items: baseline;
    gap: 2px;
}

.plan-price .currency {
    font-size: 14px;
    font-weight: 700;
    color: var(--primary);
}

.plan-price .amount {
    font-size: 22px;
    font-weight: 900;
    color: var(--primary);
}

.plan-roi {
    display: flex;
    align-items: center;
    gap: 4px;
    background: linear-gradient(135deg, rgba(249, 158, 43, 0.15), rgba(255, 193, 7, 0.1));
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 800;
    color: var(--warning);
}

.plan-roi i {
    font-size: 10px;
}

.plan-meta {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
    color: #4b5563;
    font-weight: 600;
}

.meta-item i {
    font-size: 11px;
    color: #9ca3af;
}

.plan-action {
    flex-shrink: 0;
}

.btn-details {
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    border: none;
    color: #fff;
    padding: 12px 20px;
    border-radius: 14px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 12px rgba(15, 116, 60, 0.3);
}

.btn-details:hover {
    transform: translateX(4px);
    box-shadow: 0 6px 20px rgba(15, 116, 60, 0.4);
}

.btn-details i {
    font-size: 12px;
    transition: transform 0.3s;
}

.btn-details:hover i {
    transform: translateX(4px);
}

.status-badge {
    padding: 12px 18px;
    border-radius: 14px;
    font-size: 14px;
    font-weight: 800;
    display: flex;
    align-items: center;
    gap: 6px;
}

.status-badge.active {
    background: linear-gradient(135deg, var(--success), #059669);
    color: #fff;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.35);
}

.status-badge i {
    font-size: 13px;
}

/* Trust Section */
.trust-section {
    padding: 20px 16px;
    margin-top: 8px;
}

.trust-stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.stat-item {
    background: var(--bg-white);
    border-radius: 16px;
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    box-shadow: var(--shadow-sm);
    border: 2px solid var(--border-color);
    transition: all 0.3s;
}

.stat-item:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
    border-color: var(--primary);
}

.stat-icon {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 20px;
    flex-shrink: 0;
}

.stat-info {
    flex: 1;
}

.stat-value {
    font-size: 20px;
    font-weight: 900;
    color: var(--text-dark);
    margin-bottom: 2px;
}

.stat-label {
    font-size: 11px;
    color: var(--text-gray);
    font-weight: 600;
}

/* Bottom Hint */
.bottom-hint {
    text-align: center;
    padding: 20px;
    color: var(--text-gray);
}

.bottom-hint i {
    font-size: 24px;
    color: var(--primary);
    margin-bottom: 8px;
    display: block;
}

.bottom-hint i.bounce {
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% {
        transform: translateY(0);
    }
    40% {
        transform: translateY(-8px);
    }
    60% {
        transform: translateY(-4px);
    }
}

.bottom-hint p {
    font-size: 13px;
    font-weight: 600;
    margin: 0;
}

/* ==================== */
/* Package Modal V3 - Professional Design */
/* ==================== */
.pkg-modal-v3 {
    background: var(--bg-white);
    border-radius: 28px 28px 0 0;
    border: none;
    overflow: hidden;
    max-height: 96vh;
}

.pkg-header-v3 {
    background: linear-gradient(135deg, #0F743C 0%, #16a085 50%, #1a9e52 100%);
    padding: 28px 20px 24px;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.header-glow {
    position: absolute;
    top: -50%;
    left: 50%;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 70%);
    transform: translateX(-50%);
    animation: glowPulse 3s ease-in-out infinite;
}

@keyframes glowPulse {
    0%, 100% { opacity: 0.5; transform: translateX(-50%) scale(1); }
    50% { opacity: 0.8; transform: translateX(-50%) scale(1.2); }
}

.pkg-close-v3 {
    position: absolute;
    top: 16px;
    right: 16px;
    width: 40px;
    height: 40px;
    background: rgba(255,255,255,0.25);
    border: none;
    border-radius: 50%;
    color: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 10;
    backdrop-filter: blur(10px);
}

.pkg-close-v3:hover {
    background: rgba(255,255,255,0.35);
    transform: rotate(90deg) scale(1.1);
}

.pkg-icon-v3 {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #E91E63, #9C27B0, #2196F3, #4CAF50, #FF9800);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 14px;
    font-size: 32px;
    color: #fff;
    box-shadow: 0 10px 30px rgba(0,0,0,0.25);
    position: relative;
    z-index: 2;
    animation: iconFloat 3s ease-in-out infinite;
}

@keyframes iconFloat {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-8px); }
}

.icon-particles {
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 20px;
    border: 3px solid rgba(255,255,255,0.3);
    animation: particleExpand 2s infinite;
}

@keyframes particleExpand {
    0% {
        transform: scale(1);
        opacity: 0.8;
    }
    100% {
        transform: scale(1.5);
        opacity: 0;
    }
}

.pkg-name-v3 {
    font-size: 24px;
    font-weight: 900;
    color: #fff;
    margin: 0 0 10px;
    position: relative;
    z-index: 2;
    text-shadow: 0 2px 15px rgba(0,0,0,0.2);
}

.pkg-price-v3 {
    position: relative;
    z-index: 2;
    margin-bottom: 16px;
}

.pkg-price-v3 .price-sym {
    font-size: 22px;
    font-weight: 700;
    color: #fff;
    vertical-align: top;
}

.pkg-price-v3 .price-val {
    font-size: 40px;
    font-weight: 900;
    color: #fff;
    text-shadow: 0 3px 20px rgba(0,0,0,0.3);
}

.pkg-badges {
    display: flex;
    justify-content: center;
    gap: 12px;
    position: relative;
    z-index: 2;
}

.badge-item {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    background: rgba(255,255,255,0.25);
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    color: #fff;
    backdrop-filter: blur(10px);
}

.badge-item i {
    font-size: 12px;
}

/* Modal Body V3 */
.pkg-body-v3 {
    padding: 20px 16px;
    max-height: 60vh;
    overflow-y: auto;
    background: linear-gradient(180deg, #f9fafb 0%, #ffffff 100%);
}

/* Quick Stats */
.quick-stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 20px;
}

.stat-card {
    background: var(--bg-white);
    border-radius: 16px;
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    box-shadow: var(--shadow-sm);
    border: 2px solid var(--border-color);
    transition: all 0.3s;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
    border-color: var(--primary);
}

.stat-icon {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 20px;
    flex-shrink: 0;
}

.stat-details {
    flex: 1;
}

.stat-label {
    font-size: 11px;
    color: var(--text-gray);
    font-weight: 600;
    margin-bottom: 4px;
}

.stat-value {
    font-size: 18px;
    font-weight: 900;
    color: var(--text-dark);
}

/* Section Styles */
.pkg-section {
    margin-bottom: 24px;
}

.pkg-section.highlight-section {
    background: linear-gradient(135deg, rgba(249, 158, 43, 0.08), rgba(255, 193, 7, 0.05));
    border: 2px solid rgba(249, 158, 43, 0.2);
    border-radius: 18px;
    padding: 18px;
}

.section-header {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 16px;
}

.section-icon-wrap {
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
    box-shadow: 0 4px 12px rgba(15, 116, 60, 0.25);
}

.section-title {
    font-size: 16px;
    font-weight: 800;
    color: var(--text-dark);
    margin: 0 0 4px;
}

.section-subtitle {
    font-size: 12px;
    color: var(--text-gray);
    font-weight: 500;
    margin: 0;
}

/* Withdrawal Timeline */
.withdrawal-timeline {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.timeline-item {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    background: var(--bg-white);
    border-radius: 14px;
    padding: 14px;
    border: 2px solid rgba(16, 185, 129, 0.15);
}

.timeline-icon {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}

.timeline-icon.success {
    background: linear-gradient(135deg, var(--success), #059669);
    color: #fff;
}

.timeline-content {
    flex: 1;
}

.timeline-title {
    font-size: 12px;
    color: var(--text-gray);
    font-weight: 600;
    margin-bottom: 4px;
}

.timeline-value {
    font-size: 18px;
    font-weight: 900;
    color: var(--text-dark);
    margin-bottom: 4px;
}

.timeline-desc {
    font-size: 11px;
    color: var(--text-gray);
    font-weight: 500;
}

/* Opportunities Grid */
.opportunities-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
}

.opportunity-card {
    background: var(--bg-white);
    border-radius: 14px;
    padding: 14px;
    text-align: center;
    border: 2px solid var(--border-color);
    transition: all 0.3s;
}

.opportunity-card:hover {
    transform: translateY(-4px);
    border-color: var(--primary);
    box-shadow: var(--shadow-md);
}

.opp-icon {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, rgba(15, 116, 60, 0.1), rgba(26, 158, 82, 0.05));
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 10px;
    font-size: 20px;
    color: var(--primary);
}

.opp-title {
    font-size: 12px;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 6px;
}

.opp-value {
    font-size: 11px;
    color: var(--text-gray);
    font-weight: 600;
    margin-bottom: 6px;
}

.opp-earning {
    font-size: 16px;
    font-weight: 900;
    color: var(--primary);
}

/* Benefits Cards */
.benefits-cards {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}

.benefit-card {
    background: var(--bg-white);
    border-radius: 16px;
    padding: 16px;
    border: 2px solid var(--border-color);
    transition: all 0.3s;
}

.benefit-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-md);
    border-color: var(--primary);
}

.benefit-card.highlight {
    background: linear-gradient(135deg, rgba(15, 116, 60, 0.08), rgba(26, 158, 82, 0.05));
    border-color: var(--primary);
}

.benefit-period {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    margin-bottom: 12px;
    font-size: 12px;
    font-weight: 700;
    color: var(--text-dark);
}

.benefit-period i {
    font-size: 14px;
    color: var(--primary);
}

.benefit-details {
    text-align: center;
}

.benefit-tasks {
    font-size: 11px;
    color: var(--text-gray);
    font-weight: 600;
    margin-bottom: 6px;
}

.benefit-amount {
    font-size: 18px;
    font-weight: 900;
    color: var(--primary);
}

/* Commission Grid */
.commission-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.commission-card {
    background: var(--bg-white);
    border-radius: 14px;
    padding: 14px;
    border: 2px solid var(--border-color);
    text-align: center;
    transition: all 0.3s;
}

.commission-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-md);
    border-color: var(--primary);
}

.commission-level {
    margin-bottom: 10px;
}

.level-badge {
    display: inline-block;
    padding: 4px 12px;
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    color: #fff;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
}

.commission-details {
    margin-bottom: 8px;
}

.commission-percent {
    font-size: 24px;
    font-weight: 900;
    color: var(--primary);
    margin-bottom: 4px;
}

.commission-amount {
    font-size: 14px;
    font-weight: 800;
    color: var(--warning);
}

.commission-desc {
    font-size: 11px;
    color: var(--text-gray);
    font-weight: 600;
}

/* Task Commission List */
.task-commission-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.task-commission-item {
    background: var(--bg-white);
    border-radius: 14px;
    padding: 14px 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border: 2px solid var(--border-color);
    transition: all 0.3s;
}

.task-commission-item:hover {
    border-color: var(--primary);
    box-shadow: var(--shadow-sm);
}

.task-level {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
}

.task-level-icon {
    width: 36px;
    height: 36px;
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    color: #fff;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 800;
}

.task-level-name {
    font-size: 13px;
    font-weight: 700;
    color: var(--text-dark);
}

.task-commission-rate {
    text-align: right;
}

.task-percent {
    display: block;
    font-size: 18px;
    font-weight: 900;
    color: var(--primary);
    margin-bottom: 2px;
}

.task-label {
    display: block;
    font-size: 10px;
    color: var(--text-gray);
    font-weight: 600;
}

/* Balance Section V3 */
.pkg-balance-section {
    background: linear-gradient(135deg, rgba(15, 116, 60, 0.08), rgba(26, 158, 82, 0.05));
    border: 2px solid rgba(15, 116, 60, 0.2);
    border-radius: 16px;
    padding: 0;
    margin-top: 8px;
    overflow: hidden;
}

.balance-header {
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    color: #fff;
    padding: 12px 16px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 800;
}

.balance-content {
    padding: 14px 16px;
}

.balance-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    font-size: 14px;
}

.balance-label {
    color: var(--text-dark);
    font-weight: 700;
}

.balance-val {
    font-weight: 900;
    font-size: 18px;
    color: var(--primary);
}

.need-val {
    font-weight: 900;
    font-size: 18px;
}

.text-danger {
    color: var(--error) !important;
}

/* Modal Footer V3 */
.pkg-footer-v3 {
    padding: 20px;
    background: var(--bg-white);
    border-top: 2px solid var(--border-color);
}

.pkg-btn-v3 {
    width: 100%;
    padding: 18px 24px;
    border: none;
    border-radius: 16px;
    font-size: 16px;
    font-weight: 800;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.btn-content {
    display: flex;
    align-items: center;
    gap: 14px;
    position: relative;
    z-index: 2;
}

.btn-content i {
    font-size: 24px;
}

.btn-content > div {
    text-align: left;
}

.btn-title {
    font-size: 16px;
    font-weight: 800;
    line-height: 1.2;
    margin-bottom: 2px;
}

.btn-subtitle {
    font-size: 11px;
    font-weight: 500;
    opacity: 0.9;
}

.btn-shine {
    position: absolute;
    top: -50%;
    left: -100%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent, rgba(255,255,255,0.3), transparent);
    transform: rotate(45deg);
    transition: left 0.6s;
}

.pkg-btn-v3:hover .btn-shine {
    left: 100%;
}

.pkg-btn-v3.btn-buy {
    background: linear-gradient(135deg, #0F743C 0%, #1a9e52 100%);
    color: #fff;
    box-shadow: 0 8px 25px rgba(15, 116, 60, 0.4);
}

.pkg-btn-v3.btn-buy:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 35px rgba(15, 116, 60, 0.5);
    color: #fff;
}

.pkg-btn-v3.btn-deposit {
    background: linear-gradient(135deg, #F99E2B 0%, #ffc107 100%);
    color: #fff;
    box-shadow: 0 8px 25px rgba(249, 158, 43, 0.4);
}

.pkg-btn-v3.btn-deposit:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 35px rgba(249, 158, 43, 0.5);
    color: #fff;
}

.pkg-btn-v3.btn-active {
    background: linear-gradient(135deg, rgba(15, 116, 60, 0.12), rgba(26, 158, 82, 0.08));
    color: var(--primary);
    border: 2px solid rgba(15, 116, 60, 0.3);
    cursor: default;
    padding: 16px 24px;
}

.pkg-btn-v3.btn-login {
    background: linear-gradient(135deg, #0F743C 0%, #1a9e52 100%);
    color: #fff;
    box-shadow: 0 8px 25px rgba(15, 116, 60, 0.4);
}

.pkg-btn-v3.btn-login:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 35px rgba(15, 116, 60, 0.5);
    color: #fff;
}

.footer-trust {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-top: 14px;
    font-size: 11px;
    color: var(--text-gray);
    font-weight: 600;
}

.footer-trust i {
    color: var(--success);
    font-size: 14px;
}

/* ==================== */
/* MOBILE MODAL REDESIGN - CLEAN & SIMPLE */
/* ==================== */

/* Quick Stats Mobile */
.quick-stats-mobile {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-bottom: 16px;
}

.stat-card-mobile {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 12px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.stat-icon-mobile {
    width: 40px;
    height: 40px;
    background: var(--primary);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 16px;
    flex-shrink: 0;
}

.stat-info-mobile {
    flex: 1;
}

.stat-label-mobile {
    font-size: 10px;
    color: var(--text-gray);
    font-weight: 600;
    margin-bottom: 2px;
}

.stat-value-mobile {
    font-size: 16px;
    font-weight: 800;
    color: var(--text-dark);
}

/* Section Mobile */
.pkg-section-mobile {
    background: #fff;
    border-radius: 14px;
    padding: 14px;
    margin-bottom: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.pkg-section-mobile.highlight-mobile {
    background: linear-gradient(135deg, rgba(15, 116, 60, 0.05), rgba(26, 158, 82, 0.03));
    border: 1px solid rgba(15, 116, 60, 0.15);
}

.section-header-mobile {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 12px;
}

.section-header-mobile i {
    color: var(--primary);
    font-size: 16px;
}

/* Withdrawal Info Mobile */
.withdrawal-info-mobile {
    font-size: 13px;
}

.info-row-mobile {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 4px;
}

.info-label-mobile {
    color: var(--text-gray);
    font-weight: 600;
}

.info-value-mobile {
    color: var(--text-dark);
    font-weight: 800;
    font-size: 15px;
}

.info-note-mobile {
    font-size: 11px;
    color: var(--text-gray);
    margin-top: 6px;
    font-weight: 500;
}

/* Earnings Table Mobile */
.earnings-table-mobile {
    overflow-x: auto;
}

.table-mobile {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.table-mobile thead th {
    background: #f8f9fa;
    padding: 10px 8px;
    text-align: left;
    font-weight: 700;
    font-size: 11px;
    color: var(--text-gray);
    border-bottom: 2px solid #e5e7eb;
}

.table-mobile tbody tr {
    border-bottom: 1px solid #f0f0f0;
}

.table-mobile tbody tr.highlight-row {
    background: rgba(15, 116, 60, 0.05);
}

.table-mobile tbody td {
    padding: 12px 8px;
    color: var(--text-dark);
}

.table-mobile .period-cell {
    font-weight: 700;
    color: var(--text-dark);
}

.table-mobile .amount-cell {
    font-weight: 800;
    color: var(--primary);
    font-size: 14px;
}

/* Commission List Mobile */
.commission-list-mobile {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.commission-row-mobile {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 10px;
}

.commission-text-mobile {
    flex: 1;
}

.comm-desc-mobile {
    font-size: 12px;
    color: var(--text-dark);
    font-weight: 600;
    line-height: 1.4;
}

.commission-values-mobile {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 2px;
    flex-shrink: 0;
    margin-left: 10px;
}

.comm-percent-mobile {
    font-size: 16px;
    font-weight: 800;
    color: var(--primary);
}

.comm-amount-mobile {
    font-size: 13px;
    font-weight: 700;
    color: var(--text-gray);
}

/* Task Commission Mobile */
.task-commission-list-mobile {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.task-row-mobile {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 12px;
    background: #f8f9fa;
    border-radius: 8px;
}

.task-desc-mobile {
    font-size: 12px;
    color: var(--text-dark);
    font-weight: 600;
    flex: 1;
}

.task-percent-mobile {
    font-size: 16px;
    font-weight: 800;
    color: var(--primary);
    flex-shrink: 0;
    margin-left: 10px;
}

/* ==================== */
/* PHONE-ONLY CLEAN DESIGN */
/* ==================== */

/* Clean mobile design - Hide extra elements on phones */
@media (max-width: 767px) {
    /* HIDE: Benefits scrolling banner on mobile */
    .benefits-banner {
        display: none !important;
    }
    
    /* HIDE: ROI badge on mobile cards */
    .plan-roi {
        display: none !important;
    }
    
    /* HIDE: Daily earnings in meta on mobile */
    .plan-meta .meta-item:last-child {
        display: none !important;
    }
    
    /* HIDE: Trust section on small mobile */
    .trust-section {
        display: none !important;
    }
    
    /* HIDE: Bottom hint */
    .bottom-hint {
        display: none !important;
    }
    
    /* HIDE: Header subtitle on mobile */
    .header-subtitle {
        display: none !important;
    }
    
    /* Simpler header */
    .plans-header {
        padding: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .header-center {
        flex: 1;
        text-align: center;
    }
    
    .header-title {
        font-size: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    
    .header-title i {
        display: none;
    }
    
    /* Compact balance badge */
    .header-balance {
        padding: 8px 12px;
        border-radius: 12px;
        background: rgba(255,255,255,0.2);
    }
    
    .balance-icon {
        display: none;
    }
    
    .balance-info {
        text-align: right;
    }
    
    .bal-label {
        font-size: 9px;
        display: block;
    }
    
    .bal-amount {
        font-size: 13px;
        font-weight: 800;
    }
    
    /* Clean plan cards - horizontal layout */
    .plans-list {
        padding: 16px;
        gap: 12px;
    }
    
    .plan-card {
        display: flex;
        flex-direction: row;
        align-items: center;
        padding: 16px;
        gap: 14px;
        border-radius: 16px;
        min-height: auto;
    }
    
    .plan-card .card-glow,
    .plan-card .icon-pulse {
        display: none;
    }
    
    .plan-icon-wrap {
        flex-shrink: 0;
    }
    
    .plan-icon {
        width: 56px;
        height: 56px;
        font-size: 24px;
        border-radius: 50%;
    }
    
    .plan-icon .icon-shine {
        display: none;
    }
    
    .plan-info {
        flex: 1;
        text-align: left;
    }
    
    .plan-name {
        font-size: 16px;
        font-weight: 700;
        margin-bottom: 4px;
    }
    
    .plan-price-wrap {
        flex-direction: column;
        align-items: flex-start;
        gap: 2px;
    }
    
    .plan-price {
        display: flex;
        align-items: baseline;
    }
    
    .plan-price .currency {
        font-size: 14px;
    }
    
    .plan-price .amount {
        font-size: 20px;
        font-weight: 800;
        color: var(--primary);
    }
    
    .plan-meta {
        margin-top: 4px;
    }
    
    .plan-meta .meta-item {
        font-size: 12px;
        color: var(--text-gray);
    }
    
    .plan-meta .meta-item i {
        display: none;
    }
    
    /* Action button */
    .plan-action {
        flex-shrink: 0;
    }
    
    .btn-details {
        padding: 12px 20px;
        font-size: 13px;
        border-radius: 12px;
        min-width: auto;
        min-height: 44px;
    }
    
    .btn-details i {
        display: none;
    }
    
    .status-badge {
        padding: 12px 16px;
        font-size: 13px;
        border-radius: 12px;
        min-width: auto;
        min-height: 44px;
    }
    
    .status-badge i {
        display: none;
    }
    
    /* Active ribbon smaller */
    .active-ribbon {
        font-size: 9px;
        padding: 4px 10px;
    }
    
    .active-ribbon i {
        font-size: 8px;
    }
    
    /* Simple trust indicators at bottom */
    .plans-screen::after {
        content: '';
        display: block;
        padding: 16px;
    }
}

/* Even smaller phones */
@media (max-width: 420px) {
    .plans-header {
        padding: 14px 12px;
    }
    
    .header-back {
        width: 40px;
        height: 40px;
        font-size: 16px;
    }
    
    .header-title {
        font-size: 16px;
    }
    
    .bal-amount {
        font-size: 12px;
    }
    
    .plans-list {
        padding: 12px;
        gap: 10px;
    }
    
    .plan-card {
        padding: 14px;
        gap: 12px;
    }
    
    .plan-icon {
        width: 50px;
        height: 50px;
        font-size: 22px;
    }
    
    .plan-name {
        font-size: 15px;
    }
    
    .plan-price .amount {
        font-size: 18px;
    }
    
    .btn-details {
        padding: 10px 16px;
        font-size: 12px;
    }
    
    .status-badge {
        padding: 10px 14px;
        font-size: 12px;
    }
}

@media (max-width: 360px) {
    .header-back {
        width: 36px;
        height: 36px;
        font-size: 14px;
    }
    
    .header-title {
        font-size: 15px;
    }
    
    .bal-label {
        display: none;
    }
    
    .bal-amount {
        font-size: 11px;
    }
    
    .plan-card {
        padding: 12px;
        gap: 10px;
    }
    
    .plan-icon {
        width: 44px;
        height: 44px;
        font-size: 18px;
    }
    
    .plan-name {
        font-size: 14px;
    }
    
    .plan-price .currency {
        font-size: 12px;
    }
    
    .plan-price .amount {
        font-size: 16px;
    }
    
    .plan-meta .meta-item {
        font-size: 11px;
    }
    
    .btn-details {
        padding: 10px 14px;
        font-size: 11px;
    }
    
    .status-badge {
        padding: 10px 12px;
        font-size: 11px;
    }
}

/* Modal optimizations for mobile */
@media (max-width: 767px) {
    .pkg-modal-v3 {
        border-radius: 20px 20px 0 0;
    }
    
    .pkg-header-v3 {
        padding: 20px 16px;
    }
    
    .pkg-icon-v3 {
        width: 60px;
        height: 60px;
        font-size: 26px;
        margin-bottom: 10px;
    }
    
    .pkg-name-v3 {
        font-size: 20px;
    }
    
    .pkg-price-v3 .price-val {
        font-size: 32px;
    }
    
    .pkg-badges {
        gap: 8px;
    }
    
    .badge-item {
        font-size: 10px;
        padding: 5px 10px;
    }
    
    .pkg-body-v3 {
        padding: 16px;
    }
    
    .quick-stats {
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }
    
    .opportunities-grid {
        grid-template-columns: 1fr;
        gap: 10px;
    }
    
    .benefits-cards {
        grid-template-columns: 1fr;
        gap: 8px;
    }
    
    .commission-grid {
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }
    
    .pkg-footer-v3 {
        padding: 16px;
    }
    
    .btn-content i {
        font-size: 20px;
    }
    
    .btn-title {
        font-size: 14px;
    }
    
    .btn-subtitle {
        font-size: 10px;
    }
}

@media (max-width: 420px) {
    .quick-stats {
        grid-template-columns: 1fr;
    }
    
    .commission-grid {
        grid-template-columns: 1fr;
    }
    
    .pkg-header-v3 {
        padding: 16px 14px;
    }
    
    .pkg-icon-v3 {
        width: 52px;
        height: 52px;
        font-size: 22px;
    }
    
    .pkg-name-v3 {
        font-size: 18px;
    }
    
    .pkg-price-v3 .price-val {
        font-size: 28px;
    }
    
    .pkg-body-v3 {
        padding: 14px;
    }
    
    .section-title {
        font-size: 14px;
    }
    
    .section-subtitle {
        font-size: 10px;
    }
}

/* Touch feedback for mobile */
@media (hover: none) and (pointer: coarse) {
    .plan-card:active {
        transform: scale(0.98);
        opacity: 0.95;
    }
    
    .btn-details:active,
    .pkg-btn-v3:active {
        transform: scale(0.96);
    }
    
    .plan-card:hover {
        transform: none;
    }
    
    .btn-shine {
        display: none;
    }
}

/* Safe area for modern phones */
.plans-header {
    padding-top: 20px;
    padding-top: max(20px, env(safe-area-inset-top, 20px));
}

.pkg-footer-v3 {
    padding-bottom: 16px;
    padding-bottom: max(16px, env(safe-area-inset-bottom, 16px));
}
</style>
@endpush

@push('script')
<script>
(function($) {
    "use strict";

    var userBalance = {{ auth()->check() ? auth()->user()->balance : 0 }};
    var curSym = '{{ $general->cur_sym }}';
    var depositUrl = '{{ route("user.deposit") }}';
    var packageModalInstance = bootstrap.Modal.getOrCreateInstance(document.getElementById('PackageModal'));

    // Referral commission data
    var referralCommissions = @json($referralCommissions);

    // Click on plan card to open modal
    $('.plan-card').click(function() {
        var $card = $(this);
        var modal = $('#PackageModal');

        var planId = $card.data('id');
        var planName = $card.data('name');
        var planPrice = parseFloat($card.data('price'));
        var planPriceFormatted = $card.data('price-formatted');
        var dailyLimit = parseInt($card.data('daily')) || 5;
        var refLevel = parseInt($card.data('ref')) || 10;
        var validity = parseInt($card.data('validity')) || 365;
        var dailyEarning = parseFloat($card.data('daily-earning')) || 0;
        var monthlyEarning = parseFloat($card.data('monthly-earning')) || 0;
        var yearlyEarning = parseFloat($card.data('yearly-earning')) || 0;
        var isAffordable = $card.data('affordable') == '1';
        var isActive = $card.data('active') == '1';

        // Calculate ROI
        var roiPercent = ((yearlyEarning / planPrice) * 100).toFixed(0);

        // Update modal header
        modal.find('.pkg-name-v3').text(planName);
        modal.find('.price-val').text(planPriceFormatted);
        modal.find('#planIdInput').val(planId);

        // Update quick stats
        modal.find('.pkg-validity').text(validity);
        modal.find('.pkg-roi').text(roiPercent);

        // Update opportunities section
        modal.find('.pkg-daily-ads').text(dailyLimit);
        modal.find('.pkg-daily-earn').text(dailyEarning.toFixed(2));
        modal.find('.pkg-ref-levels').text(refLevel);

        // Update benefits cards
        modal.find('.pkg-daily-ads-2').text(dailyLimit + 'টি');
        modal.find('.pkg-monthly-ads-2').text((dailyLimit * 30) + 'টি');
        modal.find('.pkg-yearly-ads-2').text((dailyLimit * validity) + 'টি');

        modal.find('.pkg-daily-earn-2').text(dailyEarning.toFixed(2));
        modal.find('.pkg-monthly-earn-2').text(monthlyEarning.toFixed(2));
        modal.find('.pkg-yearly-earn-2').text(yearlyEarning.toFixed(2));

        // Update referral commission amounts based on plan price
        if (referralCommissions && referralCommissions.length > 0) {
            referralCommissions.forEach(function(ref) {
                var commAmount = (planPrice * ref.percent / 100).toFixed(2);
                modal.find('.ref-amount-' + ref.level).text(commAmount);
            });
        }

        // Calculate need amount
        var needAmount = planPrice - userBalance;
        if (needAmount > 0) {
            modal.find('.need-row').show();
            modal.find('.need-val').text(curSym + needAmount.toFixed(2));
        } else {
            modal.find('.need-row').hide();
        }

        // Show appropriate button
        modal.find('#btnBuyNow, #btnDeposit, .btn-active').hide();

        if (isActive) {
            modal.find('.btn-active').show();
        } else if (isAffordable) {
            modal.find('#btnBuyNow').show();
        } else {
            modal.find('#btnDeposit').show().attr('href', depositUrl + '?amount=' + planPrice);
        }

        packageModalInstance.show();
    });

    // Buy button click
    $('#btnBuyNow').click(function() {
        $('#buyForm').submit();
    });

})(jQuery);
</script>
@endpush
