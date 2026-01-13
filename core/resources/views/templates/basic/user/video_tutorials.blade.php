@extends($activeTemplate.'layouts.master_mobile')
@section('main-content')
@php
    $tutorials = \App\Models\VideoTutorial::active()->ordered()->paginate(10);
@endphp

<!-- Compressed Header -->
<div class="tutorial-header-compact">
    <div class="header-content">
        <div class="header-left">
            <div class="header-icon">
                <i class="fas fa-video"></i>
            </div>
            <div class="header-text">
                <h2 class="header-title">ভিডিও টিউটোরিয়াল</h2>
                <p class="header-subtitle">ধাপে ধাপে শিখুন</p>
            </div>
        </div>
        <div class="header-badge">
            <span class="badge-number">{{ $tutorials->total() }}</span>
            <span class="badge-text">টি ভিডিও</span>
        </div>
    </div>
</div>

<div class="tutorial-content">
    @forelse($tutorials as $index => $tutorial)
    <a href="{{ route('user.video.tutorial.view', $tutorial->id) }}" class="tutorial-card">
        <div class="tutorial-thumb">
            <img src="{{ getImage(getFilePath('tutorial').'/'.$tutorial->thumbnail, '320x180') }}" alt="{{ $tutorial->title }}">
            <div class="play-button">
                <i class="fas fa-play"></i>
            </div>
            <div class="lesson-number">
                <span>পাঠ {{ $tutorial->lesson_number }}</span>
            </div>
        </div>
        <div class="tutorial-info">
            <h4 class="tutorial-title">{{ $tutorial->title }}</h4>
            <p class="tutorial-desc">{{ Str::limit($tutorial->description, 80) }}</p>
            <div class="tutorial-meta">
                <span class="meta-item">
                    <i class="fas fa-clock"></i>
                    {{ $tutorial->created_at->diffForHumans() }}
                </span>
            </div>
        </div>
        <div class="tutorial-arrow">
            <i class="fas fa-chevron-right"></i>
        </div>
    </a>
    @empty
    <div class="tutorial-empty">
        <div class="empty-icon">
            <i class="fas fa-video"></i>
        </div>
        <h4>কোনো টিউটোরিয়াল নেই</h4>
        <p>সহায়ক ভিডিও টিউটোরিয়ালের জন্য পরে আবার দেখুন</p>
    </div>
    @endforelse

    @if($tutorials->hasPages())
    <div class="tutorial-pagination">
        {{ $tutorials->links() }}
    </div>
    @endif

    <!-- Quick Help Card -->
    <div class="quick-help-card">
        <div class="qh-bg">
            <svg viewBox="0 0 100 100" preserveAspectRatio="none">
                <circle cx="85" cy="15" r="25" fill="rgba(255,255,255,0.1)"/>
            </svg>
        </div>
        <div class="qh-icon">
            <i class="fas fa-lightbulb"></i>
        </div>
        <div class="qh-text">
            <h4>আরও সাহায্য প্রয়োজন?</h4>
            <p>আমাদের FAQ দেখুন অথবা সাপোর্টে যোগাযোগ করুন</p>
        </div>
        <div class="qh-actions">
            <a href="{{ route('user.faq') }}" class="qh-btn outline">
                <i class="fas fa-question-circle"></i>
                প্রশ্নোত্তর
            </a>
            <a href="{{ route('ticket') }}" class="qh-btn filled">
                <i class="fas fa-headset"></i>
                সাপোর্ট
            </a>
        </div>
    </div>
</div>
@endsection

@push('style')
<style>
/* Compressed Header */
.tutorial-header-compact {
    background: linear-gradient(135deg, #FF6F00, #FF9800);
    padding: 16px 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.header-left {
    display: flex;
    align-items: center;
    gap: 12px;
}
.header-icon {
    width: 44px;
    height: 44px;
    background: rgba(255,255,255,0.2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.header-icon i {
    font-size: 20px;
    color: #fff;
}
.header-text {
    display: flex;
    flex-direction: column;
}
.header-title {
    color: #fff;
    font-size: 18px;
    font-weight: 700;
    margin: 0;
    line-height: 1.2;
}
.header-subtitle {
    color: rgba(255,255,255,0.9);
    font-size: 12px;
    margin: 2px 0 0;
}
.header-badge {
    display: flex;
    flex-direction: column;
    align-items: center;
    background: rgba(255,255,255,0.2);
    padding: 8px 12px;
    border-radius: 10px;
}
.badge-number {
    font-size: 20px;
    font-weight: 700;
    color: #fff;
    line-height: 1;
}
.badge-text {
    font-size: 10px;
    color: rgba(255,255,255,0.9);
    margin-top: 2px;
}

/* Tutorial Content */
.tutorial-content {
    padding: 20px 16px;
}

/* Tutorial Card */
.tutorial-card {
    display: flex;
    align-items: center;
    gap: 14px;
    background: #fff;
    border-radius: 16px;
    padding: 12px;
    margin-bottom: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    text-decoration: none;
    transition: all 0.3s ease;
}
.tutorial-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
}
.tutorial-thumb {
    width: 100px;
    height: 70px;
    border-radius: 12px;
    overflow: hidden;
    position: relative;
    flex-shrink: 0;
}
.tutorial-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.play-button {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 36px;
    height: 36px;
    background: rgba(255,111,0,0.9);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}
.play-button i {
    color: #fff;
    font-size: 12px;
    margin-left: 2px;
}
.tutorial-card:hover .play-button {
    transform: translate(-50%, -50%) scale(1.1);
    background: #FF6F00;
}
.lesson-number {
    position: absolute;
    bottom: 6px;
    left: 6px;
    background: rgba(0,0,0,0.7);
    color: #fff;
    padding: 3px 8px;
    border-radius: 6px;
    font-size: 10px;
    font-weight: 600;
}
.tutorial-info {
    flex: 1;
    min-width: 0;
}
.tutorial-title {
    font-size: 14px;
    font-weight: 600;
    color: #1a1a2e;
    margin-bottom: 4px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.tutorial-desc {
    font-size: 12px;
    color: #777;
    margin-bottom: 6px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.tutorial-meta {
    display: flex;
    gap: 12px;
}
.meta-item {
    font-size: 11px;
    color: #999;
    display: flex;
    align-items: center;
    gap: 4px;
}
.meta-item i {
    font-size: 10px;
    color: #FF9800;
}
.tutorial-arrow {
    width: 28px;
    height: 28px;
    background: rgba(255,111,0,0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.tutorial-arrow i {
    font-size: 11px;
    color: #FF6F00;
}

/* Empty State */
.tutorial-empty {
    text-align: center;
    padding: 48px 20px;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
}
.empty-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, rgba(255,111,0,0.1), rgba(255,152,0,0.1));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
}
.empty-icon i {
    font-size: 36px;
    color: #FF6F00;
}
.tutorial-empty h4 {
    color: #1a1a2e;
    font-size: 18px;
    margin-bottom: 8px;
}
.tutorial-empty p {
    color: #888;
    font-size: 14px;
    margin: 0;
}

/* Pagination */
.tutorial-pagination {
    margin: 20px 0;
}

/* Quick Help Card */
.quick-help-card {
    background: linear-gradient(135deg, #0F743C, #12925a);
    border-radius: 16px;
    padding: 20px;
    position: relative;
    overflow: hidden;
    margin-top: 20px;
}
.qh-bg {
    position: absolute;
    top: 0;
    right: 0;
    width: 120px;
    height: 120px;
}
.qh-bg svg {
    width: 100%;
    height: 100%;
}
.qh-icon {
    width: 44px;
    height: 44px;
    background: rgba(255,255,255,0.2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 12px;
    position: relative;
}
.qh-icon i {
    font-size: 20px;
    color: #fff;
}
.qh-text {
    position: relative;
    margin-bottom: 16px;
}
.qh-text h4 {
    color: #fff;
    font-size: 16px;
    font-weight: 700;
    margin-bottom: 4px;
}
.qh-text p {
    color: rgba(255,255,255,0.8);
    font-size: 13px;
    margin: 0;
}
.qh-actions {
    display: flex;
    gap: 10px;
    position: relative;
}
.qh-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 16px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
}
.qh-btn.outline {
    background: transparent;
    border: 1.5px solid rgba(255,255,255,0.5);
    color: #fff;
}
.qh-btn.outline:hover {
    background: rgba(255,255,255,0.1);
    border-color: #fff;
    color: #fff;
}
.qh-btn.filled {
    background: #fff;
    color: #0F743C;
}
.qh-btn.filled:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    color: #0F743C;
}
.qh-btn i {
    font-size: 14px;
}
</style>
@endpush

