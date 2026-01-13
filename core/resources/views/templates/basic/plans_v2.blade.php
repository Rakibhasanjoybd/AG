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

    // Preload the likely LCP image (first plan with an icon image) to reduce mobile LCP.
    $lcpPlan = $plans->first(function($p){
        return !empty($p->image);
    });
@endphp

@if($lcpPlan && $lcpPlan->image)
    @push('head')
        <link rel="preload" as="image" href="{{ asset('assets/images/plan/'.$lcpPlan->image) }}" fetchpriority="high">
    @endpush
@endif

<div class="agco-plans-wrapper">
    <!-- Compact Header -->
    <div class="agco-plans-header">
        <a href="{{ route('home') }}" class="aph-back-btn" aria-label="Back">
            <i class="las la-arrow-left"></i>
        </a>
        <div class="aph-title-section">
            <h1 class="aph-title">গার্ড প্যাকেজসমূহ</h1>
        </div>
        @auth
        <div class="aph-balance-box">
            <i class="las la-wallet"></i> ৳{{ showAmount($userBalance) }}
        </div>
        @endauth
    </div>

    <!-- Plans List Container -->
    <div class="agco-plans-content">
        @foreach($plans as $plan)
        @php
            $isActive = auth()->check() && $isCurrent == $plan->id;
            $affordable = auth()->check() && $userBalance >= $plan->price;
            $planData = [
                "id" => $plan->id,
                "name" => $plan->name,
                "image" => $plan->image ? asset("assets/images/plan/".$plan->image) : null,
                "price" => $plan->price,
                "price_formatted" => showAmount($plan->price),
                "validity" => $plan->validity,
                "daily_limit" => $plan->daily_limit,
                "ref_level" => $plan->ref_level,
                "commission_a_rate" => $plan->commission_level_a_rate ?? 12,
                "commission_a_max" => $plan->commission_level_a_max ? showAmount($plan->commission_level_a_max) : null,
                "commission_b_rate" => $plan->commission_level_b_rate ?? 4,
                "commission_b_max" => $plan->commission_level_b_max ? showAmount($plan->commission_level_b_max) : null,
                "commission_c_rate" => $plan->commission_level_c_rate ?? 1,
                "commission_c_max" => $plan->commission_level_c_max ? showAmount($plan->commission_level_c_max) : null,
                "task_commission_a" => $plan->task_commission_a_rate ?? 5,
                "task_commission_b" => $plan->task_commission_b_rate ?? 2,
                "task_commission_c" => $plan->task_commission_c_rate ?? 1,
                "is_active" => $isActive,
                "affordable" => $affordable
            ];
        @endphp

        <div class="agco-plan-card {{ $isActive ? 'active-plan' : '' }}" data-plan="{{ json_encode($planData) }}">
            <div class="apc-left">
                <div class="apc-icon-wrapper">
                    @if($plan->image)
                    <div class="apc-icon-circle">
                        @php
                            $planImageRelPath = 'assets/images/plan/'.$plan->image;
                            $planImageAbsPath = public_path($planImageRelPath);
                            $planImageWebpRelPath = preg_replace('/\.(jpe?g|png)$/i', '.webp', $planImageRelPath);
                            $planImageWebpAbsPath = public_path($planImageWebpRelPath);
                            $hasWebp = $planImageWebpRelPath !== $planImageRelPath && file_exists($planImageWebpAbsPath);
                        @endphp

                        <picture>
                            @if($hasWebp)
                                <source type="image/webp" srcset="{{ asset($planImageWebpRelPath) }}">
                            @endif
                            <img
                                src="{{ asset($planImageRelPath) }}"
                                alt="{{ $plan->name }}"
                                class="apc-icon-img"
                                width="52"
                                height="52"
                                decoding="async"
                                @if($loop->first)
                                    loading="eager" fetchpriority="high"
                                @else
                                    loading="lazy" fetchpriority="low"
                                @endif
                            >
                        </picture>
                    </div>
                    @else
                    <div class="apc-icon-circle apc-icon-gradient-{{ $loop->index % 4 + 1 }}">
                        <i class="las la-gem"></i>
                    </div>
                    @endif
                </div>
                
                <div class="apc-info">
                    <div class="apc-name">{{ $plan->name }}</div>
                    <div class="apc-price">৳{{ showAmount($plan->price) }}</div>
                    <div class="apc-validity">
                        <i class="las la-calendar"></i> {{ $plan->validity }} দিন
                    </div>
                </div>
            </div>

            <div class="apc-right">
                @if($isActive)
                    <div class="apc-status-badge active">
                        <i class="las la-check-circle"></i> সর্বিক
                    </div>
                @else
                    <div class="apc-status-badge hot">
                        পূরণ আপনা
                    </div>
                @endif
                
                <button class="apc-action-btn {{ $isActive ? 'active' : '' }}">
                    @if($isActive)
                        সর্বিক
                    @else
                        বিস্তারিত <i class="las la-arrow-right"></i>
                    @endif
                </button>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Trust Footer -->
    <div class="agco-trust-footer">
        <div class="atf-item">
            <i class="las la-shield-alt"></i>
            <span>নিরাপদ লেনদেন</span>
        </div>
        <div class="atf-item">
            <i class="las la-clock"></i>
            <span>তাৎক্ষণিক সক্রিয়করণ</span>
        </div>
        <div class="atf-item">
            <i class="las la-headset"></i>
            <span>২৪/৭সহায়তা</span>
        </div>
    </div>
</div>

<!-- Plan Details Modal -->
<div class="modal fade" id="planDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content agco-plan-modal">
            <!-- Close Button -->
            <button type="button" class="apm-close-btn" data-bs-dismiss="modal">
                <i class="las la-times"></i>
            </button>

            <!-- Modal Header -->
            <div class="apm-header">
                <div class="apm-icon-wrapper">
                    <div class="apm-icon-circle" id="modalIconCircle">
                        <img src="" alt="" id="modalPlanImage" class="apm-icon-img" style="display:none;">
                        <i class="las la-gem" id="modalPlanIcon"></i>
                    </div>
                </div>
                <div class="apm-title" id="modalPlanName"></div>
                <div class="apm-price-box">
                    <span class="apm-price-label">মাত্র</span>
                    <div class="apm-price">৳ <span id="modalPlanPrice"></span></div>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="apm-body">
                <!-- Benefits Table -->
                <div class="apm-section">
                    <div class="apm-section-header">
                        <i class="las la-gift"></i>
                        <div class="apm-section-title"><span id="modalPlanNameRepeat"></span> প্যাকেজের সুবিধা</div>
                    </div>
                    <div class="apm-table">
                        <div class="apm-table-row header">
                            <div class="cell"><i class="las la-clock"></i> সময়ের একক</div>
                            <div class="cell"><i class="las la-tasks"></i> কাজের সংখ্যা</div>
                            <div class="cell"><i class="las la-coins"></i> মোট কমিশন</div>
                        </div>
                        <div class="apm-table-row">
                            <div class="cell">দৈনিক</div>
                            <div class="cell" id="modalDailyLimit"></div>
                            <div class="cell" id="modalDailyCommission"></div>
                        </div>
                        <div class="apm-table-row">
                            <div class="cell">প্রতি মাসে</div>
                            <div class="cell" id="modalMonthlyLimit"></div>
                            <div class="cell" id="modalMonthlyCommission"></div>
                        </div>
                        <div class="apm-table-row highlight">
                            <div class="cell"><strong>প্রতি বছর</strong></div>
                            <div class="cell" id="modalYearlyLimit"></div>
                            <div class="cell" id="modalYearlyCommission"></div>
                        </div>
                    </div>
                </div>

                <!-- Commission Structure -->
                <div class="apm-section">
                    <div class="apm-section-header">
                        <i class="las la-user-friends"></i>
                        <div class="apm-section-title">আমন্ত্রণ কমিশন আয়ের অনুপাত</div>
                    </div>
                    <div class="apm-commission-list">
                        <div class="apm-commission-item level-a">
                            <div class="aci-icon">A</div>
                            <div class="aci-content">
                                <div class="aci-desc">A কে সদস্য হওয়ার জন্য আমন্ত্রণ জানান।</div>
                                <div class="aci-values">
                                    <span class="rate" id="commissionRateA"></span>
                                    <span class="amount" id="commissionAmountA"></span>
                                </div>
                            </div>
                        </div>
                        <div class="apm-commission-item level-b">
                            <div class="aci-icon">B</div>
                            <div class="aci-content">
                                <div class="aci-desc">A অথবা B কে সদস্য হওয়ার জন্য আমন্ত্রণ জানান।</div>
                                <div class="aci-values">
                                    <span class="rate" id="commissionRateB"></span>
                                    <span class="amount" id="commissionAmountB"></span>
                                </div>
                            </div>
                        </div>
                        <div class="apm-commission-item level-c">
                            <div class="aci-icon">C</div>
                            <div class="aci-content">
                                <div class="aci-desc">B তার অথবা C কে সদস্য হওয়ার জন্য আমন্ত্রণ জানান।</div>
                                <div class="aci-values">
                                    <span class="rate" id="commissionRateC"></span>
                                    <span class="amount" id="commissionAmountC"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Task Commission -->
                <div class="apm-section">
                    <div class="apm-section-header">
                        <i class="las la-chart-line"></i>
                        <div class="apm-section-title">টাস্ক কমিশন আয় অনুপাত</div>
                    </div>
                    <div class="apm-task-list">
                        <div class="apm-task-item">
                            <div class="ati-icon"><i class="las la-user-check"></i></div>
                            <div class="ati-desc">অথবা A কাজটি সম্পন্ন করে</div>
                            <div class="ati-rate" id="taskCommissionA"></div>
                        </div>
                        <div class="apm-task-item">
                            <div class="ati-icon"><i class="las la-user-check"></i></div>
                            <div class="ati-desc">অথবা B কাজটি সম্পন্ন করে</div>
                            <div class="ati-rate" id="taskCommissionB"></div>
                        </div>
                        <div class="apm-task-item">
                            <div class="ati-icon"><i class="las la-user-check"></i></div>
                            <div class="ati-desc">অথবা C কাজটি সম্পন্ন করে</div>
                            <div class="ati-rate" id="taskCommissionC"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="apm-footer">
                @auth
                <form method="post" action="{{ route('user.buyPlan') }}" id="planPurchaseForm" style="display:none;">
                    @csrf
                    <input type="hidden" name="id" id="selectedPlanId">
                </form>

                <button type="button" class="apm-purchase-btn" id="btnPurchasePlan">
                    <span class="btn-icon"><i class="las la-shopping-cart"></i></span>
                    <span class="btn-text">ভর্তি করতে হবে</span>
                </button>

                <a href="{{ route('user.deposit') }}" class="apm-purchase-btn deposit-style" id="btnNeedDeposit" style="display:none;">
                    <span class="btn-icon"><i class="las la-wallet"></i></span>
                    <span class="btn-text">জমা করতে হবে</span>
                </a>

                <button type="button" class="apm-purchase-btn active-style" disabled style="display:none;" id="btnAlreadyActive">
                    <i class="las la-check-circle"></i> সক্রিয় প্যাকেজ
                </button>
                @else
                <a href="{{ route('user.login') }}" class="apm-purchase-btn">
                    <span class="btn-icon"><i class="las la-sign-in-alt"></i></span>
                    <span class="btn-text">লগইন করুন</span>
                </a>
                @endauth
            </div>
        </div>
    </div>
</div>

<!-- Purchase Confirmation Modal -->
<div class="modal fade" id="purchaseConfirmModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content agco-confirm-modal">
            <div class="acm-header">
                <div class="acm-icon">
                    <i class="las la-check-circle"></i>
                </div>
                <h3 class="acm-title">পেমেন্ট নিশ্চিত করুন</h3>
            </div>

            <div class="acm-body">
                <div class="acm-plan-info">
                    <div class="acm-label">আপনার কেনা প্যাকেজ</div>
                    <div class="acm-plan-name" id="confirmPlanName"></div>
                </div>

                <div class="acm-details">
                    <div class="acm-row">
                        <span>মেয়াদ</span>
                        <span id="confirmValidity"></span>
                    </div>
                    <div class="acm-row">
                        <span>মূল্য</span>
                        <span id="confirmPrice"></span>
                    </div>
                    <div class="acm-row total">
                        <span>সর্বমোট</span>
                        <span id="confirmTotal"></span>
                    </div>
                </div>
            </div>

            <div class="acm-footer">
                <button type="button" class="acm-btn cancel" data-bs-dismiss="modal">বাতিল</button>
                <button type="button" class="acm-btn confirm" id="btnFinalConfirm">
                    <i class="las la-check"></i> নিশ্চিত করুন
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('style')
<style>
:root {
    --agco-primary: #0F743C;
    --agco-primary-light: #12A152;
    --agco-orange: #FF9500;
    --agco-red: #DC3545;
    --agco-bg: #F5F7FA;
    --agco-white: #FFFFFF;
    --agco-text-dark: #1A1A1A;
    --agco-text-gray: #6B7280;
    --agco-border: #E5E7EB;
    --agco-success: #10B981;
}

* {
    box-sizing: border-box;
    -webkit-tap-highlight-color: transparent;
}

/* Main Container */
.agco-plans-wrapper {
    min-height: 100vh;
    background: var(--agco-bg);
}

/* Compact Header */
.agco-plans-header {
    background: var(--agco-primary);
    padding: 12px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 100;
}

.aph-back-btn {
    width: 36px;
    height: 36px;
    background: rgba(255,255,255,0.15);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--agco-white);
    text-decoration: none;
    font-size: 20px;
    transition: background 0.2s;
}

.aph-back-btn:hover {
    background: rgba(255,255,255,0.25);
}

.aph-title-section {
    flex: 1;
    text-align: center;
}

.aph-title {
    font-size: 18px;
    font-weight: 600;
    color: var(--agco-white);
    margin: 0;
}

.aph-balance-box {
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(8px);
    padding: 8px 12px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    color: var(--agco-white);
    display: flex;
    align-items: center;
    gap: 4px;
}

.aph-balance-box i {
    font-size: 16px;
}

/* Plans Content */
.agco-plans-content {
    padding: 12px;
}

/* Plan Card - Flat Design */
.agco-plan-card {
    background: var(--agco-white);
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border: 2px solid transparent;
    cursor: pointer;
    transition: all 0.2s;
}

.agco-plan-card:hover {
    border-color: var(--agco-primary);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(15,116,60,0.12);
}

.agco-plan-card:active {
    transform: translateY(0);
}

.agco-plan-card.active-plan {
    border-color: var(--agco-success);
    background: #F0FDF4;
}

/* Left Section */
.apc-left {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
}

.apc-icon-wrapper {
    flex-shrink: 0;
}

.apc-icon-circle {
    width: 54px;
    height: 54px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background: #F3F4F6;
}

.apc-icon-circle i {
    font-size: 26px;
    color: var(--agco-white);
}

.apc-icon-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.apc-icon-gradient-1 {
    background: linear-gradient(135deg, #FF6B9D 0%, #C239B3 100%);
}

.apc-icon-gradient-2 {
    background: linear-gradient(135deg, #4776E6 0%, #8E54E9 100%);
}

.apc-icon-gradient-3 {
    background: linear-gradient(135deg, #FF9500 0%, #FF6B00 100%);
}

.apc-icon-gradient-4 {
    background: linear-gradient(135deg, #10B981 0%, #059669 100%);
}

.apc-info {
    flex: 1;
}

.apc-name {
    font-size: 16px;
    font-weight: 600;
    color: var(--agco-text-dark);
    margin-bottom: 4px;
}

.apc-price {
    font-size: 18px;
    font-weight: 700;
    color: var(--agco-text-dark);
    margin-bottom: 4px;
}

.apc-validity {
    font-size: 12px;
    color: var(--agco-text-gray);
    display: flex;
    align-items: center;
    gap: 4px;
}

.apc-validity i {
    font-size: 13px;
}

/* Right Section */
.apc-right {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 8px;
}

.apc-status-badge {
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 4px;
}

.apc-status-badge.hot {
    background: #FFF7ED;
    color: #EA580C;
}

.apc-status-badge.active {
    background: #DCFCE7;
    color: #16A34A;
}

.apc-action-btn {
    background: var(--agco-primary);
    color: var(--agco-white);
    border: none;
    border-radius: 8px;
    padding: 8px 16px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 4px;
}

.apc-action-btn:hover {
    background: var(--agco-primary-light);
    transform: translateX(2px);
}

.apc-action-btn.active {
    background: var(--agco-success);
    pointer-events: none;
}

.apc-action-btn i {
    font-size: 14px;
}

/* Trust Footer */
.agco-trust-footer {
    display: flex;
    justify-content: space-around;
    padding: 16px 12px;
    background: var(--agco-white);
    margin: 0 12px 12px;
    border-radius: 12px;
}

.atf-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    color: var(--agco-text-gray);
    text-align: center;
}

.atf-item i {
    font-size: 24px;
    color: var(--agco-primary);
}

/* Plan Modal */
.agco-plan-modal {
    border-radius: 16px 16px 0 0;
    border: none;
}

.apm-close-btn {
    position: absolute;
    top: 12px;
    right: 12px;
    width: 32px;
    height: 32px;
    background: rgba(0,0,0,0.2);
    backdrop-filter: blur(8px);
    border: none;
    border-radius: 50%;
    color: var(--agco-white);
    cursor: pointer;
    z-index: 10;
    font-size: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.apm-close-btn:hover {
    background: rgba(0,0,0,0.3);
    transform: rotate(90deg);
}

/* Modal Header - Compact */
.apm-header {
    background: var(--agco-primary);
    padding: 15px 20px;
    text-align: center;
    position: relative;
}

.apm-icon-wrapper {
    margin-bottom: 6px;
}

.apm-icon-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(8px);
    border: 2px solid rgba(255,255,255,0.3);
    position: relative;
}

.apm-icon-circle i {
    font-size: 24px;
    color: var(--agco-white);
    z-index: 2;
}

.apm-icon-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
    z-index: 2;
}

.apm-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--agco-white);
    margin-bottom: 6px;
}

.apm-price-box {
    position: relative;
}

.apm-price-label {
    font-size: 11px;
    color: rgba(255,255,255,0.85);
    display: block;
    margin-bottom: 2px;
}

.apm-price {
    font-size: 20px;
    font-weight: 700;
    color: var(--agco-white);
}

/* Modal Body */
.apm-body {
    padding: 20px 16px;
    max-height: 50vh;
    overflow-y: auto;
    background: var(--agco-bg);
}

.apm-section {
    background: var(--agco-white);
    border-radius: 10px;
    padding: 16px;
    margin-bottom: 12px;
}

.apm-section-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 12px;
}

.apm-section-header i {
    font-size: 20px;
    color: var(--agco-primary);
}

.apm-section-title {
    font-size: 14px;
    font-weight: 700;
    color: var(--agco-text-dark);
}

/* Table */
.apm-table {
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid var(--agco-border);
}

.apm-table-row {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
}

.apm-table-row.header {
    background: #F9FAFB;
}

.apm-table-row.header .cell {
    font-weight: 700;
    font-size: 11px;
    color: var(--agco-text-dark);
}

.apm-table-row.highlight {
    background: #F0FDF4;
}

.apm-table-row.highlight .cell {
    color: var(--agco-primary);
    font-weight: 600;
}

.apm-table-row .cell {
    padding: 10px 6px;
    font-size: 12px;
    border-bottom: 1px solid var(--agco-border);
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    gap: 3px;
}

.apm-table-row:last-child .cell {
    border-bottom: none;
}

.apm-table-row .cell i {
    font-size: 12px;
}

/* Commission List */
.apm-commission-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.apm-commission-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: var(--agco-bg);
    border-radius: 8px;
    border-left: 3px solid;
}

.apm-commission-item.level-a {
    border-left-color: #10B981;
}

.apm-commission-item.level-b {
    border-left-color: #3B82F6;
}

.apm-commission-item.level-c {
    border-left-color: #F59E0B;
}

.aci-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 14px;
    color: var(--agco-white);
    flex-shrink: 0;
}

.level-a .aci-icon {
    background: #10B981;
}

.level-b .aci-icon {
    background: #3B82F6;
}

.level-c .aci-icon {
    background: #F59E0B;
}

.aci-content {
    flex: 1;
}

.aci-desc {
    font-size: 12px;
    color: var(--agco-text-gray);
    margin-bottom: 6px;
}

.aci-values {
    display: flex;
    align-items: center;
    gap: 8px;
}

.aci-values .rate {
    background: var(--agco-primary);
    color: var(--agco-white);
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.aci-values .amount {
    font-size: 13px;
    font-weight: 700;
    color: var(--agco-primary);
}

/* Task List */
.apm-task-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.apm-task-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: var(--agco-bg);
    border-radius: 8px;
}

.ati-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--agco-primary);
    color: var(--agco-white);
    font-size: 16px;
    flex-shrink: 0;
}

.ati-desc {
    flex: 1;
    font-size: 12px;
    color: var(--agco-text-gray);
}

.ati-rate {
    background: var(--agco-orange);
    color: var(--agco-white);
    padding: 5px 12px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 700;
}

/* Modal Footer */
.apm-footer {
    padding: 16px;
    background: var(--agco-white);
    border-top: 1px solid var(--agco-border);
}

.apm-purchase-btn {
    width: 100%;
    background: var(--agco-primary);
    color: var(--agco-white);
    border: none;
    border-radius: 10px;
    padding: 14px;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    text-decoration: none;
}

.apm-purchase-btn .btn-icon {
    font-size: 18px;
}

.apm-purchase-btn:hover {
    background: var(--agco-primary-light);
    transform: translateY(-2px);
}

.apm-purchase-btn:active {
    transform: translateY(0);
}

.apm-purchase-btn.deposit-style {
    background: var(--agco-orange);
}

.apm-purchase-btn.active-style {
    background: #9CA3AF;
    cursor: not-allowed;
    opacity: 0.7;
}

/* Confirmation Modal */
.agco-confirm-modal {
    border-radius: 16px;
    border: none;
}

.acm-header {
    background: var(--agco-primary);
    padding: 28px 20px;
    text-align: center;
}

.acm-icon {
    width: 64px;
    height: 64px;
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(8px);
    border-radius: 50%;
    margin: 0 auto 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 3px solid rgba(255,255,255,0.3);
}

.acm-icon i {
    font-size: 32px;
    color: var(--agco-white);
}

.acm-title {
    font-size: 18px;
    font-weight: 700;
    color: var(--agco-white);
    margin: 0;
}

.acm-body {
    padding: 20px;
}

.acm-plan-info {
    text-align: center;
    margin-bottom: 16px;
}

.acm-label {
    font-size: 12px;
    color: var(--agco-text-gray);
    margin-bottom: 6px;
}

.acm-plan-name {
    font-size: 18px;
    font-weight: 700;
    color: var(--agco-text-dark);
}

.acm-details {
    background: var(--agco-bg);
    border-radius: 10px;
    padding: 14px;
}

.acm-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    font-size: 13px;
    border-bottom: 1px solid var(--agco-border);
}

.acm-row:last-child {
    border-bottom: none;
}

.acm-row.total {
    font-weight: 700;
    font-size: 15px;
    color: var(--agco-primary);
    padding-top: 12px;
    margin-top: 6px;
    border-top: 2px solid var(--agco-primary);
}

.acm-footer {
    padding: 16px;
    display: flex;
    gap: 10px;
}

.acm-btn {
    flex: 1;
    padding: 12px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
}

.acm-btn.cancel {
    background: #E5E7EB;
    color: var(--agco-text-dark);
}

.acm-btn.cancel:hover {
    background: #D1D5DB;
}

.acm-btn.confirm {
    background: var(--agco-primary);
    color: var(--agco-white);
}

.acm-btn.confirm:hover {
    background: var(--agco-primary-light);
    transform: translateY(-2px);
}

/* Scrollbar */
.apm-body::-webkit-scrollbar {
    width: 5px;
}

.apm-body::-webkit-scrollbar-track {
    background: var(--agco-bg);
}

.apm-body::-webkit-scrollbar-thumb {
    background: var(--agco-primary);
    border-radius: 10px;
}

/* Responsive */
@media (max-width: 380px) {
    .aph-title { font-size: 16px; }
    .apc-icon-circle { width: 50px; height: 50px; }
    .apc-icon-circle i { font-size: 24px; }
    .apc-name { font-size: 15px; }
    .apc-price { font-size: 17px; }
    .apc-action-btn { padding: 7px 14px; font-size: 12px; }
}
</style>
@endpush

@push('script')
<script>
(function($) {
    "use strict";

    const userBalance = {{ auth()->check() ? auth()->user()->balance : 0 }};
    const curSym = '৳';
    let currentPlanData = null;

    // Click on plan card
    $('.agco-plan-card').click(function() {
        const planData = $(this).data('plan');
        currentPlanData = planData;
        showPlanDetails(planData);
    });

    function showPlanDetails(plan) {
        const modal = $('#planDetailsModal');

        // Update header
        $('#modalPlanName').text(plan.name);
        $('#modalPlanNameRepeat').text(plan.name);
        $('#modalPlanPrice').text(plan.price_formatted);

        // Update icon/image
        const $iconCircle = $('#modalIconCircle');
        const $modalImage = $('#modalPlanImage');
        const $modalIcon = $('#modalPlanIcon');

        if (plan.image) {
            $modalImage.attr('src', plan.image).show();
            $modalIcon.hide();
        } else {
            $modalImage.hide();
            $modalIcon.show();
        }

        // Calculate package details
        const dailyLimit = plan.daily_limit;
        const monthlyLimit = dailyLimit * 30;
        const yearlyLimit = dailyLimit * 365;

        const ptcAmount = plan.price / plan.validity;
        const dailyCommission = (ptcAmount * dailyLimit).toFixed(2);
        const monthlyCommission = (ptcAmount * monthlyLimit).toFixed(2);
        const yearlyCommission = (ptcAmount * yearlyLimit).toFixed(2);

        $('#modalDailyLimit').text(dailyLimit + ' টি');
        $('#modalDailyCommission').text(curSym + dailyCommission);
        $('#modalMonthlyLimit').text(monthlyLimit + ' টি');
        $('#modalMonthlyCommission').text(curSym + monthlyCommission);
        $('#modalYearlyLimit').text(yearlyLimit + ' টি');
        $('#modalYearlyCommission').text(curSym + yearlyCommission);

        // Commission details
        const levelAAmount = plan.commission_a_max || ((plan.price * plan.commission_a_rate) / 100).toFixed(2);
        const levelBAmount = plan.commission_b_max || ((plan.price * plan.commission_b_rate) / 100).toFixed(2);
        const levelCAmount = plan.commission_c_max || ((plan.price * plan.commission_c_rate) / 100).toFixed(2);

        $('#commissionRateA').text(plan.commission_a_rate + '%');
        $('#commissionAmountA').text(curSym + levelAAmount);
        $('#commissionRateB').text(plan.commission_b_rate + '%');
        $('#commissionAmountB').text(curSym + levelBAmount);
        $('#commissionRateC').text(plan.commission_c_rate + '%');
        $('#commissionAmountC').text(curSym + levelCAmount);

        // Task commissions
        $('#taskCommissionA').text(plan.task_commission_a + '%');
        $('#taskCommissionB').text(plan.task_commission_b + '%');
        $('#taskCommissionC').text(plan.task_commission_c + '%');

        // Show appropriate button
        @auth
        $('#btnPurchasePlan, #btnNeedDeposit, #btnAlreadyActive').hide();

        if (plan.is_active) {
            $('#btnAlreadyActive').show();
        } else if (plan.affordable) {
            $('#btnPurchasePlan').show();
        } else {
            $('#btnNeedDeposit').show();
        }
        @endauth

        modal.modal('show');
    }

    // Purchase button click
    $('#btnPurchasePlan').click(function() {
        if (!currentPlanData) return;

        $('#confirmPlanName').text(currentPlanData.name);
        $('#confirmValidity').text(currentPlanData.validity + ' দিন');
        $('#confirmPrice').text(curSym + currentPlanData.price_formatted);
        $('#confirmTotal').text(curSym + currentPlanData.price_formatted);

        $('#planDetailsModal').modal('hide');
        setTimeout(function() {
            $('#purchaseConfirmModal').modal('show');
        }, 300);
    });

    // Final confirmation
    $('#btnFinalConfirm').click(function() {
        if (!currentPlanData) return;
        $('#selectedPlanId').val(currentPlanData.id);
        $('#planPurchaseForm').submit();
    });

})(jQuery);
</script>
@endpush
