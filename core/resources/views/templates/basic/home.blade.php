<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="theme-color" content="#0F743C">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="AGCO Finance">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="application-name" content="AGCO Finance">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ getImage(getFilePath('logoIcon') .'/logo.png') }}">
    <title>{{ $general->siteName(__($pageTitle ?? 'স্বাগতম')) }}</title>
    @include('partials.seo')
    <link href="{{ asset('assets/global/css/all.min.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        html,body{height:100%;-webkit-tap-highlight-color:transparent}
        body{font-family:'Noto Sans Bengali',sans-serif;background:#F3F3F1;min-height:100vh;min-height:-webkit-fill-available}

        .app{width:100%;max-width:480px;min-height:100vh;margin:0 auto;display:flex;flex-direction:column;background:#F3F3F1}

        /* Header Bar */
        .header{background:#0F743C;padding:16px 20px;display:flex;align-items:center;gap:12px;position:sticky;top:0;z-index:100}
        .header-logo{width:40px;height:40px;background:#fff;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
        .header-logo img{width:28px;height:28px;object-fit:contain}
        .header-info{flex:1}
        .header-info h1{font-size:18px;font-weight:700;color:#fff;line-height:1.2}
        .header-info span{font-size:11px;color:rgba(255,255,255,0.85);display:flex;align-items:center;gap:4px}
        .header-info span i{font-size:8px}
        .header-badge{background:rgba(255,255,255,0.15);padding:6px 10px;border-radius:20px;font-size:10px;color:#fff;font-weight:600;display:flex;align-items:center;gap:4px}
        .header-badge i{color:#F99E2B}

        /* Main Content */
        .main{flex:1;padding:16px;overflow-y:auto;-webkit-overflow-scrolling:touch}

        /* Stats Row */
        .stats{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:16px}
        .stat-card{background:#fff;border-radius:10px;padding:14px 10px;text-align:center;border-left:3px solid #0F743C}
        .stat-card.orange{border-left-color:#F99E2B}
        .stat-card.red{border-left-color:#DA3E2F}
        .stat-card i{font-size:16px;color:#0F743C;margin-bottom:6px;display:block}
        .stat-card.orange i{color:#F99E2B}
        .stat-card.red i{color:#DA3E2F}
        .stat-card .value{font-size:16px;font-weight:800;color:#333;display:block;line-height:1.2}
        .stat-card .label{font-size:10px;color:#666;margin-top:2px}

        /* Banner Slider */
        .banner-section{margin-bottom:16px;border-radius:12px;overflow:hidden;background:#fff;padding:12px}
        .banner-slider{display:flex;gap:10px;overflow-x:auto;scroll-snap-type:x mandatory;scrollbar-width:none;-ms-overflow-style:none;padding-bottom:4px}
        .banner-slider::-webkit-scrollbar{display:none}
        .banner-item{min-width:140px;height:75px;border-radius:8px;overflow:hidden;scroll-snap-align:start;flex-shrink:0}
        .banner-item img{width:100%;height:100%;object-fit:cover}
        .banner-item.placeholder{display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;color:#fff;gap:5px}
        .banner-item.bg-green{background:#0F743C}
        .banner-item.bg-orange{background:#F99E2B}
        .banner-item.bg-red{background:#DA3E2F}
        .banner-item.bg-brown{background:#C7662B}

        /* Action Buttons */
        .action-buttons{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px}
        .action-btn{display:flex;align-items:center;justify-content:center;gap:8px;padding:14px 16px;border-radius:10px;font-size:14px;font-weight:700;text-decoration:none;transition:transform 0.2s,box-shadow 0.2s}
        .action-btn:active{transform:scale(0.98)}
        .action-btn.primary{background:#0F743C;color:#fff}
        .action-btn.secondary{background:#F99E2B;color:#fff}
        .action-btn.full{grid-column:1/-1;background:#0F743C;color:#fff}
        .action-btn i{font-size:16px}

        /* Feature Cards */
        .section-title{font-size:13px;font-weight:700;color:#333;margin-bottom:10px;display:flex;align-items:center;gap:6px}
        .section-title i{color:#0F743C;font-size:12px}

        .feature-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin-bottom:16px}
        .feature-card{background:#fff;border-radius:10px;padding:14px;display:flex;align-items:center;gap:10px}
        .feature-icon{width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:14px;color:#fff}
        .feature-icon.green{background:#0F743C}
        .feature-icon.orange{background:#F99E2B}
        .feature-icon.red{background:#DA3E2F}
        .feature-icon.brown{background:#C7662B}
        .feature-text{font-size:11px;color:#333;font-weight:600;line-height:1.3}

        /* Install Card */
        .install-card{background:#fff;border-radius:10px;padding:14px;display:flex;align-items:center;gap:12px;margin-bottom:16px}
        .install-icon{width:44px;height:44px;background:#F99E2B;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
        .install-icon i{font-size:20px;color:#fff}
        .install-info{flex:1}
        .install-info h4{font-size:13px;font-weight:700;color:#333;margin-bottom:2px}
        .install-info p{font-size:10px;color:#666}
        .install-btn{background:#0F743C;color:#fff;padding:10px 14px;border-radius:8px;font-size:11px;font-weight:700;text-decoration:none;display:flex;align-items:center;gap:6px;white-space:nowrap;border:none;cursor:pointer;transition:all 0.2s}
        .install-btn:active{transform:scale(0.98)}
        .install-btn i{font-size:14px}

        /* Trust Indicators */
        .trust-section{background:#fff;border-radius:10px;padding:14px;margin-bottom:16px}
        .trust-row{display:flex;justify-content:space-around;text-align:center}
        .trust-item{display:flex;flex-direction:column;align-items:center;gap:4px}
        .trust-item i{font-size:18px;color:#0F743C}
        .trust-item span{font-size:9px;color:#666;font-weight:500}

        /* Footer */
        .footer{background:#fff;border-top:1px solid #e8e8e8;padding:14px 16px;text-align:center}
        .footer-links{display:flex;justify-content:center;gap:16px;margin-bottom:8px}
        .footer-links a{font-size:11px;color:#666;text-decoration:none;font-weight:500}
        .footer-links a:hover{color:#0F743C}
        .footer-copy{font-size:10px;color:#999}

        /* Responsive Adjustments */
        @media(max-width:360px){
            .stats{gap:8px}
            .stat-card{padding:12px 8px}
            .stat-card .value{font-size:14px}
            .feature-grid{gap:8px}
            .feature-card{padding:12px}
            .action-btn{padding:12px 14px;font-size:13px}
        }
    </style>
</head>
<body>
    <div class="app">
        <!-- Header -->
        <div class="header">
            <div class="header-logo">
                <img src="{{ getImage(getFilePath('logoIcon') .'/logo.png') }}" alt="{{ $general->site_name ?? 'AGCO' }}">
            </div>
            <div class="header-info">
                <h1>{{ $general->site_name ?? 'AGCO Finance' }}</h1>
                <span><i class="fas fa-circle"></i> বিশ্বস্ত আয়ের প্ল্যাটফর্ম</span>
            </div>
            <div class="header-badge">
                <i class="fas fa-shield-alt"></i> ভেরিফাইড
            </div>
        </div>

        <!-- Main Content -->
        <div class="main">
            <!-- Stats -->
            <div class="stats">
                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <span class="value">৫০K+</span>
                    <span class="label">সক্রিয় ইউজার</span>
                </div>
                <div class="stat-card orange">
                    <i class="fas fa-wallet"></i>
                    <span class="value">৳১০M+</span>
                    <span class="label">মোট পেআউট</span>
                </div>
                <div class="stat-card red">
                    <i class="fas fa-star"></i>
                    <span class="value">৪.৯</span>
                    <span class="label">ইউজার রেটিং</span>
                </div>
            </div>

            <!-- Banner Slider -->
            @php $banners = getContent('banner.element', false, null, true); @endphp
            <div class="banner-section">
                <div class="banner-slider">
                    @if($banners && $banners->count() > 0)
                        @foreach($banners as $b)
                        <div class="banner-item">
                            <img src="{{ asset('assets/images/frontend/banner/'.@$b->data_values->image) }}" alt="">
                        </div>
                        @endforeach
                    @else
                        <div class="banner-item placeholder bg-green"><i class="fas fa-coins"></i> আয় করুন</div>
                        <div class="banner-item placeholder bg-orange"><i class="fas fa-tasks"></i> টাস্ক</div>
                        <div class="banner-item placeholder bg-red"><i class="fas fa-gift"></i> বোনাস</div>
                        <div class="banner-item placeholder bg-brown"><i class="fas fa-crown"></i> ভিআইপি</div>
                    @endif
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                @auth
                    <a href="{{ route('user.home') }}" class="action-btn full">
                        <i class="fas fa-home"></i> ড্যাশবোর্ডে যান
                    </a>
                @else
                    <a href="{{ route('user.login') }}" class="action-btn primary">
                        <i class="fas fa-sign-in-alt"></i> লগইন
                    </a>
                    <a href="{{ route('user.register') }}" class="action-btn secondary">
                        <i class="fas fa-user-plus"></i> রেজিস্টার
                    </a>
                @endauth
            </div>

            <!-- Features -->
            <div class="section-title"><i class="fas fa-bolt"></i> কিভাবে আয় করবেন</div>
            <div class="feature-grid">
                <div class="feature-card">
                    <div class="feature-icon green"><i class="fas fa-check-circle"></i></div>
                    <span class="feature-text">প্রতিদিন টাস্ক সম্পন্ন করুন</span>
                </div>
                <div class="feature-card">
                    <div class="feature-icon orange"><i class="fas fa-users"></i></div>
                    <span class="feature-text">বন্ধুদের রেফার করুন</span>
                </div>
                <div class="feature-card">
                    <div class="feature-icon red"><i class="fas fa-money-bill-wave"></i></div>
                    <span class="feature-text">৳৫০০ থেকে উত্তোলন</span>
                </div>
                <div class="feature-card">
                    <div class="feature-icon brown"><i class="fas fa-crown"></i></div>
                    <span class="feature-text">ভিআইপি টাস্কে বেশি আয়</span>
                </div>
            </div>

            <!-- Install App -->
            <div class="install-card" id="installCard">
                <div class="install-icon"><i class="fas fa-mobile-alt"></i></div>
                <div class="install-info">
                    <h4>অ্যাপ ইনস্টল করুন</h4>
                    <p>হোম স্ক্রিনে যোগ করে দ্রুত অ্যাক্সেস পান</p>
                </div>
                <button type="button" class="install-btn" id="installBtn">
                    <i class="fas fa-download"></i> ইনস্টল
                </button>
            </div>

            <!-- Trust Indicators -->
            <div class="trust-section">
                <div class="trust-row">
                    <div class="trust-item">
                        <i class="fas fa-lock"></i>
                        <span>নিরাপদ</span>
                    </div>
                    <div class="trust-item">
                        <i class="fas fa-bolt"></i>
                        <span>দ্রুত পেমেন্ট</span>
                    </div>
                    <div class="trust-item">
                        <i class="fas fa-headset"></i>
                        <span>২৪/৭ সাপোর্ট</span>
                    </div>
                    <div class="trust-item">
                        <i class="fas fa-certificate"></i>
                        <span>বিশ্বস্ত</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-links">
                <a href="{{ route('contact') }}">সাপোর্ট</a>
                <a href="{{ route('policy.pages', ['privacy-policy', 1]) }}">গোপনীয়তা</a>
                <a href="{{ route('policy.pages', ['terms-conditions', 2]) }}">শর্তাবলী</a>
            </div>
            <div class="footer-copy">&copy; {{ date('Y') }} {{ $general->site_name ?? 'AGCO' }}। সর্বস্বত্ব সংরক্ষিত।</div>
        </div>
    </div>

    <script src="{{ asset('assets/global/js/jquery-3.6.0.min.js') }}"></script>
    @include('partials.notify')

    <script>
        // Register Service Worker for PWA
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('AGCO: Service Worker registered'))
                    .catch(err => console.log('AGCO: SW registration failed', err));
            });
        }

        // PWA Install Prompt
        let deferredPrompt;
        const installBtn = document.getElementById('installBtn');
        const installCard = document.getElementById('installCard');

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            if (installCard) installCard.style.display = 'flex';
        });

        if (installBtn) {
            installBtn.addEventListener('click', async () => {
                const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
                const isAndroid = /Android/.test(navigator.userAgent);
                const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;

                if (isStandalone) {
                    showToast('অ্যাপ ইতিমধ্যে ইনস্টল করা আছে!');
                    return;
                }

                if (deferredPrompt) {
                    deferredPrompt.prompt();
                    const { outcome } = await deferredPrompt.userChoice;
                    if (outcome === 'accepted') {
                        installBtn.innerHTML = '<i class="fas fa-check"></i> ইনস্টলড';
                        installBtn.style.background = '#27ae60';
                        showToast('অ্যাপ সফলভাবে ইনস্টল হয়েছে!');
                    }
                    deferredPrompt = null;
                } else if (isIOS) {
                    showIOSModal();
                } else if (isAndroid) {
                    showAndroidModal();
                } else {
                    showDesktopModal();
                }
            });
        }

        if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone) {
            if (installBtn) {
                installBtn.innerHTML = '<i class="fas fa-check"></i> ইনস্টলড';
                installBtn.style.background = '#27ae60';
            }
        }

        function showIOSModal() {
            showModal('📱', 'iOS এ ইনস্টল করুন', '<p>1. নিচে <b>Share</b> বাটনে ক্লিক করুন</p><p>2. <b>"Add to Home Screen"</b> সিলেক্ট করুন</p><p>3. <b>"Add"</b> বাটনে ট্যাপ করুন</p>');
        }

        function showAndroidModal() {
            showModal('📲', 'Android এ ইনস্টল করুন', '<p>1. Chrome মেনু (⋮) ক্লিক করুন</p><p>2. <b>"Install app"</b> সিলেক্ট করুন</p><p>3. <b>"Install"</b> বাটনে ট্যাপ করুন</p>');
        }

        function showDesktopModal() {
            showModal('💻', 'ব্রাউজারে ইনস্টল করুন', '<p>Address bar এ Install আইকন ক্লিক করুন অথবা ব্রাউজার মেনু থেকে "Install" সিলেক্ট করুন</p>');
        }

        function showModal(icon, title, content) {
            const m = document.createElement('div');
            m.innerHTML = '<div style="position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.85);z-index:9999;display:flex;align-items:center;justify-content:center;padding:20px;" onclick="if(event.target===this)this.remove()"><div style="background:#fff;border-radius:16px;padding:24px;max-width:300px;text-align:center;"><div style="font-size:48px;margin-bottom:12px;">'+icon+'</div><h3 style="color:#333;margin-bottom:12px;font-size:17px;">'+title+'</h3><div style="text-align:left;background:#f5f5f5;border-radius:10px;padding:14px;margin-bottom:16px;font-size:13px;color:#333;line-height:1.8;">'+content+'</div><button onclick="this.closest(\'div\').parentElement.parentElement.remove()" style="background:#0F743C;color:#fff;border:none;padding:12px 28px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;">বুঝেছি</button></div></div>';
            document.body.appendChild(m);
        }

        function showToast(msg) {
            const t = document.createElement('div');
            t.style.cssText = 'position:fixed;bottom:80px;left:50%;transform:translateX(-50%);background:#27ae60;color:#fff;padding:12px 24px;border-radius:8px;font-size:14px;z-index:9999;box-shadow:0 4px 12px rgba(0,0,0,0.2);';
            t.textContent = msg;
            document.body.appendChild(t);
            setTimeout(() => t.remove(), 3000);
        }
    </script>

    {{-- Popup Announcement --}}
    @include($activeTemplate . 'partials.popup_announcement')
</body>
</html>
