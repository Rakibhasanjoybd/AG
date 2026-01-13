@extends($activeTemplate.'layouts.master_mobile')
@section('main-content')
@php
    $banners = getContent('banner.element', false, null, true);

    try {
        $announcements = \App\Models\Announcement::active()->latest()->get();
    } catch (\Throwable $e) {
        $announcements = collect();
    }

    try {
        $audioPlayer = \App\Models\AudioPlayer::active()->first();
    } catch (\Throwable $e) {
        $audioPlayer = null;
    }

    $spotlights = \App\Models\DailySpotlight::active()->ordered()->limit(8)->get();
    $tutorials = \App\Models\VideoTutorial::active()->ordered()->limit(3)->get();
    
    try {
        $brands = \App\Models\AgcoBrand::active()->ordered()->get();
    } catch (\Throwable $e) {
        $brands = collect();
    }
    
    $blogPosts = getContent('blog.element', false, 5);
    
    $todayClicks = $user->clicks->where('view_date', Date('Y-m-d'))->count();
    $remainingClicks = $user->daily_limit - $todayClicks;
    $referralCount = \App\Models\User::where('ref_by', $user->id)->count();
@endphp

<!-- 1. Header Slide Banner - Framed Flat Rectangle -->
@php $banners = getContent('banner.element', false, null, true); @endphp
<div class="banner-frame-wrapper">
    <div class="banner-slider-new">
        <div class="banner-slider" id="bannerSlider">
            @if($banners && $banners->count() > 0)
                @foreach($banners as $key => $banner)
                <div class="banner-slide {{ $key == 0 ? 'active' : '' }}">
                    <a href="{{ @$banner->data_values->url ?? '#' }}" target="{{ @$banner->data_values->url ? '_blank' : '_self' }}" @if(@$banner->data_values->url) rel="noopener noreferrer" @endif>
                        <img
                            src="{{ @$banner->data_values->image ? asset('assets/images/frontend/banner/' . @$banner->data_values->image) : asset('assets/images/default.png') }}"
                            alt="Banner"
                            width="480"
                            height="180"
                            decoding="async"
                            loading="{{ $key == 0 ? 'eager' : 'lazy' }}"
                            fetchpriority="{{ $key == 0 ? 'high' : 'auto' }}"
                        >
                    </a>
                </div>
                @endforeach
            @else
                <div class="banner-slide active">
                    <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#0F743C,#F99E2B);color:#fff;font-weight:700;">
                        @lang('Header Banner')
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- 2. User Greeting & Quick Actions Bar -->
<div class="user-greeting-bar">
    <div class="ugb-left">
        <div class="ugb-greeting">
            <span class="ugb-hi">Hi, {{ $user->firstname ?? $user->username }}</span>
            <div class="ugb-plan">
                <div class="plan-tag {{ $user->plan ? 'vip' : '' }}">
                    <i class="fas fa-crown"></i>
                    {{ $user->plan ? $user->plan->name : 'ফ্রি সদস্যতা' }}
                </div>
            </div>
        </div>
        <div class="ugb-balance-row">
            <span class="ugb-currency">৳</span>
            <span class="ugb-amount" id="ugbAmount">{{ showAmount($user->balance) }}</span>
            <span class="ugb-amount-hidden" id="ugbAmountHidden" style="display:none;">*.**</span>
            <button class="ugb-eye" id="ugbEyeBtn" type="button" aria-pressed="false">
                <i class="fas fa-eye" id="ugbEyeIcon" aria-hidden="true"></i>
                <span class="visually-hidden" id="ugbEyeLabel">Hide balance</span>
            </button>
        </div>
    </div>
    <div class="ugb-right">
        <a href="{{ route('user.transactions') }}" class="ugb-action">
            <div class="ugb-action-icon history">
                <i class="fas fa-history"></i>
            </div>
            <span>ইতিহাস</span>
        </a>
        <a href="{{ route('user.withdraw') }}" class="ugb-action">
            <div class="ugb-action-icon withdraw">
                <i class="fas fa-minus"></i>
            </div>
            <span>উত্তোলন</span>
        </a>
        <a href="{{ route('user.deposit') }}" class="ugb-action">
            <div class="ugb-action-icon deposit">
                <i class="fas fa-plus"></i>
            </div>
            <span>জমা</span>
        </a>
    </div>
</div>

<!-- 3. Announcement + Audio Combined Strip -->
<div class="top-info-strip">
    @if($announcements->count() > 0)
    <div class="announcement-bar">
        <div class="ab-icon"><i class="fas fa-bullhorn"></i></div>
        <div class="ab-marquee">
            <div class="ab-track">
                @foreach($announcements as $announcement)
                <span class="ab-item"><span class="ab-dot"></span>{{ $announcement->title }}</span>
                @endforeach
                @foreach($announcements as $announcement)
                <span class="ab-item"><span class="ab-dot"></span>{{ $announcement->title }}</span>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    @if($audioPlayer)
    <div class="audio-mini-player">
        <div class="amp-wave">
            <span></span><span></span><span></span><span></span>
        </div>
        <div class="amp-title-wrap">
            <div class="amp-title-scroll">
                <span>{{ $audioPlayer->title }}</span>
                <span>{{ $audioPlayer->title }}</span>
            </div>
        </div>
        <button class="amp-btn" id="audioBtn" type="button" aria-pressed="false">
            <i class="fas fa-play" aria-hidden="true"></i>
            <span class="visually-hidden" id="audioBtnLabel">Play audio</span>
        </button>
        <audio id="audioEl" preload="none" controlsList="nodownload" oncontextmenu="return false;" {{ $audioPlayer->autoplay ? 'autoplay' : '' }} {{ $audioPlayer->loop ? 'loop' : '' }}></audio>
        <input type="hidden" id="audioSrc" value="{{ asset('assets/audio/'.$audioPlayer->audio_file) }}">
    </div>
    @endif
</div>

<!-- Compact Services + Stats Section -->
<div class="compact-dashboard-section">
    <!-- Stats Cards Row -->
    <div class="compact-stats-row">
        <div class="csr-item primary">
            <div class="csr-icon"><i class="fas fa-mouse-pointer"></i></div>
            <div class="csr-data">
                <strong>{{ $todayClicks }}/{{ $user->daily_limit }}</strong>
                <span>আজকের ক্লিক</span>
            </div>
        </div>
        <div class="csr-item warning">
            <div class="csr-icon"><i class="fas fa-coins"></i></div>
            <div class="csr-data">
                <strong>৳{{ showAmount($user->deposits->sum('amount')) }}</strong>
                <span>মোট ডিপোজিট</span>
            </div>
        </div>
        <div class="csr-item error">
            <div class="csr-icon"><i class="fas fa-user-friends"></i></div>
            <div class="csr-data">
                <strong>{{ $referralCount }}</strong>
                <span>রেফারেল</span>
            </div>
        </div>
    </div>

    <!-- Compact Service Grid -->
    <div class="compact-services-grid">
        <a href="{{ route('user.ptc.index') }}" class="cs-item primary">
            <div class="cs-icon"><i class="fas fa-play-circle"></i></div>
            <span>আয় করুন</span>
        </a>
        <button type="button" onclick="showRedBagFromMenu()" class="cs-item error" aria-label="Open Daily Red Bag">
            <div class="cs-icon"><i class="fas fa-gift"></i></div>
            <span>রেড ব্যাগ</span>
            <span class="cs-badge" id="redBagServiceBadge" style="display:none;">🔥</span>
        </button>
        <a href="{{ route('user.referred') }}" class="cs-item warning">
            <div class="cs-icon"><i class="fas fa-users"></i></div>
            <span>রেফার</span>
        </a>
        <a href="{{ route('user.video.tutorials') }}" class="cs-item secondary">
            <div class="cs-icon"><i class="fas fa-video"></i></div>
            <span>টিউটোরিয়াল</span>
        </a>
        <a href="{{ route('user.faq') }}" class="cs-item purple">
            <div class="cs-icon"><i class="fas fa-question-circle"></i></div>
            <span>FAQ</span>
        </a>
        <a href="#" data-bs-toggle="modal" data-bs-target="#allServicesModal" class="cs-item gold">
            <div class="cs-icon"><i class="fas fa-th"></i></div>
            <span>সব দেখুন</span>
        </a>
    </div>

    <!-- Pathao Style Spotlight Section -->
    @if($spotlights->count() > 0)
    <div class="pathao-spotlight">
        <div class="ps-header">
            <h4>AGCO Spotlight</h4>
            <a href="{{ route('user.spotlights') }}">সব দেখুন</a>
        </div>
        <div class="ps-slider-wrap">
            <div class="ps-slider" id="spotlightSlider">
                @php $chunks = $spotlights->chunk(4); @endphp
                @foreach($chunks as $chunkIndex => $chunk)
                <div class="ps-slide {{ $chunkIndex == 0 ? 'active' : '' }}">
                    <div class="ps-grid">
                        @foreach($chunk as $spotIndex => $spotlight)
                        @php $globalIndex = ($chunkIndex * 4) + $spotIndex; @endphp
                        <button type="button" class="ps-card spotlight-story-trigger" data-spotlight-index="{{ $globalIndex }}" aria-label="Open spotlight: {{ $spotlight->title }}">
                            <img src="{{ getImage(getFilePath('spotlight').'/'.$spotlight->image, '200x120') }}" alt="{{ $spotlight->title }}" width="200" height="120" loading="lazy" decoding="async">
                            <div class="ps-card-ring"></div>
                        </button>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
            @if($chunks->count() > 1)
            <div class="ps-dots">
                @foreach($chunks as $i => $chunk)
                <span class="ps-dot {{ $i == 0 ? 'active' : '' }}" data-index="{{ $i }}"></span>
                @endforeach
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- FB Story Style Spotlight Viewer Modal -->
    <div class="spotlight-story-viewer" id="spotlightStoryViewer">
        <div class="ssv-overlay"></div>
        <div class="ssv-container">
            <div class="ssv-header">
                <div class="ssv-progress-bar">
                    @foreach($spotlights as $i => $sp)
                    <div class="ssv-progress-segment" data-index="{{ $i }}">
                        <div class="ssv-progress-fill"></div>
                    </div>
                    @endforeach
                </div>
                <div class="ssv-info">
                    <div class="ssv-avatar">
                        <img src="{{ getImage(getFilePath('logoIcon') . '/logo.png') }}" alt="AGCO">
                    </div>
                    <div class="ssv-details">
                        <span class="ssv-title"></span>
                        <span class="ssv-time">এখনই</span>
                    </div>
                </div>
                <button class="ssv-close" id="ssvClose" type="button" aria-label="Close spotlight viewer">
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
            </div>
            <div class="ssv-content">
                <div class="ssv-image-wrap">
                    <img src="" alt="" class="ssv-image" id="ssvImage">
                </div>
                <div class="ssv-description" id="ssvDescription"></div>
            </div>
            <div class="ssv-nav">
                <div class="ssv-nav-prev" id="ssvPrev"><i class="fas fa-chevron-left"></i></div>
                <div class="ssv-nav-next" id="ssvNext"><i class="fas fa-chevron-right"></i></div>
            </div>
            <div class="ssv-footer">
                <a href="#" class="ssv-link-btn" id="ssvLinkBtn" target="_blank">
                    <i class="fas fa-external-link-alt"></i> বিস্তারিত দেখুন
                </a>
            </div>
        </div>
    </div>

    <!-- Spotlight Data for JS -->
    @php
        $spotlightDataArray = $spotlights->map(function($s) {
            return [
                'id' => $s->id,
                'title' => $s->title,
                'description' => $s->description,
                'link' => $s->link,
                'image' => getImage(getFilePath('spotlight').'/'.$s->image)
            ];
        })->values()->toArray();
    @endphp
    <script type="application/json" id="spotlightData">{!! json_encode($spotlightDataArray) !!}</script>

    <!-- Compact Action Banner -->
    <div class="compact-action-banner">
        <button class="cab-btn" type="button" onclick="copyReferralLink()">
            <i class="fas fa-copy"></i> <span>লিংক কপি</span>
        </button>
        <a href="https://wa.me/?text={{ urlencode('AGCO তে যুক্ত হয়ে আয় করুন! ' . route('user.register', ['reference' => $user->referral_code])) }}" target="_blank" rel="noopener noreferrer" class="cab-btn wa">
            <i class="fab fa-whatsapp"></i> <span>শেয়ার</span>
        </a>
        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('user.register', ['reference' => $user->referral_code])) }}" target="_blank" rel="noopener noreferrer" class="cab-btn fb">
            <i class="fab fa-facebook-f"></i> <span>শেয়ার</span>
        </a>
    </div>
    <input type="hidden" id="referralLink" value="{{ route('user.register', ['reference' => $user->referral_code]) }}">
</div>

<!-- Video Tutorials -->
@if($tutorials->count() > 0)
<div class="tutorials-section">
    <div class="section-head-new">
        <div class="sh-left">
            <span class="sh-emoji">🎬</span>
            <h3>ভিডিও টিউটোরিয়াল</h3>
        </div>
        <a href="{{ route('user.video.tutorials') }}" class="sh-link">সব দেখুন <i class="fas fa-chevron-right"></i></a>
    </div>
    <div class="tutorials-scroll">
        @foreach($tutorials as $tutorial)
        <a href="{{ route('user.video.tutorial.view', $tutorial->id) }}" class="tutorial-card-new">
            <div class="tc-thumb">
                <img src="{{ getImage(getFilePath('tutorial').'/'.$tutorial->thumbnail, '320x180') }}" alt="{{ $tutorial->title }}">
                <div class="tc-play">
                    <i class="fas fa-play"></i>
                </div>
                <span class="tc-lesson">পাঠ {{ $tutorial->lesson_number }}</span>
            </div>
            <div class="tc-title">{{ Str::limit($tutorial->title, 24) }}</div>
        </a>
        @endforeach
    </div>
</div>
@endif

<!-- AGCO Family Brands -->
@if($brands->count() > 0)
<div class="brands-section">
    <div class="section-head-new">
        <div class="sh-left">
            <span class="sh-emoji">🏭</span>
            <h3>AGCO পরিবারের ব্র্যান্ড</h3>
        </div>
    </div>
    <div class="brands-scroll">
        @foreach($brands as $brand)
        <div class="brand-card-new">
            <img src="{{ getImage(getFilePath('brand').'/'.$brand->image, '300x100') }}" alt="{{ $brand->name }}" loading="lazy">
        </div>
        @endforeach
    </div>
</div>
@endif

<!-- Company Blog News -->
@if($blogPosts && $blogPosts->count() > 0)
<div class="blog-section">
    <div class="section-head-new">
        <div class="sh-left">
            <span class="sh-emoji">📰</span>
            <h3>কোম্পানি খবর</h3>
        </div>
        <a href="{{ route('blog') }}" class="sh-link">সব দেখুন <i class="fas fa-chevron-right"></i></a>
    </div>
    <div class="blog-scroll">
        @foreach($blogPosts as $blog)
        <a href="{{ route('blog.details', [slug($blog->data_values->title), $blog->id]) }}" class="blog-card-compact">
            <div class="bc-thumb">
                <img src="{{ getImage('assets/images/frontend/blog/thumb_'.@$blog->data_values->image, '350x230') }}" alt="{{ __(@$blog->data_values->title) }}" loading="lazy">
                <div class="bc-date">
                    <span class="bc-day">{{ showDateTime($blog->created_at, 'd') }}</span>
                    <span class="bc-month">{{ showDateTime($blog->created_at, 'M') }}</span>
                </div>
            </div>
            <div class="bc-content">
                <h4 class="bc-title">{{ __(Str::limit(@$blog->data_values->title, 45)) }}</h4>
                <p class="bc-excerpt">{{ __(Str::limit(strip_tags(@$blog->data_values->description_nic), 60)) }}</p>
            </div>
        </a>
        @endforeach
    </div>
</div>
@endif

<!-- All Services Modal -->
<div class="modal fade" id="allServicesModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="true" data-bs-keyboard="true">
    <div class="modal-dialog modal-dialog-bottom">
        <div class="modal-content modal-colorful">
            <div class="modal-drag-handle"></div>
            <div class="modal-header">
                <h5>🎯 সব সেবা</h5>
                <button type="button" class="modal-close" data-bs-dismiss="modal" aria-label="Close"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <div class="modal-services-grid">
                    <a href="{{ route('user.faq') }}" class="ms-item">
                        <div class="ms-icon purple"><i class="fas fa-question-circle"></i></div>
                        <span>FAQ</span>
                    </a>
                    <a href="{{ route('user.video.tutorials') }}" class="ms-item">
                        <div class="ms-icon orange"><i class="fas fa-play-circle"></i></div>
                        <span>টিউটোরিয়াল</span>
                    </a>
                    <button type="button" onclick="showRedBagFromMenu()" class="ms-item" aria-label="Open daily red bag">
                        <div class="ms-icon crimson"><i class="fas fa-gift"></i></div>
                        <span>ডেইলি রেড ব্যাগ</span>
                        <span class="ms-badge" id="modalRedBagBadge" style="display:none;"></span>
                    </button>
                    <a href="{{ route('user.referred') }}" class="ms-item">
                        <div class="ms-icon gold"><i class="fas fa-users"></i></div>
                        <span>রেফার করুন</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div style="height: 60px;"></div>

{{-- Floating WhatsApp support button (Home only) --}}
@include($activeTemplate . 'partials.whatsapp_button')
@endsection

@push('style-lib')
@if($banners && $banners->count() > 0 && optional($banners->first())->data_values && optional($banners->first()->data_values)->image)
    <link rel="preload" as="image" href="{{ asset('assets/images/frontend/banner/' . $banners->first()->data_values->image) }}" fetchpriority="high">
@endif
@endpush

@push('style')
<style>
/* ========== AGCO LIGHT THEME DASHBOARD ========== */
/* Color System: Primary #0F743C, Error #DA3E2F, Warning #F99E2B, Secondary #C7662B */

:root {
    --agco-primary: #0F743C;
    --agco-primary-light: #1a9c52;
    --agco-primary-dark: #0a5a2e;
    --agco-error: #DA3E2F;
    --agco-warning: #F99E2B;
    --agco-secondary: #C7662B;
    --agco-bg: #f8f9fa;
    --agco-card-bg: #ffffff;
    --agco-text-primary: #1f2937;
    --agco-text-secondary: #6b7280;
    --agco-border: #e5e7eb;
}

/* Light Background Base */
.page-content {
    background: linear-gradient(180deg, #f0f4f8 0%, #ffffff 100%) !important;
    min-height: 100vh;
}

/* Wallet Header Card - Full Width - Light Theme */
.wallet-header-card {
    background: linear-gradient(135deg, var(--agco-primary) 0%, var(--agco-primary-light) 50%, #2ecc71 100%);
    padding: 16px;
    position: relative;
    overflow: hidden;
    border-radius: 0;
}
.whc-bg-pattern {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    opacity: 0.5;
}

/* User Greeting Bar - Premium Redesign */
.user-greeting-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, #ffffff 0%, #f8fdf9 100%);
    padding: 16px;
    margin: 0;
    border-bottom: 2px solid var(--agco-primary);
    position: relative;
}
.user-greeting-bar::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: linear-gradient(180deg, var(--agco-primary) 0%, var(--agco-warning) 100%);
}
.ugb-left {
    display: flex;
    flex-direction: column;
    gap: 6px;
    padding-left: 8px;
}
.ugb-greeting {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.ugb-hi {
    font-size: 16px;
    font-weight: 700;
    color: var(--agco-text-primary);
}
.ugb-plan {
    display: inline-flex;
}
.ugb-plan .plan-tag {
    background: linear-gradient(135deg, var(--agco-primary) 0%, var(--agco-primary-light) 100%);
    color: #fff;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 4px;
    box-shadow: 0 2px 8px rgba(15,116,60,0.3);
}
.ugb-plan .plan-tag.vip {
    background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
    color: #5D4037;
    animation: vipGlow 2s ease-in-out infinite;
}
@@keyframes vipGlow {
    0%, 100% { box-shadow: 0 2px 8px rgba(255,215,0,0.4); }
    50% { box-shadow: 0 4px 16px rgba(255,165,0,0.6); }
}
.ugb-plan .plan-tag i {
    font-size: 10px;
}
.ugb-balance-row {
    display: flex;
    align-items: center;
    gap: 4px;
}
.ugb-currency {
    font-size: 20px;
    font-weight: 800;
    color: var(--agco-primary);
}
.ugb-amount, .ugb-amount-hidden {
    font-size: 22px;
    font-weight: 900;
    color: var(--agco-text-primary);
    letter-spacing: -0.5px;
}
.ugb-eye {
    background: rgba(15,116,60,0.1);
    border: none;
    padding: 6px;
    cursor: pointer;
    color: var(--agco-primary);
    font-size: 14px;
    margin-left: 6px;
    border-radius: 50%;
    transition: all 0.2s;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.ugb-eye:hover {
    background: rgba(15,116,60,0.2);
}
.ugb-right {
    display: flex;
    gap: 10px;
}
.ugb-action {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 5px;
    text-decoration: none;
    position: relative;
}
.ugb-action-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 20px;
    transition: all 0.3s;
    position: relative;
    overflow: hidden;
}
.ugb-action-icon.history {
    background: linear-gradient(145deg, var(--agco-warning) 0%, #FF9800 100%);
    box-shadow: 0 4px 15px rgba(249,158,43,0.4);
}
.ugb-action-icon.withdraw {
    background: linear-gradient(145deg, var(--agco-error) 0%, #E53935 100%);
    box-shadow: 0 4px 15px rgba(218,62,47,0.4);
}
.ugb-action-icon.deposit {
    background: linear-gradient(145deg, var(--agco-primary) 0%, var(--agco-primary-light) 100%);
    box-shadow: 0 4px 15px rgba(15,116,60,0.4);
}
.ugb-action-icon::after {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent, rgba(255,255,255,0.2), transparent);
    transform: rotate(45deg);
    animation: shine 3s ease-in-out infinite;
}
@@keyframes shine {
    0%, 100% { transform: translateX(-100%) rotate(45deg); }
    50% { transform: translateX(100%) rotate(45deg); }
}
.ugb-action:hover .ugb-action-icon {
    transform: translateY(-3px) scale(1.05);
}
.ugb-action:hover .ugb-action-icon.history {
    box-shadow: 0 8px 25px rgba(249,158,43,0.5);
}
.ugb-action:hover .ugb-action-icon.withdraw {
    box-shadow: 0 8px 25px rgba(218,62,47,0.5);
}
.ugb-action:hover .ugb-action-icon.deposit {
    box-shadow: 0 8px 25px rgba(15,116,60,0.5);
}
.ugb-action span {
    font-size: 10px;
    font-weight: 700;
    color: var(--agco-text-primary);
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

/* Banner Frame Wrapper - Sharp Corners, No Border/Margin */
.banner-frame-wrapper,
.banner-frame-wrapper *,
.banner-slider-new,
.banner-slider-new *,
.banner-slider-new .banner-slider,
.banner-slider-new .banner-slide,
.banner-slider-new .banner-slide a,
.banner-slider-new .banner-slide img {
    border-radius: 0 !important;
    -webkit-border-radius: 0 !important;
    -moz-border-radius: 0 !important;
}
.banner-frame-wrapper {
    margin: 0;
    padding: 0;
    background: transparent;
    overflow: visible;
    box-shadow: none;
    border: none;
}
.banner-slider-new {
    margin: 0;
    padding: 0;
    overflow: visible;
    position: relative;
}
.banner-slider-new .banner-slider {
    position: relative;
    width: 100%;
    padding: 0;
    margin: 0;
}
.banner-slider-new .banner-slide {
    display: none;
    width: 100%;
    padding: 0;
    margin: 0;
}
.banner-slider-new .banner-slide.active {
    display: block;
}
.banner-slider-new .banner-slide a {
    display: block;
    line-height: 0;
}
.banner-slider-new .banner-slide img {
    width: 100%;
    height: auto;
    max-height: 180px;
    display: block;
    object-fit: cover;
    object-position: center;
    padding: 0;
    margin: 0;
}
.banner-slider-new .banner-dots {
    position: absolute;
    bottom: 10px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 6px;
    z-index: 10;
}
.banner-slider-new .banner-dots .dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: rgba(255,255,255,0.5);
    cursor: pointer;
    transition: all 0.3s;
}
.banner-slider-new .banner-dots .dot.active {
    background: #fff;
    width: 20px;
    border-radius: 4px;
}
.whc-content {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 14px;
    position: relative;
    z-index: 2;
}
.whc-label {
    font-size: 12px;
    color: rgba(255,255,255,0.8);
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 4px;
}
.whc-label i {
    color: var(--gold);
}
.whc-amount {
    font-size: 32px;
    font-weight: 800;
    color: #fff;
    margin: 0;
    text-shadow: 0 2px 10px rgba(0,0,0,0.2);
}
.plan-tag {
    background: rgba(255,255,255,0.15);
    color: rgba(255,255,255,0.9);
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
}
.plan-tag.vip {
    background: linear-gradient(135deg, var(--gold), #FFD54F);
    color: #5D4037;
}
.plan-tag i {
    font-size: 12px;
}
.whc-actions {
    display: flex;
    gap: 10px;
    position: relative;
    z-index: 2;
}
.whc-btn {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 5px;
    padding: 12px 8px;
    border-radius: 14px;
    text-decoration: none;
    transition: all 0.3s;
}
.whc-btn i { font-size: 18px; }
.whc-btn span { font-size: 11px; font-weight: 600; }
.whc-btn.deposit {
    background: linear-gradient(135deg, #1565C0, #42A5F5);
    color: #fff;
}
.whc-btn.withdraw {
    background: linear-gradient(135deg, var(--gold), #FFB300);
    color: #5D4037;
}
.whc-btn.refer {
    background: linear-gradient(135deg, #43A047, #66BB6A);
    color: #fff;
}
.whc-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.2);
}

/* Announcement Bar - Light Theme */
.announcement-bar {
    display: flex;
    align-items: center;
    background: linear-gradient(90deg, var(--agco-primary), var(--agco-primary-light));
    overflow: hidden;
    margin: 0;
}
.ab-icon {
    width: 40px;
    height: 40px;
    background: rgba(255,255,255,0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.ab-icon i {
    color: #fff;
    font-size: 14px;
    animation: shake 2s ease-in-out infinite;
}
@@keyframes shake {
    0%, 100% { transform: rotate(0deg); }
    10%, 30% { transform: rotate(-8deg); }
    20%, 40% { transform: rotate(8deg); }
    50% { transform: rotate(0deg); }
}
.ab-marquee {
    flex: 1;
    overflow: hidden;
    padding: 10px 0;
}
.ab-track {
    display: flex;
    white-space: nowrap;
    animation: marqueeScroll 20s linear infinite;
}
.ab-item {
    display: inline-flex;
    align-items: center;
    color: #fff;
    font-size: 12px;
    font-weight: 500;
    padding: 0 20px;
}
.ab-dot {
    width: 5px;
    height: 5px;
    background: var(--gold);
    border-radius: 50%;
    margin-right: 8px;
    flex-shrink: 0;
}
@@keyframes marqueeScroll {
    0% { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}

/* Top Info Strip Container */
.top-info-strip {
    display: flex;
    flex-direction: column;
}

/* Audio Mini Player - Light Theme */
.audio-mini-player {
    display: flex;
    align-items: center;
    gap: 10px;
    background: linear-gradient(90deg, #ffffff 0%, #f8faf9 100%);
    padding: 8px 14px;
    border-bottom: 1px solid var(--agco-border);
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}
.amp-wave {
    display: flex;
    align-items: center;
    gap: 2px;
    height: 20px;
}
.amp-wave span {
    width: 3px;
    height: 8px;
    background: linear-gradient(180deg, var(--agco-primary), var(--agco-primary-light));
    border-radius: 2px;
    animation: audioWave 1s ease-in-out infinite;
}
.amp-wave span:nth-child(1) { animation-delay: 0s; }
.amp-wave span:nth-child(2) { animation-delay: 0.1s; height: 14px; }
.amp-wave span:nth-child(3) { animation-delay: 0.2s; height: 10px; }
.amp-wave span:nth-child(4) { animation-delay: 0.3s; }
@@keyframes audioWave {
    0%, 100% { transform: scaleY(1); }
    50% { transform: scaleY(1.8); }
}
.amp-wave.paused span {
    animation-play-state: paused;
    transform: scaleY(0.5);
}
.amp-title-wrap {
    flex: 1;
    overflow: hidden;
    position: relative;
}
.amp-title-scroll {
    display: flex;
    white-space: nowrap;
    animation: titleScroll 12s linear infinite;
}
.amp-title-scroll span {
    font-size: 12px;
    color: var(--agco-text-primary);
    font-weight: 500;
    padding-right: 40px;
}
@@keyframes titleScroll {
    0% { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}
.amp-btn {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: none;
    background: linear-gradient(135deg, var(--agco-primary), var(--agco-primary-light));
    color: #fff;
    font-size: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 3px 10px rgba(15,116,60,0.4);
    transition: all 0.3s;
}
.amp-btn:hover {
    transform: scale(1.1);
}
.amp-btn.playing {
    background: linear-gradient(135deg, var(--agco-error), #e74c3c);
    color: #fff;
}

/* Banner Slider */
.banner-slider-section {
    padding: 10px 12px 0;
    position: relative;
}
.banner-slider {
    position: relative;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 6px 20px rgba(0,0,0,0.2);
}
.banner-slide {
    display: none;
}
.banner-slide.active {
    display: block;
    animation: fadeIn 0.5s ease;
}
@@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
.banner-slide a {
    display: block;
}
.banner-slide img {
    width: 100%;
    height: 140px;
    object-fit: cover;
    display: block;
}
.banner-dots {
    display: flex;
    justify-content: center;
    gap: 6px;
    padding: 10px 0 0;
}
.banner-dots .dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: rgba(15,116,60,0.3);
    cursor: pointer;
    transition: all 0.3s;
}
.banner-dots .dot.active {
    background: var(--agco-primary);
    width: 20px;
    border-radius: 4px;
}

/* Services Section - Light Theme */
.services-colorful {
    padding: 12px 16px;
    position: relative;
    z-index: 1;
    background: #fff;
}

/* ========== COMPACT DASHBOARD DESIGN ========== */
/* Compact Dashboard Section - Container */
.compact-dashboard-section {
    background: #fff;
    padding: 12px 16px 0;
    margin: 0;
}

/* Compact Stats Row - 3 Column Grid */
.compact-stats-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
    margin-bottom: 12px;
}

.csr-item {
    border-radius: 12px;
    padding: 12px 8px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    text-align: center;
    box-shadow: 0 3px 15px rgba(0,0,0,0.12);
}

.csr-item.primary { background: linear-gradient(135deg, var(--agco-primary), var(--agco-primary-light)); }
.csr-item.warning { background: linear-gradient(135deg, var(--agco-warning), #FFB74D); }
.csr-item.error { background: linear-gradient(135deg, var(--agco-error), #e74c3c); }

.csr-icon {
    width: 28px;
    height: 28px;
    background: rgba(255,255,255,0.25);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 14px;
}

.csr-data {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.csr-data strong {
    color: #fff;
    font-size: 14px;
    font-weight: 700;
    line-height: 1;
}

.csr-data span {
    color: rgba(255,255,255,0.9);
    font-size: 9px;
    font-weight: 500;
    line-height: 1.2;
}

/* Compact Services Grid - 3x2 Layout */
.compact-services-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
    margin-bottom: 12px;
}

.cs-item {
    border-radius: 12px;
    padding: 14px 8px;
    text-decoration: none;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    position: relative;
    transition: all 0.3s;
    box-shadow: 0 3px 15px rgba(0,0,0,0.12);
}

button.cs-item {
    border: none;
    background: transparent;
    cursor: pointer;
    font: inherit;
}

.cs-item.primary { background: linear-gradient(135deg, var(--agco-primary), var(--agco-primary-light)); }
.cs-item.error { background: linear-gradient(135deg, var(--agco-error), #e74c3c); }
.cs-item.warning { background: linear-gradient(135deg, var(--agco-warning), #FFB74D); }
.cs-item.secondary { background: linear-gradient(135deg, var(--agco-secondary), #e07c3a); }
.cs-item.purple { background: linear-gradient(135deg, #7e57c2, #9575cd); }
.cs-item.gold { background: linear-gradient(135deg, #FFD700, #FFA500); }

.cs-item:hover, .cs-item:active {
    transform: translateY(-4px) scale(1.02);
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
}

.cs-icon {
    width: 38px;
    height: 38px;
    background: rgba(255,255,255,0.25);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 19px;
    transition: all 0.3s;
}

.cs-item:hover .cs-icon {
    transform: scale(1.1);
    background: rgba(255,255,255,0.35);
}

.cs-item span {
    color: #fff;
    font-size: 10.5px;
    font-weight: 600;
    text-align: center;
    line-height: 1.2;
}

.cs-badge {
    position: absolute;
    top: 6px;
    right: 6px;
    font-size: 14px;
}

/* Compact Action Banner */
.compact-action-banner {
    display: flex;
    gap: 8px;
    background: linear-gradient(135deg, rgba(15,116,60,0.08) 0%, rgba(249,158,43,0.05) 100%);
    border-radius: 12px;
    padding: 12px;
    border: 1px solid rgba(15,116,60,0.1);
    margin: 12px 0 14px;
}

.cab-btn {
    flex: 1;
    background: linear-gradient(135deg, var(--agco-primary), var(--agco-primary-light));
    color: #fff;
    border: none;
    padding: 12px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    transition: all 0.3s;
    box-shadow: 0 3px 12px rgba(15,116,60,0.25);
}

.cab-btn.wa {
    background: #25D366;
    flex: 0 0 auto;
    padding: 12px 16px;
    box-shadow: 0 3px 12px rgba(37,211,102,0.3);
}

.cab-btn.fb {
    background: #1877F2;
    flex: 0 0 auto;
    padding: 12px 16px;
    box-shadow: 0 3px 12px rgba(24,119,242,0.3);
}

.cab-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.25);
    color: #fff;
}

.cab-btn.wa:hover {
    box-shadow: 0 6px 20px rgba(37,211,102,0.4);
}

.cab-btn.fb:hover {
    box-shadow: 0 6px 20px rgba(24,119,242,0.4);
}

.cab-btn i {
    font-size: 16px;
}

.cab-btn span {
    font-size: 13px;
    font-weight: 600;
}

/* Services Header */
.services-header h3 {
    color: var(--agco-text-primary);
    font-size: 16px;
    font-weight: 700;
    margin-bottom: 10px;
}
.services-grid-new {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-bottom: 14px;
}

/* Pathao Style Spotlight - Light Theme */
.pathao-spotlight {
    margin: 0 16px 14px 16px;
    background: linear-gradient(135deg, rgba(15,116,60,0.08) 0%, rgba(26,156,82,0.05) 100%);
    border-radius: 16px;
    padding: 14px;
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(15,116,60,0.1);
}
.pathao-spotlight::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 100%;
    height: 100%;
    background: radial-gradient(circle, rgba(15,116,60,0.05) 0%, transparent 70%);
    animation: spotlightGlow 4s ease-in-out infinite;
}
@@keyframes spotlightGlow {
    0%, 100% { opacity: 0.5; transform: scale(1); }
    50% { opacity: 1; transform: scale(1.2); }
}
.ps-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    position: relative;
    z-index: 2;
}
.ps-header h4 {
    color: var(--agco-text-primary);
    font-size: 14px;
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}
.ps-header h4::before {
    content: '✨';
    font-size: 16px;
}
.ps-header a {
    color: var(--agco-primary);
    font-size: 11px;
    font-weight: 600;
    text-decoration: none;
    background: rgba(15,116,60,0.1);
    padding: 4px 10px;
    border-radius: 12px;
    transition: all 0.3s;
}
.ps-header a:hover {
    background: var(--agco-primary);
    color: #fff;
}
.ps-slider-wrap {
    position: relative;
    z-index: 2;
}
.ps-slider {
    position: relative;
    overflow: hidden;
}
.ps-slide {
    display: none;
}
.ps-slide.active {
    display: block;
    animation: slideIn 0.5s ease;
}
@@keyframes slideIn {
    from { opacity: 0; transform: translateX(20px); }
    to { opacity: 1; transform: translateX(0); }
}
.ps-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
}
.ps-card {
    display: block;
    border-radius: 12px;
    overflow: hidden;
    text-decoration: none;
    border: none;
    padding: 0;
    cursor: pointer;
    background: linear-gradient(135deg, #fff 0%, #f8f8f8 100%);
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    aspect-ratio: 3/4;
    position: relative;
}
.ps-card::after {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 12px;
    padding: 2px;
    background: linear-gradient(135deg, var(--agco-primary), var(--agco-warning), var(--agco-secondary));
    -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    opacity: 0;
    transition: opacity 0.3s;
}
.ps-card:hover::after {
    opacity: 1;
}
.ps-card:hover {
    transform: translateY(-5px) scale(1.05);
    box-shadow: 0 10px 25px rgba(15,116,60,0.3);
}
.ps-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s;
}
.ps-card:hover img {
    transform: scale(1.1);
}
.ps-dots {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-top: 12px;
}
.ps-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: rgba(15,116,60,0.3);
    cursor: pointer;
    transition: all 0.3s;
    position: relative;
}
.ps-dot::before {
    content: '';
    position: absolute;
    inset: -3px;
    border-radius: 50%;
    border: 2px solid transparent;
    transition: all 0.3s;
}
.ps-dot.active {
    background: linear-gradient(135deg, var(--agco-primary), var(--agco-primary-light));
    width: 24px;
    border-radius: 6px;
    box-shadow: 0 0 10px rgba(15,116,60,0.4);
}
.ps-dot.active::before {
    border-color: rgba(15,116,60,0.3);
    border-radius: 8px;
}

/* Spotlight Story Card Ring - FB Style */
.ps-card-ring {
    position: absolute;
    inset: -3px;
    border-radius: 14px;
    background: linear-gradient(45deg, var(--agco-primary), var(--agco-warning), var(--agco-secondary));
    z-index: -1;
    opacity: 1;
}
.ps-card.spotlight-story-trigger {
    cursor: pointer;
    position: relative;
    z-index: 1;
}
.ps-card.spotlight-story-trigger::before {
    content: '';
    position: absolute;
    inset: 2px;
    background: #fff;
    border-radius: 10px;
    z-index: -1;
}

/* FB Story Spotlight Viewer */
.spotlight-story-viewer {
    position: fixed;
    inset: 0;
    z-index: 99999;
    display: none;
    align-items: center;
    justify-content: center;
}
.spotlight-story-viewer.active {
    display: flex;
    animation: ssvFadeIn 0.3s ease;
}
@@keyframes ssvFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
.ssv-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.95);
    backdrop-filter: blur(20px);
}
.ssv-container {
    position: relative;
    width: 100%;
    max-width: 400px;
    height: 100%;
    max-height: 100vh;
    display: flex;
    flex-direction: column;
    z-index: 2;
}
.ssv-header {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    padding: 12px 16px;
    z-index: 10;
    background: linear-gradient(180deg, rgba(0,0,0,0.7) 0%, transparent 100%);
}
.ssv-progress-bar {
    display: flex;
    gap: 4px;
    margin-bottom: 12px;
}
.ssv-progress-segment {
    flex: 1;
    height: 3px;
    background: rgba(255,255,255,0.3);
    border-radius: 3px;
    overflow: hidden;
}
.ssv-progress-fill {
    width: 0%;
    height: 100%;
    background: #fff;
    border-radius: 3px;
    transition: width 0.1s linear;
}
.ssv-progress-segment.completed .ssv-progress-fill {
    width: 100%;
}
.ssv-progress-segment.active .ssv-progress-fill {
    animation: ssvProgress 5s linear forwards;
}
@@keyframes ssvProgress {
    from { width: 0%; }
    to { width: 100%; }
}
.ssv-info {
    display: flex;
    align-items: center;
    gap: 10px;
}
.ssv-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    overflow: hidden;
    border: 2px solid #fff;
}
.ssv-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.ssv-details {
    flex: 1;
}
.ssv-title {
    display: block;
    color: #fff;
    font-size: 14px;
    font-weight: 600;
}
.ssv-time {
    color: rgba(255,255,255,0.7);
    font-size: 11px;
}
.ssv-close {
    position: absolute;
    top: 50px;
    right: 12px;
    width: 36px;
    height: 36px;
    border: none;
    background: rgba(255,255,255,0.1);
    color: #fff;
    border-radius: 50%;
    font-size: 18px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;
}
.ssv-close:hover {
    background: rgba(255,255,255,0.2);
    transform: rotate(90deg);
}
.ssv-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 80px 16px 100px;
}
.ssv-image-wrap {
    width: 100%;
    max-height: 70vh;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,0.5);
}
.ssv-image {
    width: 100%;
    height: 100%;
    object-fit: contain;
    max-height: 70vh;
}
.ssv-description {
    margin-top: 16px;
    padding: 0 8px;
    color: #fff;
    font-size: 14px;
    text-align: center;
    line-height: 1.5;
    max-height: 80px;
    overflow-y: auto;
}
.ssv-nav {
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    transform: translateY(-50%);
    display: flex;
    justify-content: space-between;
    padding: 0 8px;
    pointer-events: none;
}
.ssv-nav-prev, .ssv-nav-next {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(10px);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 18px;
    cursor: pointer;
    pointer-events: auto;
    transition: all 0.3s;
}
.ssv-nav-prev:hover, .ssv-nav-next:hover {
    background: rgba(255,255,255,0.3);
    transform: scale(1.1);
}
.ssv-footer {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 16px;
    text-align: center;
    background: linear-gradient(0deg, rgba(0,0,0,0.7) 0%, transparent 100%);
}
.ssv-link-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: linear-gradient(135deg, #f09433, #e6683c, #dc2743);
    color: #fff;
    font-size: 14px;
    font-weight: 600;
    border-radius: 25px;
    text-decoration: none;
    transition: all 0.3s;
    box-shadow: 0 4px 15px rgba(220,39,67,0.4);
}
.ssv-link-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(220,39,67,0.5);
    color: #fff;
}
.ssv-link-btn.hidden {
    display: none;
}

.service-card-new {
    border-radius: 16px;
    padding: 14px;
    text-decoration: none;
    position: relative;
    overflow: hidden;
    min-height: 120px;
    display: flex;
    flex-direction: column;
    transition: all 0.3s;
}
button.service-card-new {
    border: none;
    background: transparent;
    width: 100%;
    text-align: left;
    cursor: pointer;
    font: inherit;
}
.service-card-new:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.15);
}
.service-card-new.faq {
    background: linear-gradient(135deg, #7e57c2, #9575cd);
}
.service-card-new.tutorial {
    background: linear-gradient(135deg, #FF6F00, #FF9800);
}
.service-card-new.redbag {
    background: linear-gradient(135deg, #D32F2F, #E53935);
}
.service-card-new.refer {
    background: linear-gradient(135deg, var(--agco-warning), #FFB74D);
}
.service-card-new.task {
    background: linear-gradient(135deg, var(--agco-primary), var(--agco-primary-light));
}
.service-card-new.vip {
    background: linear-gradient(135deg, var(--agco-warning), #FFB74D);
}
.service-card-new.deposit {
    background: linear-gradient(135deg, var(--agco-secondary), #e07c3a);
}
.service-card-new.withdraw {
    background: linear-gradient(135deg, var(--agco-error), #e74c3c);
}
.sc-bg-art {
    position: absolute;
    top: 0;
    right: 0;
    width: 100px;
    height: 100px;
    opacity: 0.5;
}
.sc-svg {
    width: 100%;
    height: 100%;
}
.sc-icon {
    width: 50px;
    height: 50px;
    margin-bottom: 10px;
}
.sc-icon img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    filter: brightness(0) invert(1);
}
.sc-icon-fallback {
    width: 100%;
    height: 100%;
    display: none;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    color: #fff;
}
.sc-info {
    margin-top: auto;
}
.sc-info h4 {
    color: #fff;
    font-size: 16px;
    font-weight: 700;
    margin-bottom: 2px;
}
.sc-info p {
    color: rgba(255,255,255,0.85);
    font-size: 11px;
    margin: 0;
}
.sc-badge {
    position: absolute;
    top: 12px;
    right: 12px;
    background: #fff;
    color: var(--agco-primary);
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: 700;
}
.sc-badge.gold {
    background: #fff;
    color: var(--agco-warning);
}
.sc-badge.pulse {
    animation: pulse 2s infinite;
}

/* Quick Links Row - Light Theme */
.quick-links-row {
    display: flex;
    gap: 10px;
    background: linear-gradient(135deg, rgba(15,116,60,0.08) 0%, rgba(249,158,43,0.05) 100%);
    border-radius: 16px;
    padding: 14px 10px;
    border: 1px solid rgba(15,116,60,0.1);
}
.ql-item {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    padding: 8px 4px;
    border-radius: 12px;
    transition: all 0.3s;
}
.ql-item:hover {
    background: rgba(15,116,60,0.08);
    transform: translateY(-2px);
}
.ql-icon {
    width: 46px;
    height: 46px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: #fff;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    transition: all 0.3s;
}
.ql-item:hover .ql-icon {
    transform: scale(1.1);
}
.ql-icon.purple { background: linear-gradient(135deg, var(--agco-primary), var(--agco-primary-light)); }
.ql-icon.crimson { background: linear-gradient(135deg, var(--agco-error), #e74c3c); }
.ql-icon.orange { background: linear-gradient(135deg, var(--agco-secondary), #e07c3a); }
.ql-icon.gold { background: linear-gradient(135deg, var(--agco-warning), #FFB74D); color: #5D4037; }
.ql-item span {
    font-size: 11px;
    color: var(--agco-text-primary);
    font-weight: 600;
}

/* Stats Strip - Light Theme */
.stats-strip {
    display: flex;
    align-items: center;
    background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
    margin: 12px;
    border-radius: 18px;
    padding: 16px 12px;
    box-shadow: 0 6px 25px rgba(15,116,60,0.1);
    border: 1px solid rgba(15,116,60,0.08);
}
.stat-item {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 0 6px;
}
.stat-icon-wrap {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    color: #fff;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.stat-icon-wrap.purple { background: linear-gradient(135deg, var(--agco-primary), var(--agco-primary-light)); }
.stat-icon-wrap.crimson { background: linear-gradient(135deg, var(--agco-error), #e74c3c); }
.stat-icon-wrap.gold { background: linear-gradient(135deg, var(--agco-warning), #FFB74D); color: #5D4037; }
.stat-text strong {
    display: block;
    font-size: 15px;
    font-weight: 800;
    color: var(--agco-primary);
}
.stat-text span {
    font-size: 10px;
    color: var(--agco-text-secondary);
    font-weight: 500;
}
.stat-divider {
    width: 1px;
    height: 35px;
    background: linear-gradient(180deg, transparent, rgba(15,116,60,0.2), transparent);
    margin: 0 4px;
}

/* Section Header - Light Theme */
.section-head-new {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    padding: 0 16px;
}
.sh-left {
    display: flex;
    align-items: center;
    gap: 8px;
}
.sh-emoji {
    font-size: 20px;
}
.section-head-new h3 {
    color: var(--agco-text-primary);
    font-size: 16px;
    font-weight: 700;
    margin: 0;
}
.sh-link {
    color: var(--agco-primary);
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 4px;
}
.sh-link i {
    font-size: 10px;
}

/* Spotlight Colorful */
.spotlight-colorful {
    margin-bottom: 20px;
}
.spotlight-scroll {
    display: flex;
    gap: 12px;
    overflow-x: auto;
    padding: 0 16px 12px;
    -webkit-overflow-scrolling: touch;
}
.spotlight-scroll::-webkit-scrollbar { display: none; }
.spotlight-card-new {
    flex-shrink: 0;
    width: 140px;
    height: 95px;
    border-radius: 16px;
    overflow: hidden;
    position: relative;
    text-decoration: none;
    box-shadow: 0 6px 20px rgba(0,0,0,0.2);
}
.spotlight-card-new img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.spot-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 30px;
    color: #fff;
}
.spotlight-card-new.color-1 .spot-placeholder { background: linear-gradient(135deg, #E91E63, #F06292); }
.spotlight-card-new.color-2 .spot-placeholder { background: linear-gradient(135deg, #FF8F00, #FFB300); }
.spotlight-card-new.color-3 .spot-placeholder { background: linear-gradient(135deg, #7B1FA2, #AB47BC); }
.spotlight-card-new.color-4 .spot-placeholder { background: linear-gradient(135deg, #F4511E, #FF7043); }
.spot-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 8px 10px;
    background: linear-gradient(transparent, rgba(0,0,0,0.8));
}
.spot-title {
    color: #fff;
    font-size: 11px;
    font-weight: 600;
}
.spot-badge {
    position: absolute;
    top: 8px;
    left: 8px;
    background: var(--agco-primary);
    color: #fff;
    padding: 3px 8px;
    border-radius: 8px;
    font-size: 9px;
    font-weight: 700;
}

/* Earn Now Section - Light Theme */
.earn-now-section {
    background: linear-gradient(135deg, var(--agco-primary), var(--agco-primary-light));
    margin: 0 16px 16px;
    border-radius: 20px;
    padding: 20px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 8px 30px rgba(15,116,60,0.3);
}
.ens-decoration {
    position: absolute;
    top: 0;
    right: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
}
.coin {
    position: absolute;
    font-size: 24px;
    opacity: 0.3;
    animation: coinFloat 3s ease-in-out infinite;
}
.coin.c1 { top: 10px; right: 20px; animation-delay: 0s; }
.coin.c2 { top: 50%; right: 10%; animation-delay: 0.5s; }
.coin.c3 { bottom: 10px; right: 30%; animation-delay: 1s; }
@@keyframes coinFloat {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-10px) rotate(15deg); }
}
.ens-content {
    display: flex;
    align-items: center;
    gap: 14px;
    position: relative;
    z-index: 1;
}
.ens-icon {
    width: 50px;
    height: 50px;
    flex-shrink: 0;
}
.ens-icon img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}
.ens-text {
    flex: 1;
}
.ens-text h4 {
    color: #fff;
    font-size: 15px;
    font-weight: 700;
    margin-bottom: 2px;
}
.ens-text p {
    color: rgba(255,255,255,0.9);
    font-size: 12px;
    margin: 0;
}
.ens-btn {
    background: #fff;
    color: var(--agco-primary);
    padding: 10px 18px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
    transition: all 0.3s;
}
.ens-btn:hover {
    transform: scale(1.05);
    color: var(--agco-primary);
}

/* Tutorials Section */
.tutorials-section {
    margin-bottom: 16px;
    background: #fff;
    padding: 14px 0;
}
.tutorials-scroll {
    display: flex;
    gap: 12px;
    overflow-x: auto;
    padding: 0 16px 12px;
    -webkit-overflow-scrolling: touch;
}
.tutorials-scroll::-webkit-scrollbar { display: none; }
.tutorial-card-new {
    flex-shrink: 0;
    width: 180px;
    text-decoration: none;
}
.tc-thumb {
    position: relative;
    height: 100px;
    border-radius: 14px;
    overflow: hidden;
    margin-bottom: 8px;
}
.tc-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.tc-play {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 40px;
    height: 40px;
    background: var(--agco-primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 14px;
    box-shadow: 0 4px 15px rgba(15,116,60,0.5);
}
.tc-play i {
    margin-left: 3px;
}
.tc-lesson {
    position: absolute;
    top: 8px;
    left: 8px;
    background: var(--agco-warning);
    color: #1f2937;
    padding: 3px 10px;
    border-radius: 8px;
    font-size: 10px;
    font-weight: 700;
}
.tc-title {
    color: var(--agco-text-primary);
    font-size: 13px;
    font-weight: 600;
    line-height: 1.3;
}

/* AGCO Family Brands Section */
.brands-section {
    margin-bottom: 16px;
    background: #fff;
    padding: 14px 0;
}
.brands-scroll {
    display: flex;
    gap: 16px;
    overflow-x: auto;
    padding: 0 16px 12px;
    -webkit-overflow-scrolling: touch;
}
.brands-scroll::-webkit-scrollbar { display: none; }
.brand-card-new {
    flex-shrink: 0;
    width: 140px;
    height: 80px;
    background: #f8f9fa;
    border-radius: 12px;
    padding: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #e5e7eb;
    transition: all 0.3s;
}
.brand-card-new:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}
.brand-card-new img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    filter: grayscale(0);
    transition: all 0.3s;
}
.brand-card-new:hover img {
    filter: grayscale(0) brightness(1.1);
}

/* Company Blog News Section */
.blog-section {
    margin-bottom: 16px;
    background: #fff;
    padding: 14px 0;
}
.blog-scroll {
    display: flex;
    gap: 12px;
    overflow-x: auto;
    padding: 0 16px 12px;
    -webkit-overflow-scrolling: touch;
}
.blog-scroll::-webkit-scrollbar { display: none; }
.blog-card-compact {
    flex-shrink: 0;
    width: 260px;
    background: #fff;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    text-decoration: none;
    transition: all 0.3s;
}
.blog-card-compact:hover {
    transform: translateY(-4px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.12);
}
.bc-thumb {
    position: relative;
    height: 140px;
    overflow: hidden;
    background: linear-gradient(135deg, #f5f5f5, #e0e0e0);
}
.bc-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s;
}
.blog-card-compact:hover .bc-thumb img {
    transform: scale(1.05);
}
.bc-date {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(255,255,255,0.95);
    backdrop-filter: blur(10px);
    border-radius: 10px;
    padding: 6px 10px;
    text-align: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.bc-day {
    display: block;
    font-size: 18px;
    font-weight: 700;
    color: var(--agco-primary);
    line-height: 1;
}
.bc-month {
    display: block;
    font-size: 10px;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    margin-top: 2px;
}
.bc-content {
    padding: 12px;
}
.bc-title {
    font-size: 14px;
    font-weight: 700;
    color: var(--agco-text-primary);
    line-height: 1.4;
    margin: 0 0 6px 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.bc-excerpt {
    font-size: 12px;
    color: #64748b;
    line-height: 1.5;
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Stats Grid - Light Theme */
.stats-grid-colorful {
    margin-bottom: 20px;
}
.sgc-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    padding: 0 16px;
}
.sgc-item {
    border-radius: 16px;
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
}
.sgc-item.purple { background: linear-gradient(135deg, var(--agco-primary), var(--agco-primary-light)); }
.sgc-item.crimson { background: linear-gradient(135deg, var(--agco-error), #e74c3c); }
.sgc-item.orange { background: linear-gradient(135deg, var(--agco-secondary), #e07c3a); }
.sgc-item.gold { background: linear-gradient(135deg, var(--agco-warning), #FFB74D); }
.sgc-icon {
    width: 40px;
    height: 40px;
    background: rgba(255,255,255,0.2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 16px;
}
.sgc-data strong {
    display: block;
    color: #fff;
    font-size: 16px;
    font-weight: 700;
}
.sgc-data span {
    color: rgba(255,255,255,0.85);
    font-size: 10px;
}

/* Referral Banner - Light Theme */
.referral-banner {
    background: linear-gradient(135deg, var(--agco-primary), var(--agco-secondary));
    margin: 0 16px 16px;
    border-radius: 20px;
    padding: 20px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 8px 30px rgba(15,116,60,0.3);
}
.rb-bg-art {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    pointer-events: none;
}
.rb-circle {
    position: absolute;
    border-radius: 50%;
    background: rgba(255,255,255,0.1);
}
.rb-circle.c1 { width: 80px; height: 80px; top: -20px; right: -20px; }
.rb-circle.c2 { width: 60px; height: 60px; bottom: -15px; left: 20%; }
.rb-star {
    position: absolute;
    top: 20px;
    right: 25%;
    font-size: 24px;
    opacity: 0.3;
    animation: twinkle 2s ease-in-out infinite;
}
.rb-content {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 16px;
    position: relative;
    z-index: 1;
}
.rb-icon {
    width: 50px;
    height: 50px;
    flex-shrink: 0;
}
.rb-icon img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}
.rb-text h4 {
    color: #fff;
    font-size: 16px;
    font-weight: 700;
    margin-bottom: 4px;
}
.rb-text p {
    color: rgba(255,255,255,0.9);
    font-size: 12px;
    margin: 0;
}
.rb-text strong {
    color: var(--agco-warning);
}
.rb-actions {
    display: flex;
    gap: 10px;
    position: relative;
    z-index: 1;
}
.rb-copy {
    flex: 1;
    background: var(--agco-warning);
    color: #1f2937;
    border: none;
    padding: 12px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.3s;
}
.rb-copy:hover { transform: scale(1.02); }
.rb-share {
    width: 46px;
    height: 46px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 20px;
    text-decoration: none;
    transition: all 0.3s;
}
.rb-share.wa { background: #25D366; }
.rb-share.fb { background: #1877F2; }
.rb-share:hover { transform: scale(1.05); color: #fff; }

/* Audio Player New - Light Theme */
.audio-player-new {
    background: linear-gradient(135deg, var(--agco-primary), var(--agco-primary-light));
    margin: 0 16px 16px;
    border-radius: 16px;
    padding: 12px 14px;
    display: flex;
    align-items: center;
    gap: 12px;
    box-shadow: 0 4px 20px rgba(15,116,60,0.3);
}
.ap-img {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    object-fit: cover;
}
.ap-info {
    flex: 1;
}
.ap-title {
    color: #fff;
    font-size: 12px;
    font-weight: 600;
    display: block;
    margin-bottom: 6px;
}
.ap-progress {
    height: 4px;
    background: rgba(255,255,255,0.2);
    border-radius: 2px;
}
.ap-bar {
    height: 100%;
    background: var(--agco-warning);
    border-radius: 2px;
    width: 0;
    transition: width 0.1s;
}
.ap-btn {
    width: 44px;
    height: 44px;
    background: var(--agco-warning);
    border: none;
    border-radius: 50%;
    color: #5D4037;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s;
}
.ap-btn:hover { transform: scale(1.05); }

/* Modal Colorful - Bottom Sheet Style */
.modal-drag-handle {
    width: 40px;
    height: 4px;
    background: #d1d5db;
    border-radius: 2px;
    margin: 10px auto 0;
}
.modal-dialog-bottom {
    margin: 0;
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    max-width: 100%;
    transform: translateY(100%);
    transition: transform 0.3s ease-out;
}
.modal.show .modal-dialog-bottom {
    transform: translateY(0);
}
.modal-colorful {
    border-radius: 24px 24px 0 0;
    border: none;
    background: #fff;
    box-shadow: 0 -10px 40px rgba(0,0,0,0.15);
}
.modal-colorful .modal-header {
    border: none;
    padding: 20px 20px 15px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.modal-colorful .modal-header h5 {
    font-size: 18px;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}
.modal-close {
    width: 36px;
    height: 36px;
    background: #f3f4f6;
    border: none;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6b7280;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.2s;
}
.modal-close:hover {
    background: #e5e7eb;
    color: #374151;
}
.modal-colorful .modal-body {
    padding: 10px 16px 35px;
}
.modal-services-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px 12px;
}
.ms-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    text-decoration: none;
    padding: 8px 4px;
    border-radius: 12px;
    transition: all 0.2s;
    position: relative;
}
button.ms-item {
    border: none;
    background: transparent;
    cursor: pointer;
    font: inherit;
}
.ms-item:hover,
.ms-item:active {
    background: #f9fafb;
    transform: scale(0.97);
}
.ms-badge {
    position: absolute;
    top: 4px;
    right: 4px;
    background: #D32F2F;
    color: #fff;
    font-size: 9px;
    font-weight: 700;
    padding: 2px 6px;
    border-radius: 8px;
    min-width: 18px;
    text-align: center;
}
.sc-badge.hot {
    background: linear-gradient(135deg, #ff6b6b, #ee5a6f);
    color: #fff;
    animation: pulse 2s infinite;
}
.ms-icon {
    width: 54px;
    height: 54px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;
    font-size: 24px;
    color: #fff;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.ms-icon.purple { background: linear-gradient(135deg, var(--agco-primary), var(--agco-primary-light)); }
.ms-icon.crimson { background: linear-gradient(135deg, var(--agco-error), #e74c3c); }
.ms-icon.orange { background: linear-gradient(135deg, var(--agco-secondary), #e07c3a); }
.ms-icon.gold { background: linear-gradient(135deg, var(--agco-warning), #FFB74D); color: #5D4037; }
.ms-item span {
    font-size: 11px;
    color: #374151;
    font-weight: 600;
    line-height: 1.3;
    max-width: 70px;
    word-wrap: break-word;
}

/* Modal safe area for iOS devices */
@@supports (padding-bottom: env(safe-area-inset-bottom)) {
    .modal-colorful .modal-body {
        padding-bottom: calc(35px + env(safe-area-inset-bottom));
    }
}

/* Modal backdrop enhancement */
#allServicesModal.modal {
    background: rgba(0,0,0,0.5);
}
#allServicesModal .modal-content {
    border: none;
}

/* Responsive adjustments for smaller screens */
@media (max-width: 360px) {
    .modal-services-grid {
        gap: 15px 8px;
    }
    .ms-icon {
        width: 48px;
        height: 48px;
        font-size: 20px;
    }
    .ms-item span {
        font-size: 10px;
    }
}
</style>
@endpush

@push('script')
<script>
(function($) {
    "use strict";

    // Enhanced modal backdrop cleanup - using global cleanup function
    var allServicesModal = document.getElementById('allServicesModal');
    if (allServicesModal) {
        allServicesModal.addEventListener('hidden.bs.modal', function () {
            // Trigger global cleanup
            if (typeof cleanupStuckModalBackdrops === 'function') {
                cleanupStuckModalBackdrops();
            }
        });

        // Close modal when clicking outside (on backdrop)
        allServicesModal.addEventListener('click', function(e) {
            if (e.target === allServicesModal) {
                var modal = bootstrap.Modal.getInstance(allServicesModal);
                if (modal) modal.hide();
            }
        });
    }

    // Copy Referral Link
    window.copyReferralLink = function() {
        var link = document.getElementById('referralLink').value;
        navigator.clipboard.writeText(link).then(function() {
            notify('success', 'রেফারেল লিংক কপি হয়েছে!');
        }).catch(function() {
            var temp = document.createElement('input');
            document.body.appendChild(temp);
            temp.value = link;
            temp.select();
            document.execCommand('copy');
            document.body.removeChild(temp);
            notify('success', 'রেফারেল লিংক কপি হয়েছে!');
        });
    };

    // Banner Auto Slider
    var bannerSlides = document.querySelectorAll('.banner-slide');
    var bannerDots = document.querySelectorAll('.banner-dots .dot');
    var currentSlide = 0;
    var slideInterval;

    function showSlide(index) {
        if(bannerSlides.length === 0) return;
        bannerSlides.forEach(function(slide) { slide.classList.remove('active'); });
        bannerDots.forEach(function(dot) { dot.classList.remove('active'); });
        currentSlide = index;
        if(currentSlide >= bannerSlides.length) currentSlide = 0;
        if(currentSlide < 0) currentSlide = bannerSlides.length - 1;
        bannerSlides[currentSlide].classList.add('active');
        if(bannerDots[currentSlide]) bannerDots[currentSlide].classList.add('active');
    }

    function nextSlide() {
        showSlide(currentSlide + 1);
    }

    if(bannerSlides.length > 1) {
        slideInterval = setInterval(nextSlide, 4000);
        bannerDots.forEach(function(dot, index) {
            dot.addEventListener('click', function() {
                clearInterval(slideInterval);
                showSlide(index);
                slideInterval = setInterval(nextSlide, 4000);
            });
        });
    }

    // Spotlight Auto Slider
    var spotSlides = document.querySelectorAll('.ps-slide');
    var spotDots = document.querySelectorAll('.ps-dot');
    var currentSpot = 0;
    var spotInterval;

    function showSpotSlide(index) {
        if(spotSlides.length === 0) return;
        spotSlides.forEach(function(s) { s.classList.remove('active'); });
        spotDots.forEach(function(d) { d.classList.remove('active'); });
        currentSpot = index;
        if(currentSpot >= spotSlides.length) currentSpot = 0;
        if(currentSpot < 0) currentSpot = spotSlides.length - 1;
        spotSlides[currentSpot].classList.add('active');
        if(spotDots[currentSpot]) spotDots[currentSpot].classList.add('active');
    }

    function nextSpotSlide() {
        showSpotSlide(currentSpot + 1);
    }

    if(spotSlides.length > 1) {
        spotInterval = setInterval(nextSpotSlide, 5000);
        spotDots.forEach(function(dot, index) {
            dot.addEventListener('click', function() {
                clearInterval(spotInterval);
                showSpotSlide(index);
                spotInterval = setInterval(nextSpotSlide, 5000);
            });
        });
    }

    // Audio Mini Player - Protected
    var audio = document.getElementById('audioEl');
    var btn = document.getElementById('audioBtn');
    var audioBtnLabel = document.getElementById('audioBtnLabel');
    var audioBtnIcon = btn ? btn.querySelector('i') : null;
    var wave = document.querySelector('.amp-wave');
    var audioSrcEl = document.getElementById('audioSrc');
    var audioLoaded = false;

    if(btn && audio && audioSrcEl) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            // Load audio source only on first play (prevents direct URL access)
            if(!audioLoaded && audioSrcEl.value) {
                audio.src = audioSrcEl.value;
                audioLoaded = true;
            }

            if(audio.paused) {
                audio.play().catch(function(err) {
                    console.log('Audio play failed:', err);
                });
            } else {
                audio.pause();
            }
        });

        audio.addEventListener('play', function() {
            if (audioBtnIcon) audioBtnIcon.className = 'fas fa-pause';
            btn.setAttribute('aria-pressed', 'true');
            if (audioBtnLabel) audioBtnLabel.textContent = 'Pause audio';
            btn.classList.add('playing');
            if(wave) wave.classList.remove('paused');
        });

        audio.addEventListener('pause', function() {
            if (audioBtnIcon) audioBtnIcon.className = 'fas fa-play';
            btn.setAttribute('aria-pressed', 'false');
            if (audioBtnLabel) audioBtnLabel.textContent = 'Play audio';
            btn.classList.remove('playing');
            if(wave) wave.classList.add('paused');
        });

        // Prevent right-click download
        audio.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            return false;
        });

        // Initial state
        if(wave) wave.classList.add('paused');
    }

    // Balance Hide/Show Toggle
    var ugbEyeBtn = document.getElementById('ugbEyeBtn');
    var ugbAmount = document.getElementById('ugbAmount');
    var ugbAmountHidden = document.getElementById('ugbAmountHidden');
    var ugbEyeIcon = document.getElementById('ugbEyeIcon');
    var ugbEyeLabel = document.getElementById('ugbEyeLabel');
    var balanceVisible = true;

    if(ugbEyeBtn && ugbAmount && ugbAmountHidden && ugbEyeIcon) {
        ugbEyeBtn.addEventListener('click', function() {
            balanceVisible = !balanceVisible;
            if(balanceVisible) {
                ugbAmount.style.display = 'inline';
                ugbAmountHidden.style.display = 'none';
                ugbEyeIcon.className = 'fas fa-eye';
                ugbEyeBtn.setAttribute('aria-pressed', 'false');
                if (ugbEyeLabel) ugbEyeLabel.textContent = 'Hide balance';
            } else {
                ugbAmount.style.display = 'none';
                ugbAmountHidden.style.display = 'inline';
                ugbEyeIcon.className = 'fas fa-eye-slash';
                ugbEyeBtn.setAttribute('aria-pressed', 'true');
                if (ugbEyeLabel) ugbEyeLabel.textContent = 'Show balance';
            }
        });
    }

    // ============================================
    // FB Story Style Spotlight Viewer
    // ============================================
    var spotlightDataEl = document.getElementById('spotlightData');
    var spotlightViewer = document.getElementById('spotlightStoryViewer');

    if(spotlightDataEl && spotlightViewer) {
        var spotlightData = JSON.parse(spotlightDataEl.textContent);
        var currentSpotIndex = 0;
        var storyTimer = null;
        var storyDuration = 5000; // 5 seconds per story

        var ssvImage = document.getElementById('ssvImage');
        var ssvTitle = document.querySelector('.ssv-title');
        var ssvDescription = document.getElementById('ssvDescription');
        var ssvLinkBtn = document.getElementById('ssvLinkBtn');
        var ssvClose = document.getElementById('ssvClose');
        var ssvPrev = document.getElementById('ssvPrev');
        var ssvNext = document.getElementById('ssvNext');
        var ssvOverlay = document.querySelector('.ssv-overlay');
        var progressSegments = document.querySelectorAll('.ssv-progress-segment');

        // Open spotlight viewer
        document.querySelectorAll('.spotlight-story-trigger').forEach(function(trigger) {
            trigger.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var index = parseInt(this.getAttribute('data-spotlight-index'));
                openSpotlightViewer(index);
            });
        });

        function openSpotlightViewer(index) {
            currentSpotIndex = index;
            spotlightViewer.classList.add('active');
            document.body.style.overflow = 'hidden';
            showSpotlight(currentSpotIndex);
            startStoryTimer();
        }

        function closeSpotlightViewer() {
            spotlightViewer.classList.remove('active');
            document.body.style.overflow = '';
            clearTimeout(storyTimer);
            resetProgress();
        }

        function showSpotlight(index) {
            if(index < 0 || index >= spotlightData.length) return;

            var spotlight = spotlightData[index];
            ssvImage.src = spotlight.image;
            ssvTitle.textContent = spotlight.title;
            ssvDescription.textContent = spotlight.description || '';

            if(spotlight.link) {
                ssvLinkBtn.href = spotlight.link;
                ssvLinkBtn.classList.remove('hidden');
            } else {
                ssvLinkBtn.classList.add('hidden');
            }

            // Update progress bars
            updateProgressBars(index);
        }

        function updateProgressBars(activeIndex) {
            progressSegments.forEach(function(seg, i) {
                seg.classList.remove('active', 'completed');
                var fill = seg.querySelector('.ssv-progress-fill');
                fill.style.width = '0%';
                fill.style.animation = 'none';

                if(i < activeIndex) {
                    seg.classList.add('completed');
                    fill.style.width = '100%';
                } else if(i === activeIndex) {
                    seg.classList.add('active');
                    // Trigger reflow to restart animation
                    void fill.offsetWidth;
                    fill.style.animation = '';
                }
            });
        }

        function resetProgress() {
            progressSegments.forEach(function(seg) {
                seg.classList.remove('active', 'completed');
                var fill = seg.querySelector('.ssv-progress-fill');
                fill.style.width = '0%';
                fill.style.animation = 'none';
            });
        }

        function startStoryTimer() {
            clearTimeout(storyTimer);
            storyTimer = setTimeout(function() {
                nextStory();
            }, storyDuration);
        }

        function nextStory() {
            if(currentSpotIndex < spotlightData.length - 1) {
                currentSpotIndex++;
                showSpotlight(currentSpotIndex);
                startStoryTimer();
            } else {
                closeSpotlightViewer();
            }
        }

        function prevStory() {
            if(currentSpotIndex > 0) {
                currentSpotIndex--;
                showSpotlight(currentSpotIndex);
                startStoryTimer();
            }
        }

        // Event listeners
        ssvClose.addEventListener('click', closeSpotlightViewer);
        ssvOverlay.addEventListener('click', closeSpotlightViewer);
        ssvNext.addEventListener('click', function(e) {
            e.stopPropagation();
            nextStory();
        });
        ssvPrev.addEventListener('click', function(e) {
            e.stopPropagation();
            prevStory();
        });

        // Touch/click zones for navigation
        var ssvContent = document.querySelector('.ssv-content');
        if(ssvContent) {
            ssvContent.addEventListener('click', function(e) {
                var rect = this.getBoundingClientRect();
                var x = e.clientX - rect.left;
                var width = rect.width;

                if(x < width * 0.3) {
                    prevStory();
                } else if(x > width * 0.7) {
                    nextStory();
                }
            });
        }

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            if(!spotlightViewer.classList.contains('active')) return;

            if(e.key === 'Escape') {
                closeSpotlightViewer();
            } else if(e.key === 'ArrowRight' || e.key === ' ') {
                nextStory();
            } else if(e.key === 'ArrowLeft') {
                prevStory();
            }
        });

        // Pause timer when hovering/touching content
        var isPaused = false;
        ssvImage.addEventListener('mousedown', function() {
            clearTimeout(storyTimer);
            isPaused = true;
            var activeFill = document.querySelector('.ssv-progress-segment.active .ssv-progress-fill');
            if(activeFill) activeFill.style.animationPlayState = 'paused';
        });

        document.addEventListener('mouseup', function() {
            if(isPaused && spotlightViewer.classList.contains('active')) {
                isPaused = false;
                var activeFill = document.querySelector('.ssv-progress-segment.active .ssv-progress-fill');
                if(activeFill) activeFill.style.animationPlayState = 'running';
                startStoryTimer();
            }
        });

        // Touch support
        ssvImage.addEventListener('touchstart', function() {
            clearTimeout(storyTimer);
            isPaused = true;
            var activeFill = document.querySelector('.ssv-progress-segment.active .ssv-progress-fill');
            if(activeFill) activeFill.style.animationPlayState = 'paused';
        }, { passive: true });

        ssvImage.addEventListener('touchend', function() {
            if(isPaused && spotlightViewer.classList.contains('active')) {
                isPaused = false;
                var activeFill = document.querySelector('.ssv-progress-segment.active .ssv-progress-fill');
                if(activeFill) activeFill.style.animationPlayState = 'running';
                startStoryTimer();
            }
        }, { passive: true });
    }

    // Red Bag Badge Update for Service Grid and Modal
    window.updateRedBagServiceBadge = function(isAvailable, count) {
        var serviceBadge = document.getElementById('redBagServiceBadge');
        var modalBadge = document.getElementById('modalRedBagBadge');
        
        if (serviceBadge) {
            if (isAvailable && count > 0) {
                serviceBadge.style.display = 'block';
            } else {
                serviceBadge.style.display = 'none';
            }
        }
        
        if (modalBadge) {
            if (isAvailable && count > 0) {
                modalBadge.textContent = count;
                modalBadge.style.display = 'block';
            } else {
                modalBadge.style.display = 'none';
            }
        }
    };

    // Hook into the existing red bag check function
    var originalCheckRedBagAvailability = window.checkRedBagAvailability;
    if (typeof originalCheckRedBagAvailability === 'function') {
        window.checkRedBagAvailability = function() {
            originalCheckRedBagAvailability();
            // The badge will be updated by the check response
        };
    }

    // Auto-scroll loop for brands section
    var brandsScroll = document.querySelector('.brands-scroll');
    if (brandsScroll && brandsScroll.children.length > 0) {
        var scrollSpeed = 1; // pixels per frame
        var scrollDelay = 30; // milliseconds between frames
        var pauseAtEnd = 2000; // pause duration at end (ms)
        var isUserScrolling = false;
        var userScrollTimeout;
        var autoScrollInterval;
        
        function startAutoScroll() {
            if (autoScrollInterval) clearInterval(autoScrollInterval);
            
            autoScrollInterval = setInterval(function() {
                if (isUserScrolling) return;
                
                var maxScroll = brandsScroll.scrollWidth - brandsScroll.clientWidth;
                
                if (brandsScroll.scrollLeft >= maxScroll - 1) {
                    // Reached end, pause then reset
                    clearInterval(autoScrollInterval);
                    setTimeout(function() {
                        brandsScroll.scrollTo({ left: 0, behavior: 'smooth' });
                        setTimeout(startAutoScroll, 1000);
                    }, pauseAtEnd);
                } else {
                    brandsScroll.scrollLeft += scrollSpeed;
                }
            }, scrollDelay);
        }
        
        // Detect user scrolling
        brandsScroll.addEventListener('touchstart', function() {
            isUserScrolling = true;
            clearInterval(autoScrollInterval);
        });
        
        brandsScroll.addEventListener('touchend', function() {
            clearTimeout(userScrollTimeout);
            userScrollTimeout = setTimeout(function() {
                isUserScrolling = false;
                startAutoScroll();
            }, 3000); // Resume after 3 seconds of no touch
        });
        
        brandsScroll.addEventListener('scroll', function() {
            if (!isUserScrolling) return;
            clearTimeout(userScrollTimeout);
            userScrollTimeout = setTimeout(function() {
                isUserScrolling = false;
                startAutoScroll();
            }, 3000);
        });
        
        // Start auto-scroll after page load
        setTimeout(startAutoScroll, 1500);
    }

    // Auto-scroll loop for blog section
    var blogScroll = document.querySelector('.blog-scroll');
    if (blogScroll && blogScroll.children.length > 0) {
        var blogScrollSpeed = 1;
        var blogScrollDelay = 30;
        var blogPauseAtEnd = 2000;
        var isBlogUserScrolling = false;
        var blogUserScrollTimeout;
        var blogAutoScrollInterval;
        
        function startBlogAutoScroll() {
            if (blogAutoScrollInterval) clearInterval(blogAutoScrollInterval);
            
            blogAutoScrollInterval = setInterval(function() {
                if (isBlogUserScrolling) return;
                
                var maxScroll = blogScroll.scrollWidth - blogScroll.clientWidth;
                
                if (blogScroll.scrollLeft >= maxScroll - 1) {
                    clearInterval(blogAutoScrollInterval);
                    setTimeout(function() {
                        blogScroll.scrollTo({ left: 0, behavior: 'smooth' });
                        setTimeout(startBlogAutoScroll, 1000);
                    }, blogPauseAtEnd);
                } else {
                    blogScroll.scrollLeft += blogScrollSpeed;
                }
            }, blogScrollDelay);
        }
        
        blogScroll.addEventListener('touchstart', function() {
            isBlogUserScrolling = true;
            clearInterval(blogAutoScrollInterval);
        });
        
        blogScroll.addEventListener('touchend', function() {
            clearTimeout(blogUserScrollTimeout);
            blogUserScrollTimeout = setTimeout(function() {
                isBlogUserScrolling = false;
                startBlogAutoScroll();
            }, 3000);
        });
        
        blogScroll.addEventListener('scroll', function() {
            if (!isBlogUserScrolling) return;
            clearTimeout(blogUserScrollTimeout);
            blogUserScrollTimeout = setTimeout(function() {
                isBlogUserScrolling = false;
                startBlogAutoScroll();
            }, 3000);
        });
        
        setTimeout(startBlogAutoScroll, 2000);
    }

})(jQuery);
</script>
@endpush
