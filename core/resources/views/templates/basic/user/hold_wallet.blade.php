@extends($activeTemplate.'layouts.master_mobile')
@section('main-content')
@php
    $achievementProgress = $achievementProgress ?? $user->getAchievementProgress();
    $transferStatus = $transferStatus ?? $user->canTransferFromHoldWallet();
    $achievementConfig = $achievementConfig ?? \App\Models\HoldWalletSetting::getAchievementConfig();
    $feeConfig = $feeConfig ?? \App\Models\HoldWalletSetting::getTransferFeeConfig();
    $availableBalance = $availableBalance ?? $user->getAvailableHoldBalanceByLevel();
    $pendingBalance = $pendingBalance ?? $user->pendingHoldBalance();
    $totalHoldBalance = $totalHoldBalance ?? $user->totalHoldBalance;
    $estimatedFee = $estimatedFee ?? \App\Models\HoldWalletSetting::calculateTransferFee($availableBalance);
    $netAmount = $netAmount ?? max(0, $availableBalance - $estimatedFee);
    $holdTransactions = $holdTransactions ?? $user->holdWalletTransactions()->latest()->paginate(20);
    $transferHistory = $transferHistory ?? $user->holdWalletTransfers()->latest()->take(5)->get();
@endphp

<div class="hold-wallet-screen">
    <!-- Achievement Hero Section -->
    <div class="achievement-hero level-{{ $achievementProgress['current_level'] }}">
        <div class="hero-content">
            <div class="achievement-badge-large">
                <i class="{{ $achievementProgress['current_config']['icon'] }}"></i>
            </div>
            <div class="hero-info">
                <span class="hero-label">আপনার অর্জন লেভেল</span>
                <h2 class="hero-title">{{ $achievementProgress['current_config']['name'] }}</h2>
                <div class="hero-stats">
                    <span><i class="fas fa-users"></i> {{ $achievementProgress['referral_count'] }} জন রেফার</span>
                    <span><i class="fas fa-clock"></i> {{ $achievementProgress['current_config']['hold_days'] }} দিন অপেক্ষা</span>
                </div>
            </div>
        </div>

        @if(!$achievementProgress['is_max_level'])
        <div class="hero-progress">
            <div class="progress-info">
                <span>পরবর্তী: {{ $achievementProgress['next_config']['name'] }}</span>
                <span>{{ $achievementProgress['referrals_needed'] }} জন বাকি</span>
            </div>
            <div class="progress-bar-large">
                <div class="progress-fill" style="width: {{ $achievementProgress['progress_percent'] }}%"></div>
            </div>
        </div>
        @endif
    </div>

    <!-- Achievement Levels Grid -->
    <div class="section-card">
        <div class="section-header">
            <h3><i class="fas fa-trophy me-2"></i>অর্জন লেভেল সমূহ</h3>
        </div>
        <div class="levels-grid">
            @foreach($achievementConfig as $lvl => $cfg)
            <div class="level-card {{ $achievementProgress['current_level'] == $lvl ? 'current' : '' }} {{ $achievementProgress['current_level'] > $lvl ? 'completed' : '' }}">
                <div class="level-badge" style="background: {{ $cfg['color'] }}">
                    <i class="{{ $cfg['icon'] }}"></i>
                </div>
                <div class="level-info">
                    <strong>{{ $cfg['name'] }}</strong>
                    <span>{{ $cfg['min_referrals'] }}{{ $cfg['max_referrals'] ? '-'.$cfg['max_referrals'] : '+' }} রেফার</span>
                </div>
                <div class="level-benefit">
                    @if($cfg['hold_days'] == 0)
                        <span class="instant">তাৎক্ষণিক</span>
                    @else
                        <span>{{ $cfg['hold_days'] }} দিন</span>
                    @endif
                </div>
                @if($achievementProgress['current_level'] == $lvl)
                <div class="current-badge">বর্তমান</div>
                @elseif($achievementProgress['current_level'] > $lvl)
                <div class="completed-badge"><i class="fas fa-check"></i></div>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    <!-- Hold Balance Summary -->
    <div class="section-card balance-card">
        <div class="section-header">
            <h3><i class="fas fa-wallet me-2"></i>হোল্ড ব্যালেন্স</h3>
        </div>

        <div class="balance-hero">
            <span class="balance-label">মোট হোল্ড ব্যালেন্স</span>
            <h2 class="balance-amount">৳{{ showAmount($totalHoldBalance) }}</h2>
        </div>

        <div class="balance-breakdown">
            <div class="breakdown-item">
                <div class="breakdown-icon purple"><i class="fas fa-user-friends"></i></div>
                <div class="breakdown-info">
                    <span>রেফারেল কমিশন</span>
                    <strong>৳{{ showAmount($user->referral_commission_hold ?? 0) }}</strong>
                </div>
            </div>
            <div class="breakdown-item">
                <div class="breakdown-icon orange"><i class="fas fa-level-up-alt"></i></div>
                <div class="breakdown-info">
                    <span>আপগ্রেড কমিশন</span>
                    <strong>৳{{ showAmount($user->upgrade_commission_hold ?? 0) }}</strong>
                </div>
            </div>
            <div class="breakdown-item">
                <div class="breakdown-icon gold"><i class="fas fa-ad"></i></div>
                <div class="breakdown-info">
                    <span>PTC কমিশন</span>
                    <strong>৳{{ showAmount($user->ptc_commission_hold ?? 0) }}</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Transfer Section -->
    <div class="section-card transfer-card">
        <div class="section-header">
            <h3><i class="fas fa-exchange-alt me-2"></i>মূল ওয়ালেটে ট্রান্সফার</h3>
        </div>

        <!-- Transfer Status -->
        <div class="transfer-status-box {{ $transferStatus['can_transfer'] ? 'can-transfer' : 'cannot-transfer' }}">
            <div class="status-icon">
                <i class="fas {{ $transferStatus['can_transfer'] ? 'fa-check-circle' : 'fa-hourglass-half' }}"></i>
            </div>
            <div class="status-content">
                <strong>{{ $transferStatus['can_transfer'] ? 'ট্রান্সফার করতে পারবেন' : 'অপেক্ষা করুন' }}</strong>
                <p>{{ $transferStatus['reason'] }}</p>
                @if(!$transferStatus['can_transfer'] && $transferStatus['next_transfer_date'])
                <span class="next-date">পরবর্তী তারিখ: {{ \Carbon\Carbon::parse($transferStatus['next_transfer_date'])->format('d M, Y') }}</span>
                @endif
            </div>
        </div>

        <!-- Transfer Calculator -->
        <div class="transfer-calculator">
            <div class="calc-row">
                <span>ট্রান্সফারযোগ্য:</span>
                <strong class="text-success">৳{{ showAmount($availableBalance) }}</strong>
            </div>
            <div class="calc-row">
                <span>পেন্ডিং:</span>
                <strong class="text-muted">৳{{ showAmount($pendingBalance) }}</strong>
            </div>
            @if($availableBalance > 0)
            <div class="calc-divider"></div>
            <div class="calc-row fee">
                <span>ফি ({{ $feeConfig['type'] == 'percent' ? $feeConfig['amount'].'%' : '৳'.showAmount($feeConfig['amount']) }}):</span>
                <strong class="text-warning">-৳{{ showAmount($estimatedFee) }}</strong>
            </div>
            <div class="calc-row net">
                <span>নেট পাবেন:</span>
                <strong>৳{{ showAmount($netAmount) }}</strong>
            </div>
            @endif
        </div>

        <!-- Transfer Button -->
        @if($transferStatus['can_transfer'] && $availableBalance > 0 && $availableBalance >= $feeConfig['min_transfer'])
        <form action="{{ route('user.hold.wallet.transfer') }}" method="POST">
            @csrf
            <button type="submit" class="transfer-btn-large">
                <i class="fas fa-exchange-alt"></i>
                <span>৳{{ showAmount($netAmount) }} মূল ব্যালেন্সে ট্রান্সফার করুন</span>
            </button>
        </form>
        @elseif($availableBalance > 0 && $availableBalance < $feeConfig['min_transfer'])
        <div class="min-amount-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <span>সর্বনিম্ন ট্রান্সফার পরিমাণ ৳{{ showAmount($feeConfig['min_transfer']) }}</span>
        </div>
        @elseif(!$transferStatus['can_transfer'])
        <button class="transfer-btn-large disabled" disabled>
            <i class="fas fa-hourglass-half"></i>
            <span>{{ $transferStatus['wait_days'] }} দিন অপেক্ষা করুন</span>
        </button>
        @else
        <button class="transfer-btn-large disabled" disabled>
            <i class="fas fa-lock"></i>
            <span>ট্রান্সফারযোগ্য ব্যালেন্স নেই</span>
        </button>
        @endif
    </div>

    <!-- How It Works -->
    <div class="section-card info-card">
        <div class="section-header">
            <h3><i class="fas fa-info-circle me-2"></i>কিভাবে কাজ করে</h3>
        </div>
        <div class="info-steps">
            <div class="info-step">
                <div class="step-number">১</div>
                <div class="step-content">
                    <strong>কমিশন বিতরণ</strong>
                    <p>রেফারেল কমিশনের ৪০% তাৎক্ষণিক মূল ওয়ালেটে, ৬০% হোল্ড ওয়ালেটে জমা হয়।</p>
                </div>
            </div>
            <div class="info-step">
                <div class="step-number">২</div>
                <div class="step-content">
                    <strong>লেভেল অনুযায়ী অপেক্ষা</strong>
                    <p>আপনার রেফারেল সংখ্যা অনুযায়ী ট্রান্সফারের জন্য অপেক্ষার সময় নির্ধারিত হয়।</p>
                </div>
            </div>
            <div class="info-step">
                <div class="step-number">৩</div>
                <div class="step-content">
                    <strong>ট্রান্সফার</strong>
                    <p>নির্ধারিত সময় পর, হোল্ড ব্যালেন্স মূল ওয়ালেটে ট্রান্সফার করতে পারবেন (ফি প্রযোজ্য)।</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transfers -->
    @if($transferHistory && $transferHistory->count() > 0)
    <div class="section-card">
        <div class="section-header">
            <h3><i class="fas fa-history me-2"></i>সাম্প্রতিক ট্রান্সফার</h3>
        </div>
        <div class="transfer-history">
            @foreach($transferHistory as $transfer)
            <div class="history-item">
                <div class="history-icon"><i class="fas fa-exchange-alt"></i></div>
                <div class="history-info">
                    <strong>৳{{ showAmount($transfer->net_amount) }}</strong>
                    <span>ফি: ৳{{ showAmount($transfer->fee) }} | লেভেল {{ $transfer->achievement_level }}</span>
                </div>
                <div class="history-date">{{ $transfer->created_at->format('d M') }}</div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Commission History -->
    <div class="section-card">
        <div class="section-header">
            <h3><i class="fas fa-list me-2"></i>কমিশন ইতিহাস</h3>
        </div>

        <div class="commission-list">
            @forelse($holdTransactions as $tx)
            <div class="commission-item">
                <div class="comm-icon {{ $tx->commission_type }}">
                    @if($tx->commission_type == 'referral')
                        <i class="fas fa-user-friends"></i>
                    @elseif($tx->commission_type == 'upgrade' || $tx->commission_type == 'plan_subscribe' || $tx->commission_type == 'deposit')
                        <i class="fas fa-level-up-alt"></i>
                    @else
                        <i class="fas fa-mouse-pointer"></i>
                    @endif
                </div>
                <div class="comm-info">
                    <strong>{{ ucfirst(str_replace('_', ' ', $tx->commission_type)) }} কমিশন</strong>
                    <span>{{ $tx->created_at->format('d M Y, h:i A') }}</span>
                    @if($tx->from_user_id)
                    <span class="from-user">From: {{ optional($tx->fromUser)->username ?? 'N/A' }}</span>
                    @endif
                </div>
                <div class="comm-amount">
                    <strong>৳{{ showAmount($tx->hold_amount) }}</strong>
                    @if($tx->is_transferred)
                        <span class="status transferred">ট্রান্সফার হয়েছে</span>
                    @elseif($user->getAchievementLevel() >= 3)
                        <span class="status available">ট্রান্সফারযোগ্য</span>
                    @elseif($tx->available_date <= now()->toDateString())
                        <span class="status available">ট্রান্সফারযোগ্য</span>
                    @else
                        <span class="status pending">{{ \Carbon\Carbon::parse($tx->available_date)->format('d M') }}</span>
                    @endif
                </div>
            </div>
            @empty
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>এখনো কোনো কমিশন ইতিহাস নেই</p>
            </div>
            @endforelse
        </div>

        @if($holdTransactions->hasPages())
        <div class="pagination-wrapper">
            {{ $holdTransactions->links() }}
        </div>
        @endif
    </div>
</div>

<div style="height: 20px;"></div>
@endsection

@push('style')
<style>
.hold-wallet-screen{padding-bottom:20px}

/* Achievement Hero */
.achievement-hero{padding:24px 20px;margin:16px;border-radius:20px;color:#fff}
.achievement-hero.level-1{background:linear-gradient(135deg,#9333ea,#7c3aed)}
.achievement-hero.level-2{background:linear-gradient(135deg,#6b7280,#4b5563)}
.achievement-hero.level-3{background:linear-gradient(135deg,#f59e0b,#d97706)}
.hero-content{display:flex;align-items:center;gap:16px}
.achievement-badge-large{width:70px;height:70px;background:rgba(255,255,255,0.2);border-radius:20px;display:flex;align-items:center;justify-content:center;font-size:32px}
.hero-info{flex:1}
.hero-label{font-size:12px;opacity:0.8}
.hero-title{font-size:28px;font-weight:800;margin:4px 0 8px}
.hero-stats{display:flex;gap:16px;font-size:12px}
.hero-stats i{margin-right:4px}
.hero-progress{margin-top:20px;padding-top:16px;border-top:1px solid rgba(255,255,255,0.2)}
.progress-info{display:flex;justify-content:space-between;font-size:12px;margin-bottom:8px}
.progress-bar-large{height:10px;background:rgba(255,255,255,0.3);border-radius:5px;overflow:hidden}
.progress-fill{height:100%;background:#fff;border-radius:5px}

/* Section Cards */
.section-card{background:var(--white);margin:16px;border-radius:20px;padding:16px;box-shadow:0 2px 10px rgba(0,0,0,0.04)}
.section-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px}
.section-header h3{font-size:16px;font-weight:700;color:var(--dark-text);display:flex;align-items:center;margin:0}

/* Levels Grid */
.levels-grid{display:flex;flex-direction:column;gap:12px}
.level-card{display:flex;align-items:center;gap:12px;padding:14px;border-radius:14px;background:#f8f9fa;position:relative}
.level-card.current{background:linear-gradient(135deg,#f3e8ff,#fef3c7);border:2px solid var(--purple)}
.level-card.completed{opacity:0.7}
.level-badge{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:18px;color:#fff}
.level-info{flex:1}
.level-info strong{display:block;font-size:14px;color:var(--dark-text)}
.level-info span{font-size:11px;color:var(--gray)}
.level-benefit{text-align:right}
.level-benefit span{font-size:12px;font-weight:600;color:var(--gray)}
.level-benefit .instant{color:#22c55e}
.current-badge{position:absolute;top:8px;right:8px;background:var(--purple);color:#fff;font-size:9px;padding:3px 8px;border-radius:10px;font-weight:600}
.completed-badge{position:absolute;top:8px;right:8px;background:#22c55e;color:#fff;width:20px;height:20px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:10px}

/* Balance Card */
.balance-hero{text-align:center;padding:20px;background:linear-gradient(135deg,#f3e8ff,#fef3c7);border-radius:16px;margin-bottom:16px}
.balance-label{font-size:12px;color:var(--gray)}
.balance-amount{font-size:36px;font-weight:800;color:var(--purple);margin-top:4px}
.balance-breakdown{display:flex;flex-direction:column;gap:10px}
.breakdown-item{display:flex;align-items:center;gap:12px;padding:12px;background:#f8f9fa;border-radius:12px}
.breakdown-icon{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:16px;color:#fff}
.breakdown-icon.purple{background:var(--purple)}
.breakdown-icon.orange{background:var(--orange)}
.breakdown-icon.gold{background:var(--gold);color:var(--purple)}
.breakdown-info{flex:1}
.breakdown-info span{display:block;font-size:11px;color:var(--gray)}
.breakdown-info strong{font-size:15px;color:var(--dark-text)}

/* Transfer Section */
.transfer-status-box{display:flex;gap:14px;padding:16px;border-radius:14px;margin-bottom:16px}
.transfer-status-box.can-transfer{background:rgba(34,197,94,0.1)}
.transfer-status-box.cannot-transfer{background:rgba(245,158,11,0.1)}
.transfer-status-box .status-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px}
.can-transfer .status-icon{background:#22c55e;color:#fff}
.cannot-transfer .status-icon{background:#f59e0b;color:#fff}
.status-content{flex:1}
.status-content strong{display:block;font-size:14px;color:var(--dark-text)}
.status-content p{font-size:12px;color:var(--gray);margin:4px 0 0}
.next-date{display:inline-block;margin-top:6px;font-size:11px;background:rgba(245,158,11,0.2);color:#d97706;padding:4px 10px;border-radius:8px}

.transfer-calculator{background:#f8f9fa;border-radius:14px;padding:14px}
.calc-row{display:flex;justify-content:space-between;padding:8px 0;font-size:13px;color:var(--gray)}
.calc-row strong{color:var(--dark-text)}
.calc-row.fee strong{color:var(--orange)}
.calc-row.net{border-top:1px dashed #e5e7eb;padding-top:12px;margin-top:4px}
.calc-row.net strong{font-size:18px;color:var(--purple)}
.calc-divider{height:1px;background:#e5e7eb;margin:8px 0}

.transfer-btn-large{width:100%;padding:16px;background:linear-gradient(135deg,var(--purple),var(--crimson));color:#fff;border:none;border-radius:14px;font-size:15px;font-weight:700;font-family:inherit;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:10px;margin-top:16px}
.transfer-btn-large.disabled{background:#e5e7eb;color:#9ca3af;cursor:not-allowed}
.transfer-btn-large i{font-size:18px}

.min-amount-warning{display:flex;align-items:center;gap:10px;padding:14px;background:rgba(245,158,11,0.1);border-radius:12px;margin-top:16px;font-size:12px;color:#d97706}
.min-amount-warning i{font-size:16px}

/* Info Steps */
.info-steps{display:flex;flex-direction:column;gap:14px}
.info-step{display:flex;gap:14px}
.step-number{width:32px;height:32px;background:var(--purple);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;flex-shrink:0}
.step-content{flex:1}
.step-content strong{display:block;font-size:13px;color:var(--dark-text);margin-bottom:4px}
.step-content p{font-size:11px;color:var(--gray);margin:0}

/* Transfer History */
.transfer-history{display:flex;flex-direction:column;gap:10px}
.history-item{display:flex;align-items:center;gap:12px;padding:12px;background:#f8f9fa;border-radius:12px}
.history-icon{width:36px;height:36px;background:#22c55e;color:#fff;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:14px}
.history-info{flex:1}
.history-info strong{display:block;font-size:14px;color:var(--dark-text)}
.history-info span{font-size:10px;color:var(--gray)}
.history-date{font-size:11px;color:var(--gray)}

/* Commission List */
.commission-list{display:flex;flex-direction:column;gap:10px}
.commission-item{display:flex;align-items:center;gap:12px;padding:12px;background:#f8f9fa;border-radius:12px}
.comm-icon{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:16px;color:#fff}
.comm-icon.referral{background:var(--purple)}
.comm-icon.upgrade,.comm-icon.plan_subscribe,.comm-icon.deposit{background:var(--orange)}
.comm-icon.ptc_view{background:var(--gold);color:var(--purple)}
.comm-info{flex:1}
.comm-info strong{display:block;font-size:13px;color:var(--dark-text)}
.comm-info span{display:block;font-size:10px;color:var(--gray)}
.comm-info .from-user{color:var(--purple)}
.comm-amount{text-align:right}
.comm-amount strong{display:block;font-size:14px;color:var(--dark-text)}
.comm-amount .status{font-size:9px;padding:3px 8px;border-radius:8px;display:inline-block;margin-top:4px}
.comm-amount .status.available{background:rgba(34,197,94,0.1);color:#22c55e}
.comm-amount .status.pending{background:rgba(245,158,11,0.1);color:#d97706}
.comm-amount .status.transferred{background:rgba(107,114,128,0.1);color:#6b7280}

.empty-state{text-align:center;padding:40px 20px}
.empty-state i{font-size:48px;color:#e5e7eb;margin-bottom:12px}
.empty-state p{color:var(--gray);font-size:13px}

.pagination-wrapper{margin-top:16px;display:flex;justify-content:center}
</style>
@endpush
