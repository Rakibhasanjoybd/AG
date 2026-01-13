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
@endphp

<!-- Smart Plan Screen - Professional Compact UI -->
<div class="smart-plan-container">

    <!-- Top Navigation Bar -->
    <div class="sp-navbar">
        <a href="{{ route('home') }}" class="sp-nav-back">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="sp-nav-title">@lang('Subscription Plans')</h1>
        @auth
        <div class="sp-nav-balance">
            <span class="sp-bal-label">@lang('Balance')</span>
            <span class="sp-bal-value">{{ $general->cur_sym }}{{ showAmount($userBalance) }}</span>
        </div>
        @else
        <a href="{{ route('user.login') }}" class="sp-nav-login">
            <i class="fas fa-sign-in-alt"></i>
        </a>
        @endauth
    </div>

    <!-- Current Plan Status (if any) -->
    @auth
    @if($isCurrent)
    @php $currentPlan = $plans->where('id', $isCurrent)->first(); @endphp
    @if($currentPlan)
    <div class="sp-current-plan">
        <div class="sp-cp-left">
            <div class="sp-cp-badge">
                <i class="fas fa-crown"></i>
            </div>
            <div class="sp-cp-info">
                <span class="sp-cp-label">@lang('Current Plan')</span>
                <span class="sp-cp-name">{{ __($currentPlan->name) }}</span>
            </div>
        </div>
        @if(auth()->user()->expire_date)
        <div class="sp-cp-right">
            <span class="sp-cp-exp-label">@lang('Expires')</span>
            <span class="sp-cp-exp-date">{{ \Carbon\Carbon::parse(auth()->user()->expire_date)->format('M d, Y') }}</span>
        </div>
        @endif
    </div>
    @endif
    @endif
    @endauth

    <!-- Quick Info Strip -->
    <div class="sp-info-strip">
        <div class="sp-info-item">
            <i class="fas fa-shield-alt"></i>
            <span>@lang('Secure')</span>
        </div>
        <div class="sp-info-item">
            <i class="fas fa-bolt"></i>
            <span>@lang('Instant')</span>
        </div>
        <div class="sp-info-item">
            <i class="fas fa-sync-alt"></i>
            <span>@lang('Auto-Renew')</span>
        </div>
    </div>

    <!-- Plans Grid -->
    <div class="sp-plans-grid">
        @foreach ($plans as $index => $plan)
            @php
                $isActive = auth()->check() && $isCurrent == $plan->id;
                $affordable = auth()->check() && $userBalance >= $plan->price;
                $isPopular = $loop->index == 1;
                $planColors = ['#6366f1', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981', '#3b82f6'];
                $planColor = $planColors[$index % count($planColors)];
            @endphp

            <div class="sp-plan-card {{ $isActive ? 'is-active' : '' }} {{ $isPopular ? 'is-popular' : '' }}">
                <!-- Popular Tag -->
                @if($isPopular && !$isActive)
                <div class="sp-popular-tag">
                    <i class="fas fa-star"></i> @lang('POPULAR')
                </div>
                @endif

                <!-- Active Tag -->
                @if($isActive)
                <div class="sp-active-tag">
                    <i class="fas fa-check-circle"></i> @lang('ACTIVE')
                </div>
                @endif

                <!-- Plan Header -->
                <div class="sp-plan-header" style="background: linear-gradient(135deg, {{ $planColor }}, {{ $planColor }}dd);">
                    <div class="sp-plan-icon">
                        @if($index == 0)
                        <i class="fas fa-rocket"></i>
                        @elseif($index == 1)
                        <i class="fas fa-gem"></i>
                        @elseif($index == 2)
                        <i class="fas fa-crown"></i>
                        @else
                        <i class="fas fa-star"></i>
                        @endif
                    </div>
                    <h3 class="sp-plan-name">{{ __($plan->name) }}</h3>
                </div>

                <!-- Price Section -->
                <div class="sp-plan-price">
                    <span class="sp-price-currency">{{ $general->cur_sym }}</span>
                    <span class="sp-price-amount">{{ showAmount($plan->price) }}</span>
                    <span class="sp-price-period">/ {{ $plan->validity }} @lang('days')</span>
                </div>

                <!-- Features List -->
                <ul class="sp-plan-features">
                    <li>
                        <i class="fas fa-check"></i>
                        <span><strong>{{ $plan->daily_limit }}</strong> @lang('Daily Ads')</span>
                    </li>
                    <li>
                        <i class="fas fa-check"></i>
                        <span><strong>{{ $plan->ref_level }}</strong> @lang('Referral Levels')</span>
                    </li>
                    <li>
                        <i class="fas fa-check"></i>
                        <span>@lang('Priority Support')</span>
                    </li>
                    <li>
                        <i class="fas fa-check"></i>
                        <span>@lang('Daily Earnings')</span>
                    </li>
                </ul>

                <!-- Action Button -->
                <div class="sp-plan-action">
                    @if($isActive)
                        <button class="sp-btn sp-btn-current" disabled>
                            <i class="fas fa-check"></i> @lang('Current Plan')
                        </button>
                    @else
                        <button class="sp-btn sp-btn-subscribe buyBtn"
                                data-id="{{ $plan->id }}"
                                data-name="{{ $plan->name }}"
                                data-price="{{ showAmount($plan->price) }}"
                                style="background: linear-gradient(135deg, {{ $planColor }}, {{ $planColor }}dd);">
                            @auth
                                @if($affordable)
                                    <i class="fas fa-bolt"></i> @lang('Subscribe Now')
                                @else
                                    <i class="fas fa-wallet"></i> @lang('Deposit & Subscribe')
                                @endif
                            @else
                                <i class="fas fa-sign-in-alt"></i> @lang('Login to Subscribe')
                            @endauth
                        </button>
                        @auth
                        @if(!$affordable)
                        <div class="sp-need-more">
                            <i class="fas fa-info-circle"></i>
                            @lang('Need') {{ $general->cur_sym }}{{ showAmount($plan->price - $userBalance) }} @lang('more')
                        </div>
                        @endif
                        @endauth
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- Bottom Help Section -->
    <div class="sp-help-section">
        <div class="sp-help-icon">
            <i class="fas fa-question-circle"></i>
        </div>
        <div class="sp-help-content">
            <span class="sp-help-title">@lang('Need Help?')</span>
            <span class="sp-help-text">@lang('Contact our 24/7 support team')</span>
        </div>
        <a href="{{ route('contact') }}" class="sp-help-btn">
            <i class="fas fa-headset"></i>
        </a>
    </div>

</div>

<!-- Purchase Confirmation Modal -->
<div class="modal fade" id="BuyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content sp-modal">
            <form method="post" action="{{ route('user.buyPlan') }}">
                @csrf
                <input type="hidden" name="id">

                <div class="sp-modal-header">
                    <div class="sp-modal-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h5 class="sp-modal-title">@lang('Confirm Subscription')</h5>
                    <button type="button" class="sp-modal-close" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="sp-modal-body">
                    @auth
                        <div class="sp-modal-plan">
                            <div class="sp-modal-plan-name"></div>
                            <div class="sp-modal-plan-price"></div>
                        </div>

                        <div class="sp-modal-summary">
                            <div class="sp-summary-row">
                                <span>@lang('Current Balance')</span>
                                <span>{{ $general->cur_sym }}{{ showAmount($userBalance) }}</span>
                            </div>
                            <div class="sp-summary-row sp-summary-total">
                                <span>@lang('Plan Cost')</span>
                                <span class="sp-modal-cost"></span>
                            </div>
                        </div>

                        @if(auth()->user()->runningPlan)
                        <div class="sp-modal-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>@lang('Your current plan will be replaced with the new one.')</span>
                        </div>
                        @endif
                    @else
                        <div class="sp-modal-login-prompt">
                            <i class="fas fa-lock"></i>
                            <p>@lang('Please login to subscribe to a plan')</p>
                        </div>
                    @endauth
                </div>

                <div class="sp-modal-footer">
                    @auth
                        <button type="button" class="sp-modal-btn sp-modal-btn-cancel" data-bs-dismiss="modal">
                            @lang('Cancel')
                        </button>
                        <button type="submit" class="sp-modal-btn sp-modal-btn-confirm">
                            <i class="fas fa-check"></i> @lang('Confirm')
                        </button>
                    @else
                        <a href="{{ route('user.login') }}" class="sp-modal-btn sp-modal-btn-login">
                            <i class="fas fa-sign-in-alt"></i> @lang('Login Now')
                        </a>
                    @endauth
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('style')
<style>
/* ===== SMART PLAN SCREEN - PROFESSIONAL COMPACT UI ===== */
:root {
    --sp-bg-dark: #0f172a;
    --sp-bg-card: #1e293b;
    --sp-text-primary: #f8fafc;
    --sp-text-secondary: #94a3b8;
    --sp-accent: #6366f1;
    --sp-success: #22c55e;
    --sp-warning: #f59e0b;
}

/* Container */
.smart-plan-container {
    background: linear-gradient(180deg, var(--sp-bg-dark) 0%, #0c1222 100%);
    min-height: 100vh;
    padding-bottom: 20px;
}

/* Navigation Bar */
.sp-navbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    background: rgba(15, 23, 42, 0.95);
    backdrop-filter: blur(10px);
    border-bottom: 1px solid rgba(255,255,255,0.05);
    position: sticky;
    top: 0;
    z-index: 100;
}
.sp-nav-back {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,0.08);
    border-radius: 12px;
    color: var(--sp-text-primary);
    text-decoration: none;
    transition: all 0.2s;
}
.sp-nav-back:hover {
    background: rgba(255,255,255,0.15);
    color: var(--sp-text-primary);
}
.sp-nav-title {
    font-size: 18px;
    font-weight: 700;
    color: var(--sp-text-primary);
    margin: 0;
}
.sp-nav-balance {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}
.sp-bal-label {
    font-size: 10px;
    color: var(--sp-text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.sp-bal-value {
    font-size: 16px;
    font-weight: 700;
    color: var(--sp-success);
}
.sp-nav-login {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--sp-accent), #8b5cf6);
    border-radius: 12px;
    color: #fff;
    text-decoration: none;
}

/* Current Plan Status */
.sp-current-plan {
    margin: 16px 20px;
    padding: 16px;
    background: linear-gradient(135deg, rgba(34,197,94,0.15), rgba(34,197,94,0.05));
    border: 1px solid rgba(34,197,94,0.3);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.sp-cp-left {
    display: flex;
    align-items: center;
    gap: 12px;
}
.sp-cp-badge {
    width: 44px;
    height: 44px;
    background: linear-gradient(135deg, var(--sp-success), #16a34a);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.sp-cp-badge i {
    font-size: 20px;
    color: #fff;
}
.sp-cp-info {
    display: flex;
    flex-direction: column;
}
.sp-cp-label {
    font-size: 11px;
    color: var(--sp-success);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.sp-cp-name {
    font-size: 16px;
    font-weight: 700;
    color: var(--sp-text-primary);
}
.sp-cp-right {
    text-align: right;
}
.sp-cp-exp-label {
    display: block;
    font-size: 10px;
    color: var(--sp-text-secondary);
}
.sp-cp-exp-date {
    font-size: 13px;
    font-weight: 600;
    color: var(--sp-text-primary);
}

/* Info Strip */
.sp-info-strip {
    display: flex;
    justify-content: center;
    gap: 24px;
    padding: 12px 20px;
    background: rgba(255,255,255,0.02);
    border-bottom: 1px solid rgba(255,255,255,0.05);
}
.sp-info-item {
    display: flex;
    align-items: center;
    gap: 6px;
    color: var(--sp-text-secondary);
    font-size: 12px;
}
.sp-info-item i {
    color: var(--sp-accent);
    font-size: 14px;
}

/* Plans Grid */
.sp-plans-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 16px;
    padding: 20px;
    max-width: 900px;
    margin: 0 auto;
}

/* Plan Card */
.sp-plan-card {
    background: var(--sp-bg-card);
    border-radius: 20px;
    overflow: hidden;
    position: relative;
    border: 1px solid rgba(255,255,255,0.08);
    transition: all 0.3s ease;
}
.sp-plan-card:hover {
    transform: translateY(-4px);
    border-color: rgba(99, 102, 241, 0.3);
    box-shadow: 0 20px 40px rgba(0,0,0,0.3);
}
.sp-plan-card.is-active {
    border-color: var(--sp-success);
    box-shadow: 0 0 0 2px rgba(34,197,94,0.2);
}
.sp-plan-card.is-popular {
    border-color: var(--sp-accent);
    box-shadow: 0 0 0 2px rgba(99,102,241,0.2);
}

/* Tags */
.sp-popular-tag, .sp-active-tag {
    position: absolute;
    top: 12px;
    right: 12px;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 4px;
    z-index: 10;
}
.sp-popular-tag {
    background: linear-gradient(135deg, var(--sp-accent), #8b5cf6);
    color: #fff;
}
.sp-active-tag {
    background: linear-gradient(135deg, var(--sp-success), #16a34a);
    color: #fff;
}

/* Plan Header */
.sp-plan-header {
    padding: 24px 20px;
    text-align: center;
    position: relative;
}
.sp-plan-icon {
    width: 56px;
    height: 56px;
    background: rgba(255,255,255,0.2);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 12px;
}
.sp-plan-icon i {
    font-size: 26px;
    color: #fff;
}
.sp-plan-name {
    font-size: 20px;
    font-weight: 700;
    color: #fff;
    margin: 0;
}

/* Price Section */
.sp-plan-price {
    padding: 20px;
    text-align: center;
    background: rgba(0,0,0,0.2);
}
.sp-price-currency {
    font-size: 18px;
    font-weight: 600;
    color: var(--sp-text-secondary);
    vertical-align: top;
}
.sp-price-amount {
    font-size: 36px;
    font-weight: 800;
    color: var(--sp-text-primary);
}
.sp-price-period {
    font-size: 13px;
    color: var(--sp-text-secondary);
}

/* Features List */
.sp-plan-features {
    list-style: none;
    padding: 20px;
    margin: 0;
}
.sp-plan-features li {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 0;
    border-bottom: 1px solid rgba(255,255,255,0.05);
    font-size: 14px;
    color: var(--sp-text-secondary);
}
.sp-plan-features li:last-child {
    border-bottom: none;
}
.sp-plan-features li i {
    width: 20px;
    height: 20px;
    background: rgba(34,197,94,0.15);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--sp-success);
    font-size: 10px;
    flex-shrink: 0;
}
.sp-plan-features li strong {
    color: var(--sp-text-primary);
    font-weight: 600;
}

/* Action Button */
.sp-plan-action {
    padding: 0 20px 20px;
}
.sp-btn {
    width: 100%;
    padding: 14px 20px;
    border: none;
    border-radius: 14px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.3s ease;
}
.sp-btn-subscribe {
    color: #fff;
    box-shadow: 0 4px 15px rgba(99,102,241,0.4);
}
.sp-btn-subscribe:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(99,102,241,0.5);
}
.sp-btn-current {
    background: rgba(34,197,94,0.15);
    color: var(--sp-success);
    cursor: default;
}
.sp-need-more {
    text-align: center;
    margin-top: 10px;
    font-size: 12px;
    color: var(--sp-warning);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

/* Help Section */
.sp-help-section {
    margin: 20px;
    padding: 16px;
    background: var(--sp-bg-card);
    border-radius: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    border: 1px solid rgba(255,255,255,0.05);
}
.sp-help-icon {
    width: 44px;
    height: 44px;
    background: rgba(99,102,241,0.15);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.sp-help-icon i {
    font-size: 20px;
    color: var(--sp-accent);
}
.sp-help-content {
    flex: 1;
    display: flex;
    flex-direction: column;
}
.sp-help-title {
    font-size: 14px;
    font-weight: 600;
    color: var(--sp-text-primary);
}
.sp-help-text {
    font-size: 12px;
    color: var(--sp-text-secondary);
}
.sp-help-btn {
    width: 44px;
    height: 44px;
    background: linear-gradient(135deg, var(--sp-accent), #8b5cf6);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    text-decoration: none;
    transition: all 0.2s;
}
.sp-help-btn:hover {
    transform: scale(1.05);
    color: #fff;
}

/* Modal Styles */
.sp-modal {
    background: var(--sp-bg-card);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 24px;
    overflow: hidden;
}
.sp-modal-header {
    background: linear-gradient(135deg, var(--sp-accent), #8b5cf6);
    padding: 24px;
    text-align: center;
    position: relative;
}
.sp-modal-icon {
    width: 60px;
    height: 60px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 12px;
}
.sp-modal-icon i {
    font-size: 26px;
    color: #fff;
}
.sp-modal-title {
    font-size: 18px;
    font-weight: 700;
    color: #fff;
    margin: 0;
}
.sp-modal-close {
    position: absolute;
    top: 16px;
    right: 16px;
    width: 32px;
    height: 32px;
    background: rgba(255,255,255,0.2);
    border: none;
    border-radius: 50%;
    color: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}
.sp-modal-close:hover {
    background: rgba(255,255,255,0.3);
}
.sp-modal-body {
    padding: 24px;
}
.sp-modal-plan {
    background: rgba(255,255,255,0.05);
    border-radius: 16px;
    padding: 16px;
    text-align: center;
    margin-bottom: 16px;
}
.sp-modal-plan-name {
    font-size: 16px;
    font-weight: 700;
    color: var(--sp-text-primary);
    margin-bottom: 4px;
}
.sp-modal-plan-price {
    font-size: 24px;
    font-weight: 800;
    color: var(--sp-accent);
}
.sp-modal-summary {
    background: rgba(0,0,0,0.2);
    border-radius: 12px;
    padding: 12px 16px;
    margin-bottom: 16px;
}
.sp-summary-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    font-size: 14px;
    color: var(--sp-text-secondary);
}
.sp-summary-row.sp-summary-total {
    border-top: 1px solid rgba(255,255,255,0.1);
    margin-top: 8px;
    padding-top: 12px;
    font-weight: 600;
    color: var(--sp-text-primary);
}
.sp-modal-warning {
    background: rgba(245,158,11,0.15);
    border: 1px solid rgba(245,158,11,0.3);
    border-radius: 12px;
    padding: 12px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
}
.sp-modal-warning i {
    color: var(--sp-warning);
    font-size: 18px;
    flex-shrink: 0;
    margin-top: 2px;
}
.sp-modal-warning span {
    font-size: 13px;
    color: var(--sp-warning);
    line-height: 1.4;
}
.sp-modal-login-prompt {
    text-align: center;
    padding: 20px;
}
.sp-modal-login-prompt i {
    font-size: 48px;
    color: rgba(255,255,255,0.2);
    margin-bottom: 12px;
}
.sp-modal-login-prompt p {
    font-size: 14px;
    color: var(--sp-text-secondary);
    margin: 0;
}
.sp-modal-footer {
    padding: 16px 24px 24px;
    display: flex;
    gap: 12px;
}
.sp-modal-btn {
    flex: 1;
    padding: 14px 20px;
    border: none;
    border-radius: 14px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    text-decoration: none;
    transition: all 0.2s;
}
.sp-modal-btn-cancel {
    background: rgba(255,255,255,0.08);
    color: var(--sp-text-secondary);
}
.sp-modal-btn-cancel:hover {
    background: rgba(255,255,255,0.12);
}
.sp-modal-btn-confirm, .sp-modal-btn-login {
    background: linear-gradient(135deg, var(--sp-accent), #8b5cf6);
    color: #fff;
}
.sp-modal-btn-confirm:hover, .sp-modal-btn-login:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(99,102,241,0.4);
    color: #fff;
}

/* Responsive */
@media (max-width: 600px) {
    .sp-plans-grid {
        grid-template-columns: 1fr;
        padding: 16px;
    }
    .sp-navbar {
        padding: 12px 16px;
    }
    .sp-nav-title {
        font-size: 16px;
    }
    .sp-info-strip {
        gap: 16px;
        padding: 10px 16px;
    }
    .sp-info-item span {
        display: none;
    }
}
</style>
@endpush

@push('script')
<script>
(function($) {
    "use strict";
    $('.buyBtn').click(function() {
        var modal = $('#BuyModal');
        var planName = $(this).data('name');
        var planPrice = $(this).data('price');

        modal.find('input[name=id]').val($(this).data('id'));
        modal.find('.sp-modal-plan-name').text(planName);
        modal.find('.sp-modal-plan-price').text('{{ $general->cur_sym }}' + planPrice);
        modal.find('.sp-modal-cost').text('{{ $general->cur_sym }}' + planPrice);
        modal.modal('show');
    });
})(jQuery);
</script>
@endpush
