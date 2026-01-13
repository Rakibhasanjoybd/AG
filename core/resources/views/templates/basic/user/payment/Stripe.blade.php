@extends($activeTemplate.'layouts.master_mobile')
@section('main-content')

<!-- Page Header -->
<div class="page-header-simple stripe-header">
    <div class="ph-icon"><i class="fab fa-cc-stripe"></i></div>
    <h2>@lang('Stripe Payment')</h2>
    <p>কার্ড দিয়ে পেমেন্ট করুন</p>
</div>

<!-- Card Form -->
<div class="section-block">
    <div class="card-wrapper mb-4"></div>
    
    <form role="form" id="payment-form" method="{{$data->method}}" action="{{$data->url}}">
        @csrf
        <input type="hidden" value="{{$data->track}}" name="track">
        
        <div class="form-group">
            <label class="form-lbl"><i class="fas fa-user me-2"></i>@lang('Name on Card')</label>
            <div class="input-wrap">
                <i class="fas fa-font"></i>
                <input type="text" class="form-input" name="name" value="{{ old('name') }}" placeholder="কার্ডধারীর নাম" required autocomplete="off" autofocus/>
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-lbl"><i class="fas fa-credit-card me-2"></i>@lang('Card Number')</label>
            <div class="input-wrap">
                <i class="fas fa-credit-card"></i>
                <input type="tel" class="form-input" name="cardNumber" autocomplete="off" value="{{ old('cardNumber') }}" placeholder="0000 0000 0000 0000" required autofocus/>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-col">
                <label class="form-lbl">@lang('Expiry Date')</label>
                <input type="tel" class="form-input" name="cardExpiry" value="{{ old('cardExpiry') }}" placeholder="MM/YY" autocomplete="off" required/>
            </div>
            <div class="form-col">
                <label class="form-lbl">@lang('CVC Code')</label>
                <input type="tel" class="form-input" name="cardCVC" value="{{ old('cardCVC') }}" placeholder="123" autocomplete="off" required/>
            </div>
        </div>
        
        <button class="btn-submit" type="submit">
            <i class="fas fa-lock me-2"></i>@lang('Pay Now')
        </button>
    </form>
</div>

<!-- Security Note -->
<div class="info-card">
    <div class="info-icon"><i class="fas fa-shield-alt"></i></div>
    <div class="info-content">
        <strong>নিরাপদ পেমেন্ট</strong>
        <p>আপনার কার্ড তথ্য SSL এনক্রিপশন দ্বারা সুরক্ষিত</p>
    </div>
</div>

<div style="height: 20px;"></div>
@endsection

@push('style')
<style>
.page-header-simple{background:linear-gradient(135deg,var(--purple) 0%,var(--crimson) 100%);padding:30px 20px;text-align:center}
.page-header-simple.stripe-header{background:linear-gradient(135deg,#635bff 0%,#7c3aed 100%)}
.ph-icon{width:60px;height:60px;background:rgba(255,255,255,0.2);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-size:24px;color:var(--white)}
.page-header-simple h2{color:var(--white);font-size:22px;font-weight:700;margin-bottom:6px}
.page-header-simple p{color:rgba(255,255,255,0.8);font-size:13px;margin:0}

.section-block{background:var(--white);margin:16px;border-radius:20px;padding:20px;box-shadow:0 2px 10px rgba(0,0,0,0.04)}

.card-wrapper{margin:0 auto}

.form-group{margin-bottom:16px}
.form-row{display:flex;gap:12px;margin-bottom:16px}
.form-col{flex:1}
.form-lbl{display:flex;align-items:center;font-size:12px;color:var(--gray);margin-bottom:8px;font-weight:500}
.form-lbl i{color:#635bff}
.input-wrap{position:relative}
.input-wrap > i{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--gray);font-size:14px}
.form-input{width:100%;padding:14px 14px 14px 42px;border:1.5px solid #e5e7eb;border-radius:12px;font-size:14px;font-family:inherit;background:var(--white);transition:all 0.2s}
.form-col .form-input{padding:14px}
.form-input:focus{outline:none;border-color:#635bff;box-shadow:0 0 0 3px rgba(99,91,255,0.1)}

.btn-submit{width:100%;padding:15px;background:linear-gradient(135deg,#635bff,#7c3aed);color:var(--white);border:none;border-radius:14px;font-size:15px;font-weight:700;font-family:inherit;cursor:pointer;margin-top:8px;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 15px rgba(99,91,255,0.3)}
.btn-submit:active{transform:scale(0.98)}

.info-card{display:flex;gap:12px;background:var(--white);margin:0 16px;padding:14px;border-radius:14px;border-left:4px solid #635bff}
.info-icon{width:40px;height:40px;background:rgba(99,91,255,0.1);border-radius:10px;display:flex;align-items:center;justify-content:center;color:#635bff;font-size:16px;flex-shrink:0}
.info-content{font-size:12px;color:var(--gray)}
.info-content strong{display:block;color:var(--dark-text);margin-bottom:2px;font-size:13px}
.info-content p{margin:0}
</style>
@endpush

@push('script')
<script src="{{ asset('assets/global/js/card.js') }}"></script>
<script>
(function ($) {
    "use strict";
    var card = new Card({
        form: '#payment-form',
        container: '.card-wrapper',
        formSelectors: {
            numberInput: 'input[name="cardNumber"]',
            expiryInput: 'input[name="cardExpiry"]',
            cvcInput: 'input[name="cardCVC"]',
            nameInput: 'input[name="name"]'
        }
    });
})(jQuery);
</script>
@endpush
