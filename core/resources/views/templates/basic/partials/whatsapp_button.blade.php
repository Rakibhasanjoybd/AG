@php
    $whatsappContacts = \App\Models\WhatsappContact::active()->ordered()->get();
@endphp

@if($whatsappContacts->count() > 0)
<style>
/* Floating WhatsApp Button - Flat Design */
.floating-whatsapp-btn {
    position: fixed;
    bottom: 170px;
    right: 20px;
    width: 54px;
    height: 54px;
    background: #25D366;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 26px;
    text-decoration: none;
    z-index: 999;
    box-shadow: 0 3px 12px rgba(37, 211, 102, 0.35);
    transition: all 0.25s ease;
    cursor: pointer;
}

.floating-whatsapp-btn:hover {
    transform: scale(1.08);
    background: #20c45e;
    color: #fff;
}

.floating-whatsapp-btn:active {
    transform: scale(0.96);
}

/* WhatsApp Popup Modal - Flat Design */
.whatsapp-popup-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 10000;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 15px;
}

.whatsapp-popup-overlay.active {
    display: flex;
}

.whatsapp-popup {
    background: #fff;
    border-radius: 16px;
    max-width: 380px;
    width: 100%;
    max-height: 85vh;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    animation: wa-slideUp 0.3s ease;
}

@keyframes wa-slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.whatsapp-popup-header {
    background: #25D366;
    color: #fff;
    padding: 18px 20px;
    position: relative;
}

.whatsapp-popup-close {
    position: absolute;
    top: 50%;
    right: 16px;
    transform: translateY(-50%);
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: #fff;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    font-size: 18px;
    cursor: pointer;
    transition: background 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.whatsapp-popup-close:hover {
    background: rgba(255, 255, 255, 0.3);
}

.whatsapp-popup-title {
    font-size: 18px;
    font-weight: 600;
    margin: 0 0 4px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.whatsapp-popup-subtitle {
    font-size: 13px;
    opacity: 0.9;
    margin: 0;
    font-weight: 400;
}

/* Contacts Grid Container */
.whatsapp-contacts-list {
    padding: 16px;
    max-height: 55vh;
    overflow-y: auto;
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.whatsapp-contacts-list::-webkit-scrollbar {
    width: 4px;
}

.whatsapp-contacts-list::-webkit-scrollbar-track {
    background: #f0f0f0;
    border-radius: 4px;
}

.whatsapp-contacts-list::-webkit-scrollbar-thumb {
    background: #25D366;
    border-radius: 4px;
}

/* Single Contact - Show Full Width */
.whatsapp-contacts-list.single-contact {
    grid-template-columns: 1fr;
}

/* Contact Card - Flat Design */
.whatsapp-contact-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 16px 12px;
    border-radius: 12px;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    transition: all 0.2s ease;
    cursor: pointer;
}

.whatsapp-contact-item:hover {
    background: #f0fdf4;
    border-color: #25D366;
}

.whatsapp-contact-avatar {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #25D366;
    margin-bottom: 10px;
    background: #e9ecef;
}

.whatsapp-contact-info {
    width: 100%;
}

.whatsapp-contact-name {
    font-size: 14px;
    font-weight: 600;
    color: #1a1a1a;
    margin: 0 0 6px 0;
    line-height: 1.3;
    display: block;
}

.whatsapp-contact-dept {
    display: inline-block;
    background: #25D366;
    color: #fff;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 6px;
}

.whatsapp-contact-desc {
    font-size: 11px;
    color: #666;
    margin: 0 0 6px 0;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.whatsapp-contact-number {
    font-size: 11px;
    color: #128C7E;
    font-weight: 600;
    margin: 0 0 10px 0;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
}

.whatsapp-contact-number i {
    font-size: 10px;
}

.whatsapp-contact-action {
    background: #25D366;
    color: #fff;
    border: none;
    padding: 10px 16px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    width: 100%;
}

.whatsapp-contact-action:hover {
    background: #20c45e;
}

.whatsapp-contact-action i {
    font-size: 14px;
}

/* Mobile Responsive */
@media (max-width: 480px) {
    .whatsapp-popup {
        max-width: 100%;
        border-radius: 12px;
    }

    .whatsapp-contacts-list {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        padding: 12px;
    }

    .whatsapp-contact-item {
        padding: 14px 10px;
    }

    .whatsapp-contact-avatar {
        width: 48px;
        height: 48px;
    }

    .whatsapp-contact-name {
        font-size: 13px;
    }

    .floating-whatsapp-btn {
        bottom: 160px;
        right: 15px;
        width: 48px;
        height: 48px;
        font-size: 22px;
    }
}

/* List view for 3+ contacts */
@media (min-width: 481px) {
    .whatsapp-contacts-list.list-view {
        grid-template-columns: 1fr;
    }

    .whatsapp-contacts-list.list-view .whatsapp-contact-item {
        flex-direction: row;
        text-align: left;
        gap: 14px;
        padding: 14px 16px;
    }

    .whatsapp-contacts-list.list-view .whatsapp-contact-avatar {
        margin-bottom: 0;
        flex-shrink: 0;
    }

    .whatsapp-contacts-list.list-view .whatsapp-contact-info {
        flex: 1;
        min-width: 0;
    }

    .whatsapp-contacts-list.list-view .whatsapp-contact-number {
        justify-content: flex-start;
        margin-bottom: 0;
    }

    .whatsapp-contacts-list.list-view .whatsapp-contact-action {
        width: auto;
        flex-shrink: 0;
    }
}
</style>

<!-- Floating WhatsApp Button -->
<div class="floating-whatsapp-btn" id="whatsappFloatingBtn" title="WhatsApp Support">
    <i class="fab fa-whatsapp"></i>
</div>

<!-- WhatsApp Popup Modal -->
<div class="whatsapp-popup-overlay" id="whatsappPopupOverlay">
    <div class="whatsapp-popup">
        <div class="whatsapp-popup-header">
            <button class="whatsapp-popup-close" id="closeWhatsappPopup">
                <i class="las la-times"></i>
            </button>
            <h3 class="whatsapp-popup-title">
                <i class="fab fa-whatsapp"></i>
                কাস্টমার কেয়ার
            </h3>
            <p class="whatsapp-popup-subtitle">আমরা সাহায্য করতে এখানে আছি! একটি বিভাগ নির্বাচন করুন</p>
        </div>

        <div class="whatsapp-contacts-list {{ $whatsappContacts->count() == 1 ? 'single-contact' : '' }}">
            @foreach($whatsappContacts as $contact)
            <div class="whatsapp-contact-item" onclick="openWhatsAppChat('{{ $contact->whatsapp_url }}')">
                <img src="{{ $contact->profile_image_url }}"
                     alt="{{ $contact->name }}"
                     class="whatsapp-contact-avatar"
                     onerror="this.src='{{ asset('assets/images/default-avatar.png') }}'">

                <div class="whatsapp-contact-info">
                    <h4 class="whatsapp-contact-name">{{ $contact->name }}</h4>
                    <span class="whatsapp-contact-dept">{{ $contact->department }}</span>

                    @if($contact->description)
                    <p class="whatsapp-contact-desc">{{ Str::limit($contact->description, 50) }}</p>
                    @endif

                    <p class="whatsapp-contact-number">
                        <i class="fas fa-phone-alt"></i>
                        {{ $contact->phone_number }}
                    </p>
                </div>

                <button class="whatsapp-contact-action" type="button">
                    <i class="fab fa-whatsapp"></i>
                    চ্যাট করুন
                </button>
            </div>
            @endforeach
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';

    const floatingBtn = document.getElementById('whatsappFloatingBtn');
    const popupOverlay = document.getElementById('whatsappPopupOverlay');
    const closeBtn = document.getElementById('closeWhatsappPopup');

    // Open popup
    if (floatingBtn) {
        floatingBtn.addEventListener('click', function(e) {
            e.preventDefault();
            popupOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    }

    // Close popup
    function closePopup() {
        popupOverlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', closePopup);
    }

    // Close on overlay click
    if (popupOverlay) {
        popupOverlay.addEventListener('click', function(e) {
            if (e.target === popupOverlay) {
                closePopup();
            }
        });
    }

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && popupOverlay.classList.contains('active')) {
            closePopup();
        }
    });

    // Open WhatsApp chat
    window.openWhatsAppChat = function(url) {
        window.open(url, '_blank');
        closePopup();
    };
})();
</script>
@endif

