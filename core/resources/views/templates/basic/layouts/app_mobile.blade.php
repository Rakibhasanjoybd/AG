<!doctype html>
<html lang="bn" itemscope itemtype="http://schema.org/WebPage">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#52006A">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>{{ $general->siteName(__($pageTitle)) }}</title>
    @include('partials.seo')

    @include('partials.preload-style', ['href' => asset('assets/global/css/bootstrap.min.css')])
    @include('partials.preload-style', ['href' => asset('assets/global/css/all.min.css')])
    @include('partials.preload-style', ['href' => asset('assets/global/css/line-awesome.min.css')])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @include('partials.preload-style', ['href' => 'https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700;800&display=swap', 'crossorigin' => true])

    @include('partials.preload-style', ['href' => asset($activeTemplateTrue . 'css/wallet-header.css')])

    @stack('style-lib')
    @stack('style')

    <style>
        :root {
            --purple: #52006A;
            --crimson: #CD113B;
            --orange: #FF7600;
            --gold: #FFA900;
            --white: #FFFFFF;
            --light-bg: #F8F9FA;
            --dark-text: #1a1a2e;
            --gray: #6c757d;
            --deep-red: #1a0a0a;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Noto Sans Bengali', sans-serif;
            background: linear-gradient(180deg, #1a0a1e 0%, #0f0515 50%, #0a0310 100%);
            color: var(--dark-text);
            min-height: 100vh;
            padding-bottom: 80px;
        }
        .app-container {
            max-width: 480px;
            margin: 0 auto;
            background: linear-gradient(180deg, #1a0a1e 0%, #0f0515 50%, #0a0310 100%);
            min-height: 100vh;
            position: relative;
        }
        .page-content {
            background: transparent;
            min-height: 100vh;
            position: relative;
            z-index: 1;
        }
    </style>
</head>
<body>
    <div class="app-container">
        @yield('content')
    </div>

    {{-- Red Bag Popup for authenticated users --}}
    @auth
        @include($activeTemplate . 'partials.red_bag_popup')
    @endauth

    <script src="{{ asset('assets/global/js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('assets/global/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset($activeTemplateTrue . 'js/wallet-header.js') }}" defer></script>

    @stack('script-lib')
    @stack('script')
    @include('partials.notify')
    <script>
        (function () {
            "use strict";

            function cleanupStuckModalBackdrops() {
                var openModals = Array.prototype.filter.call(
                    document.querySelectorAll('.modal.show'),
                    function (modalEl) {
                        return modalEl && window.getComputedStyle(modalEl).display !== 'none';
                    }
                );
                if (openModals && openModals.length) {
                    return;
                }

                // Remove ALL modal backdrops with a more thorough approach
                document.querySelectorAll('.modal-backdrop').forEach(function (backdrop) {
                    if (backdrop && backdrop.parentNode) {
                        backdrop.parentNode.removeChild(backdrop);
                    }
                });

                // Also remove any orphaned backdrops that might be stuck
                document.querySelectorAll('[class*="modal-backdrop"]').forEach(function (backdrop) {
                    if (backdrop && backdrop.parentNode) {
                        backdrop.parentNode.removeChild(backdrop);
                    }
                });

                // Reset body styles completely
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
                document.body.style.marginRight = '';

                // Remove any inline styles that might have been added
                if (document.body.style.removeProperty) {
                    document.body.style.removeProperty('overflow');
                    document.body.style.removeProperty('padding-right');
                    document.body.style.removeProperty('margin-right');
                }
            }

            // Enhanced cleanup with multiple triggers
            function enhancedCleanup() {
                setTimeout(cleanupStuckModalBackdrops, 10);
                setTimeout(cleanupStuckModalBackdrops, 100);
            }

            document.addEventListener('hidden.bs.modal', enhancedCleanup);
            document.addEventListener('hide.bs.modal', enhancedCleanup);
            document.addEventListener('hidePrevented.bs.modal', enhancedCleanup);

            window.addEventListener('pageshow', function () {
                cleanupStuckModalBackdrops();
            });

            window.addEventListener('popstate', function () {
                cleanupStuckModalBackdrops();
            });

            document.addEventListener(
                'click',
                function (e) {
                    if (e && e.target && (
                        e.target.classList.contains('modal-backdrop') ||
                        e.target.classList.contains('modal')
                    )) {
                        enhancedCleanup();
                    }
                },
                true
            );

            // Cleanup on ESC key press
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    setTimeout(cleanupStuckModalBackdrops, 50);
                }
            });

            // Make cleanup function globally available
            window.cleanupStuckModalBackdrops = cleanupStuckModalBackdrops;

            // Initial cleanup on page load
            setTimeout(cleanupStuckModalBackdrops, 500);

            // Force cleanup any stuck backdrops on page ready
            document.addEventListener('DOMContentLoaded', function() {
                cleanupStuckModalBackdrops();
                // Also add click-to-dismiss on any backdrop
                document.addEventListener('click', function(e) {
                    if (e.target && e.target.classList && e.target.classList.contains('modal-backdrop')) {
                        e.target.remove();
                        cleanupStuckModalBackdrops();
                    }
                });
            });

            // Periodic cleanup every 2 seconds to catch any stuck backdrops
            setInterval(function() {
                var openModals = document.querySelectorAll('.modal.show');
                var visibleModals = Array.prototype.filter.call(openModals, function(m) {
                    return m && window.getComputedStyle(m).display !== 'none';
                });
                if (!visibleModals.length) {
                    var backdrops = document.querySelectorAll('.modal-backdrop');
                    if (backdrops.length) {
                        cleanupStuckModalBackdrops();
                    }
                }
            }, 2000);
        })();
    </script>

    {{-- Popup Announcement for home page --}}
    @if(request()->routeIs('user.home'))
        @include($activeTemplate . 'partials.popup_announcement')
    @endif
</body>
</html>
