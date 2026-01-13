@extends($activeTemplate .'layouts.master_mobile')
@section('main-content')
<style>
/* Modern Blog Page Styles */
.blog-page-header {
    background: linear-gradient(135deg, var(--agco-primary), #0d5f31);
    padding: 60px 16px 40px;
    text-align: center;
    color: #fff;
    margin-bottom: 20px;
}
.blog-page-title {
    font-size: 28px;
    font-weight: 800;
    margin: 0 0 8px 0;
}
.blog-page-subtitle {
    font-size: 14px;
    opacity: 0.9;
    margin: 0;
}

.blog-grid {
    padding: 0 16px 80px;
}

.blog-card-modern {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    margin-bottom: 20px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    transition: all 0.3s;
    text-decoration: none;
    display: block;
}
.blog-card-modern:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.15);
}

.bcm-image-wrapper {
    position: relative;
    width: 100%;
    height: 200px;
    overflow: hidden;
    background: linear-gradient(135deg, #f5f5f5, #e0e0e0);
}
.bcm-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s;
}
.blog-card-modern:hover .bcm-image {
    transform: scale(1.08);
}

.bcm-date-badge {
    position: absolute;
    top: 16px;
    left: 16px;
    background: rgba(255,255,255,0.95);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    padding: 8px 12px;
    text-align: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.bcm-date-day {
    display: block;
    font-size: 22px;
    font-weight: 800;
    color: var(--agco-primary);
    line-height: 1;
}
.bcm-date-month {
    display: block;
    font-size: 11px;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    margin-top: 2px;
    letter-spacing: 0.5px;
}

.bcm-category {
    position: absolute;
    bottom: 16px;
    right: 16px;
    background: var(--agco-warning);
    color: #1f2937;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.bcm-content {
    padding: 20px;
}
.bcm-title {
    font-size: 18px;
    font-weight: 800;
    color: var(--agco-text-primary);
    line-height: 1.4;
    margin: 0 0 12px 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.bcm-excerpt {
    font-size: 14px;
    color: #64748b;
    line-height: 1.6;
    margin: 0 0 16px 0;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.bcm-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 16px;
    border-top: 1px solid #e5e7eb;
}
.bcm-meta {
    display: flex;
    align-items: center;
    gap: 16px;
    font-size: 12px;
    color: #94a3b8;
}
.bcm-meta-item {
    display: flex;
    align-items: center;
    gap: 6px;
}
.bcm-meta-item i {
    color: var(--agco-primary);
    font-size: 14px;
}
.bcm-read-more {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 700;
    color: var(--agco-primary);
    text-decoration: none;
}
.bcm-read-more i {
    transition: transform 0.3s;
}
.blog-card-modern:hover .bcm-read-more i {
    transform: translateX(4px);
}

/* Pagination Styles */
.blog-pagination {
    display: flex;
    justify-content: center;
    gap: 8px;
    padding: 20px 16px;
    flex-wrap: wrap;
}
.blog-pagination .page-item {
    list-style: none;
}
.blog-pagination .page-link {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 40px;
    height: 40px;
    padding: 8px 12px;
    background: #fff;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 700;
    color: var(--agco-text-primary);
    text-decoration: none;
    transition: all 0.3s;
}
.blog-pagination .page-link:hover {
    background: var(--agco-primary);
    border-color: var(--agco-primary);
    color: #fff;
    transform: translateY(-2px);
}
.blog-pagination .page-item.active .page-link {
    background: var(--agco-primary);
    border-color: var(--agco-primary);
    color: #fff;
}
.blog-pagination .page-item.disabled .page-link {
    opacity: 0.5;
    pointer-events: none;
}

/* Empty State */
.blog-empty-state {
    text-align: center;
    padding: 60px 16px;
}
.blog-empty-icon {
    font-size: 64px;
    color: #cbd5e1;
    margin-bottom: 16px;
}
.blog-empty-title {
    font-size: 20px;
    font-weight: 700;
    color: var(--agco-text-primary);
    margin: 0 0 8px 0;
}
.blog-empty-text {
    font-size: 14px;
    color: #64748b;
    margin: 0;
}
</style>

<!-- Blog Page Header -->
<div class="blog-page-header">
    <h1 class="blog-page-title">📰 কোম্পানি ব্লগ</h1>
    <p class="blog-page-subtitle">সর্বশেষ খবর এবং আপডেট</p>
</div>

<!-- Blog Grid -->
<div class="blog-grid">
    @if($blogs && $blogs->count() > 0)
        @foreach($blogs as $blog)
        <a href="{{ route('blog.details', [slug(@$blog->data_values->title), $blog->id]) }}" class="blog-card-modern">
            <div class="bcm-image-wrapper">
                <img src="{{ getImage('assets/images/frontend/blog/'.@$blog->data_values->image, '800x450') }}" 
                     alt="{{ __(@$blog->data_values->title) }}" 
                     class="bcm-image"
                     loading="lazy">
                <div class="bcm-date-badge">
                    <span class="bcm-date-day">{{ showDateTime($blog->created_at, 'd') }}</span>
                    <span class="bcm-date-month">{{ showDateTime($blog->created_at, 'M') }}</span>
                </div>
                <span class="bcm-category">খবর</span>
            </div>
            <div class="bcm-content">
                <h2 class="bcm-title">{{ __(@$blog->data_values->title) }}</h2>
                <p class="bcm-excerpt">{{ strLimit(strip_tags(@$blog->data_values->description), 120) }}</p>
                <div class="bcm-footer">
                    <div class="bcm-meta">
                        <span class="bcm-meta-item">
                            <i class="fas fa-clock"></i>
                            {{ $blog->created_at->diffForHumans() }}
                        </span>
                    </div>
                    <span class="bcm-read-more">
                        আরও পড়ুন <i class="fas fa-arrow-right"></i>
                    </span>
                </div>
            </div>
        </a>
        @endforeach
        
        <!-- Pagination -->
        @if($blogs->hasPages())
        <div class="blog-pagination">
            {{ $blogs->links() }}
        </div>
        @endif
    @else
        <!-- Empty State -->
        <div class="blog-empty-state">
            <div class="blog-empty-icon">📭</div>
            <h3 class="blog-empty-title">কোন ব্লগ পোস্ট নেই</h3>
            <p class="blog-empty-text">এখনও কোন ব্লগ পোস্ট প্রকাশ করা হয়নি।</p>
        </div>
    @endif
</div>

@endsection
