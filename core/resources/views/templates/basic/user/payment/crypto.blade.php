@extends($activeTemplate.'layouts.master_mobile')

@section('main-content')

<!-- Page Header -->
<div class="page-header-simple crypto-header">
    <div class="ph-icon"><i class="fab fa-bitcoin"></i></div>
    <h2>@lang('Crypto Payment')</h2>
    <p>স্ক্যান করে পেমেন্ট করুন</p>
</div>

<!-- Payment Info -->
<div class="section-block text-center">
    <div class="crypto-qr-wrap">
        <img src="{{$data->img}}" alt="QR Code" class="crypto-qr">
    </div>
    
    <div class="crypto-amount">
        <span class="label">প্রদেয় পরিমাণ</span>
        <h3 class="amount">{{ $data->amount }} {{__($data->currency)}}</h3>
    </div>
    
    <div class="crypto-address">
        <span class="label">পেমেন্ট ঠিকানা</span>
        <div class="address-box">
            <input type="text" value="{{ $data->sendto }}" id="cryptoAddress" readonly class="address-input">
            <button type="button" class="copy-btn" onclick="copyAddress()">
                <i class="fas fa-copy"></i>
            </button>
        </div>
    </div>
    
    <div class="crypto-note">
        <i class="fas fa-exclamation-triangle"></i>
        <span>সঠিক পরিমাণ এবং ঠিকানায় পেমেন্ট করুন। ভুল পেমেন্ট ফেরত পাওয়া যায় না।</span>
    </div>
</div>

<div style="height: 20px;"></div>
@endsection

@push('style')
<style>
.page-header-simple{background:linear-gradient(135deg,var(--purple) 0%,var(--crimson) 100%);padding:30px 20px;text-align:center}
.page-header-simple.crypto-header{background:linear-gradient(135deg,#f7931a 0%,#ffab00 100%)}
.ph-icon{width:60px;height:60px;background:rgba(255,255,255,0.2);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-size:24px;color:var(--white)}
.page-header-simple h2{color:var(--white);font-size:22px;font-weight:700;margin-bottom:6px}
.page-header-simple p{color:rgba(255,255,255,0.8);font-size:13px;margin:0}

.section-block{background:var(--white);margin:16px;border-radius:20px;padding:24px;box-shadow:0 2px 10px rgba(0,0,0,0.04)}

.crypto-qr-wrap{background:#f8f9fa;border-radius:16px;padding:20px;display:inline-block;margin-bottom:20px}
.crypto-qr{max-width:200px;height:auto;display:block}

.crypto-amount{margin-bottom:20px}
.crypto-amount .label{font-size:12px;color:var(--gray);display:block;margin-bottom:6px}
.crypto-amount .amount{font-size:22px;font-weight:700;color:#f7931a;margin:0}

.crypto-address{margin-bottom:20px}
.crypto-address .label{font-size:12px;color:var(--gray);display:block;margin-bottom:8px}
.address-box{display:flex;gap:10px}
.address-input{flex:1;padding:12px;border:1.5px solid #e5e7eb;border-radius:12px;font-size:11px;font-family:monospace;background:#f8f9fa}
.copy-btn{width:50px;background:#f7931a;border:none;border-radius:12px;color:var(--white);font-size:16px;cursor:pointer}

.crypto-note{display:flex;align-items:flex-start;gap:10px;padding:12px;background:#fef9c3;border-radius:12px;font-size:12px;color:#92400e;text-align:left}
.crypto-note i{flex-shrink:0;margin-top:2px}
</style>
@endpush

@push('script')
<script>
function copyAddress() {
    var copyText = document.getElementById("cryptoAddress");
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    document.execCommand("copy");
    if(typeof iziToast !== 'undefined') {
        iziToast.success({message: "কপি হয়েছে!", position: "topRight"});
    }
}
</script>
@endpush
