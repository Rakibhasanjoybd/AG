@extends($activeTemplate .'layouts.master_mobile')
@section('main-content')
<style>
/* Modern Blog Details Styles */
.blog-detail-header {
    position: relative;
    height: 250px;
    overflow: hidden;
    background: linear-gradient(135deg, #1f2937, #111827);
}
.bdh-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: 0.4;
}
.bdh-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to bottom, transparent, rgba(0,0,0,0.8));
    display: flex;
    align-items: flex-end;
    padding: 24px 16px;
}
.bdh-back {
    position: absolute;
    top: 16px;
    left: 16px;
    width: 40px;
    height: 40px;
    background: rgba(255,255,255,0.95);
    backdrop-filter: blur(10px);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--agco-text-primary);
    text-decoration: none;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 10;
}
.bdh-date-badge {
    position: absolute;
    top: 16px;
    right: 16px;
    background: rgba(255,255,255,0.95);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    padding: 8px 12px;
    text-align: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.bdh-date-day {
    display: block;
    font-size: 22px;
    font-weight: 800;
    color: var(--agco-primary);
    line-height: 1;
}
.bdh-date-month {
    display: block;
    font-size: 11px;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    margin-top: 2px;
}

.blog-detail-content {
    background: #fff;
    margin-top: -30px;
    border-radius: 24px 24px 0 0;
    position: relative;
    z-index: 5;
    padding: 24px 16px;
    min-height: calc(100vh - 220px);
}
.bdc-category {
    display: inline-block;
    background: var(--agco-warning);
    color: #1f2937;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    margin-bottom: 12px;
}
.bdc-title {
    font-size: 24px;
    font-weight: 800;
    color: var(--agco-text-primary);
    line-height: 1.3;
    margin: 0 0 16px 0;
}
.bdc-meta {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 16px 0;
    border-bottom: 2px solid #f1f5f9;
    margin-bottom: 24px;
    flex-wrap: wrap;
}
.bdc-meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #64748b;
}
.bdc-meta-item i {
    color: var(--agco-primary);
    font-size: 16px;
}

.bdc-body {
    font-size: 16px;
    line-height: 1.8;
    color: var(--agco-text-primary);
    margin-bottom: 32px;
}
.bdc-body p {
    margin-bottom: 16px;
}
.bdc-body h1, .bdc-body h2, .bdc-body h3, .bdc-body h4 {
    font-weight: 700;
    margin: 24px 0 16px;
    color: var(--agco-text-primary);
}
.bdc-body img {
    max-width: 100%;
    height: auto;
    border-radius: 12px;
    margin: 20px 0;
}
.bdc-body ul, .bdc-body ol {
    padding-left: 24px;
    margin-bottom: 16px;
}
.bdc-body li {
    margin-bottom: 8px;
}
.bdc-body a {
    color: var(--agco-primary);
    font-weight: 600;
    text-decoration: underline;
}

.bdc-share {
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 24px;
}
.bdc-share-title {
    font-size: 14px;
    font-weight: 700;
    color: var(--agco-text-primary);
    margin: 0 0 16px 0;
    display: flex;
    align-items: center;
    gap: 8px;
}
.bdc-share-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}
.bdc-share-btn {
    flex: 1;
    min-width: 70px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 700;
    color: #fff;
    text-decoration: none;
    transition: all 0.3s;
}
.bdc-share-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.bdc-share-btn.facebook {
    background: #1877f2;
}
.bdc-share-btn.twitter {
    background: #1da1f2;
}
.bdc-share-btn.linkedin {
    background: #0077b5;
}
.bdc-share-btn.whatsapp {
    background: #25d366;
}

.related-posts-section {
    padding-top: 32px;
    border-top: 2px solid #f1f5f9;
}
.rps-title {
    font-size: 18px;
    font-weight: 800;
    color: var(--agco-text-primary);
    margin: 0 0 20px 0;
    display: flex;
    align-items: center;
    gap: 8px;
}
.rps-grid {
    display: grid;
    gap: 16px;
}
.related-post-card {
    display: flex;
    gap: 12px;
    background: #f8fafc;
    border-radius: 12px;
    padding: 12px;
    text-decoration: none;
    transition: all 0.3s;
}
.related-post-card:hover {
    background: #f1f5f9;
    transform: translateX(4px);
}
.rpc-thumb {
    flex-shrink: 0;
    width: 80px;
    height: 80px;
    border-radius: 10px;
    overflow: hidden;
    background: linear-gradient(135deg, #e5e7eb, #d1d5db);
}
.rpc-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.rpc-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.rpc-title {
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
.rpc-date {
    font-size: 11px;
    color: #94a3b8;
    display: flex;
    align-items: center;
    gap: 4px;
}
</style>

<!-- Blog Header with Image -->
<div class="blog-detail-header">
    <img src="{{ getImage('assets/images/frontend/blog/'.@$blog->data_values->image, '800x600') }}"
         alt="{{ __(@$blog->data_values->title) }}"
         class="bdh-image">
    <div class="bdh-overlay"></div>
    <a href="{{ route('blog') }}" class="bdh-back">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div class="bdh-date-badge">
        <span class="bdh-date-day">{{ $blog->created_at->format('d') }}</span>
        <span class="bdh-date-month">{{ $blog->created_at->format('M') }}</span>
    </div>
</div>

<!-- Blog Content -->
<div class="blog-detail-content">
    <span class="bdc-category">📰 খবর</span>
    <h1 class="bdc-title">{{ __(@$blog->data_values->title) }}</h1>

    <div class="bdc-meta">
        <span class="bdc-meta-item">
            <i class="fas fa-clock"></i>
            {{ $blog->created_at->diffForHumans() }}
        </span>
        <span class="bdc-meta-item">
            <i class="fas fa-calendar"></i>
            {{ showDateTime($blog->created_at, 'd M, Y') }}
        </span>
    </div>

    <div class="bdc-body">
        @php echo @$blog->data_values->description @endphp
    </div>

    <!-- Share Section -->
    <div class="bdc-share">
        <h3 class="bdc-share-title">
            <i class="fas fa-share-alt"></i>
            শেয়ার করুন
        </h3>
        <div class="bdc-share-buttons">
            <a href="https://www.facebook.com/sharer/sharer.php?u={{urlencode(url()->current())}}" target="_blank" class="bdc-share-btn facebook">
                <i class="fab fa-facebook-f"></i>
                <span>Facebook</span>
            </a>
            <a href="https://twitter.com/intent/tweet?url={{urlencode(url()->current())}}&text={{ urlencode(@$blog->data_values->title) }}" target="_blank" class="bdc-share-btn twitter">
                <i class="fab fa-twitter"></i>
                <span>Twitter</span>
            </a>
            <a href="http://www.linkedin.com/shareArticle?mini=true&url={{urlencode(url()->current())}}&title={{ urlencode(@$blog->data_values->title) }}" target="_blank" class="bdc-share-btn linkedin">
                <i class="fab fa-linkedin-in"></i>
                <span>LinkedIn</span>
            </a>
            <a href="https://wa.me/?text={{ urlencode(@$blog->data_values->title.' '.url()->current()) }}" target="_blank" class="bdc-share-btn whatsapp">
                <i class="fab fa-whatsapp"></i>
                <span>WhatsApp</span>
            </a>
        </div>
    </div>

    <!-- Related Posts -->
    @if(isset($latests) && $latests->count() > 0)
    <div class="related-posts-section">
        <h3 class="rps-title">
            <i class="fas fa-newspaper"></i>
            আরও পড়ুন
        </h3>
        <div class="rps-grid">
            @foreach($latests->take(5) as $related)
            <a href="{{ route('blog.details', [slug(@$related->data_values->title), $related->id]) }}" class="related-post-card">
                <div class="rpc-thumb">
                    <img src="{{ getImage('assets/images/frontend/blog/thumb_'.@$related->data_values->image, '160x160') }}"
                         alt="{{ __(@$related->data_values->title) }}"
                         loading="lazy">
                </div>
                <div class="rpc-content">
                    <h4 class="rpc-title">{{ __(@$related->data_values->title) }}</h4>
                    <span class="rpc-date">
                        <i class="fas fa-clock"></i>
                        {{ $related->created_at->diffForHumans() }}
                    </span>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif
</div>

@endsection
