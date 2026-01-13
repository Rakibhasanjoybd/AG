@extends($activeTemplate.'layouts.master_mobile')
@section('main-content')

<!-- Page Header -->
<div class="page-header-simple flutter-header">
    <div class="ph-icon"><i class="fas fa-money-bill-wave"></i></div>
    <h2>@lang('Flutterwave Payment')</h2>
    <p>নিরাপদ পেমেন্ট গেটওয়ে</p>
</div>

<!-- Payment Summary -->
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
    
    <button type="button" class="btn-submit flutter-btn" id="btn-confirm" onClick="payWithRave()">
        <i class="fas fa-bolt me-2"></i>@lang('Pay Now')
    </button>
</div>

<!-- Security Note -->
<div class="info-card">
    <div class="info-icon"><i class="fas fa-lock"></i></div>
    <div class="info-content">
        <strong>নিরাপদ লেনদেন</strong>
        <p>Flutterwave দ্বারা আপনার পেমেন্ট সম্পূর্ণ নিরাপদ</p>
    </div>
</div>

<div style="height: 20px;"></div>
@endsection

@push('style')
<style>
.page-header-simple{background:linear-gradient(135deg,var(--purple) 0%,var(--crimson) 100%);padding:30px 20px;text-align:center}
.page-header-simple.flutter-header{background:linear-gradient(135deg,#f5a623 0%,#ff6b00 100%)}
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

.btn-submit{width:100%;padding:15px;background:linear-gradient(135deg,var(--purple),var(--crimson));color:var(--white);border:none;border-radius:14px;font-size:15px;font-weight:700;font-family:inherit;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 15px rgba(82,0,106,0.3)}
.btn-submit.flutter-btn{background:linear-gradient(135deg,#f5a623,#ff6b00);box-shadow:0 4px 15px rgba(245,166,35,0.4)}
.btn-submit:active{transform:scale(0.98)}

.info-card{display:flex;gap:12px;background:var(--white);margin:0 16px;padding:14px;border-radius:14px;border-left:4px solid #f5a623}
.info-icon{width:40px;height:40px;background:rgba(245,166,35,0.1);border-radius:10px;display:flex;align-items:center;justify-content:center;color:#f5a623;font-size:16px;flex-shrink:0}
.info-content{font-size:12px;color:var(--gray)}
.info-content strong{display:block;color:var(--dark-text);margin-bottom:2px;font-size:13px}
.info-content p{margin:0}
</style>
@endpush

@push('script')
<script src="https://api.ravepay.co/flwv3-pug/getpaidx/api/flwpbf-inline.js"></script>
<script>
"use strict"
var btn = document.querySelector("#btn-confirm");
btn.setAttribute("type", "button");
const API_publicKey = "{{$data->API_publicKey}}";
function payWithRave() {
    var x = getpaidSetup({
        PBFPubKey: API_publicKey,
        customer_email: "{{$data->customer_email}}",
        amount: "{{$data->amount }}",
        customer_phone: "{{$data->customer_phone}}",
        currency: "{{$data->currency}}",
        txref: "{{$data->txref}}",
        onclose: function () {},
        callback: function (response) {
            var txref = response.tx.txRef;
            var status = response.tx.status;
            var chargeResponse = response.tx.chargeResponseCode;
            if (chargeResponse == "00" || chargeResponse == "0") {
                window.location = '{{ url('ipn/flutterwave') }}/' + txref + '/' + status;
            } else {
                window.location = '{{ url('ipn/flutterwave') }}/' + txref + '/' + status;
            }
        }
    });
}
</script>
@endpush
