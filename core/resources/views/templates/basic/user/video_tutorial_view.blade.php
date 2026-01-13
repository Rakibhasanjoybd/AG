@extends($activeTemplate.'layouts.master_mobile')
@section('main-content')
<!-- Back Navigation -->
<div class="tutorial-view-header">
    <a href="{{ route('user.video.tutorials') }}" class="back-button">
        <i class="fas fa-arrow-left"></i>
    </a>
    <span class="header-title">@lang('Video Tutorial')</span>
</div>

<div class="tutorial-view-container">
    <!-- Video Player Section -->
    <div class="video-player-wrapper">
        @if(Str::contains($tutorial->video_url, ['youtube.com', 'youtu.be']))
            @php
                preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $tutorial->video_url, $matches);
                $videoId = $matches[1] ?? '';
            @endphp
            <div class="video-container">
                <iframe
                    title="{{ $tutorial->title }}"
                    src="https://www.youtube.com/embed/{{ $videoId }}?rel=0&modestbranding=1"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen>
                </iframe>
            </div>
        @else
            <div class="video-container">
                <video controls controlsList="nodownload">
                    <source src="{{ $tutorial->video_url }}" type="video/mp4">
                    <track kind="captions" src="" label="English" default>
                    @lang('Your browser does not support the video tag.')
                </video>
            </div>
        @endif
    </div>

    <!-- Tutorial Info Card -->
    <div class="tutorial-details-card">
        <div class="lesson-tag">
            <i class="fas fa-graduation-cap"></i>
            <span>@lang('Lesson') {{ $tutorial->lesson_number }}</span>
        </div>
        <h2 class="tutorial-main-title">{{ $tutorial->title }}</h2>
        <div class="tutorial-meta-info">
            <div class="meta-item-view">
                <i class="fas fa-calendar-alt"></i>
                <span>{{ $tutorial->created_at->format('M d, Y') }}</span>
            </div>
            <div class="meta-item-view">
                <i class="fas fa-clock"></i>
                <span>{{ $tutorial->created_at->diffForHumans() }}</span>
            </div>
        </div>
        <div class="description-section">
            <h4 class="section-heading">
                <i class="fas fa-info-circle"></i>
                @lang('About This Tutorial')
            </h4>
            <p class="tutorial-description-text">{{ $tutorial->description }}</p>
        </div>
    </div>

    @php
        $relatedTutorials = \App\Models\VideoTutorial::active()
            ->where('id', '!=', $tutorial->id)
            ->ordered()
            ->limit(3)
            ->get();
    @endphp

    @if($relatedTutorials->count() > 0)
    <!-- Related Tutorials Section -->
    <div class="related-section">
        <div class="related-header">
            <h3 class="related-title">
                <i class="fas fa-video"></i>
                @lang('More Tutorials')
            </h3>
            <a href="{{ route('user.video.tutorials') }}" class="view-all-link">
                @lang('View All')
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <div class="related-tutorials-grid">
            @foreach($relatedTutorials as $related)
            <a href="{{ route('user.video.tutorial.view', $related->id) }}" class="related-tutorial-card">
                <div class="related-thumb">
                    <img src="{{ getImage(getFilePath('tutorial').'/'.$related->thumbnail, '320x180') }}" alt="{{ $related->title }}">
                    <div class="related-play-btn">
                        <i class="fas fa-play"></i>
                    </div>
                    <span class="related-lesson-badge">@lang('Lesson') {{ $related->lesson_number }}</span>
                </div>
                <div class="related-info">
                    <h5 class="related-title-text">{{ Str::limit($related->title, 50) }}</h5>
                    <p class="related-desc-text">{{ Str::limit($related->description, 60) }}</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Help Section -->
    <div class="help-section-card">
        <div class="help-icon-wrapper">
            <i class="fas fa-question-circle"></i>
        </div>
        <div class="help-content">
            <h4 class="help-title">@lang('Need Help?')</h4>
            <p class="help-text">@lang('If you have questions, check our FAQ or contact support')</p>
        </div>
        <div class="help-actions">
            <a href="{{ route('user.faq') }}" class="help-btn secondary">
                <i class="fas fa-book"></i>
                FAQ
            </a>
            <a href="{{ route('ticket') }}" class="help-btn primary">
                <i class="fas fa-headset"></i>
                @lang('Support')
            </a>
        </div>
    </div>
</div>
@endsection

@push('style')
<style>
/* Header Navigation */
.tutorial-view-header {
    background: linear-gradient(135deg, #FF6F00, #FF9800);
    padding: 16px 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    position: sticky;
    top: 0;
    z-index: 100;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
.back-button {
    width: 36px;
    height: 36px;
    background: rgba(255,255,255,0.2);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    text-decoration: none;
    transition: all 0.3s ease;
}
.back-button:hover {
    background: rgba(255,255,255,0.3);
    transform: translateX(-2px);
}
.back-button i {
    font-size: 16px;
}
.header-title {
    color: #fff;
    font-size: 18px;
    font-weight: 700;
}

/* Container */
.tutorial-view-container {
    padding: 0 0 20px;
    background: #f5f5f5;
}

/* Video Player */
.video-player-wrapper {
    background: #000;
    margin-bottom: 16px;
}
.video-container {
    position: relative;
    width: 100%;
    padding-top: 56.25%; /* 16:9 Aspect Ratio */
    background: #000;
}
.video-container iframe,
.video-container video {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border: none;
}
.video-container video {
    object-fit: contain;
}

/* Tutorial Details Card */
.tutorial-details-card {
    background: #fff;
    padding: 20px;
    margin-bottom: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
}
.lesson-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: linear-gradient(135deg, #FF6F00, #FF9800);
    color: #fff;
    padding: 8px 14px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 14px;
}
.lesson-tag i {
    font-size: 13px;
}
.tutorial-main-title {
    color: #1a1a2e;
    font-size: 20px;
    font-weight: 700;
    line-height: 1.4;
    margin-bottom: 14px;
}
.tutorial-meta-info {
    display: flex;
    gap: 16px;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid #eee;
}
.meta-item-view {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: #666;
}
.meta-item-view i {
    color: #FF9800;
    font-size: 12px;
}
.description-section {
    margin-top: 20px;
}
.section-heading {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #1a1a2e;
    font-size: 15px;
    font-weight: 700;
    margin-bottom: 12px;
}
.section-heading i {
    color: #FF9800;
    font-size: 14px;
}
.tutorial-description-text {
    color: #555;
    font-size: 14px;
    line-height: 1.7;
    margin: 0;
}

/* Related Tutorials Section */
.related-section {
    background: #fff;
    padding: 20px;
    margin-bottom: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
}
.related-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}
.related-title {
    color: #1a1a2e;
    font-size: 16px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
}
.related-title i {
    color: #FF9800;
    font-size: 15px;
}
.view-all-link {
    color: #FF6F00;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 4px;
    transition: all 0.3s ease;
}
.view-all-link:hover {
    gap: 6px;
}
.view-all-link i {
    font-size: 11px;
}
.related-tutorials-grid {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.related-tutorial-card {
    display: flex;
    gap: 12px;
    background: #f9f9f9;
    border-radius: 12px;
    padding: 10px;
    text-decoration: none;
    transition: all 0.3s ease;
}
.related-tutorial-card:hover {
    background: #f0f0f0;
    transform: translateX(4px);
}
.related-thumb {
    width: 100px;
    height: 70px;
    border-radius: 10px;
    overflow: hidden;
    position: relative;
    flex-shrink: 0;
}
.related-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.related-play-btn {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 32px;
    height: 32px;
    background: rgba(255,111,0,0.9);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}
.related-play-btn i {
    color: #fff;
    font-size: 11px;
    margin-left: 2px;
}
.related-lesson-badge {
    position: absolute;
    bottom: 6px;
    left: 6px;
    background: rgba(0,0,0,0.75);
    color: #fff;
    padding: 3px 7px;
    border-radius: 5px;
    font-size: 9px;
    font-weight: 600;
}
.related-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.related-title-text {
    color: #1a1a2e;
    font-size: 13px;
    font-weight: 600;
    margin: 0 0 4px;
    line-height: 1.3;
}
.related-desc-text {
    color: #777;
    font-size: 11px;
    line-height: 1.4;
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Help Section */
.help-section-card {
    background: linear-gradient(135deg, #0F743C, #12925a);
    border-radius: 16px;
    padding: 20px;
    margin: 0 16px;
    position: relative;
    overflow: hidden;
}
.help-section-card::before {
    content: '';
    position: absolute;
    top: -50px;
    right: -50px;
    width: 150px;
    height: 150px;
    background: rgba(255,255,255,0.08);
    border-radius: 50%;
}
.help-icon-wrapper {
    width: 48px;
    height: 48px;
    background: rgba(255,255,255,0.2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 12px;
    position: relative;
    z-index: 1;
}
.help-icon-wrapper i {
    font-size: 22px;
    color: #fff;
}
.help-content {
    margin-bottom: 16px;
    position: relative;
    z-index: 1;
}
.help-title {
    color: #fff;
    font-size: 17px;
    font-weight: 700;
    margin-bottom: 6px;
}
.help-text {
    color: rgba(255,255,255,0.85);
    font-size: 13px;
    line-height: 1.5;
    margin: 0;
}
.help-actions {
    display: flex;
    gap: 10px;
    position: relative;
    z-index: 1;
}
.help-btn {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 12px 16px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
}
.help-btn.secondary {
    background: transparent;
    border: 1.5px solid rgba(255,255,255,0.5);
    color: #fff;
}
.help-btn.secondary:hover {
    background: rgba(255,255,255,0.15);
    border-color: #fff;
    transform: translateY(-2px);
}
.help-btn.primary {
    background: #fff;
    color: #0F743C;
}
.help-btn.primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}
.help-btn i {
    font-size: 14px;
}

/* Responsive */
@media (max-width: 375px) {
    .tutorial-main-title {
        font-size: 18px;
    }
    .tutorial-meta-info {
        flex-direction: column;
        gap: 8px;
    }
    .help-actions {
        flex-direction: column;
    }
}
</style>
@endpush
