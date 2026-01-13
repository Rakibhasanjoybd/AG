{{-- Popup Announcement Modal - Flat Compact Design --}}
@php
    use App\Models\PopupAnnouncement;
    
    $activePopups = collect();
    $allPopups = PopupAnnouncement::active()->valid()->orderBy('priority', 'desc')->get();
    
    foreach ($allPopups as $popup) {
        if ($popup->shouldShowToUser(auth()->user())) {
            $activePopups->push($popup);
        }
    }
    
    $currentPopup = $activePopups->first();
    $totalPopups = $activePopups->count();
@endphp

@if($currentPopup)
<div class="modal fade" id="popupAnnouncementModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" aria-labelledby="popupAnnouncementTitle">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content popup-flat">
            {{-- Close Button --}}
            <button type="button" class="popup-flat__close" data-bs-dismiss="modal" aria-label="Close announcement">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="currentColor">
                    <path d="M14 1.41L12.59 0L7 5.59L1.41 0L0 1.41L5.59 7L0 12.59L1.41 14L7 8.41L12.59 14L14 12.59L8.41 7L14 1.41Z"/>
                </svg>
            </button>

            {{-- Image/Video Section --}}
            @php
                $hasMedia = (bool) $currentPopup->image;
            @endphp
            <div class="popup-flat__media {{ $hasMedia ? '' : 'is-empty' }}" id="popupMedia" @unless($hasMedia) style="display:none" @endunless>
                @if($currentPopup->image)
                @php
                    $videoExtensions = ['mp4', 'webm', 'mov', 'avi', 'mkv', 'flv', 'wmv'];
                    $extension = strtolower(pathinfo($currentPopup->image, PATHINFO_EXTENSION));
                    $isVideo = in_array($extension, $videoExtensions);
                    
                    // Check if it's an external URL or local file
                    $isExternalUrl = filter_var($currentPopup->image, FILTER_VALIDATE_URL);
                    $mediaPath = $isExternalUrl ? $currentPopup->image : asset('assets/images/popup/' . $currentPopup->image);
                @endphp
                @if($isVideo)
                    <video autoplay muted loop playsinline class="popup-flat__media-el" aria-label="{{ $currentPopup->title }}">
                        <source src="{{ $mediaPath }}" type="video/{{ $extension == 'mov' ? 'mp4' : $extension }}">
                        <track kind="captions" srclang="en" label="English" src="">
                    </video>
                @else
                    <img src="{{ $mediaPath }}" alt="{{ $currentPopup->title }}" class="popup-flat__media-el">
                @endif
                @endif
            </div>

            {{-- Content Section --}}
            <div class="popup-flat__body">
                <div class="popup-flat__badge">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                    @lang('Announcement')
                </div>
                
                <h3 class="popup-flat__title" id="popupAnnouncementTitle">{!! $currentPopup->title !!}</h3>
                
                @if($currentPopup->content)
                <p class="popup-flat__text">{!! nl2br(e($currentPopup->content)) !!}</p>
                @endif

                {{-- Action Button / Navigation --}}
                <div class="popup-flat__actions">
                    <a href="{{ $currentPopup->button_link ?? '#' }}" class="popup-flat__btn popup-flat__btn--primary {{ $currentPopup->button_text && $currentPopup->button_link ? '' : 'd-none' }}" id="popupCtaLink" target="_self" rel="noopener">
                        <span class="popup-flat__btn-text">{{ $currentPopup->button_text ?? '' }}</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 4l-1.41 1.41L16.17 11H4v2h12.17l-5.58 5.59L12 20l8-8z"/>
                        </svg>
                    </a>

                    <button type="button" class="popup-flat__btn popup-flat__btn--primary {{ $currentPopup->button_text && $currentPopup->button_link ? 'd-none' : '' }}" id="popupActionButton" @if(!($totalPopups > 1)) data-bs-dismiss="modal" @endif>
                        <span class="popup-flat__btn-text">@lang('Got it')</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                        </svg>
                    </button>
                </div>

                {{-- Navigation for Multiple Popups --}}
                @if($totalPopups > 1)
                <div class="popup-flat__nav">
                    <button type="button" class="popup-flat__nav-btn" id="popupPrevBtn" disabled aria-label="Previous announcement">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
                        </svg>
                    </button>
                    <div class="popup-flat__dots">
                        @for($i = 0; $i < $totalPopups; $i++)
                        <span class="popup-flat__dot {{ $i === 0 ? 'active' : '' }}" data-index="{{ $i }}"></span>
                        @endfor
                    </div>
                    <button type="button" class="popup-flat__nav-btn" id="popupNextBtn" aria-label="Next announcement">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/>
                        </svg>
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.popup-flat {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    border: none;
    box-shadow: 0 8px 32px rgba(0,0,0,0.15);
    max-width: 280px;
    margin: 0 auto;
}

.popup-flat__close {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 26px;
    height: 26px;
    border-radius: 6px;
    background: rgba(0,0,0,0.5);
    border: none;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 10;
    transition: background 0.2s;
}

.popup-flat__close:hover {
    background: rgba(0,0,0,0.7);
}

.popup-flat__media {
    width: 100%;
    aspect-ratio: 16 / 9;
    max-height: 320px;
    min-height: 140px;
    overflow: hidden;
    background: #f1f5f9;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: center;
}

.popup-flat__media.is-empty {
    display: none;
}

.popup-flat__media-el {
    width: 100%;
    height: 100%;
    object-fit: contain;
    display: block;
}

.popup-flat__body {
    padding: 12px;
}

.popup-flat__badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 8px;
    background: #e8f5e9;
    color: #2e7d32;
    font-size: 9px;
    font-weight: 600;
    border-radius: 12px;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.popup-flat__title {
    color: #1a1a2e;
    font-size: 14px;
    font-weight: 600;
    margin: 0 0 10px;
    line-height: 1.3;
}

.popup-flat__text {
    color: #64748b;
    font-size: 12px;
    line-height: 1.4;
    margin: 0 0 10px;
}

.popup-flat__actions {
    margin-bottom: 0;
}

.popup-flat__btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    width: 100%;
    padding: 9px 14px;
    font-size: 12px;
    font-weight: 600;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s;
}

.popup-flat__btn--primary {
    background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
    color: #1a1a2e;
}

.popup-flat__btn--primary:hover {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: #1a1a2e;
    transform: translateY(-1px);
}

.popup-flat__nav {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid #f1f5f9;
}

.popup-flat__nav-btn {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    background: #f1f5f9;
    border: none;
    color: #475569;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
}

.popup-flat__nav-btn:hover:not(:disabled) {
    background: #e2e8f0;
    color: #1e293b;
}

.popup-flat__nav-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.popup-flat__dots {
    display: flex;
    align-items: center;
    gap: 5px;
}

.popup-flat__dot {
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: #cbd5e1;
    transition: all 0.2s;
}

.popup-flat__dot.active {
    width: 14px;
    border-radius: 8px;
    background: #f59e0b;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var popupModal = document.getElementById('popupAnnouncementModal');
    if (popupModal) {
        setTimeout(function() {
            var modal = new bootstrap.Modal(popupModal);
            modal.show();
            
            @auth
            fetch('{{ route("popup.viewed") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ popup_id: {{ $currentPopup->id }} })
            });
            @endauth
        }, 400);
        
        @if($totalPopups > 1)
        var popups = {!! json_encode($activePopups->map(function($p) {
            $ext = $p->image ? strtolower(pathinfo($p->image, PATHINFO_EXTENSION)) : null;
            $videoExtensions = ['mp4', 'webm', 'mov', 'avi', 'mkv', 'flv', 'wmv'];
            $isExternalUrl = $p->image ? filter_var($p->image, FILTER_VALIDATE_URL) : false;
            $imagePath = $p->image ? ($isExternalUrl ? $p->image : asset('assets/images/popup/' . $p->image)) : null;
            return [
                'id' => $p->id,
                'title' => $p->title,
                'content' => $p->content,
                'image' => $imagePath,
                'is_video' => $ext ? in_array($ext, $videoExtensions) : false,
                'extension' => $ext,
                'button_text' => $p->button_text,
                'button_link' => $p->button_link,
            ];
        })->values()) !!};
        var currentIndex = 0;
        var totalPopups = {{ $totalPopups }};
        var dots = document.querySelectorAll('.popup-flat__dot');
        var mediaContainer = document.getElementById('popupMedia');
        var ctaLink = document.getElementById('popupCtaLink');
        var actionBtn = document.getElementById('popupActionButton');
        var nextBtn = document.getElementById('popupNextBtn');
        var prevBtn = document.getElementById('popupPrevBtn');

        function goTo(index) {
            if (index < 0 || index > totalPopups - 1) return;
            currentIndex = index;
            updatePopupContent();
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', function() {
                goTo(currentIndex + 1);
            });
        }
        
        if (prevBtn) {
            prevBtn.addEventListener('click', function() {
                goTo(currentIndex - 1);
            });
        }

        // Also use the main action button as Next when no CTA link is defined
        if (actionBtn) {
            actionBtn.addEventListener('click', function() {
                // If we're not on the last popup, treat this as Next
                if (currentIndex < totalPopups - 1 && !(ctaLink && !ctaLink.classList.contains('d-none'))) {
                    goTo(currentIndex + 1);
                }
            });
        }
        
        function renderMedia(popup) {
            if (!mediaContainer) return;
            if (!popup.image) {
                mediaContainer.innerHTML = '';
                mediaContainer.classList.add('is-empty');
                mediaContainer.style.display = 'none';
                return;
            }
            mediaContainer.classList.remove('is-empty');
            mediaContainer.style.display = 'flex';
            var isVideo = popup.is_video;
            var ext = (popup.extension || '').toLowerCase();
            var sourceType = ext === 'mov' ? 'mp4' : ext;
            if (isVideo) {
                mediaContainer.innerHTML = '<video autoplay muted loop playsinline class="popup-flat__media-el" aria-label="'+ (popup.title || '') +'"><source src="'+ popup.image +'" type="video/'+ sourceType +'"><track kind="captions" srclang="en" label="English" src=""></video>';
            } else {
                mediaContainer.innerHTML = '<img src="'+ popup.image +'" alt="'+ (popup.title || '') +'" class="popup-flat__media-el">';
            }
        }
        
        function updateActions(popup) {
            if (!ctaLink || !actionBtn) return;
            var hasCta = popup.button_link && popup.button_text;
            if (hasCta) {
                ctaLink.href = popup.button_link;
                ctaLink.querySelector('.popup-flat__btn-text').textContent = popup.button_text;
                ctaLink.classList.remove('d-none');
                actionBtn.classList.add('d-none');
                actionBtn.removeAttribute('data-bs-dismiss');
            } else {
                ctaLink.classList.add('d-none');
                actionBtn.classList.remove('d-none');
                var isLast = currentIndex === totalPopups - 1;
                var label = isLast ? '{{ __('Got it') }}' : '{{ __('Next') }}';
                actionBtn.querySelector('.popup-flat__btn-text').textContent = label;
                if (isLast) {
                    actionBtn.setAttribute('data-bs-dismiss', 'modal');
                } else {
                    actionBtn.removeAttribute('data-bs-dismiss');
                }
            }
        }
        
        function updatePopupContent() {
            var popup = popups[currentIndex];
            document.querySelector('.popup-flat__title').innerHTML = popup.title;
            
            var textEl = document.querySelector('.popup-flat__text');
            if (textEl) {
                textEl.innerHTML = popup.content ? popup.content.replace(/\n/g, '<br>') : '';
            }
            
            renderMedia(popup);
            updateActions(popup);
            
            dots.forEach(function(dot, i) {
                dot.classList.toggle('active', i === currentIndex);
            });
            
            if (prevBtn) prevBtn.disabled = currentIndex === 0;
            if (nextBtn) nextBtn.disabled = currentIndex === totalPopups - 1;
            
            @auth
            fetch('{{ route("popup.viewed") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ popup_id: popup.id })
            });
            @endauth
        }

        // Initialize the dynamic parts for the first popup
        updatePopupContent();
        @endif
    }
});
</script>
@endif
