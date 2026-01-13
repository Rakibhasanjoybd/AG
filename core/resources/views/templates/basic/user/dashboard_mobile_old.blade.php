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
    $spotlights = \App\Models\DailySpotlight::active()->ordered()->limit(6)->get();
    $tutorials = \App\Models\VideoTutorial::active()->ordered()->limit(3)->get();
    $todayClicks = $user->clicks->where('view_date', Date('Y-m-d'))->count();
    $remainingClicks = $user->daily_limit - $todayClicks;
@endphp

<!-- Announcement Marquee -->
@if($announcements->count() > 0)
<div class="announce-bar">
    <div class="announce-icon"><i class="fas fa-bullhorn"></i></div>
    <div class="announce-scroll">
        <div class="announce-text">
            @foreach($announcements as $a){{ $a->content }} &nbsp;&nbsp;•&nbsp;&nbsp; @endforeach
        </div>
    </div>
</div>
@endif

<!-- Balance Card -->
<div class="balance-card">
    <div class="balance-top">
        <div class="balance-info">
            <span class="balance-label">মোট ব্যালেন্স</span>
            <h2 class="balance-amount">৳{{ showAmount($user->balance) }}</h2>
        </div>
        <div class="plan-badge {{ $user->plan ? 'active' : '' }}">
            <i class="fas fa-crown"></i>
            <span>{{ $user->plan ? $user->plan->name : 'ফ্রি' }}</span>
        </div>
    </div>
    <div class="balance-actions">
        <a href="{{ route('user.deposit') }}" class="action-btn deposit">
            <i class="fas fa-plus-circle"></i>
            <span>ডিপোজিট</span>
        </a>
        <a href="{{ route('user.withdraw') }}" class="action-btn withdraw">
            <i class="fas fa-arrow-up"></i>
            <span>উত্তোলন</span>
        </a>
        <a href="{{ route('user.referred') }}" class="action-btn refer">
            <i class="fas fa-users"></i>
            <span>রেফার</span>
        </a>
    </div>
</div>

<!-- Banner Slider -->
@if($banners->count() > 0)
<div class="banner-section">
    <div id="bannerCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            @foreach($banners as $key => $banner)
            <div class="carousel-item {{ $key == 0 ? 'active' : '' }}">
                <img src="{{ getImage('assets/images/frontend/banner/' . @$banner->data_values->image) }}" alt="Banner" class="banner-img">
            </div>
            @endforeach
        </div>
        <div class="carousel-dots">
            @foreach($banners as $key => $banner)
            <span class="dot {{ $key == 0 ? 'active' : '' }}" data-bs-target="#bannerCarousel" data-bs-slide-to="{{ $key }}"></span>
            @endforeach
        </div>
    </div>
</div>
@endif

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card purple">
        <div class="stat-icon"><i class="fas fa-mouse-pointer"></i></div>
        <div class="stat-info">
            <span class="stat-value">{{ $todayClicks }}/{{ $user->daily_limit }}</span>
            <span class="stat-label">আজকের ক্লিক</span>
        </div>
    </div>
    <div class="stat-card orange">
        <div class="stat-icon"><i class="fas fa-coins"></i></div>
        <div class="stat-info">
            <span class="stat-value">৳{{ showAmount($user->deposits->sum('amount')) }}</span>
            <span class="stat-label">মোট ডিপোজিট</span>
        </div>
    </div>
    <div class="stat-card crimson">
        <div class="stat-icon"><i class="fas fa-wallet"></i></div>
        <div class="stat-info">
            <span class="stat-value">৳{{ showAmount($user->withdrawals->where('status',1)->sum('amount')) }}</span>
            <span class="stat-label">মোট উত্তোলন</span>
        </div>
    </div>
    <div class="stat-card gold">
        <div class="stat-icon"><i class="fas fa-user-friends"></i></div>
        <div class="stat-info">
            <span class="stat-value">৳{{ showAmount($user->referral_commission_hold ?? 0) }}</span>
            <span class="stat-label">রেফারেল আয়</span>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="section-block">
    <div class="section-head">
        <h3>দ্রুত মেনু</h3>
    </div>
    <div class="quick-menu">
        <a href="{{ route('user.ptc.index') }}" class="quick-item">
            <div class="quick-icon purple"><i class="fas fa-ad"></i></div>
            <span>বিজ্ঞাপন দেখুন</span>
        </a>
        <a href="{{ route('plans') }}" class="quick-item">
            <div class="quick-icon gold"><i class="fas fa-crown"></i></div>
            <span>প্ল্যান কিনুন</span>
        </a>
        <a href="{{ route('user.deposit.history') }}" class="quick-item">
            <div class="quick-icon crimson"><i class="fas fa-history"></i></div>
            <span>ডিপোজিট ইতিহাস</span>
        </a>
        <a href="{{ route('user.withdraw.history') }}" class="quick-item">
            <div class="quick-icon orange"><i class="fas fa-receipt"></i></div>
            <span>উত্তোলন ইতিহাস</span>
        </a>
        <a href="{{ route('user.video.tutorials') }}" class="quick-item">
            <div class="quick-icon purple"><i class="fas fa-play-circle"></i></div>
            <span>টিউটোরিয়াল</span>
        </a>
        <a href="{{ route('user.faq') }}" class="quick-item">
            <div class="quick-icon gold"><i class="fas fa-question-circle"></i></div>
            <span>প্রশ্নোত্তর</span>
        </a>
        <a href="{{ route('ticket') }}" class="quick-item">
            <div class="quick-icon crimson"><i class="fas fa-headset"></i></div>
            <span>সাপোর্ট</span>
        </a>
        <a href="{{ route('user.referred') }}" class="quick-item">
            <div class="quick-icon orange"><i class="fas fa-share-alt"></i></div>
            <span>রেফারেল লিংক</span>
        </a>
    </div>
</div>

<!-- Earn Section -->
<div class="section-block">
    <div class="section-head">
        <h3>আয় করুন</h3>
        <span class="remaining-badge">বাকি: {{ $remainingClicks }} টি</span>
    </div>

    <a href="{{ route('user.ptc.index') }}" class="earn-card">
        <div class="earn-left">
            <div class="earn-icon"><i class="fas fa-tv"></i></div>
            <div class="earn-info">
                <h4>বিজ্ঞাপন দেখে আয়</h4>
                <p>প্রতিটি বিজ্ঞাপনে ৳০.৯৫ আয় করুন</p>
            </div>
        </div>
        <div class="earn-btn">শুরু করুন</div>
    </a>

    @if($user->is_premium)
    <a href="{{ route('plans') }}" class="earn-card vip">
        <div class="earn-left">
            <div class="earn-icon vip"><i class="fas fa-star"></i></div>
            <div class="earn-info">
                <h4>ভিআইপি টাস্ক</h4>
                <p>বড় আয়ের সুযোগ - প্রিমিয়াম সদস্যদের জন্য</p>
            </div>
        </div>
        <div class="earn-btn vip">দেখুন</div>
    </a>
    @else
    <a href="{{ route('plans') }}" class="earn-card locked">
        <div class="earn-left">
            <div class="earn-icon locked"><i class="fas fa-lock"></i></div>
            <div class="earn-info">
                <h4>ভিআইপি টাস্ক</h4>
                <p>প্রিমিয়াম প্ল্যান কিনে ভিআইপি টাস্ক আনলক করুন</p>
            </div>
        </div>
        <div class="earn-btn locked">আনলক করুন</div>
    </a>
    @endif
</div>

<!-- Daily Spotlight -->
@if($spotlights->count() > 0)
<div class="section-block">
    <div class="section-head">
        <h3>ডেইলি স্পটলাইট</h3>
        <a href="{{ route('user.spotlights') }}" class="see-all">সব দেখুন</a>
    </div>
    <div class="spotlight-scroll">
        @foreach($spotlights as $spotlight)
        <a href="{{ $spotlight->link ?? '#' }}" class="spotlight-item">
            <img src="{{ getImage(getFilePath('spotlight').'/'.$spotlight->image, '140x100') }}" alt="{{ $spotlight->title }}">
            <span>{{ Str::limit($spotlight->title, 12) }}</span>
        </a>
        @endforeach
    </div>
</div>
@endif

<!-- Video Tutorials -->
@if($tutorials->count() > 0)
<div class="section-block">
    <div class="section-head">
        <h3>ভিডিও টিউটোরিয়াল</h3>
        <a href="{{ route('user.video.tutorials') }}" class="see-all">সব দেখুন</a>
    </div>
    @foreach($tutorials as $tutorial)
    <a href="{{ route('user.video.tutorial.view', $tutorial->id) }}" class="tutorial-item">
        <div class="tutorial-thumb">
            <img src="{{ getImage(getFilePath('tutorial').'/'.$tutorial->thumbnail, '320x180') }}" alt="{{ $tutorial->title }}">
            <div class="play-icon"><i class="fas fa-play"></i></div>
        </div>
        <div class="tutorial-info">
            <h4>{{ $tutorial->title }}</h4>
            <p>পাঠ {{ $tutorial->lesson_number }}</p>
        </div>
    </a>
    @endforeach
</div>
@endif

<!-- Audio Player -->
@if($audioPlayer)
<div class="audio-section">
    <div class="audio-card">
        <img src="{{ getImage(getFilePath('audioPlayer').'/'.$audioPlayer->thumbnail, '50x50') }}" alt="{{ $audioPlayer->title }}" class="audio-img">
        <div class="audio-details">
            <span class="audio-title">{{ $audioPlayer->title }}</span>
            <div class="audio-progress">
                <div class="progress-fill" id="audioProgress"></div>
            </div>
        </div>
        <button class="audio-play" id="playAudioBtn"><i class="fas fa-play"></i></button>
    </div>
    <audio id="mainAudio" src="{{ asset('assets/audio/'.$audioPlayer->audio_file) }}" {{ $audioPlayer->autoplay ? 'autoplay' : '' }} {{ $audioPlayer->loop ? 'loop' : '' }}></audio>
</div>
@endif

<div style="height: 20px;"></div>
@endsection

@push('style')
<style>
/* Announcement */
.announce-bar{display:flex;align-items:center;background:var(--gold);padding:10px 16px;gap:10px}
.announce-icon{color:var(--purple);font-size:14px}
.announce-scroll{flex:1;overflow:hidden}
.announce-text{white-space:nowrap;animation:scroll 20s linear infinite;color:var(--purple);font-size:12px;font-weight:600}
@keyframes scroll{0%{transform:translateX(0)}100%{transform:translateX(-50%)}}

/* Balance Card */
.balance-card{background:var(--purple);margin:16px;border-radius:20px;padding:20px}
.balance-top{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px}
.balance-label{color:rgba(255,255,255,0.7);font-size:13px}
.balance-amount{color:var(--white);font-size:32px;font-weight:800;margin-top:4px}
.plan-badge{background:var(--crimson);color:var(--white);padding:6px 12px;border-radius:20px;font-size:11px;font-weight:600;display:flex;align-items:center;gap:6px}
.plan-badge.active{background:var(--gold);color:var(--purple)}
.balance-actions{display:flex;gap:10px}
.action-btn{flex:1;background:rgba(255,255,255,0.15);border-radius:14px;padding:14px 10px;text-align:center;text-decoration:none;color:var(--white)}
.action-btn i{font-size:20px;display:block;margin-bottom:6px}
.action-btn span{font-size:12px;font-weight:600}
.action-btn.deposit{background:var(--crimson)}
.action-btn.withdraw{background:var(--orange)}
.action-btn.refer{background:var(--gold);color:var(--purple)}

/* Banner */
.banner-section{margin:0 16px 16px;border-radius:16px;overflow:hidden}
.banner-img{width:100%;height:140px;object-fit:cover;border-radius:16px}
.carousel-dots{text-align:center;padding:10px 0}
.dot{width:8px;height:8px;background:#ddd;border-radius:50%;display:inline-block;margin:0 4px;cursor:pointer}
.dot.active{background:var(--purple);width:20px;border-radius:4px}

/* Stats Grid */
.stats-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;padding:0 16px;margin-bottom:20px}
.stat-card{border-radius:16px;padding:16px;display:flex;align-items:center;gap:12px;box-shadow:0 2px 8px rgba(0,0,0,0.08)}
.stat-card.purple{background:linear-gradient(135deg,#52006A,#6b0a85)}
.stat-card.orange{background:linear-gradient(135deg,#FF7600,#ff8c2e)}
.stat-card.crimson{background:linear-gradient(135deg,#CD113B,#e01d4f)}
.stat-card.gold{background:linear-gradient(135deg,#FFA900,#ffb726)}
.stat-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:18px;background:rgba(255,255,255,0.2);color:var(--white)}
.stat-info{flex:1}
.stat-value{font-size:16px;font-weight:700;color:var(--white);display:block}
.stat-label{font-size:11px;color:rgba(255,255,255,0.8)}

/* Section Block */
.section-block{background:var(--white);margin:0 16px 16px;border-radius:20px;padding:16px;box-shadow:0 2px 10px rgba(0,0,0,0.04)}
.section-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px}
.section-head h3{font-size:16px;font-weight:700;color:var(--dark-text)}
.see-all{color:var(--purple);font-size:12px;font-weight:600;text-decoration:none}
.remaining-badge{background:var(--crimson);color:var(--white);padding:4px 10px;border-radius:20px;font-size:11px;font-weight:600}

/* Quick Menu */
.quick-menu{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}
.quick-item{text-align:center;text-decoration:none}
.quick-icon{width:48px;height:48px;border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 8px;font-size:20px}
.quick-icon.purple{background:#f3e8ff;color:var(--purple)}
.quick-icon.crimson{background:#fce7f3;color:var(--crimson)}
.quick-icon.orange{background:#fff7ed;color:var(--orange)}
.quick-icon.gold{background:#fef9c3;color:var(--gold)}
.quick-item span{font-size:10px;color:var(--dark-text);font-weight:500;display:block}

/* Earn Cards */
.earn-card{display:flex;align-items:center;justify-content:space-between;background:#f8f9fa;border-radius:16px;padding:16px;margin-bottom:12px;text-decoration:none}
.earn-left{display:flex;align-items:center;gap:14px}
.earn-icon{width:50px;height:50px;background:var(--purple);border-radius:14px;display:flex;align-items:center;justify-content:center;color:var(--white);font-size:20px}
.earn-icon.vip{background:var(--gold);color:var(--purple)}
.earn-icon.locked{background:#e5e7eb;color:#9ca3af}
.earn-info h4{font-size:14px;font-weight:700;color:var(--dark-text);margin-bottom:4px}
.earn-info p{font-size:11px;color:var(--gray)}
.earn-btn{background:var(--purple);color:var(--white);padding:10px 16px;border-radius:10px;font-size:12px;font-weight:600}
.earn-btn.vip{background:var(--gold);color:var(--purple)}
.earn-btn.locked{background:#e5e7eb;color:#6b7280}

/* Spotlight */
.spotlight-scroll{display:flex;gap:12px;overflow-x:auto;padding-bottom:8px;-webkit-overflow-scrolling:touch}
.spotlight-scroll::-webkit-scrollbar{display:none}
.spotlight-item{flex-shrink:0;width:100px;text-align:center;text-decoration:none}
.spotlight-item img{width:100px;height:70px;object-fit:cover;border-radius:12px;margin-bottom:6px}
.spotlight-item span{font-size:11px;color:var(--dark-text);font-weight:500}

/* Tutorial */
.tutorial-item{display:flex;gap:14px;padding:12px 0;border-bottom:1px solid #f0f0f0;text-decoration:none}
.tutorial-item:last-child{border-bottom:none}
.tutorial-thumb{position:relative;width:100px;height:60px;flex-shrink:0}
.tutorial-thumb img{width:100%;height:100%;object-fit:cover;border-radius:10px}
.play-icon{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:28px;height:28px;background:var(--crimson);border-radius:50%;display:flex;align-items:center;justify-content:center}
.play-icon i{color:var(--white);font-size:10px;margin-left:2px}
.tutorial-info h4{font-size:13px;font-weight:600;color:var(--dark-text);margin-bottom:4px}
.tutorial-info p{font-size:11px;color:var(--gray)}

/* Audio */
.audio-section{margin:0 16px 16px}
.audio-card{background:var(--purple);border-radius:16px;padding:14px;display:flex;align-items:center;gap:12px}
.audio-img{width:44px;height:44px;border-radius:10px;object-fit:cover}
.audio-details{flex:1}
.audio-title{color:var(--white);font-size:13px;font-weight:600;display:block;margin-bottom:8px}
.audio-progress{height:4px;background:rgba(255,255,255,0.2);border-radius:2px}
.progress-fill{height:100%;background:var(--gold);border-radius:2px;width:0}
.audio-play{width:40px;height:40px;background:var(--gold);border:none;border-radius:50%;color:var(--purple);font-size:14px;cursor:pointer}
</style>
@endpush

@push('script')
<script>
(function($) {
    "use strict";

    // Audio Player
    var audio = document.getElementById('mainAudio');
    var playBtn = document.getElementById('playAudioBtn');
    var progress = document.getElementById('audioProgress');

    if(playBtn && audio) {
        playBtn.addEventListener('click', function() {
            if(audio.paused) {
                audio.play();
                this.innerHTML = '<i class="fas fa-pause"></i>';
            } else {
                audio.pause();
                this.innerHTML = '<i class="fas fa-play"></i>';
            }
        });

        audio.addEventListener('timeupdate', function() {
            var percent = (audio.currentTime / audio.duration) * 100;
            progress.style.width = percent + '%';
        });
    }

    // Carousel dots
    $('.dot').on('click', function(){
        var index = $(this).index();
        $('#bannerCarousel').carousel(index);
    });

    $('#bannerCarousel').on('slid.bs.carousel', function(e) {
        $('.dot').removeClass('active');
        $('.dot').eq(e.to).addClass('active');
    });

})(jQuery);
</script>
@endpush
