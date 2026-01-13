@extends($activeTemplate.'layouts.master_mobile')
@section('main-content')

<!-- Modern Header with Balance -->
<div class="modern-header">
    <div class="header-top">
        <div class="user-greeting">
            <span class="greeting-text">Hi, {{ $user->username }}</span>
            @if($isPremium)
            <span class="crown-badge premium">
                <i class="fas fa-crown"></i> প্রিমিয়াম সদস্য
            </span>
            @else
            <span class="crown-badge free">
                <i class="fas fa-user"></i> ফ্রি সদস্য
            </span>
            @endif
        </div>
        <div class="balance-display">
            <span class="balance-amount">৳ {{ showAmount($user->balance) }}</span>
            <button class="balance-toggle" onclick="toggleBalance(this)">
                <i class="fas fa-eye"></i>
            </button>
        </div>
    </div>
    <div class="header-actions">
        <a href="{{ route('user.transactions') }}" class="action-btn history-btn">
            <div class="btn-icon">
                <i class="fas fa-history"></i>
            </div>
            <span class="btn-label">ইতিহাস</span>
        </a>
        <a href="{{ route('user.withdraw') }}" class="action-btn withdraw-btn">
            <div class="btn-icon">
                <i class="fas fa-minus-circle"></i>
            </div>
            <span class="btn-label">উত্তোলন</span>
        </a>
        <a href="{{ route('user.deposit') }}" class="action-btn deposit-btn">
            <div class="btn-icon">
                <i class="fas fa-plus-circle"></i>
            </div>
            <span class="btn-label">জমা</span>
        </a>
    </div>
    <div class="page-title-bar">
        <div class="title-icon">
            <i class="fas fa-users"></i>
        </div>
        <h1 class="page-title">রেফারেল সিস্টেম</h1>
        <div class="stats-mini">
            <span class="stat-item">
                <strong>{{ $refUsers->total() }}</strong> রেফার
            </span>
            <span class="stat-divider">•</span>
            <span class="stat-item">
                <strong>৳{{ showAmount($totalReferralEarnings ?? 0) }}</strong> কমিশন
            </span>
        </div>
    </div>
</div>

<!-- ✅ Non-Premium Warning Banner -->
@if(!$isPremium)
<div class="warning-banner non-premium">
    <div class="warning-icon"><i class="fas fa-info-circle"></i></div>
    <div class="warning-content">
        <strong>আপনি ফ্রি সদস্য</strong>
        <p>ফ্রি সদস্যদের উইথড্র লিমিট সীমিত। প্রিমিয়াম প্ল্যান নিলে সব সুবিধা পাবেন।</p>
    </div>
    <a href="{{ route('plans') }}" class="warning-action">আপগ্রেড</a>
</div>
@endif

<!-- ✅ User's Own Signup Bonus Notification (if referred by someone) -->
@if($userGotSignupBonus && $referrer)
<div class="info-banner signup-bonus">
    <div class="banner-icon"><i class="fas fa-gift"></i></div>
    <div class="banner-content">
        <strong>🎉 আপনি ৳{{ showAmount($signupReferredAmount) }} বোনাস পেয়েছেন!</strong>
        <p>{{ $referrer->username }} এর রেফারেলে জয়েন করার জন্য</p>
    </div>
</div>
@endif

<!-- ✅ How Referral Works Section -->
@if($signupCommissionEnabled)
<div class="section-block how-it-works">
    <div class="section-head">
        <h3><i class="fas fa-question-circle"></i> রেফারেল কিভাবে কাজ করে?</h3>
    </div>
    <div class="steps-container">
        <div class="step-item">
            <div class="step-number">১</div>
            <div class="step-content">
                <strong>লিংক শেয়ার করুন</strong>
                <p>আপনার রেফারেল লিংক বন্ধুদের পাঠান</p>
            </div>
        </div>
        <div class="step-arrow"><i class="fas fa-arrow-right"></i></div>
        <div class="step-item">
            <div class="step-number">২</div>
            <div class="step-content">
                <strong>রেজিস্ট্রেশন সম্পন্ন</strong>
                <p>বন্ধু একাউন্ট খুলে রেজিস্ট্রেশন করবে</p>
            </div>
        </div>
        <div class="step-arrow"><i class="fas fa-arrow-right"></i></div>
        <div class="step-item">
            <div class="step-number">৩</div>
            <div class="step-content">
                <strong>দুইজনই পাবেন</strong>
                <p>আপনি ৳{{ showAmount($signupReferrerAmount) }}, বন্ধু ৳{{ showAmount($signupReferredAmount) }}</p>
            </div>
        </div>
    </div>
    <div class="reward-highlight">
        <div class="reward-card">
            <i class="fas fa-user-plus"></i>
            <span>রেফারার পাবেন</span>
            <strong>৳{{ showAmount($signupReferrerAmount) }}</strong>
        </div>
        <div class="reward-plus">+</div>
        <div class="reward-card">
            <i class="fas fa-user-check"></i>
            <span>নতুন ইউজার পাবেন</span>
            <strong>৳{{ showAmount($signupReferredAmount) }}</strong>
        </div>
        <div class="reward-equals">=</div>
        <div class="reward-card total">
            <i class="fas fa-coins"></i>
            <span>মোট</span>
            <strong>৳{{ showAmount($signupReferrerAmount + $signupReferredAmount) }}</strong>
        </div>
    </div>
    <div class="info-note">
        <i class="fas fa-info-circle"></i>
        <span>রেফারেল রিওয়ার্ড শুধুমাত্র একাউন্ট রেজিস্ট্রেশন সম্পূর্ণ হলে যোগ হবে।</span>
    </div>
</div>
@endif

<!-- Referral Link Card -->
<div class="section-block">
    <div class="section-head">
        <h3><i class="fas fa-link"></i> আপনার রেফারেল লিংক</h3>
    </div>
    <div class="ref-link-box">
        <input type="text" value="{{ route('home') }}?reference={{ $user->referral_code }}" id="referralURL" readonly class="ref-input">
        <button class="copy-btn" id="copyBoard">
            <i class="fas fa-copy"></i>
        </button>
    </div>
    <div class="share-btns">
        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('home').'?reference='.$user->referral_code) }}" target="_blank" class="share-btn facebook">
            <i class="fab fa-facebook-f"></i>
        </a>
        <a href="https://wa.me/?text={{ urlencode('Join AGCO and earn money! '.route('home').'?reference='.$user->referral_code) }}" target="_blank" class="share-btn whatsapp">
            <i class="fab fa-whatsapp"></i>
        </a>
        <a href="https://telegram.me/share/url?url={{ urlencode(route('home').'?reference='.$user->referral_code) }}" target="_blank" class="share-btn telegram">
            <i class="fab fa-telegram-plane"></i>
        </a>
    </div>
</div>

<!-- ✅ Balance Breakdown Card -->
<div class="section-block balance-breakdown">
    <div class="section-head">
        <h3><i class="fas fa-wallet"></i> ব্যালেন্স ব্রেকডাউন</h3>
    </div>
    <div class="breakdown-grid">
        <div class="breakdown-item main">
            <div class="breakdown-icon"><i class="fas fa-money-bill-wave"></i></div>
            <div class="breakdown-info">
                <span class="breakdown-label">মেইন ব্যালেন্স</span>
                <strong class="breakdown-value">৳{{ showAmount($balanceBreakdown['main_balance']) }}</strong>
            </div>
            <span class="breakdown-badge withdrawable">উইথড্র যোগ্য</span>
        </div>
        <div class="breakdown-item referral">
            <div class="breakdown-icon"><i class="fas fa-user-friends"></i></div>
            <div class="breakdown-info">
                <span class="breakdown-label">রেফারেল আয়</span>
                <strong class="breakdown-value">৳{{ showAmount($totalReferralEarnings) }}</strong>
            </div>
            <span class="breakdown-badge approved">✓ এপ্রুভড</span>
        </div>
        <div class="breakdown-item ptc">
            <div class="breakdown-icon"><i class="fas fa-mouse-pointer"></i></div>
            <div class="breakdown-info">
                <span class="breakdown-label">PTC আয়</span>
                <strong class="breakdown-value">৳{{ showAmount($ptcEarnings) }}</strong>
            </div>
        </div>
        <div class="breakdown-item hold">
            <div class="breakdown-icon"><i class="fas fa-lock"></i></div>
            <div class="breakdown-info">
                <span class="breakdown-label">হোল্ড ব্যালেন্স</span>
                <strong class="breakdown-value">৳{{ showAmount($balanceBreakdown['total_hold']) }}</strong>
            </div>
            <span class="breakdown-badge locked">🔒 লকড</span>
        </div>
    </div>
    @if($balanceBreakdown['total_hold'] > 0)
    <div class="hold-details">
        <small><i class="fas fa-clock"></i> হোল্ড ব্যালেন্স ৩০ দিন পর স্বয়ংক্রিয়ভাবে রিলিজ হবে</small>
    </div>
    @endif
</div>

<!-- Commission Structure - Free vs Premium -->
@if($hasAnyCommission)
<div class="section-block commission-structure-section">
    <div class="section-head">
        <h3><i class="fas fa-percentage me-2"></i>কমিশন স্ট্রাকচার</h3>
    </div>

    <!-- Commission Type Tabs -->
    <div class="commission-tabs">
        <button class="commission-tab active" data-tab="free">
            <i class="fas fa-user"></i> ফ্রি কমিশন
        </button>
        <button class="commission-tab" data-tab="premium">
            <i class="fas fa-crown"></i> প্ল্যান কমিশন
        </button>
    </div>

    <!-- Free User Commission Table -->
    <div class="commission-tab-content active" id="free-commission">
        @if(!$isPremium)
        <div class="current-plan-badge free-badge">
            <i class="fas fa-check-circle"></i> আপনার বর্তমান প্ল্যান
        </div>
        @endif

        @php
            // Get max referral level allowed for free users from admin settings
            $maxAllowedLevel = $freeUserSettings['referral_level'] ?? 1;

            // Filter commission arrays to only include levels up to max allowed
            $filteredDepositCommission = collect($freeUserDepositCommission)->filter(function($item) use ($maxAllowedLevel) {
                return ($item['level'] ?? 0) <= $maxAllowedLevel && ($item['amount'] ?? 0) > 0;
            })->values()->all();

            $filteredTaskCommission = collect($freeUserTaskCommission)->filter(function($item) use ($maxAllowedLevel) {
                return ($item['level'] ?? 0) <= $maxAllowedLevel && ($item['amount'] ?? 0) > 0;
            })->values()->all();

            $filteredPlanCommission = collect($freeUserPlanCommission)->filter(function($item) use ($maxAllowedLevel) {
                return ($item['level'] ?? 0) <= $maxAllowedLevel && ($item['amount'] ?? 0) > 0;
            })->values()->all();

            // Check if free user can earn referral
            $canEarnReferral = $freeUserSettings['can_earn_referral'] ?? 1;

            // Get actual levels to display (only configured levels up to max allowed)
            $hasAnyFreeCommission = count($filteredDepositCommission) > 0 || count($filteredTaskCommission) > 0 || count($filteredPlanCommission) > 0;

            // Get max level from filtered commissions
            $freeMaxLevel = max(
                count($filteredDepositCommission) > 0 ? collect($filteredDepositCommission)->max('level') : 0,
                count($filteredTaskCommission) > 0 ? collect($filteredTaskCommission)->max('level') : 0,
                count($filteredPlanCommission) > 0 ? collect($filteredPlanCommission)->max('level') : 0,
                0
            );

            // Limit to admin-set max level
            $freeMaxLevel = min($freeMaxLevel, $maxAllowedLevel);
        @endphp

        @if(!$canEarnReferral || $maxAllowedLevel == 0)
        <div class="no-commission-notice">
            <i class="fas fa-info-circle"></i>
            <span>ফ্রি সদস্যদের জন্য কমিশন বর্তমানে বন্ধ আছে। প্রিমিয়াম প্ল্যান নিন।</span>
        </div>
        @elseif(!$hasAnyFreeCommission)
        <div class="no-commission-notice">
            <i class="fas fa-info-circle"></i>
            <span>এখনো কোনো কমিশন স্ট্রাকচার সেট করা হয়নি।</span>
        </div>
        @else
        <div class="commission-grid-table">
            <table class="grid-table free-table">
                <thead>
                    <tr>
                        <th class="level-col">লেভেল</th>
                        @if(count($filteredDepositCommission) > 0)
                        <th class="deposit-col"><i class="fas fa-coins"></i> ডিপোজিট</th>
                        @endif
                        @if(count($filteredTaskCommission) > 0)
                        <th class="task-col"><i class="fas fa-tasks"></i> টাস্ক</th>
                        @endif
                        @if(count($filteredPlanCommission) > 0)
                        <th class="plan-col"><i class="fas fa-crown"></i> প্ল্যান</th>
                        @endif
                        <th class="details-col">বিবরণ</th>
                    </tr>
                </thead>
                <tbody>
                    @for($i = 1; $i <= $freeMaxLevel; $i++)
                    @php
                        $depositCom = collect($filteredDepositCommission)->firstWhere('level', $i);
                        $taskCom = collect($filteredTaskCommission)->firstWhere('level', $i);
                        $planCom = collect($filteredPlanCommission)->firstWhere('level', $i);

                        // Skip if no commission at this level
                        $hasThisLevel = $depositCom || $taskCom || $planCom;
                    @endphp
                    @if($hasThisLevel)
                    <tr>
                        <td class="level-cell">
                            <div class="level-badge free">
                                <span class="level-num">{{ $i }}</span>
                                <span class="level-label">{{ $i == 1 ? 'A' : ($i == 2 ? 'B' : chr(64 + $i)) }}</span>
                            </div>
                        </td>
                        @if(count($filteredDepositCommission) > 0)
                        <td class="commission-cell deposit">
                            @php $amount = $depositCom ? ($depositCom['amount'] ?? 0) : 0; @endphp
                            <span class="amount-badge deposit-free">৳{{ showAmount($amount) }}</span>
                        </td>
                        @endif
                        @if(count($filteredTaskCommission) > 0)
                        <td class="commission-cell task">
                            @php $amount = $taskCom ? ($taskCom['amount'] ?? 0) : 0; @endphp
                            <span class="amount-badge task-free">৳{{ showAmount($amount) }}</span>
                        </td>
                        @endif
                        @if(count($filteredPlanCommission) > 0)
                        <td class="commission-cell plan">
                            @php $amount = $planCom ? ($planCom['amount'] ?? 0) : 0; @endphp
                            <span class="amount-badge plan-free">৳{{ showAmount($amount) }}</span>
                        </td>
                        @endif
                        <td class="details-cell">
                            @if($i == 1)
                                <span class="detail-text">আপনি সরাসরি রেফার করলে</span>
                            @elseif($i == 2)
                                <span class="detail-text">আপনার রেফার এর রেফার</span>
                            @else
                                <span class="detail-text">লেভেল {{ $i }} রেফার</span>
                            @endif
                        </td>
                    </tr>
                    @endif
                    @endfor
                </tbody>
            </table>
        </div>
        @endif

        @if(!$isPremium && $freeUserSettings['enabled'] && $canEarnReferral && $maxAllowedLevel > 0)
        <div class="free-user-limits">
            <div class="limit-item">
                <i class="fas fa-layer-group"></i>
                <span>সর্বোচ্চ লেভেল: <strong>{{ $maxAllowedLevel }}</strong></span>
            </div>
            <div class="limit-item">
                <i class="fas fa-money-bill-wave"></i>
                <span>দৈনিক উত্তোলন: <strong>৳{{ showAmount($freeUserSettings['daily_withdraw_limit']) }}</strong></span>
            </div>
            <div class="limit-item">
                <i class="fas fa-arrow-down"></i>
                <span>সর্বনিম্ন উত্তোলন: <strong>৳{{ showAmount($freeUserSettings['min_withdraw']) }}</strong></span>
            </div>
            <div class="limit-item">
                <i class="fas fa-arrow-up"></i>
                <span>সর্বোচ্চ উত্তোলন: <strong>৳{{ showAmount($freeUserSettings['max_withdraw']) }}</strong></span>
            </div>
            @if($freeUserSettings['can_view_ptc'])
            <div class="limit-item">
                <i class="fas fa-ad"></i>
                <span>দৈনিক PTC: <strong>{{ $freeUserSettings['ptc_limit'] }} টি</strong></span>
            </div>
            @endif
        </div>
        @endif
    </div>

    <!-- Premium User Commission Table -->
    <div class="commission-tab-content" id="premium-commission">
        @if($isPremium)
        <div class="current-plan-badge premium-badge">
            <i class="fas fa-crown"></i> আপনার বর্তমান প্ল্যান
        </div>
        @else
        <div class="upgrade-prompt">
            <i class="fas fa-arrow-up"></i>
            <span>প্রিমিয়াম প্ল্যান নিলে এই কমিশন পাবেন</span>
            <a href="{{ route('plans') }}" class="upgrade-btn">আপগ্রেড করুন</a>
        </div>
        @endif

        <div class="commission-grid-table">
            <table class="grid-table premium-table">
                <thead>
                    <tr>
                        <th class="level-col">লেভেল</th>
                        @if($depositCommissions->count() > 0)
                        <th class="deposit-col"><i class="fas fa-coins"></i> ডিপোজিট</th>
                        @endif
                        @if($taskCommissions->count() > 0)
                        <th class="task-col"><i class="fas fa-tasks"></i> টাস্ক</th>
                        @endif
                        @if($planCommissions->count() > 0)
                        <th class="plan-col"><i class="fas fa-crown"></i> প্ল্যান</th>
                        @endif
                        <th class="details-col">বিবরণ</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $maxLevel = max(
                            $depositCommissions->count(),
                            $taskCommissions->count(),
                            $planCommissions->count()
                        );
                        if($maxLevel == 0) $maxLevel = 3;
                    @endphp

                    @for($i = 1; $i <= $maxLevel; $i++)
                    <tr>
                        <td class="level-cell premium-level">
                            <div class="level-badge premium">
                                <span class="level-num">{{ $i }}</span>
                                <span class="level-label">{{ $i == 1 ? 'A' : ($i == 2 ? 'B' : 'C') }}</span>
                            </div>
                        </td>
                        @if($depositCommissions->count() > 0)
                        <td class="commission-cell deposit">
                            @php
                                $depositCom = $depositCommissions->firstWhere('level', $i);
                                $percent = $depositCom ? showAmount($depositCom->percent) : 0;
                            @endphp
                            <span class="percent-badge deposit">{{ $percent }}%</span>
                        </td>
                        @endif
                        @if($taskCommissions->count() > 0)
                        <td class="commission-cell task">
                            @php
                                $taskCom = $taskCommissions->firstWhere('level', $i);
                                $percent = $taskCom ? showAmount($taskCom->percent) : 0;
                            @endphp
                            <span class="percent-badge task">{{ $percent }}%</span>
                        </td>
                        @endif
                        @if($planCommissions->count() > 0)
                        <td class="commission-cell plan">
                            @php
                                $planCom = $planCommissions->firstWhere('level', $i);
                                $percent = $planCom ? showAmount($planCom->percent) : 0;
                            @endphp
                            <span class="percent-badge plan">{{ $percent }}%</span>
                        </td>
                        @endif
                        <td class="details-cell">
                            @if($i == 1)
                                <span class="detail-text">আপনি সরাসরি রেফার করলে</span>
                            @elseif($i == 2)
                                <span class="detail-text">আপনার রেফার এর রেফার</span>
                            @elseif($i == 3)
                                <span class="detail-text">লেভেল {{ $i }} রেফার</span>
                            @else
                                <span class="detail-text">লেভেল {{ $i }}</span>
                            @endif
                        </td>
                    </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    </div>

    <!-- Commission Legend -->
    <div class="commission-legend">
        @if($depositCommissions->count() > 0 || count($freeUserDepositCommission) > 0)
        <div class="legend-item">
            <span class="legend-icon deposit"><i class="fas fa-coins"></i></span>
            <span class="legend-text">ডিপোজিট কমিশন</span>
        </div>
        @endif
        @if($taskCommissions->count() > 0 || count($freeUserTaskCommission) > 0)
        <div class="legend-item">
            <span class="legend-icon task"><i class="fas fa-tasks"></i></span>
            <span class="legend-text">টাস্ক কমিশন</span>
        </div>
        @endif
        @if($planCommissions->count() > 0 || count($freeUserPlanCommission) > 0)
        <div class="legend-item">
            <span class="legend-icon plan"><i class="fas fa-crown"></i></span>
            <span class="legend-text">প্ল্যান কমিশন</span>
        </div>
        @endif
    </div>
</div>
@endif

<!-- ✅ Referral Reward History (Enhanced) -->
@if($signupCommissions->count() > 0)
<div class="section-block">
    <div class="section-head">
        <h3><i class="fas fa-history"></i> রেফারেল রিওয়ার্ড হিস্ট্রি</h3>
        <span class="count-badge">{{ $signupCommissions->count() }}</span>
    </div>
    <div class="reward-history-list">
        @foreach($signupCommissions->take(5) as $commission)
        <div class="reward-history-item">
            <div class="reward-icon"><i class="fas fa-user-plus"></i></div>
            <div class="reward-details">
                <div class="reward-title">
                    <strong>সাইনআপ কমিশন</strong>
                    <span class="reward-type-badge signup">অ্যাকাউন্ট ওপেন</span>
                </div>
                <div class="reward-meta">
                    <span class="reward-from">{{ $commission->details }}</span>
                </div>
                <div class="reward-date">
                    <i class="fas fa-calendar-alt"></i> {{ $commission->created_at->format('d M, Y h:i A') }}
                </div>
            </div>
            <div class="reward-amount-col">
                <span class="reward-amount">+৳{{ showAmount($commission->amount) }}</span>
                <span class="reward-status approved"><i class="fas fa-check-circle"></i> এপ্রুভড</span>
            </div>
        </div>
        @endforeach
    </div>
    @if($signupCommissions->count() > 5)
    <div class="view-all-link">
        <a href="{{ route('user.transactions') }}?type=referral">সব দেখুন <i class="fas fa-arrow-right"></i></a>
    </div>
    @endif
</div>
@endif

<!-- Referred Users List (Enhanced) -->
<div class="section-block">
    <div class="section-head">
        <h3><i class="fas fa-user-friends me-2"></i>রেফার্ড ইউজার</h3>
        <span class="count-badge">{{ $refUsers->total() }}</span>
    </div>

    <!-- Referred Users Table -->
    <div class="ref-users-table-wrap">
        <table class="ref-users-table">
            <thead>
                <tr>
                    <th>ইউজার</th>
                    <th>রেফারেল টাইপ</th>
                    <th>কমিশন</th>
                    <th>তারিখ</th>
                    <th>স্ট্যাটাস</th>
                </tr>
            </thead>
            <tbody>
                @forelse($refUsers as $log)
                <tr>
                    <td>
                        <div class="user-cell">
                            <div class="user-avatar">{{ strtoupper(substr($log->username, 0, 1)) }}</div>
                            <div class="user-info-col">
                                <span class="user-name">{{ $log->fullname }}</span>
                                <span class="username-text">{{ '@' . $log->username }}</span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="ref-type-badge account-open">
                            <i class="fas fa-user-plus"></i> অ্যাকাউন্ট ওপেন
                        </span>
                    </td>
                    <td>
                        <span class="commission-earned">+৳{{ showAmount($signupReferrerAmount) }}</span>
                    </td>
                    <td>
                        <span class="join-date">{{ $log->created_at->format('d M, Y') }}</span>
                    </td>
                    <td>
                        @if($log->status == 1)
                        <span class="reward-status-badge approved">
                            <i class="fas fa-check-circle"></i> এপ্রুভড
                        </span>
                        @else
                        <span class="reward-status-badge pending">
                            <i class="fas fa-clock"></i> পেন্ডিং
                        </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">
                        <div class="empty-state-sm">
                            <i class="fas fa-user-plus"></i>
                            <p>এখনো কেউ জয়েন করেনি</p>
                            <small>আপনার রেফারেল লিংক শেয়ার করুন এবং আয় করুন!</small>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($refUsers->hasPages())
<div class="pagination-wrap">
    {{ $refUsers->links() }}
</div>
@endif

<div style="height: 20px;"></div>
@endsection

@push('style')
<style>
/* Modern Header Design */
.modern-header{background:#fff;padding:16px;margin-bottom:12px;border-bottom:1px solid #e5e7eb}
.header-top{display:flex;justify-content:space-between;align-items:center;margin-bottom:14px}
.user-greeting{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.greeting-text{font-size:15px;font-weight:700;color:#1a1a1a}
.crown-badge{padding:4px 10px;border-radius:20px;font-size:10px;font-weight:700;display:inline-flex;align-items:center;gap:4px;box-shadow:0 2px 6px rgba(0,0,0,0.1)}
.crown-badge.premium{background:linear-gradient(135deg,#F99E2B 0%,#e88a1a 100%);color:#fff}
.crown-badge.free{background:#e5e7eb;color:#666}
.crown-badge i{font-size:11px}

/* ✅ Warning & Info Banners */
.warning-banner{display:flex;align-items:center;gap:12px;background:#fff3cd;border:1px solid #ffc107;border-radius:12px;padding:12px 16px;margin:12px 16px}
.warning-banner.non-premium{background:linear-gradient(135deg,#fff8e1 0%,#ffecb3 100%);border-color:#ffb300}
.warning-icon{width:36px;height:36px;background:#ffc107;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:18px;flex-shrink:0}
.warning-content{flex:1}
.warning-content strong{display:block;font-size:13px;color:#856404;margin-bottom:2px}
.warning-content p{font-size:11px;color:#856404;margin:0}
.warning-action{background:linear-gradient(135deg,#F99E2B 0%,#e88a1a 100%);color:#fff;padding:8px 16px;border-radius:8px;font-size:11px;font-weight:700;text-decoration:none;white-space:nowrap}

.info-banner{display:flex;align-items:center;gap:12px;background:linear-gradient(135deg,#e8f5e9 0%,#c8e6c9 100%);border:1px solid #4caf50;border-radius:12px;padding:12px 16px;margin:12px 16px}
.info-banner.signup-bonus{background:linear-gradient(135deg,#e8f5e9 0%,#c8e6c9 100%)}
.banner-icon{width:36px;height:36px;background:#4caf50;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:18px;flex-shrink:0}
.banner-content{flex:1}
.banner-content strong{display:block;font-size:13px;color:#2e7d32;margin-bottom:2px}
.banner-content p{font-size:11px;color:#388e3c;margin:0}

/* ✅ How Referral Works Section */
.how-it-works{background:linear-gradient(135deg,#f8faf9 0%,#e8f5e9 100%);border:1px solid #c8e6c9}
.steps-container{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:16px;flex-wrap:wrap}
.step-item{flex:1;min-width:80px;text-align:center;padding:12px 8px;background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.05)}
.step-number{width:32px;height:32px;background:linear-gradient(135deg,#0F743C 0%,#0a5229 100%);color:#fff;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:14px;font-weight:800;margin-bottom:8px}
.step-content strong{display:block;font-size:11px;color:#1a1a1a;margin-bottom:2px}
.step-content p{font-size:10px;color:#666;margin:0}
.step-arrow{color:#0F743C;font-size:16px;flex-shrink:0}

.reward-highlight{display:flex;align-items:center;justify-content:center;gap:8px;flex-wrap:wrap;padding:16px;background:#fff;border-radius:12px;margin-bottom:12px}
.reward-card{text-align:center;padding:12px 16px;background:linear-gradient(135deg,#f8f9fa 0%,#e9ecef 100%);border-radius:10px;min-width:80px}
.reward-card i{font-size:20px;color:#0F743C;display:block;margin-bottom:4px}
.reward-card span{font-size:10px;color:#666;display:block}
.reward-card strong{font-size:16px;color:#0F743C;font-weight:800}
.reward-card.total{background:linear-gradient(135deg,#0F743C 0%,#0a5229 100%)}
.reward-card.total i,.reward-card.total span,.reward-card.total strong{color:#fff}
.reward-plus,.reward-equals{font-size:18px;font-weight:800;color:#0F743C}

.info-note{display:flex;align-items:center;gap:8px;padding:10px 14px;background:rgba(15,116,60,0.1);border-radius:8px;border-left:3px solid #0F743C}
.info-note i{color:#0F743C;font-size:14px}
.info-note span{font-size:11px;color:#0F743C;font-weight:600}

/* ✅ Balance Breakdown */
.balance-breakdown{background:linear-gradient(135deg,#fff 0%,#f8faf9 100%)}
.breakdown-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px}
.breakdown-item{display:flex;align-items:center;gap:10px;padding:14px;background:#fff;border-radius:12px;border:1px solid #e5e7eb;position:relative}
.breakdown-item.main{border-color:#0F743C;background:linear-gradient(135deg,#f0fdf4 0%,#dcfce7 100%)}
.breakdown-item.referral{border-color:#F99E2B}
.breakdown-item.ptc{border-color:#3b82f6}
.breakdown-item.hold{border-color:#ef4444;background:linear-gradient(135deg,#fef2f2 0%,#fee2e2 100%)}
.breakdown-icon{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:16px;color:#fff;flex-shrink:0}
.breakdown-item.main .breakdown-icon{background:linear-gradient(135deg,#0F743C 0%,#0a5229 100%)}
.breakdown-item.referral .breakdown-icon{background:linear-gradient(135deg,#F99E2B 0%,#e88a1a 100%)}
.breakdown-item.ptc .breakdown-icon{background:linear-gradient(135deg,#3b82f6 0%,#2563eb 100%)}
.breakdown-item.hold .breakdown-icon{background:linear-gradient(135deg,#ef4444 0%,#dc2626 100%)}
.breakdown-info{flex:1;min-width:0}
.breakdown-label{font-size:10px;color:#666;display:block;margin-bottom:2px}
.breakdown-value{font-size:15px;font-weight:800;color:#1a1a1a;display:block}
.breakdown-badge{position:absolute;top:6px;right:6px;font-size:8px;padding:2px 6px;border-radius:6px;font-weight:700}
.breakdown-badge.withdrawable{background:#dcfce7;color:#0F743C}
.breakdown-badge.approved{background:#fef3c7;color:#d97706}
.breakdown-badge.locked{background:#fee2e2;color:#dc2626}
.hold-details{text-align:center;padding-top:12px;border-top:1px solid #e5e7eb;margin-top:12px}
.hold-details small{font-size:10px;color:#666;display:flex;align-items:center;justify-content:center;gap:6px}
.hold-details i{color:#ef4444}

/* ✅ Reward History */
.reward-history-list{display:flex;flex-direction:column;gap:10px}
.reward-history-item{display:flex;align-items:center;gap:12px;padding:12px;background:#f8f9fa;border-radius:12px;border:1px solid #e5e7eb}
.reward-icon{width:40px;height:40px;background:linear-gradient(135deg,#0F743C 0%,#0a5229 100%);border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:16px;flex-shrink:0}
.reward-details{flex:1;min-width:0}
.reward-title{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:4px}
.reward-title strong{font-size:12px;color:#1a1a1a}
.reward-type-badge{font-size:9px;padding:2px 8px;border-radius:6px;font-weight:700}
.reward-type-badge.signup{background:#dcfce7;color:#0F743C}
.reward-meta{font-size:10px;color:#666;margin-bottom:2px}
.reward-date{font-size:9px;color:#999;display:flex;align-items:center;gap:4px}
.reward-amount-col{text-align:right;flex-shrink:0}
.reward-amount{display:block;font-size:14px;font-weight:800;color:#0F743C}
.reward-status{display:flex;align-items:center;gap:4px;font-size:9px;font-weight:700;justify-content:flex-end;margin-top:2px}
.reward-status.approved{color:#0F743C}
.reward-status.pending{color:#F99E2B}
.reward-status.locked{color:#ef4444}
.view-all-link{text-align:center;padding-top:12px;border-top:1px solid #e5e7eb;margin-top:10px}
.view-all-link a{font-size:12px;color:#0F743C;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:6px}

/* ✅ Enhanced Table Styles */
.user-info-col{display:flex;flex-direction:column}
.user-info-col .user-name{font-size:12px;font-weight:700;color:#1a1a1a}
.user-info-col .username-text{font-size:10px;color:#999}
.ref-type-badge{display:inline-flex;align-items:center;gap:4px;font-size:9px;padding:4px 8px;border-radius:6px;font-weight:700}
.ref-type-badge.account-open{background:#dcfce7;color:#0F743C}
.commission-earned{font-size:12px;font-weight:800;color:#0F743C}
.join-date{font-size:10px;color:#666}
.reward-status-badge{display:inline-flex;align-items:center;gap:4px;font-size:9px;padding:4px 8px;border-radius:6px;font-weight:700}
.reward-status-badge.approved{background:#dcfce7;color:#0F743C}
.reward-status-badge.pending{background:#fef3c7;color:#d97706}
.reward-status-badge.locked{background:#fee2e2;color:#dc2626}
.balance-display{display:flex;align-items:center;gap:8px;background:#f8f9fa;padding:8px 14px;border-radius:12px;border:1px solid #e5e7eb}
.balance-amount{font-size:18px;font-weight:800;color:#0F743C;letter-spacing:-0.5px}
.balance-toggle{background:transparent;border:none;color:#666;font-size:16px;cursor:pointer;padding:0;width:24px;height:24px;display:flex;align-items:center;justify-content:center;transition:all 0.2s}
.balance-toggle:hover{color:#0F743C;transform:scale(1.1)}
.header-actions{display:flex;gap:10px;margin-bottom:14px}
.action-btn{flex:1;background:#f8f9fa;border:2px solid #e5e7eb;border-radius:14px;padding:12px 8px;text-decoration:none;display:flex;flex-direction:column;align-items:center;gap:6px;transition:all 0.2s;min-width:0}
.action-btn:hover{transform:translateY(-2px);box-shadow:0 4px 12px rgba(0,0,0,0.08)}
.btn-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;color:#fff;box-shadow:0 3px 8px rgba(0,0,0,0.15)}
.history-btn .btn-icon{background:linear-gradient(135deg,#F99E2B 0%,#e88a1a 100%)}
.withdraw-btn .btn-icon{background:linear-gradient(135deg,#F99E2B 0%,#e88a1a 100%)}
.deposit-btn .btn-icon{background:linear-gradient(135deg,#0F743C 0%,#0a5229 100%)}
.btn-label{font-size:11px;font-weight:700;color:#333;letter-spacing:0.2px}
.page-title-bar{display:flex;align-items:center;gap:10px;padding-top:10px;border-top:1px solid #f0f0f0}
.title-icon{width:32px;height:32px;background:linear-gradient(135deg,#0F743C 0%,#0a5229 100%);border-radius:8px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:16px;flex-shrink:0}
.page-title{font-size:15px;font-weight:800;color:#1a1a1a;margin:0;flex-shrink:0}
.stats-mini{display:flex;align-items:center;gap:6px;font-size:11px;color:#666;margin-left:auto;flex-shrink:0}
.stat-item strong{color:#0F743C;font-weight:800}
.stat-divider{color:#ddd;font-weight:700}

.section-block{background:#fff;margin:12px 16px;border-radius:16px;padding:16px;box-shadow:0 2px 8px rgba(0,0,0,0.04);border:1px solid #f0f0f0}
.section-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid #f0f0f0}
.section-head h3{font-size:14px;font-weight:700;color:#1a1a1a;display:flex;align-items:center;gap:6px;margin:0}
.section-head h3 i{color:#0F743C;font-size:15px}
.count-badge{background:#0F743C;color:#fff;padding:4px 10px;border-radius:10px;font-size:11px;font-weight:700}

.ref-link-box{display:flex;gap:8px;margin-bottom:14px}
.ref-input{flex:1;padding:12px;border:1.5px solid #e5e7eb;border-radius:10px;font-size:12px;background:#f8f9fa;color:#333;min-width:0;font-weight:500}
.copy-btn{width:48px;background:#0F743C;border:none;border-radius:10px;color:#fff;font-size:16px;cursor:pointer;transition:all 0.2s}
.copy-btn:hover{background:#0a5229;transform:scale(1.05)}
.copy-btn:active{transform:scale(0.95)}

.share-btns{display:flex;justify-content:center;gap:10px;margin-top:12px}
.share-btn{width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:17px;text-decoration:none;transition:all 0.2s;box-shadow:0 2px 6px rgba(0,0,0,0.1)}
.share-btn:hover{transform:translateY(-2px);box-shadow:0 4px 10px rgba(0,0,0,0.15)}
.share-btn.facebook{background:#1877f2}
.share-btn.whatsapp{background:#25d366}
.share-btn.telegram{background:#0088cc}

/* Commission Tabs */
.commission-tabs{display:flex;gap:8px;margin-bottom:16px}
.commission-tab{flex:1;padding:12px 16px;border:2px solid #e5e7eb;border-radius:12px;background:#fff;font-size:12px;font-weight:700;color:#666;cursor:pointer;transition:all 0.2s;display:flex;align-items:center;justify-content:center;gap:6px}
.commission-tab:hover{border-color:#0F743C;color:#0F743C}
.commission-tab.active{background:linear-gradient(135deg,#0F743C 0%,#0a5229 100%);color:#fff;border-color:#0F743C}
.commission-tab i{font-size:14px}
.commission-tab-content{display:none}
.commission-tab-content.active{display:block}

/* Current Plan Badge */
.current-plan-badge{display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:8px;font-size:11px;font-weight:700;margin-bottom:12px}
.current-plan-badge.free-badge{background:#e8f5e9;color:#2e7d32;border:1px solid #4caf50}
.current-plan-badge.premium-badge{background:linear-gradient(135deg,#fff8e1 0%,#ffecb3 100%);color:#f57c00;border:1px solid #ff9800}
.current-plan-badge i{font-size:12px}

/* Upgrade Prompt */
.upgrade-prompt{display:flex;align-items:center;gap:10px;padding:12px 16px;background:linear-gradient(135deg,#fff8e1 0%,#ffecb3 100%);border:1px solid #ffb300;border-radius:10px;margin-bottom:12px;flex-wrap:wrap}
.upgrade-prompt i{color:#f57c00;font-size:16px}
.upgrade-prompt span{flex:1;font-size:11px;color:#e65100;font-weight:600}
.upgrade-btn{background:linear-gradient(135deg,#F99E2B 0%,#e88a1a 100%);color:#fff;padding:8px 16px;border-radius:8px;font-size:10px;font-weight:700;text-decoration:none;white-space:nowrap}

/* Free User Limits */
.free-user-limits{display:flex;flex-wrap:wrap;gap:8px;margin-top:12px;padding-top:12px;border-top:1px dashed #e5e7eb}
.limit-item{flex:1;min-width:100px;display:flex;align-items:center;gap:6px;padding:8px 10px;background:#f8f9fa;border-radius:8px;font-size:10px;color:#666}
.limit-item i{color:#0F743C;font-size:12px}
.limit-item strong{color:#0F743C}

/* No Commission Notice */
.no-commission-notice{display:flex;align-items:center;gap:10px;padding:16px;background:linear-gradient(135deg,#fff8e1 0%,#ffecb3 100%);border:1px solid #ffb300;border-radius:12px;margin-bottom:12px}
.no-commission-notice i{color:#f57c00;font-size:18px;flex-shrink:0}
.no-commission-notice span{font-size:12px;color:#e65100;font-weight:600}

/* Free User Commission Badge Colors (Flat Amounts) */
.amount-badge{display:inline-block;padding:6px 14px;border-radius:8px;font-weight:800;font-size:13px;color:#fff;min-width:55px;box-shadow:0 2px 6px rgba(0,0,0,0.1)}
.amount-badge.deposit-free{background:linear-gradient(135deg,#78909c 0%,#546e7a 100%)}
.amount-badge.task-free{background:linear-gradient(135deg,#ffb74d 0%,#ffa726 100%)}
.amount-badge.plan-free{background:linear-gradient(135deg,#a1887f 0%,#8d6e63 100%)}

/* Free Table Styling */
.grid-table.free-table thead{background:linear-gradient(135deg,#78909c 0%,#546e7a 100%)}
.level-badge.free{background:rgba(255,255,255,0.2)}
.level-cell .level-badge.free .level-num{color:#fff}

/* Premium Table Styling */
.grid-table.premium-table thead{background:linear-gradient(135deg,#F99E2B 0%,#e88a1a 100%)}
.level-cell.premium-level{background:linear-gradient(135deg,#F99E2B 0%,#e88a1a 100%)!important}
.level-badge.premium{background:rgba(255,255,255,0.2)}

/* Commission Grid Table */
.commission-grid-table{overflow-x:auto;margin:0 -4px}
.grid-table{width:100%;border-collapse:separate;border-spacing:0;font-size:11px;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden}
.grid-table thead{background:linear-gradient(135deg,#0F743C 0%,#0a5229 100%)}
.grid-table thead th{color:#fff;font-weight:700;padding:12px 8px;text-align:center;font-size:10px;white-space:nowrap;border-right:1px solid rgba(255,255,255,0.15);text-transform:uppercase;letter-spacing:0.3px}
.grid-table thead th:last-child{border-right:none}
.grid-table thead th i{font-size:12px;display:block;margin-bottom:3px}

.grid-table tbody tr{transition:all 0.15s;background:#fff}
.grid-table tbody tr:hover{background:#f8faf9}
.grid-table tbody tr:nth-child(even){background:#fafafa}
.grid-table tbody tr:nth-child(even):hover{background:#f8faf9}

.grid-table tbody td{padding:12px 8px;text-align:center;border-bottom:1px solid #e5e7eb;border-right:1px solid #e5e7eb}
.grid-table tbody td:last-child{border-right:none}
.grid-table tbody tr:last-child td{border-bottom:none}

.level-cell{background:linear-gradient(135deg,#0F743C 0%,#0a5229 100%)!important}
.level-badge{display:flex;flex-direction:column;align-items:center;gap:2px}
.level-num{font-size:16px;font-weight:800;color:#fff}
.level-label{font-size:10px;font-weight:700;color:rgba(255,255,255,0.9);background:rgba(255,255,255,0.2);padding:2px 8px;border-radius:6px}

.commission-cell{position:relative}
.commission-cell.deposit{background:rgba(15,116,60,0.03)}
.commission-cell.task{background:rgba(249,158,43,0.03)}
.commission-cell.plan{background:rgba(199,102,43,0.03)}

.percent-badge{display:inline-block;padding:6px 14px;border-radius:8px;font-weight:800;font-size:13px;color:#fff;min-width:55px;box-shadow:0 2px 6px rgba(0,0,0,0.1)}
.percent-badge.deposit{background:linear-gradient(135deg,#0F743C 0%,#0a5229 100%)}
.percent-badge.task{background:linear-gradient(135deg,#F99E2B 0%,#e88a1a 100%)}
.percent-badge.plan{background:linear-gradient(135deg,#C7662B 0%,#b35620 100%)}

.details-cell{background:#fafafa;text-align:left;padding-left:12px!important}
.detail-text{font-size:10px;color:#666;font-weight:600}

/* Commission Legend */
.commission-legend{display:flex;justify-content:center;gap:12px;margin-top:14px;flex-wrap:wrap}
.legend-item{display:flex;align-items:center;gap:6px;padding:6px 10px;background:#f8f9fa;border-radius:8px;border:1px solid #e5e7eb}
.legend-icon{width:26px;height:26px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:11px;color:#fff}
.legend-icon.deposit{background:linear-gradient(135deg,#0F743C 0%,#0a5229 100%)}
.legend-icon.task{background:linear-gradient(135deg,#F99E2B 0%,#e88a1a 100%)}
.legend-icon.plan{background:linear-gradient(135deg,#C7662B 0%,#b35620 100%)}
.legend-text{font-size:10px;color:#666;font-weight:700}

/* Referred Users Table */
.ref-users-table-wrap{overflow-x:auto;margin:0 -4px}
.ref-users-table{width:100%;border-collapse:separate;border-spacing:0;font-size:11px;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden}
.ref-users-table thead{background:linear-gradient(135deg,#0F743C 0%,#0a5229 100%)}
.ref-users-table thead th{color:#fff;font-weight:700;padding:11px 10px;text-align:left;font-size:10px;white-space:nowrap;border-right:1px solid rgba(255,255,255,0.15);text-transform:uppercase;letter-spacing:0.3px}
.ref-users-table thead th:last-child{border-right:none;text-align:center}
.ref-users-table thead th:nth-child(3),
.ref-users-table thead th:nth-child(4){text-align:center}

.ref-users-table tbody tr{transition:all 0.15s;background:#fff}
.ref-users-table tbody tr:hover{background:#f8faf9}
.ref-users-table tbody tr:nth-child(even){background:#fafafa}
.ref-users-table tbody tr:nth-child(even):hover{background:#f8faf9}

.ref-users-table tbody td{padding:11px 10px;border-bottom:1px solid #e5e7eb;border-right:1px solid #e5e7eb}
.ref-users-table tbody td:last-child{border-right:none;text-align:center}
.ref-users-table tbody td:nth-child(3),
.ref-users-table tbody td:nth-child(4){text-align:center}
.ref-users-table tbody tr:last-child td{border-bottom:none}

.user-cell{display:flex;align-items:center;gap:10px}
.user-avatar{width:34px;height:34px;background:linear-gradient(135deg,#0F743C 0%,#0a5229 100%);border-radius:8px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:13px;font-weight:800;flex-shrink:0}
.user-name{font-size:12px;font-weight:700;color:#1a1a1a}

.username-text{font-size:11px;color:#666;font-weight:600}

.plan-badge{display:inline-block;padding:5px 11px;border-radius:8px;font-size:10px;font-weight:700;letter-spacing:0.2px}
.plan-badge.free{background:#e5e7eb;color:#6b7280}
.plan-badge.active{background:linear-gradient(135deg,#C7662B 0%,#b35620 100%);color:#fff}

.status-badge{display:inline-block;padding:5px 11px;border-radius:8px;font-size:10px;font-weight:700;letter-spacing:0.2px}
.status-badge.active{background:linear-gradient(135deg,#0F743C 0%,#0a5229 100%);color:#fff}
.status-badge.inactive{background:#e5e7eb;color:#6b7280}

.empty-state-sm{text-align:center;padding:30px;color:#999}
.empty-state-sm i{font-size:32px;margin-bottom:10px;color:#e5e7eb}
.empty-state-sm p{font-size:12px;margin:0;color:#666}

.pagination-wrap{padding:0 16px;display:flex;justify-content:center;margin-top:16px}

/* Responsive adjustments */
@media (max-width: 768px) {
    .modern-header{padding:12px}
    .greeting-text{font-size:14px}
    .crown-badge{font-size:9px;padding:3px 8px}
    .balance-amount{font-size:16px}
    .balance-toggle{font-size:14px}
    .action-btn{padding:10px 6px}
    .btn-icon{width:44px;height:44px;font-size:18px}
    .btn-label{font-size:10px}
    .page-title{font-size:14px}
    .title-icon{width:28px;height:28px;font-size:14px}
    .stats-mini{font-size:10px}
    .grid-table thead th,
    .grid-table tbody td{padding:10px 6px}
    .percent-badge{padding:5px 12px;font-size:12px;min-width:50px}
    .level-num{font-size:15px}
    .commission-legend{gap:8px}
    .legend-item{padding:5px 8px}
    .user-avatar{width:32px;height:32px;font-size:12px}
}
</style>
@endpush

@push('script')
<script>
// Balance toggle functionality
function toggleBalance(btn) {
    const balanceAmount = document.querySelector('.balance-amount');
    const icon = btn.querySelector('i');

    if (balanceAmount.style.filter === 'blur(6px)') {
        balanceAmount.style.filter = 'none';
        icon.className = 'fas fa-eye';
    } else {
        balanceAmount.style.filter = 'blur(6px)';
        icon.className = 'fas fa-eye-slash';
    }
}

// Copy referral link
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

// ✅ Show toast notification for recent referral commission
@if($signupCommissions->count() > 0)
    @php $latestCommission = $signupCommissions->first(); @endphp
    @if($latestCommission && $latestCommission->created_at->diffInMinutes(now()) < 60)
    document.addEventListener('DOMContentLoaded', function() {
        if(typeof iziToast !== 'undefined') {
            setTimeout(function() {
                iziToast.success({
                    title: '🎉 রেফারেল কমিশন!',
                    message: 'আপনি ৳{{ showAmount($latestCommission->amount) }} কমিশন পেয়েছেন!',
                    position: 'topCenter',
                    timeout: 5000,
                    progressBar: true,
                    icon: 'fas fa-gift'
                });
            }, 1000);
        }
    });
    @endif
@endif

// ✅ Show welcome bonus notification for new referred users
@if($userGotSignupBonus && $referrer)
    @php
        $signupBonus = \App\Models\Transaction::where('user_id', $user->id)
            ->where('remark', 'referral_signup_bonus')
            ->first();
    @endphp
    @if($signupBonus && $signupBonus->created_at->diffInHours(now()) < 24)
    document.addEventListener('DOMContentLoaded', function() {
        if(typeof iziToast !== 'undefined') {
            setTimeout(function() {
                iziToast.info({
                    title: '🎁 স্বাগতম বোনাস!',
                    message: 'রেফারেলে জয়েন করার জন্য ৳{{ showAmount($signupReferredAmount) }} বোনাস পেয়েছেন!',
                    position: 'topCenter',
                    timeout: 6000,
                    progressBar: true,
                    icon: 'fas fa-user-plus'
                });
            }, 2000);
        }
    });
    @endif
@endif

// ✅ Commission Tabs Switching
document.querySelectorAll('.commission-tab').forEach(function(tab) {
    tab.addEventListener('click', function() {
        // Remove active from all tabs
        document.querySelectorAll('.commission-tab').forEach(function(t) {
            t.classList.remove('active');
        });
        // Hide all content
        document.querySelectorAll('.commission-tab-content').forEach(function(c) {
            c.classList.remove('active');
        });
        // Add active to clicked tab
        this.classList.add('active');
        // Show corresponding content
        var tabType = this.getAttribute('data-tab');
        document.getElementById(tabType + '-commission').classList.add('active');
    });
});

// Set default tab based on user's premium status
@if($isPremium)
document.addEventListener('DOMContentLoaded', function() {
    var premiumTab = document.querySelector('.commission-tab[data-tab="premium"]');
    if(premiumTab) {
        premiumTab.click();
    }
});
@endif
</script>
@endpush
