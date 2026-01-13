@extends($activeTemplate.'layouts.master_mobile')
@section('main-content')

<!-- Page Header -->
<div class="page-header-simple" style="background: linear-gradient(135deg, #f97316 0%, #dc2626 100%);">
    <div class="header-content">
        <div class="header-icon">
            <i class="fas fa-star"></i>
        </div>
        <h1 class="header-title">@lang('Daily Spotlights')</h1>
        <p class="header-subtitle">@lang('স্টোরি মোডে দেখতে ক্লিক করুন')</p>
    </div>
</div>

<!-- Spotlights Grid -->
<div class="section-block">
    @if($spotlights->count() > 0)
    <div class="spotlight-grid">
        @foreach($spotlights as $index => $spotlight)
        <div class="spotlight-story-card" data-spotlight-index="{{ $index }}">
            <div class="ssc-ring"></div>
            <div class="ssc-inner">
                @if($spotlight->image)
                <img src="{{ getImage(getFilePath('spotlight').'/'.$spotlight->image) }}"
                     alt="{{ $spotlight->title }}">
                @endif
                <div class="ssc-overlay">
                    <span class="ssc-title">{{ Str::limit($spotlight->title, 20) }}</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if($spotlights->hasPages())
    <div class="pagination-wrap mt-4">
        {{ paginateLinks($spotlights) }}
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
                        <span class="ssv-time">@lang('Just now')</span>
                    </div>
                </div>
                <button class="ssv-close" id="ssvClose"><i class="fas fa-times"></i></button>
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
                    <i class="fas fa-external-link-alt"></i> @lang('View Details')
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

    @else
    <div class="empty-state">
        <div class="empty-icon">
            <i class="fas fa-star"></i>
        </div>
        <h4>@lang('কোনো স্পটলাইট নেই')</h4>
        <p>@lang('বর্তমানে কোনো স্পটলাইট উপলব্ধ নেই')</p>
    </div>
    @endif
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
        margin-top: 20px;
    }
    .spotlight-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }
    @media (min-width: 576px) {
        .spotlight-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        background: white;
        border-radius: 16px;
    }
    .empty-icon {
        width: 70px;
        height: 70px;
        background: #f3f4f6;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
    }
    .empty-icon i { font-size: 1.8rem; color: #9ca3af; }
    .empty-state h4 { color: #666; margin-bottom: 5px; }
    .empty-state p { color: #999; font-size: 0.9rem; }
    
    /* Story Card Grid Style */
    .spotlight-story-card {
        position: relative;
        cursor: pointer;
        border-radius: 16px;
        overflow: hidden;
        aspect-ratio: 9/16;
        max-height: 220px;
    }
    .ssc-ring {
        position: absolute;
        inset: 0;
        border-radius: 16px;
        background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888);
        padding: 3px;
    }
    .ssc-inner {
        position: absolute;
        inset: 3px;
        border-radius: 14px;
        overflow: hidden;
        background: #1a1a2e;
    }
    .ssc-inner img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
    }
    .spotlight-story-card:hover .ssc-inner img {
        transform: scale(1.1);
    }
    .ssc-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 40px 12px 12px;
        background: linear-gradient(transparent, rgba(0,0,0,0.8));
    }
    .ssc-title {
        color: #fff;
        font-size: 13px;
        font-weight: 600;
        display: block;
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
    @keyframes ssvFadeIn {
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
        max-width: 500px;
        height: 100%;
        max-height: 90vh;
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
    @keyframes ssvProgress {
        from { width: 0%; }
        to { width: 100%; }
    }
    .ssv-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .ssv-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        overflow: hidden;
        border: 2px solid #fff;
        background: #fff;
    }
    .ssv-avatar img {
        width: 100%;
        height: 100%;
        object-fit: contain;
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
        font-size: 12px;
    }
    .ssv-close {
        position: absolute;
        top: 50px;
        right: 12px;
        width: 40px;
        height: 40px;
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
        max-height: 60vh;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0,0,0,0.5);
    }
    .ssv-image {
        width: 100%;
        height: 100%;
        object-fit: contain;
        max-height: 60vh;
    }
    .ssv-description {
        margin-top: 16px;
        padding: 0 8px;
        color: #fff;
        font-size: 14px;
        text-align: center;
        line-height: 1.6;
        max-height: 100px;
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
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: rgba(255,255,255,0.15);
        backdrop-filter: blur(10px);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 20px;
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
        padding: 20px;
        text-align: center;
        background: linear-gradient(0deg, rgba(0,0,0,0.7) 0%, transparent 100%);
    }
    .ssv-link-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 14px 28px;
        background: linear-gradient(135deg, #f09433, #e6683c, #dc2743);
        color: #fff;
        font-size: 14px;
        font-weight: 600;
        border-radius: 30px;
        text-decoration: none;
        transition: all 0.3s;
        box-shadow: 0 4px 20px rgba(220,39,67,0.4);
    }
    .ssv-link-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(220,39,67,0.5);
        color: #fff;
    }
    .ssv-link-btn.hidden {
        display: none;
    }
    
    @media (max-width: 768px) {
        .ssv-container {
            max-height: 100vh;
        }
    }
</style>
@endpush

@push('script')
<script>
(function($) {
    "use strict";

    var spotlightDataEl = document.getElementById('spotlightData');
    var spotlightViewer = document.getElementById('spotlightStoryViewer');
    
    if(spotlightDataEl && spotlightViewer) {
        var spotlightData = JSON.parse(spotlightDataEl.textContent);
        var currentSpotIndex = 0;
        var storyTimer = null;
        var storyDuration = 5000;
        
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
        document.querySelectorAll('.spotlight-story-card').forEach(function(card) {
            card.addEventListener('click', function(e) {
                e.preventDefault();
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
        
        // Pause timer when holding
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

})(jQuery);
</script>
@endpush
