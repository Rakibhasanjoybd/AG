@extends($activeTemplate.'layouts.master_mobile')
@section('main-content')

<div class="page-header-simple razorpay-header">
    <div class="ph-icon"><i class="fas fa-rupee-sign"></i></div>
    <h2>@lang('Razorpay Payment')</h2>
    <p>নিরাপদ পেমেন্ট গেটওয়ে</p>
</div>

<div class="section-block text-center">
    <div class="payment-preview">
        <div class="pp-row">
            <span>প্রদেয় পরিমাণ</span>
            <strong class="text-primary">{{showAmount($deposit->final_amo)}} {{__($deposit->method_currency)}}</strong>
        </div>
        <div class="pp-row highlight">
            <span>আপনি পাবেন</span>
            <strong class="text-success">{{showAmount($deposit->amount)}} {{__($general->cur_text)}}</strong>
        </div>
    </div>
    
    <form action="{{$data->url}}" method="{{$data->method}}" id="razorpay-form">
        <input type="hidden" custom="{{$data->custom}}" name="hidden">
        <script src="{{$data->checkout_js}}"
            @foreach($data->val as $key=>$value)
            data-{{$key}}="{{$value}}"
            @endforeach >
        </script>
    </form>
</div>

<div class="info-card">
    <div class="info-icon"><i class="fas fa-lock"></i></div>
    <div class="info-content">
        <strong>নিরাপদ লেনদেন</strong>
        <p>Razorpay দ্বারা আপনার পেমেন্ট সম্পূর্ণ নিরাপদ</p>
    </div>
</div>

<div style="height: 20px;"></div>
@endsection

@push('style')
<style>
.page-header-simple{background:linear-gradient(135deg,var(--purple) 0%,var(--crimson) 100%);padding:30px 20px;text-align:center}
.page-header-simple.razorpay-header{background:linear-gradient(135deg,#072654 0%,#3395ff 100%)}
.ph-icon{width:60px;height:60px;background:rgba(255,255,255,0.2);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-size:24px;color:var(--white)}
.page-header-simple h2{color:var(--white);font-size:22px;font-weight:700;margin-bottom:6px}
.page-header-simple p{color:rgba(255,255,255,0.8);font-size:13px;margin:0}

.section-block{background:var(--white);margin:16px;border-radius:20px;padding:24px;box-shadow:0 2px 10px rgba(0,0,0,0.04)}

.payment-preview{margin-bottom:20px}
.pp-row{display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px dashed #e5e7eb;font-size:14px}
.pp-row:last-child{border-bottom:none}
.pp-row span{color:var(--gray)}
.pp-row strong{font-weight:700}
.pp-row.highlight{background:rgba(22,163,74,0.06);margin:8px -24px -24px;padding:16px 24px;border-radius:0 0 20px 20px;border:none}
.text-primary{color:var(--purple)!important}
.text-success{color:#16a34a!important}

#razorpay-form input[type="submit"],.razorpay-payment-button{width:100%;padding:15px!important;background:linear-gradient(135deg,#072654,#3395ff)!important;color:var(--white)!important;border:none!important;border-radius:14px!important;font-size:15px!important;font-weight:700!important;font-family:inherit!important;cursor:pointer!important;display:flex!important;align-items:center!important;justify-content:center!important;box-shadow:0 4px 15px rgba(51,149,255,0.4)!important;margin-top:10px!important}

.info-card{display:flex;gap:12px;background:var(--white);margin:0 16px;padding:14px;border-radius:14px;border-left:4px solid #3395ff}
.info-icon{width:40px;height:40px;background:rgba(51,149,255,0.1);border-radius:10px;display:flex;align-items:center;justify-content:center;color:#3395ff;font-size:16px;flex-shrink:0}
.info-content{font-size:12px;color:var(--gray)}
.info-content strong{display:block;color:var(--dark-text);margin-bottom:2px;font-size:13px}
.info-content p{margin:0}
</style>
@endpush

@push('script')
<script>
(function ($) {
    "use strict";
    $('input[type="submit"]').addClass("razorpay-payment-button");
})(jQuery);
</script>
@endpush
