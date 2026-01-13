@extends($activeTemplate.'layouts.master_mobile')
@section('main-content')

<!-- Page Header -->
<div class="page-header-simple" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
    <div class="header-content">
        <div class="header-icon">
            <i class="fas fa-plus-circle"></i>
        </div>
        <h1 class="header-title">@lang('বিজ্ঞাপন তৈরি করুন')</h1>
        <p class="header-subtitle">@lang('নতুন বিজ্ঞাপন যোগ করুন')</p>
    </div>
</div>

<!-- Back Button -->
<div class="section-block">
    <a href="{{ route('user.ptc.ads') }}" class="back-btn">
        <i class="fas fa-arrow-left"></i>
        @lang('আমার বিজ্ঞাপনসমূহ')
    </a>
</div>

<!-- Form -->
<div class="section-block">
    <div class="form-card">
        <form role="form" method="POST" action="{{ route("user.ptc.store") }}" enctype="multipart/form-data">
            @csrf
            
            <div class="form-group">
                <label class="form-label">@lang('বিজ্ঞাপনের শিরোনাম')</label>
                <input type="text" name="title" class="form-control" value="{{ old('title') }}" required placeholder="@lang('শিরোনাম লিখুন')">
            </div>

            <div class="form-group">
                <label class="form-label">@lang('বিজ্ঞাপনের ধরন')</label>
                <select class="form-select" id="ads_type" name="ads_type" required>
                    <option value="1" {{ old('ads_type')==1?'selected':'' }}>@lang('লিংক / URL')</option>
                    <option value="2" {{ old('ads_type')==2?'selected':'' }}>@lang('ব্যানার / ছবি')</option>
                    <option value="3" {{ old('ads_type')==3?'selected':'' }}>@lang('স্ক্রিপ্ট / কোড')</option>
                    <option value="4" {{ old('ads_type')==4?'selected':'' }}>@lang('ইউটিউব এম্বেড লিংক')</option>
                </select>
                <div class="price-info">
                    @lang('প্রতি বিজ্ঞাপনের মূল্য') <span class="price-per-ad">0</span> {{ $general->cur_text }}
                </div>
            </div>

            <div class="form-group" id="websiteLink">
                <label class="form-label">@lang('লিংক')</label>
                <input type="text" name="website_link" class="form-control" value="{{ old('website_link') }}" placeholder="@lang('http://example.com')">
            </div>

            <div class="form-group" id="youtube">
                <label class="form-label">@lang('ইউটিউব এম্বেড লিংক')</label>
                <input type="text" name="youtube" class="form-control" value="{{ old('youtube') }}" placeholder="@lang('https://www.youtube.com/embed/your_code')">
            </div>

            <div class="form-group d-none" id="bannerImage">
                <label class="form-label">@lang('ব্যানার ছবি')</label>
                <input type="file" class="form-control" name="banner_image">
            </div>

            <div class="form-group d-none" id="script">
                <label class="form-label">@lang('স্ক্রিপ্ট কোড')</label>
                <textarea name="script" class="form-control" rows="4">{{ old('script') }}</textarea>
            </div>

            <div class="form-row">
                <div class="form-group half">
                    <label class="form-label">@lang('সময়কাল')</label>
                    <div class="input-with-addon">
                        <input type="number" name="duration" class="form-control" value="{{ old('duration') }}" required>
                        <span class="addon">@lang('সেকেন্ড')</span>
                    </div>
                </div>

                <div class="form-group half">
                    <label class="form-label">@lang('সর্বোচ্চ দেখানো')</label>
                    <div class="input-with-addon">
                        <input type="number" name="max_show" class="form-control" value="{{ old('max_show') }}" required>
                        <span class="addon">@lang('বার')</span>
                    </div>
                </div>
            </div>

            <div class="total-price-box">
                <span class="label">@lang('মোট খরচ হবে')</span>
                <span class="value"><span class="total-price">0</span> {{ $general->cur_text }}</span>
            </div>

            <button type="submit" class="submit-btn">
                <i class="fas fa-paper-plane"></i>
                @lang('সাবমিট করুন')
            </button>
        </form>
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
    .header-content { color: white; }
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
    .header-icon i { font-size: 1.5rem; color: white; }
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
        margin-bottom: 15px;
    }
    .section-block:first-of-type { margin-top: 20px; }
    .back-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #8b5cf6;
        font-weight: 500;
        text-decoration: none;
        font-size: 0.95rem;
    }
    .back-btn:hover { color: #7c3aed; }
    .form-card {
        background: white;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    .form-group {
        margin-bottom: 18px;
    }
    .form-label {
        display: block;
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
        font-size: 0.9rem;
    }
    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        font-size: 1rem;
        transition: border-color 0.2s;
    }
    .form-control:focus {
        outline: none;
        border-color: #8b5cf6;
    }
    .form-select {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        font-size: 1rem;
        background: white;
    }
    .price-info {
        margin-top: 8px;
        padding: 10px 12px;
        background: #fef3c7;
        border-radius: 8px;
        color: #92400e;
        font-size: 0.85rem;
        font-weight: 500;
    }
    .form-row {
        display: flex;
        gap: 12px;
    }
    .form-group.half { flex: 1; }
    .input-with-addon {
        display: flex;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        overflow: hidden;
    }
    .input-with-addon .form-control {
        border: none;
        border-radius: 0;
    }
    .input-with-addon .addon {
        padding: 12px;
        background: #f3f4f6;
        color: #666;
        font-size: 0.85rem;
        white-space: nowrap;
        display: flex;
        align-items: center;
    }
    .total-price-box {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        border-radius: 12px;
        padding: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    .total-price-box .label { color: #92400e; font-weight: 500; }
    .total-price-box .value {
        font-weight: 700;
        color: #b45309;
        font-size: 1.1rem;
    }
    .submit-btn {
        width: 100%;
        padding: 15px;
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(139, 92, 246, 0.4);
    }
</style>
@endpush


@push('script')
<script>
    (function($){
        "use strict";
        var price = 0
        $('#ads_type').change(function(){
            var adType = $(this).val();
            if (adType == 1) {
                $("#websiteLink").removeClass('d-none');
                $("#bannerImage").addClass('d-none');
                $("#script").addClass('d-none');
                $("#youtube").addClass('d-none');
                price = {{ @$general->ads_setting->ad_price->url }}
            } else if (adType == 2) {
                $("#bannerImage").removeClass('d-none');
                $("#websiteLink").addClass('d-none');
                $("#script").addClass('d-none');
                $("#youtube").addClass('d-none');
                price = {{ @$general->ads_setting->ad_price->image }}
            } else if(adType == 3) {
                $("#bannerImage").addClass('d-none');
                $("#websiteLink").addClass('d-none');
                $("#script").removeClass('d-none');
                $("#youtube").addClass('d-none');
                price = {{ @$general->ads_setting->ad_price->script }}
            } else {
                $("#bannerImage").addClass('d-none');
                $("#websiteLink").addClass('d-none');
                $("#script").addClass('d-none');
                $("#youtube").removeClass('d-none');
                price = {{ @$general->ads_setting->ad_price->youtube ?? 0}}
            }
            $('.price-per-ad').text(price);
            $('[name=max_show]').trigger('input');
        }).change();

        $('[name=max_show]').on('input', function () {
            var maxShow = $(this).val();
            var totalPrice = price * maxShow;
            $('.total-price').text(totalPrice);
        });

    })(jQuery);
</script>
@endpush
