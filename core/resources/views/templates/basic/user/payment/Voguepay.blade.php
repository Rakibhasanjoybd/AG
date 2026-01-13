@extends($activeTemplate.'layouts.master_mobile')
@section('main-content')

<!-- Page Header -->
<div class="page-header-simple" style="background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);">
    <div class="header-content">
        <div class="header-icon">
            <i class="fas fa-wallet"></i>
        </div>
        <h1 class="header-title">@lang('Voguepay পেমেন্ট')</h1>
        <p class="header-subtitle">@lang('নিরাপদ অনলাইন পেমেন্ট')</p>
    </div>
</div>

<!-- Payment Preview -->
<div class="section-block">
    <div class="preview-card">
        <div class="preview-header">
            <div class="gateway-logo" style="background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);">
                <i class="fas fa-wallet"></i>
            </div>
            <span class="preview-label">@lang('পেমেন্ট সামারি')</span>
        </div>
        <div class="preview-body">
            <div class="preview-row">
                <span class="preview-label-text">@lang('পেমেন্ট করতে হবে')</span>
                <span class="preview-value" style="color: #f97316;">{{showAmount($deposit->final_amo)}} {{__($deposit->method_currency)}}</span>
            </div>
            <div class="preview-row">
                <span class="preview-label-text">@lang('জমা হবে')</span>
                <span class="preview-value highlight">{{showAmount($deposit->amount)}} {{__($general->cur_text)}}</span>
            </div>
        </div>
        
        <button type="button" class="pay-btn" id="btn-confirm">
            <i class="fas fa-lock"></i>
            @lang('Pay Now')
        </button>
    </div>
</div>

<!-- Security Info -->
<div class="section-block">
    <div class="info-card security-info">
        <div class="info-icon">
            <i class="fas fa-shield-alt"></i>
        </div>
        <div class="info-content">
            <span class="info-title">@lang('নিরাপদ পেমেন্ট')</span>
            <span class="info-desc">@lang('আপনার তথ্য Voguepay দ্বারা সুরক্ষিত')</span>
        </div>
    </div>
</div>

@endsection

@push('style')
<style>
    .page-header-simple {
        padding: 30px 20px;
        text-align: center;
        margin-bottom: 0;
    }
    .header-content {
        color: white;
    }
    .header-icon {
        width: 60px;
        height: 60px;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
    }
    .header-icon i {
        font-size: 1.5rem;
        color: white;
    }
    .header-title {
        font-size: 1.4rem;
        font-weight: 700;
        margin-bottom: 5px;
        color: white;
    }
    .header-subtitle {
        font-size: 0.9rem;
        opacity: 0.9;
        color: rgba(255,255,255,0.9);
        margin: 0;
    }
    .section-block {
        padding: 0 15px;
        margin-bottom: 20px;
    }
    .section-block:first-of-type {
        margin-top: 20px;
    }
    .preview-card {
        background: white;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    .preview-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #f0f0f0;
    }
    .gateway-logo {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .gateway-logo i {
        color: white;
        font-size: 1.2rem;
    }
    .preview-label {
        font-weight: 600;
        color: #333;
    }
    .preview-body {
        display: flex;
        flex-direction: column;
        gap: 15px;
        margin-bottom: 20px;
    }
    .preview-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .preview-label-text {
        color: #666;
        font-size: 0.95rem;
    }
    .preview-value {
        font-weight: 700;
        font-size: 1rem;
    }
    .preview-value.highlight {
        color: #10b981;
    }
    .pay-btn {
        width: 100%;
        padding: 16px;
        border: none;
        border-radius: 12px;
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        color: white;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .pay-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(249, 115, 22, 0.4);
    }
    .info-card {
        background: white;
        border-radius: 16px;
        padding: 16px;
        display: flex;
        align-items: center;
        gap: 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    .info-card.security-info {
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        border: 1px solid #bbf7d0;
    }
    .info-icon {
        width: 45px;
        height: 45px;
        background: #10b981;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .info-icon i {
        color: white;
        font-size: 1.2rem;
    }
    .info-content {
        display: flex;
        flex-direction: column;
    }
    .info-title {
        font-weight: 600;
        color: #166534;
        font-size: 0.95rem;
    }
    .info-desc {
        color: #15803d;
        font-size: 0.85rem;
    }
</style>
@endpush
@push('script')
    <script src="//pay.voguepay.com/js/voguepay.js"></script>
    <script>
        "use strict";
        var closedFunction = function() {
        }
        var successFunction = function(transaction_id) {
            window.location.href = '{{ route(gatewayRedirectUrl()) }}';
        }
        var failedFunction=function(transaction_id) {
            window.location.href = '{{ route(gatewayRedirectUrl()) }}' ;
        }
        function pay(item, price) {
            //Initiate voguepay inline payment
            Voguepay.init({
                v_merchant_id: "{{ $data->v_merchant_id}}",
                total: price,
                notify_url: "{{ $data->notify_url }}",
                cur: "{{$data->cur}}",
                merchant_ref: "{{ $data->merchant_ref }}",
                memo:"{{$data->memo}}",
                recurrent: true,
                frequency: 10,
                developer_code: '60a4ecd9bbc77',
                custom: "{{ $data->custom }}",
                customer: {
                  name: 'Customer name',
                  country: 'Country',
                  address: 'Customer address',
                  city: 'Customer city',
                  state: 'Customer state',
                  zipcode: 'Customer zip/post code',
                  email: 'example@example.com',
                  phone: 'Customer phone'
                },
                closed:closedFunction,
                success:successFunction,
                failed:failedFunction
            });
        }
        (function ($) {
            $('#btn-confirm').on('click', function (e) {
                e.preventDefault();
                pay('Buy', {{ $data->Buy }});
            });
        })(jQuery);
    </script>
@endpush
