@extends($activeTemplate.'layouts.master_mobile')
@section('main-content')

<!-- Page Header -->
<div class="page-header-simple security-header">
    <div class="ph-icon"><i class="fas fa-shield-alt"></i></div>
    <h2>২ ফ্যাক্টর সিকিউরিটি</h2>
    <p>Google Authenticator দিয়ে অ্যাকাউন্ট সুরক্ষিত করুন</p>
</div>

@if(!auth()->user()->ts)
<!-- QR Code Section -->
<div class="section-block">
    <div class="qr-section">
        <div class="qr-wrapper">
            <img src="{{$qrCodeUrl}}" alt="QR Code" class="qr-image">
        </div>
        <p class="qr-instruction">Google Authenticator অ্যাপে এই QR কোড স্ক্যান করুন</p>
    </div>
    
    <div class="divider-text">অথবা</div>
    
    <div class="setup-key-box">
        <label class="form-lbl"><i class="fas fa-key me-2"></i>Setup Key</label>
        <div class="key-input-wrap">
            <input type="text" value="{{$secret}}" class="form-input referralURL" id="setupKey" readonly>
            <button type="button" class="copy-btn" id="copyBoard">
                <i class="fas fa-copy"></i>
            </button>
        </div>
    </div>
</div>

<!-- Enable Form -->
<div class="section-block">
    <div class="section-head">
        <h3><i class="fas fa-lock me-2"></i>২FA সক্রিয় করুন</h3>
    </div>
    <form action="{{ route('user.twofactor.enable') }}" method="POST">
        @csrf
        <input type="hidden" name="key" value="{{$secret}}">
        <div class="form-group">
            <label class="form-lbl">@lang('Google Authenticator OTP')</label>
            <div class="input-wrap">
                <i class="fas fa-mobile-alt"></i>
                <input type="text" class="form-input" name="code" required placeholder="৬ ডিজিট কোড লিখুন">
            </div>
        </div>
        <button type="submit" class="btn-submit enable-btn">
            <i class="fas fa-check-circle me-2"></i>@lang('Enable 2FA')
        </button>
    </form>
</div>

<!-- Help Info -->
<div class="info-card">
    <div class="info-icon"><i class="fas fa-mobile-alt"></i></div>
    <div class="info-content">
        <strong>Google Authenticator</strong>
        <p>মোবাইলে অ্যাপটি ইন্সটল করুন এবং QR কোড স্ক্যান করে অ্যাকাউন্ট যোগ করুন।</p>
        <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2&hl=en" target="_blank" class="download-link">
            <i class="fab fa-google-play me-1"></i>ডাউনলোড করুন
        </a>
    </div>
</div>

@else
<!-- Enabled Status -->
<div class="section-block">
    <div class="status-card enabled">
        <div class="status-icon"><i class="fas fa-check-circle"></i></div>
        <h3>২FA সক্রিয় আছে</h3>
        <p>আপনার অ্যাকাউন্ট Google Authenticator দ্বারা সুরক্ষিত</p>
    </div>
</div>

<!-- Disable Form -->
<div class="section-block">
    <div class="section-head">
        <h3><i class="fas fa-unlock me-2"></i>২FA নিষ্ক্রিয় করুন</h3>
    </div>
    <form action="{{route('user.twofactor.disable')}}" method="POST">
        @csrf
        <input type="hidden" name="key" value="{{$secret}}">
        <div class="form-group">
            <label class="form-lbl">@lang('Google Authenticator OTP')</label>
            <div class="input-wrap">
                <i class="fas fa-mobile-alt"></i>
                <input type="text" class="form-input" name="code" required placeholder="৬ ডিজিট কোড লিখুন">
            </div>
        </div>
        <button type="submit" class="btn-submit disable-btn">
            <i class="fas fa-power-off me-2"></i>@lang('Disable 2FA')
        </button>
    </form>
</div>

<!-- Warning Info -->
<div class="info-card warning">
    <div class="info-icon"><i class="fas fa-exclamation-triangle"></i></div>
    <div class="info-content">
        <strong>সতর্কতা</strong>
        <p>২FA নিষ্ক্রিয় করলে আপনার অ্যাকাউন্টের সুরক্ষা কমে যাবে।</p>
    </div>
</div>
@endif

<div style="height: 20px;"></div>
@endsection

@push('style')
<style>
.page-header-simple{background:linear-gradient(135deg,var(--purple) 0%,var(--crimson) 100%);padding:30px 20px;text-align:center}
.page-header-simple.security-header{background:linear-gradient(135deg,#1e40af 0%,#3b82f6 100%)}
.ph-icon{width:60px;height:60px;background:rgba(255,255,255,0.2);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-size:24px;color:var(--white)}
.page-header-simple h2{color:var(--white);font-size:22px;font-weight:700;margin-bottom:6px}
.page-header-simple p{color:rgba(255,255,255,0.8);font-size:13px;margin:0}

.section-block{background:var(--white);margin:16px;border-radius:20px;padding:20px;box-shadow:0 2px 10px rgba(0,0,0,0.04)}
.section-head{margin-bottom:16px}
.section-head h3{font-size:16px;font-weight:700;color:var(--dark-text);display:flex;align-items:center;margin:0}
.section-head h3 i{color:#3b82f6}

.qr-section{text-align:center;margin-bottom:16px}
.qr-wrapper{background:#f8f9fa;border-radius:16px;padding:20px;display:inline-block;margin-bottom:12px}
.qr-image{max-width:180px;height:auto;display:block}
.qr-instruction{font-size:12px;color:var(--gray);margin:0}

.divider-text{display:flex;align-items:center;gap:12px;color:var(--gray);font-size:12px;margin:16px 0}
.divider-text::before,.divider-text::after{content:'';flex:1;height:1px;background:#e5e7eb}

.setup-key-box{margin-top:16px}
.form-lbl{display:flex;align-items:center;font-size:12px;color:var(--gray);margin-bottom:8px;font-weight:500}
.form-lbl i{color:#3b82f6}
.key-input-wrap{display:flex;gap:10px}
.form-input{flex:1;padding:14px;border:1.5px solid #e5e7eb;border-radius:12px;font-size:13px;font-family:inherit;background:#f8f9fa}
.copy-btn{width:50px;background:#3b82f6;border:none;border-radius:12px;color:var(--white);font-size:16px;cursor:pointer}

.form-group{margin-bottom:16px}
.input-wrap{position:relative}
.input-wrap > i{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--gray);font-size:14px}
.input-wrap .form-input{padding:14px 14px 14px 42px;background:var(--white)}
.input-wrap .form-input:focus{outline:none;border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,0.1)}

.btn-submit{width:100%;padding:15px;color:var(--white);border:none;border-radius:14px;font-size:15px;font-weight:700;font-family:inherit;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all 0.3s}
.btn-submit.enable-btn{background:linear-gradient(135deg,#16a34a,#059669);box-shadow:0 4px 15px rgba(22,163,74,0.3)}
.btn-submit.disable-btn{background:linear-gradient(135deg,var(--crimson),#be123c);box-shadow:0 4px 15px rgba(205,17,59,0.3)}
.btn-submit:active{transform:scale(0.98)}

.status-card{text-align:center;padding:24px}
.status-card.enabled .status-icon{width:70px;height:70px;background:#dcfce7;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:32px;color:#16a34a}
.status-card h3{font-size:18px;font-weight:700;color:var(--dark-text);margin-bottom:6px}
.status-card p{font-size:13px;color:var(--gray);margin:0}

.info-card{display:flex;gap:12px;background:var(--white);margin:0 16px;padding:14px;border-radius:14px;border-left:4px solid #3b82f6}
.info-card.warning{border-left-color:var(--crimson)}
.info-icon{width:40px;height:40px;background:#dbeafe;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#3b82f6;font-size:16px;flex-shrink:0}
.info-card.warning .info-icon{background:#fce7f3;color:var(--crimson)}
.info-content{font-size:12px;color:var(--gray)}
.info-content strong{display:block;color:var(--dark-text);margin-bottom:4px;font-size:13px}
.info-content p{margin:0 0 8px}
.download-link{display:inline-flex;align-items:center;padding:6px 12px;background:#3b82f6;color:var(--white);border-radius:8px;text-decoration:none;font-size:11px;font-weight:600}
</style>
@endpush

@push('script')
<script>
document.getElementById('copyBoard')?.addEventListener('click', function(){
    var copyText = document.getElementById("setupKey");
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
