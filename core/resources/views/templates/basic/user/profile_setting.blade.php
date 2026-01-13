@extends($activeTemplate.'layouts.master_mobile')
@section('main-content')
@php
    $achievementProgress = $user->getAchievementProgress();
    $transferStatus = $user->canTransferFromHoldWallet();
    $availableBalance = $user->getAvailableHoldBalanceByLevel();
    $totalHoldBalance = $user->totalHoldBalance;
    $feeConfig = \App\Models\HoldWalletSetting::getTransferFeeConfig();
    $estimatedFee = \App\Models\HoldWalletSetting::calculateTransferFee($availableBalance);
    $netAmount = max(0, $availableBalance - $estimatedFee);
    $totalBalance = $user->balance + $totalHoldBalance;
@endphp

<!-- Profile Header -->
<div class="profile-header">
    <div class="profile-avatar-wrap">
        <img src="{{ getImage(getFilePath('userProfile').'/'.$user->image, getFileSize('userProfile')) }}" alt="{{ $user->username }}" class="profile-avatar">
        <span class="avatar-badge"><i class="fas fa-check"></i></span>
    </div>
    <h3 class="profile-name">{{ $user->fullname }}</h3>
    <p class="profile-username">{{ $user->user_id }}</p>
</div>

<!-- Wallet Overview Cards -->
<div class="wallet-overview">
    <div class="wallet-card main-wallet">
        <a href="{{ route('user.withdraw') }}" class="wallet-action">
            <i class="fas fa-arrow-up"></i>
        </a>
        <div class="wallet-icon-wrap">
            <i class="fas fa-wallet"></i>
        </div>
        <div class="wallet-info">
            <span class="wallet-label">মূল ব্যালেন্স</span>
            <h3 class="wallet-amount">৳{{ showAmount($user->balance) }}</h3>
        </div>
    </div>

    <div class="wallet-card hold-wallet">
        <div class="wallet-icon-wrap">
            <i class="fas fa-lock"></i>
        </div>
        <div class="wallet-info">
            <span class="wallet-label">হোল্ড ব্যালেন্স</span>
            <h3 class="wallet-amount">৳{{ showAmount($totalHoldBalance) }}</h3>
        </div>
        <span class="wallet-badge {{ $transferStatus['can_transfer'] ? 'active' : 'pending' }}">
            {{ $transferStatus['can_transfer'] ? 'সক্রিয়' : 'অপেক্ষারত' }}
        </span>
    </div>
</div>

<!-- Wallet Details Section -->
<div class="compact-section">
    <div class="section-header" onclick="toggleSection('wallet')">
        <div class="header-left">
            <i class="fas fa-chart-pie"></i>
            <h3>ওয়ালেট বিস্তারিত</h3>
        </div>
        <i class="fas fa-chevron-down toggle-icon"></i>
    </div>
    <div class="section-content" id="wallet-content">
        <div class="wallet-breakdown">
            <div class="breakdown-item">
                <span>মোট সম্পদ</span>
                <strong>৳{{ showAmount($totalBalance) }}</strong>
            </div>
            <div class="breakdown-item">
                <span>রেফারেল কমিশন</span>
                <strong>৳{{ showAmount($user->referral_commission_hold ?? 0) }}</strong>
            </div>
            <div class="breakdown-item">
                <span>আপগ্রেড কমিশন</span>
                <strong>৳{{ showAmount($user->upgrade_commission_hold ?? 0) }}</strong>
            </div>
            <div class="breakdown-item">
                <span>PTC কমিশন</span>
                <strong>৳{{ showAmount($user->ptc_commission_hold ?? 0) }}</strong>
            </div>
        </div>

        @if($availableBalance > 0)
        <div class="transfer-box">
            <div class="transfer-info">
                <span>ট্রান্সফারযোগ্য</span>
                <h4>৳{{ showAmount($availableBalance) }}</h4>
            </div>
            <div class="transfer-calc">
                <div class="calc-row">
                    <span>ফি</span>
                    <span>-৳{{ showAmount($estimatedFee) }}</span>
                </div>
                <div class="calc-row total">
                    <span>নেট</span>
                    <strong>৳{{ showAmount($netAmount) }}</strong>
                </div>
            </div>
            @if($transferStatus['can_transfer'])
            <form action="{{ route('user.hold.wallet.transfer') }}" method="POST">
                @csrf
                <button type="submit" class="btn-transfer">
                    <i class="fas fa-exchange-alt"></i> ট্রান্সফার করুন
                </button>
            </form>
            @else
            <div class="transfer-status">
                <i class="fas fa-clock"></i>
                <span>{{ $transferStatus['reason'] }}</span>
            </div>
            @endif
        </div>
        @endif
    </div>
</div>

<!-- Quick Actions -->
<div class="compact-section">
    <div class="section-header" onclick="toggleSection('actions')">
        <div class="header-left">
            <i class="fas fa-bolt"></i>
            <h3>দ্রুত অ্যাকশন</h3>
        </div>
        <i class="fas fa-chevron-down toggle-icon"></i>
    </div>
    <div class="section-content" id="actions-content">
        <div class="action-grid">
            <a href="{{ route('user.change.password') }}" class="action-item">
                <i class="fas fa-lock"></i>
                <span>পাসওয়ার্ড</span>
            </a>
            <a href="{{ route('user.twofactor') }}" class="action-item">
                <i class="fas fa-shield-alt"></i>
                <span>সিকিউরিটি</span>
            </a>
            <a href="{{ route('ticket') }}" class="action-item">
                <i class="fas fa-headset"></i>
                <span>সাপোর্ট</span>
            </a>
            <a href="{{ route('user.logout') }}" class="action-item logout">
                <i class="fas fa-sign-out-alt"></i>
                <span>লগআউট</span>
            </a>
        </div>
    </div>
</div>

<!-- Profile Information -->
<div class="compact-section active">
    <div class="section-header" onclick="toggleSection('profile')">
        <div class="header-left">
            <i class="fas fa-user-edit"></i>
            <h3>প্রোফাইল তথ্য</h3>
        </div>
        <i class="fas fa-chevron-down toggle-icon"></i>
    </div>
    <div class="section-content" id="profile-content">
        <form action="" method="post" enctype="multipart/form-data">
            @csrf
            <div class="form-row">
                <div class="form-group">
                    <label>@lang('First Name')</label>
                    <input type="text" class="form-control" name="firstname" value="{{$user->firstname}}" required>
                </div>
                <div class="form-group">
                    <label>@lang('Last Name')</label>
                    <input type="text" class="form-control" name="lastname" value="{{$user->lastname}}" required>
                </div>
            </div>

            <div class="form-group">
                <label>@lang('Mobile Number')</label>
                <input class="form-control readonly" value="{{$user->mobile}}" readonly>
            </div>

            <div class="form-group">
                <label>@lang('Address')</label>
                <input type="text" class="form-control" name="address" value="{{@$user->address->address}}">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>@lang('City')</label>
                    <input type="text" class="form-control" name="city" value="{{@$user->address->city}}">
                </div>
                <div class="form-group">
                    <label>@lang('State')</label>
                    <input type="text" class="form-control" name="state" value="{{@$user->address->state}}">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>@lang('Zip Code')</label>
                    <input type="text" class="form-control" name="zip" value="{{@$user->address->zip}}">
                </div>
                <div class="form-group">
                    <label>@lang('Country')</label>
                    <input class="form-control readonly" value="{{@$user->address->country}}" disabled>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fas fa-save"></i> @lang('Save Changes')
            </button>
        </form>
    </div>
</div>

<div style="height: 20px;"></div>
@endsection

@push('style')
<style>
@import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap');

body{font-family:'Hind Siliguri',sans-serif}

/* Profile Header */
.profile-header{background:linear-gradient(135deg,#0F743C 0%,#1a9e52 50%,#0F743C 100%);padding:28px 16px 36px;text-align:center;position:relative;overflow:hidden;margin-bottom:0}
.profile-header::before{content:'';position:absolute;top:-50%;right:-50%;width:200%;height:200%;background:radial-gradient(circle,rgba(255,255,255,0.12) 0%,transparent 70%);animation:rotate 20s linear infinite}
@keyframes rotate{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}
.profile-avatar-wrap{position:relative;display:inline-block;margin-bottom:12px;z-index:1}
.profile-avatar{width:90px;height:90px;border-radius:50%;border:4px solid rgba(255,255,255,0.3);box-shadow:0 0 0 2px #fff,0 8px 24px rgba(0,0,0,0.15);object-fit:cover}
.avatar-badge{position:absolute;bottom:4px;right:4px;width:26px;height:26px;background:linear-gradient(135deg,#F99E2B,#ffc970);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:12px;border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,0.15)}
.profile-name{color:#fff;font-size:20px;font-weight:800;margin:0 0 6px;z-index:1;position:relative;text-shadow:0 2px 8px rgba(0,0,0,0.1)}
.profile-username{color:rgba(255,255,255,0.9);font-size:13px;z-index:1;position:relative;margin:0;font-weight:500;background:rgba(255,255,255,0.15);padding:4px 16px;border-radius:20px;display:inline-block;backdrop-filter:blur(10px)}

/* Wallet Overview */
.wallet-overview{display:grid;grid-template-columns:1fr 1fr;gap:12px;padding:16px;margin:0;position:relative;z-index:2;background:#f8faf8}
.wallet-card{background:#fff;border-radius:16px;padding:16px;box-shadow:0 4px 12px rgba(0,0,0,0.06);display:flex;flex-direction:column;gap:12px;position:relative;border:2px solid transparent;transition:all 0.3s}
.wallet-card:active{transform:scale(0.97);border-color:#0F743C}
.wallet-icon-wrap{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0}
.main-wallet .wallet-icon-wrap{background:linear-gradient(135deg,#e0f2fe,#bae6fd);color:#0369a1;box-shadow:0 2px 8px rgba(3,105,161,0.2)}
.hold-wallet .wallet-icon-wrap{background:linear-gradient(135deg,#fef3c7,#fde68a);color:#d97706;box-shadow:0 2px 8px rgba(217,119,6,0.2)}
.wallet-info{flex:1}
.wallet-label{font-size:11px;color:#6b7280;display:block;margin-bottom:4px;font-weight:600;text-transform:uppercase;letter-spacing:0.3px}
.wallet-amount{font-size:18px;font-weight:800;color:#1f2937;margin:0;line-height:1.2}
.wallet-action{position:absolute;top:12px;right:12px;width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#0F743C,#1a9e52);color:#fff;display:flex;align-items:center;justify-content:center;text-decoration:none;font-size:16px;box-shadow:0 2px 8px rgba(15,116,60,0.3);transition:all 0.2s}
.wallet-action:active{transform:scale(0.9)}
.wallet-badge{padding:5px 10px;border-radius:8px;font-size:10px;font-weight:700;align-self:flex-start}
.wallet-badge.active{background:linear-gradient(135deg,#d1fae5,#a7f3d0);color:#065f46}
.wallet-badge.pending{background:linear-gradient(135deg,#fef3c7,#fde68a);color:#92400e}

/* Compact Sections */
.compact-section{background:#fff;margin:0 0 12px;border-radius:0;box-shadow:none;overflow:hidden;border-bottom:1px solid #f3f4f6}
.compact-section:first-of-type{margin-top:0}
.section-header{display:flex;justify-content:space-between;align-items:center;padding:16px;cursor:pointer;user-select:none;background:#fff;transition:background 0.2s}
.section-header:active{background:#f9fafb}
.header-left{display:flex;align-items:center;gap:12px}
.header-left i{width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg,#f0f9ff,#e0f2fe);color:#0369a1;display:flex;align-items:center;justify-content:center;font-size:18px;box-shadow:0 2px 6px rgba(3,105,161,0.1)}
.header-left h3{margin:0;font-size:15px;font-weight:700;color:#1f2937}
.toggle-icon{font-size:14px;color:#9ca3af;transition:transform 0.3s}
.compact-section.active .toggle-icon{transform:rotate(180deg)}
.section-content{max-height:0;overflow:hidden;transition:max-height 0.4s ease;padding:0 16px;background:#fafbfc}
.compact-section.active .section-content{max-height:2000px;padding:16px}

/* Wallet Breakdown */
.wallet-breakdown{margin-bottom:16px}
.breakdown-item{display:flex;justify-content:space-between;align-items:center;padding:12px 14px;background:#fff;border-radius:10px;margin-bottom:8px;border:1px solid #f0f0f0}
.breakdown-item:last-child{margin-bottom:0}
.breakdown-item span{font-size:13px;color:#6b7280;font-weight:600}
.breakdown-item strong{font-size:15px;color:#1f2937;font-weight:800}

/* Transfer Box */
.transfer-box{background:linear-gradient(135deg,#f0fff4,#dcfce7);padding:16px;border-radius:12px;margin-top:16px;border:1px solid #d1fae5}
.transfer-info{margin-bottom:10px}
.transfer-info span{font-size:11px;color:#059669;display:block;margin-bottom:4px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px}
.transfer-info h4{margin:0;font-size:24px;font-weight:900;color:#0F743C}
.transfer-calc{border-top:1.5px solid #a7f3d0;padding-top:10px;margin-top:10px}
.calc-row{display:flex;justify-content:space-between;padding:6px 0;font-size:13px;color:#6b7280;font-weight:600}
.calc-row.total{border-top:1.5px solid #a7f3d0;margin-top:8px;padding-top:10px}
.calc-row.total span{font-size:14px;color:#1f2937;font-weight:700}
.calc-row.total strong{font-size:18px;color:#16a34a;font-weight:900}
.btn-transfer{width:100%;padding:14px;background:linear-gradient(135deg,#0F743C,#1a9e52);color:#fff;border:none;border-radius:10px;font-size:15px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;margin-top:12px;font-family:inherit;box-shadow:0 4px 12px rgba(15,116,60,0.3);transition:all 0.2s}
.btn-transfer:active{background:linear-gradient(135deg,#0d6334,#16a34a);transform:scale(0.97)}
.transfer-status{display:flex;align-items:center;gap:10px;padding:12px 14px;background:#fef3c7;border-radius:10px;margin-top:12px;border:1px solid #fde68a}
.transfer-status i{color:#f59e0b;font-size:18px}
.transfer-status span{font-size:13px;color:#1f2937;font-weight:600}

/* Action Grid */
.action-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}
.action-item{display:flex;flex-direction:column;align-items:center;gap:8px;padding:14px 10px;background:#fff;border-radius:12px;text-decoration:none;color:#1f2937;font-size:12px;font-weight:700;transition:all 0.2s;border:1px solid #f0f0f0}
.action-item:active{background:#f9fafb;transform:scale(0.96);border-color:#e5e7eb}
.action-item i{width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,#e0f2fe,#bae6fd);color:#0369a1;display:flex;align-items:center;justify-content:center;font-size:18px;box-shadow:0 2px 8px rgba(3,105,161,0.15)}
.action-item.logout i{background:linear-gradient(135deg,#fee2e2,#fecaca);color:#dc2626;box-shadow:0 2px 8px rgba(220,38,38,0.15)}

/* Form Styles */
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px}
.form-group{margin-bottom:14px}
.form-group label{display:block;font-size:12px;color:#6b7280;margin-bottom:8px;font-weight:700;text-transform:uppercase;letter-spacing:0.3px}
.form-control{width:100%;padding:12px 14px;border:2px solid #e5e7eb;border-radius:10px;font-size:14px;font-family:inherit;background:#fff;transition:all 0.2s;font-weight:500}
.form-control:focus{outline:none;border-color:#0F743C;box-shadow:0 0 0 4px rgba(15,116,60,0.08);background:#fafffe}
.form-control.readonly{background:#f9fafb;color:#9ca3af;border-color:#f0f0f0}
.btn-submit{width:100%;padding:14px;background:linear-gradient(135deg,#0F743C,#1a9e52);color:#fff;border:none;border-radius:10px;font-size:15px;font-weight:800;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:10px;margin-top:10px;font-family:inherit;box-shadow:0 4px 15px rgba(15,116,60,0.3);transition:all 0.2s}
.btn-submit:active{background:linear-gradient(135deg,#0d6334,#16a34a);transform:scale(0.98);box-shadow:0 2px 8px rgba(15,116,60,0.2)}

@media (max-width:400px){
    .wallet-overview{grid-template-columns:1fr}
    .action-grid{grid-template-columns:repeat(2,1fr)}
}
</style>
@endpush

@push('script')
<script>
function toggleSection(id){
    const section = event.currentTarget.closest('.compact-section');
    section.classList.toggle('active');
}
</script>
@endpush
