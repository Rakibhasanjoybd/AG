@extends($activeTemplate.'layouts.master_mobile')
@section('main-content')
@php
    $faqs = \App\Models\Faq::active()->ordered()->get();
@endphp

<!-- Hero Header -->
<div class="faq-hero">
    <div class="faq-hero-bg">
        <svg viewBox="0 0 100 100" preserveAspectRatio="none">
            <circle cx="90" cy="10" r="30" fill="rgba(255,255,255,0.1)"/>
            <circle cx="10" cy="90" r="20" fill="rgba(255,255,255,0.08)"/>
        </svg>
    </div>
    <div class="faq-hero-icon">
        <i class="fas fa-question-circle"></i>
    </div>
    <h2 class="faq-hero-title">@lang('Frequently Asked Questions')</h2>
    <p class="faq-hero-subtitle">@lang('Find answers to common questions')</p>
</div>

<div class="faq-content">
    <div class="faq-list">
        @forelse($faqs as $key => $faq)
        <div class="faq-card">
            <div class="faq-card-header" data-bs-toggle="collapse" data-bs-target="#faq{{ $key }}" aria-expanded="{{ $key == 0 ? 'true' : 'false' }}">
                <div class="faq-number">{{ str_pad($key + 1, 2, '0', STR_PAD_LEFT) }}</div>
                <span class="faq-question-text">{{ $faq->question }}</span>
                <div class="faq-toggle">
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>
            <div id="faq{{ $key }}" class="collapse {{ $key == 0 ? 'show' : '' }}" data-bs-parent=".faq-list">
                <div class="faq-card-body">
                    <div class="faq-answer-content">
                        {!! nl2br(e($faq->answer)) !!}
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="faq-empty">
            <div class="faq-empty-icon">
                <i class="fas fa-question-circle"></i>
            </div>
            <h4>@lang('No FAQs Available')</h4>
            <p>@lang('Check back later for frequently asked questions')</p>
        </div>
        @endforelse
    </div>

    <!-- Support Card -->
    <div class="support-card">
        <div class="support-card-bg">
            <svg viewBox="0 0 100 100" preserveAspectRatio="none">
                <circle cx="85" cy="15" r="25" fill="rgba(255,255,255,0.1)"/>
            </svg>
        </div>
        <div class="support-icon">
            <i class="fas fa-headset"></i>
        </div>
        <div class="support-text">
            <h4>@lang("Can't find your answer?")</h4>
            <p>@lang('Contact our support team for personalized assistance')</p>
        </div>
        <a href="{{ route('ticket') }}" class="support-btn">
            <i class="fas fa-envelope"></i>
            @lang('Contact Support')
        </a>
    </div>
</div>
@endsection

@push('style')
<style>
/* FAQ Hero */
.faq-hero {
    background: linear-gradient(135deg, #7e57c2, #9575cd);
    padding: 32px 20px;
    text-align: center;
    position: relative;
    overflow: hidden;
}
.faq-hero-bg {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
}
.faq-hero-bg svg {
    width: 100%;
    height: 100%;
}
.faq-hero-icon {
    width: 70px;
    height: 70px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
    position: relative;
}
.faq-hero-icon i {
    font-size: 32px;
    color: #fff;
}
.faq-hero-title {
    color: #fff;
    font-size: 22px;
    font-weight: 700;
    margin-bottom: 8px;
    position: relative;
}
.faq-hero-subtitle {
    color: rgba(255,255,255,0.85);
    font-size: 14px;
    margin: 0;
    position: relative;
}

/* FAQ Content */
.faq-content {
    padding: 20px 16px;
}

/* FAQ Cards */
.faq-list {
    margin-bottom: 20px;
}
.faq-card {
    background: #fff;
    border-radius: 16px;
    margin-bottom: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    overflow: hidden;
}
.faq-card-header {
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
}
.faq-card-header:hover {
    background: rgba(126,87,194,0.04);
}
.faq-number {
    width: 36px;
    height: 36px;
    background: linear-gradient(135deg, #7e57c2, #9575cd);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 13px;
    font-weight: 700;
    flex-shrink: 0;
}
.faq-question-text {
    flex: 1;
    font-size: 14px;
    font-weight: 600;
    color: #1a1a2e;
    line-height: 1.4;
}
.faq-toggle {
    width: 28px;
    height: 28px;
    background: rgba(126,87,194,0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    flex-shrink: 0;
}
.faq-toggle i {
    font-size: 12px;
    color: #7e57c2;
    transition: transform 0.3s ease;
}
.faq-card-header[aria-expanded="true"] .faq-toggle {
    background: #7e57c2;
}
.faq-card-header[aria-expanded="true"] .faq-toggle i {
    color: #fff;
    transform: rotate(180deg);
}
.faq-card-body {
    padding: 0 16px 16px;
}
.faq-answer-content {
    padding: 16px;
    background: rgba(126,87,194,0.05);
    border-radius: 12px;
    font-size: 13px;
    color: #555;
    line-height: 1.7;
    border-left: 3px solid #7e57c2;
}

/* Empty State */
.faq-empty {
    text-align: center;
    padding: 48px 20px;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
}
.faq-empty-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, rgba(126,87,194,0.1), rgba(149,117,205,0.1));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
}
.faq-empty-icon i {
    font-size: 36px;
    color: #7e57c2;
}
.faq-empty h4 {
    color: #1a1a2e;
    font-size: 18px;
    margin-bottom: 8px;
}
.faq-empty p {
    color: #888;
    font-size: 14px;
    margin: 0;
}

/* Support Card */
.support-card {
    background: linear-gradient(135deg, #0F743C, #12925a);
    border-radius: 16px;
    padding: 24px;
    position: relative;
    overflow: hidden;
}
.support-card-bg {
    position: absolute;
    top: 0;
    right: 0;
    width: 150px;
    height: 150px;
}
.support-card-bg svg {
    width: 100%;
    height: 100%;
}
.support-icon {
    width: 50px;
    height: 50px;
    background: rgba(255,255,255,0.2);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 16px;
    position: relative;
}
.support-icon i {
    font-size: 22px;
    color: #fff;
}
.support-text {
    position: relative;
    margin-bottom: 16px;
}
.support-text h4 {
    color: #fff;
    font-size: 17px;
    font-weight: 700;
    margin-bottom: 6px;
}
.support-text p {
    color: rgba(255,255,255,0.8);
    font-size: 13px;
    margin: 0;
    line-height: 1.5;
}
.support-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #fff;
    color: #0F743C;
    padding: 12px 24px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    position: relative;
}
.support-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.2);
    color: #0F743C;
}
.support-btn i {
    font-size: 16px;
}
</style>
@endpush

