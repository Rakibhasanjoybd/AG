@extends($activeTemplate.'layouts.master_mobile')
@section('main-content')

<!-- Page Header -->
<div class="page-header-simple" style="background: linear-gradient(135deg, #635bff 0%, #8b5cf6 100%);">
    <div class="header-content">
        <div class="header-icon">
            <i class="fas fa-credit-card"></i>
        </div>
        <h1 class="header-title">@lang('Stripe পেমেন্ট')</h1>
        <p class="header-subtitle">@lang('নিরাপদে কার্ডে পেমেন্ট করুন')</p>
    </div>
</div>

<!-- Payment Preview -->
<div class="section-block">
    <div class="preview-card">
        <div class="preview-header">
            <i class="fab fa-stripe" style="font-size: 2rem; color: #635bff;"></i>
            <span class="preview-label">@lang('পেমেন্ট সামারি')</span>
        </div>
        <div class="preview-body">
            <div class="preview-row">
                <span class="preview-label-text">@lang('পেমেন্ট করতে হবে')</span>
                <span class="preview-value" style="color: #635bff;">{{showAmount($deposit->final_amo)}} {{__($deposit->method_currency)}}</span>
            </div>
            <div class="preview-row">
                <span class="preview-label-text">@lang('জমা হবে')</span>
                <span class="preview-value highlight">{{showAmount($deposit->amount)}} {{__($general->cur_text)}}</span>
            </div>
        </div>
    </div>
</div>

<!-- Stripe Button Section -->
<div class="section-block">
    <form action="{{$data->url}}" method="{{$data->method}}" class="stripe-form-wrap">
        <script src="{{$data->src}}"
            class="stripe-button"
            @foreach($data->val as $key=> $value)
            data-{{$key}}="{{$value}}"
            @endforeach
        >
        </script>
    </form>
</div>

<!-- Security Info -->
<div class="section-block">
    <div class="info-card security-info">
        <div class="info-icon">
            <i class="fas fa-shield-alt"></i>
        </div>
        <div class="info-content">
            <span class="info-title">@lang('নিরাপদ পেমেন্ট')</span>
            <span class="info-desc">@lang('আপনার কার্ড তথ্য Stripe দ্বারা সুরক্ষিত')</span>
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
    .preview-label {
        font-weight: 600;
        color: #333;
    }
    .preview-body {
        display: flex;
        flex-direction: column;
        gap: 15px;
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
    .stripe-form-wrap {
        background: white;
        border-radius: 16px;
        padding: 25px;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    .stripe-form-wrap .stripe-button-el {
        width: 100% !important;
        margin: 0 !important;
        border-radius: 12px !important;
        padding: 15px !important;
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
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        (function ($) {
            "use strict";
            $('button[type="submit"]').addClass("stripe-submit-btn").css({
                'width': '100%',
                'padding': '15px 25px',
                'border-radius': '12px',
                'font-weight': '600',
                'font-size': '1rem',
                'margin-top': '10px'
            });
        })(jQuery);
    </script>
@endpush
