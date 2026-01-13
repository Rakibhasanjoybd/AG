@extends($activeTemplate.'layouts.master_mobile')
@section('main-content')

<!-- Profile Header -->
<div class="profile-header">
    <div class="profile-avatar-wrap">
        <img src="{{ getImage(getFilePath('userProfile').'/'.$user->image, getFileSize('userProfile')) }}" alt="{{ $user->username }}" class="profile-avatar">
        <span class="avatar-badge"><i class="fas fa-check"></i></span>
    </div>
    <h3 class="profile-name">{{ $user->fullname }}</h3>
    <p class="profile-username">{{ $user->user_id }}</p>
</div>

<!-- Quick Links -->
<div class="profile-quick-links">
    <a href="{{ route('user.change.password') }}" class="pq-link">
        <i class="fas fa-lock"></i>
        <span>পাসওয়ার্ড</span>
    </a>
    <a href="{{ route('user.twofactor') }}" class="pq-link">
        <i class="fas fa-shield-alt"></i>
        <span>সিকিউরিটি</span>
    </a>
    <a href="{{ route('ticket') }}" class="pq-link">
        <i class="fas fa-headset"></i>
        <span>সাপোর্ট</span>
    </a>
    <a href="{{ route('user.logout') }}" class="pq-link logout">
        <i class="fas fa-sign-out-alt"></i>
        <span>লগআউট</span>
    </a>
</div>

<!-- Wallet Cards Section -->
<div class="wallet-cards-container">
    <div class="wallet-card hold-wallet">
        <div class="wallet-card-header">
            <div class="wallet-icon">
                <i class="fas fa-lock"></i>
            </div>
            <div class="wallet-badge">
                <i class="fas fa-shield-alt"></i>
            </div>
        </div>
        <div class="wallet-card-body">
            <div class="wallet-label">হোল্ড ব্যালেন্স</div>
            <div class="wallet-label-en">Hold Balance</div>
            <div class="wallet-amount">{{ showAmount($user->hold_balance) }}</div>
            <div class="wallet-currency">{{ $general->cur_text }}</div>
        </div>
        <div class="wallet-card-footer">
            <span class="wallet-status">
                <i class="fas fa-clock"></i> রানিং অ্যাক্টিভ সেটেলিং চলছে...
            </span>
        </div>
        <div class="wallet-shine"></div>
    </div>

    <div class="wallet-card transfer-wallet">
        <div class="wallet-card-header">
            <div class="wallet-icon">
                <i class="fas fa-wallet"></i>
            </div>
            <div class="wallet-badge">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
        <div class="wallet-card-body">
            <div class="wallet-label">ট্রান্সফারেবল</div>
            <div class="wallet-label-en">Transferable</div>
            <div class="wallet-amount">{{ showAmount($user->balance) }}</div>
            <div class="wallet-currency">{{ $general->cur_text }}</div>
        </div>
        <div class="wallet-card-footer">
            <a href="{{ route('user.withdraw') }}" class="wallet-action-btn">
                <i class="fas fa-arrow-right"></i> উত্তোলন করুন
            </a>
        </div>
        <div class="wallet-shine"></div>
    </div>
</div>

<!-- Profile Form -->
<div class="section-block">
    <div class="section-head">
        <h3><i class="fas fa-user-edit me-2"></i>প্রোফাইল তথ্য</h3>
    </div>
    <form action="" method="post" enctype="multipart/form-data">
        @csrf
        <div class="form-row">
            <div class="form-col">
                <label class="form-lbl">@lang('First Name')</label>
                <input type="text" class="form-input" name="firstname" value="{{$user->firstname}}" required>
            </div>
            <div class="form-col">
                <label class="form-lbl">@lang('Last Name')</label>
                <input type="text" class="form-input" name="lastname" value="{{$user->lastname}}" required>
            </div>
        </div>

        <div class="form-group">
            <label class="form-lbl">@lang('Mobile Number')</label>
            <input class="form-input readonly" value="{{$user->mobile}}" readonly>
        </div>

        <div class="form-group">
            <label class="form-lbl">@lang('Address')</label>
            <input type="text" class="form-input" name="address" value="{{@$user->address->address}}">
        </div>

        <div class="form-row">
            <div class="form-col">
                <label class="form-lbl">@lang('City')</label>
                <input type="text" class="form-input" name="city" value="{{@$user->address->city}}">
            </div>
            <div class="form-col">
                <label class="form-lbl">@lang('State')</label>
                <input type="text" class="form-input" name="state" value="{{@$user->address->state}}">
            </div>
        </div>

        <div class="form-row">
            <div class="form-col">
                <label class="form-lbl">@lang('Zip Code')</label>
                <input type="text" class="form-input" name="zip" value="{{@$user->address->zip}}">
            </div>
            <div class="form-col">
                <label class="form-lbl">@lang('Country')</label>
                <input class="form-input readonly" value="{{@$user->address->country}}" disabled>
            </div>
        </div>

        <button type="submit" class="btn-submit">
            <i class="fas fa-save me-2"></i>@lang('Save Changes')
        </button>
    </form>
</div>

<div style="height: 20px;"></div>
@endsection

@push('style')
<style>
.profile-header{background:linear-gradient(135deg, #52006A 0%, #7B2D8E 100%);padding:30px 20px;text-align:center;position:relative;overflow:hidden}
.profile-header::before{content:'';position:absolute;top:-50%;right:-50%;width:200%;height:200%;background:radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);animation:rotate 20s linear infinite}
@keyframes rotate{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}
.profile-avatar-wrap{position:relative;display:inline-block;margin-bottom:12px;z-index:1}
.profile-avatar{width:90px;height:90px;border-radius:50%;border:4px solid #FFD700;object-fit:cover;box-shadow:0 8px 20px rgba(255,215,0,0.3)}
.avatar-badge{position:absolute;bottom:4px;right:4px;width:24px;height:24px;background:linear-gradient(135deg, #FFD700 0%, #FFA500 100%);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#52006A;font-size:11px;box-shadow:0 3px 8px rgba(0,0,0,0.2)}
.profile-name{color:#fff;font-size:20px;font-weight:700;margin-bottom:4px;z-index:1;position:relative}
.profile-username{color:rgba(255,255,255,0.85);font-size:13px;z-index:1;position:relative}

.profile-quick-links{display:flex;justify-content:space-around;padding:16px;background:#fff;margin:-20px 16px 16px;border-radius:16px;box-shadow:0 4px 15px rgba(0,0,0,0.08)}
.pq-link{display:flex;flex-direction:column;align-items:center;text-decoration:none;color:#333;font-size:11px;font-weight:500;gap:6px;transition:transform 0.2s}
.pq-link:hover{transform:translateY(-2px)}
.pq-link i{width:40px;height:40px;background:#f3e8ff;color:#52006A;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:16px;transition:all 0.3s}
.pq-link:hover i{background:#52006A;color:#fff}
.pq-link.logout i{background:#fce7f3;color:#dc2626}
.pq-link.logout:hover i{background:#dc2626;color:#fff}

/* Wallet Cards Container */
.wallet-cards-container{display:grid;grid-template-columns:1fr 1fr;gap:12px;padding:0 16px;margin-bottom:16px}

/* Wallet Card Base Styles */
.wallet-card{position:relative;border-radius:20px;padding:20px 16px;overflow:hidden;box-shadow:0 8px 25px rgba(0,0,0,0.15);transition:all 0.3s ease;min-height:180px;display:flex;flex-direction:column;border:2px solid transparent}
.wallet-card::before{content:'';position:absolute;top:0;left:0;right:0;bottom:0;background:inherit;opacity:0.9;z-index:0}
.wallet-card:active{transform:scale(0.98)}

/* Hold Wallet - Dark Purple/Black Theme */
.hold-wallet{background:linear-gradient(135deg, #1a1a1a 0%, #2d1f3d 50%, #1a1a1a 100%);border-color:rgba(255,215,0,0.3)}
.hold-wallet::after{content:'';position:absolute;top:-50%;right:-50%;width:100%;height:100%;background:radial-gradient(circle, rgba(130,71,229,0.2) 0%, transparent 70%);pointer-events:none}

/* Transfer Wallet - Crimson/Red Theme */
.transfer-wallet{background:linear-gradient(135deg, #8B0000 0%, #DC143C 50%, #8B0000 100%);border-color:rgba(255,215,0,0.3)}
.transfer-wallet::after{content:'';position:absolute;top:-50%;left:-50%;width:100%;height:100%;background:radial-gradient(circle, rgba(255,100,100,0.2) 0%, transparent 70%);pointer-events:none}

/* Wallet Card Header */
.wallet-card-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;position:relative;z-index:1}
.wallet-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;color:#FFD700;background:rgba(255,255,255,0.1);backdrop-filter:blur(10px);box-shadow:0 4px 12px rgba(0,0,0,0.2)}
.wallet-badge{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;color:#FFD700;background:rgba(255,255,255,0.15);backdrop-filter:blur(10px)}

/* Wallet Card Body */
.wallet-card-body{flex:1;position:relative;z-index:1;text-align:center;margin:8px 0}
.wallet-label{font-size:13px;font-weight:600;color:rgba(255,255,255,0.9);margin-bottom:2px;letter-spacing:0.3px}
.wallet-label-en{font-size:10px;color:rgba(255,255,255,0.6);margin-bottom:8px;text-transform:uppercase;letter-spacing:0.5px}
.wallet-amount{font-size:26px;font-weight:800;color:#FFD700;margin-bottom:2px;text-shadow:0 2px 8px rgba(0,0,0,0.3);letter-spacing:-0.5px}
.wallet-currency{font-size:11px;color:rgba(255,255,255,0.7);font-weight:500}

/* Wallet Card Footer */
.wallet-card-footer{position:relative;z-index:1;margin-top:auto;padding-top:8px}
.wallet-status{display:flex;align-items:center;justify-content:center;gap:6px;font-size:10px;color:rgba(255,255,255,0.75);background:rgba(255,255,255,0.1);padding:6px 10px;border-radius:20px;backdrop-filter:blur(10px)}
.wallet-status i{font-size:9px;animation:pulse 2s ease-in-out infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:0.5}}

.wallet-action-btn{display:flex;align-items:center;justify-content:center;gap:6px;width:100%;padding:8px;background:rgba(255,255,255,0.2);backdrop-filter:blur(10px);border-radius:12px;color:#FFD700;font-size:12px;font-weight:600;text-decoration:none;transition:all 0.3s;border:1px solid rgba(255,215,0,0.3)}
.wallet-action-btn:hover{background:rgba(255,215,0,0.3);transform:translateY(-2px);box-shadow:0 4px 12px rgba(255,215,0,0.2)}
.wallet-action-btn i{font-size:11px;transition:transform 0.3s}
.wallet-action-btn:hover i{transform:translateX(3px)}

/* Shine Effect */
.wallet-shine{position:absolute;top:-50%;left:-50%;width:40%;height:200%;background:linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);transform:rotate(45deg);animation:shine 6s ease-in-out infinite;pointer-events:none;z-index:2}
@keyframes shine{0%,100%{left:-50%}50%{left:150%}}

/* Responsive */
@media (max-width: 400px){
    .wallet-cards-container{grid-template-columns:1fr;gap:10px}
    .wallet-card{min-height:160px}
    .wallet-amount{font-size:22px}
}

.section-block{background:#fff;margin:0 16px 16px;border-radius:20px;padding:16px;box-shadow:0 2px 10px rgba(0,0,0,0.04)}
.section-head{margin-bottom:16px}
.section-head h3{font-size:16px;font-weight:700;color:#333;display:flex;align-items:center}

.form-group{margin-bottom:14px}
.form-row{display:flex;gap:12px;margin-bottom:14px}
.form-col{flex:1}
.form-lbl{display:block;font-size:12px;color:#666;margin-bottom:6px;font-weight:500}
.form-input{width:100%;padding:12px 14px;border:1.5px solid #e5e7eb;border-radius:12px;font-size:14px;font-family:inherit;background:#fff;transition:all 0.2s}
.form-input:focus{outline:none;border-color:#52006A;box-shadow:0 0 0 3px rgba(82,0,106,0.1)}
.form-input.readonly{background:#f8f9fa;color:#666}

.btn-submit{width:100%;padding:14px;background:linear-gradient(135deg, #52006A 0%, #7B2D8E 100%);color:#fff;border:none;border-radius:14px;font-size:15px;font-weight:700;font-family:inherit;cursor:pointer;margin-top:6px;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(82,0,106,0.3);transition:all 0.3s}
.btn-submit:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(82,0,106,0.4)}
.btn-submit:active{transform:scale(0.98)}
</style>
@endpush
