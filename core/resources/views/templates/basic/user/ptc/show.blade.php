<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <link rel="shortcut icon" type="image/png" href="{{ getImage(fileManager()->logoIcon()->path . '/favicon.png') }}" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    @php
        $activeTemplateTrue = activeTemplate(true);
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/global/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="{{ asset('assets/global/js/jquery-3.6.0.min.js') }}"></script>
    <title> {{ $general->sitename(__($pageTitle)) }}</title>
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
    <script>
        // Fraud Prevention System
        var FraudPrevention = {
            tabSwitchCount: 0,
            maxTabSwitches: 3,
            isPaused: false,
            pausedTime: 0,
            lastActiveTime: Date.now(),
            startTime: Date.now(),
            // Security token generated server-side for security
            securityToken: '{{ hash_hmac('sha256', auth()->user()->id . '|' . $ptc->id . '|' . date('Y-m-d H'), config('app.key', env('APP_KEY', 'default-secret'))) }}',
            deviceFingerprint: '',

            init: function() {
                this.generateFingerprint();
                this.setupVisibilityListener();
                this.setupAntiSpeedHack();
                this.setupDevToolsDetection();
            },

            generateFingerprint: function() {
                var canvas = document.createElement('canvas');
                var ctx = canvas.getContext('2d');
                ctx.textBaseline = 'top';
                ctx.font = '14px Arial';
                ctx.fillText('AGCO Security', 2, 2);
                var canvasData = canvas.toDataURL();

                var fingerprint = [
                    navigator.userAgent,
                    navigator.language,
                    screen.width + 'x' + screen.height,
                    new Date().getTimezoneOffset(),
                    canvasData.substring(0, 50)
                ].join('|');

                this.deviceFingerprint = btoa(fingerprint).substring(0, 32);
            },

            setupVisibilityListener: function() {
                var self = this;
                document.addEventListener('visibilitychange', function() {
                    if (document.visibilityState === 'hidden') {
                        self.onTabHidden();
                    } else {
                        self.onTabVisible();
                    }
                });

                // Detect window blur (more reliable for some cases)
                window.addEventListener('blur', function() {
                    self.onTabHidden();
                });

                window.addEventListener('focus', function() {
                    self.onTabVisible();
                });
            },

            onTabHidden: function() {
                if (!this.isPaused) {
                    this.isPaused = true;
                    this.pausedTime = Date.now();
                    this.tabSwitchCount++;

                    if (this.tabSwitchCount >= this.maxTabSwitches) {
                        this.showBlockedMessage();
                    } else {
                        this.showPausedOverlay();
                    }
                }
            },

            onTabVisible: function() {
                if (this.isPaused && this.tabSwitchCount < this.maxTabSwitches) {
                    this.isPaused = false;
                    this.hidePausedOverlay();
                    this.lastActiveTime = Date.now();
                }
            },

            showPausedOverlay: function() {
                if (!document.getElementById('pauseOverlay')) {
                    var overlay = document.createElement('div');
                    overlay.id = 'pauseOverlay';
                    overlay.innerHTML = `
                        <div class="pause-content">
                            <div class="pause-icon"><i class="fas fa-pause-circle"></i></div>
                            <h2>বিজ্ঞাপন বিরতি দেওয়া হয়েছে</h2>
                            <p>আপনি ট্যাব ছেড়ে গেছেন। বিজ্ঞাপন দেখা চালিয়ে যেতে এই পেজে থাকুন।</p>
                            <div class="warning-badge">
                                <i class="fas fa-exclamation-triangle"></i>
                                সতর্কতা: ${this.maxTabSwitches - this.tabSwitchCount} বার ট্যাব সুইচ বাকি আছে
                            </div>
                        </div>
                    `;
                    document.body.appendChild(overlay);
                }
                document.getElementById('pauseOverlay').style.display = 'flex';
            },

            hidePausedOverlay: function() {
                var overlay = document.getElementById('pauseOverlay');
                if (overlay) {
                    overlay.style.display = 'none';
                }
            },

            showBlockedMessage: function() {
                document.body.innerHTML = `
                    <div class="blocked-container">
                        <div class="blocked-content">
                            <div class="blocked-icon"><i class="fas fa-ban"></i></div>
                            <h2>বিজ্ঞাপন বাতিল হয়েছে</h2>
                            <p>আপনি অনেকবার ট্যাব সুইচ করেছেন। অনুগ্রহ করে পুনরায় চেষ্টা করুন।</p>
                            <a href="{{ route('user.ptc.index') }}" class="back-btn">
                                <i class="fas fa-arrow-left"></i> বিজ্ঞাপনে ফিরে যান
                            </a>
                        </div>
                    </div>
                `;
            },

            setupAntiSpeedHack: function() {
                var self = this;
                var checkInterval = 1000;
                var lastCheck = Date.now();

                setInterval(function() {
                    var now = Date.now();
                    var elapsed = now - lastCheck;

                    // If more than 2 seconds passed in what should be 1 second, something is wrong
                    if (elapsed > checkInterval * 2) {
                        console.warn('Time manipulation detected');
                        self.showBlockedMessage();
                    }
                    lastCheck = now;
                }, checkInterval);
            },

            setupDevToolsDetection: function() {
                var self = this;
                var threshold = 160;

                setInterval(function() {
                    var widthThreshold = window.outerWidth - window.innerWidth > threshold;
                    var heightThreshold = window.outerHeight - window.innerHeight > threshold;

                    if (widthThreshold || heightThreshold) {
                        // DevTools might be open - just log, don't block
                        console.log('DevTools detection triggered');
                    }
                }, 1000);
            },

            getSecurityData: function() {
                return {
                    token: this.securityToken,
                    fingerprint: this.deviceFingerprint,
                    watchTime: Math.floor((Date.now() - this.startTime) / 1000),
                    tabSwitches: this.tabSwitchCount,
                    timestamp: Date.now()
                };
            }
        };

        // Initialize fraud prevention
        FraudPrevention.init();
    </script>
    <style>
        :root {
            --primary-color: #0F743C;
            --error-color: #DA3E2F;
            --warning-color: #F99E2B;
            --secondary-color: #C7662B;
            --success-color: #0F743C;
            --light-bg: #F5F7FA;
            --white: #FFFFFF;
            --overlay: rgba(0, 0, 0, 0.5);
            --text-dark: #1F2937;
            --text-muted: #6B7280;
            --border-color: #E5E7EB;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        .ptc-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: linear-gradient(135deg, var(--primary-color) 0%, #0a5229 100%);
            padding: 8px 12px;
            box-shadow: 0 2px 10px rgba(15, 116, 60, 0.2);
            z-index: 1000;
        }

        .progress-container {
            max-width: 100%;
            margin: 0 auto;
            background: var(--white);
            border-radius: 25px;
            padding: 4px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        #myProgress {
            width: 100%;
            background: linear-gradient(90deg, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0.1) 100%);
            border-radius: 25px;
            overflow: hidden;
            height: 36px;
            box-shadow: inset 0 1px 4px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        #myProgress::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            to { left: 100%; }
        }

        #myBar {
            width: 0%;
            height: 100%;
            background: linear-gradient(90deg, #FFD700 0%, #FFA500 50%, #FF8C00 100%);
            text-align: center;
            line-height: 36px;
            color: white;
            font-weight: 700;
            font-size: 14px;
            transition: width 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 8px rgba(255, 165, 0, 0.4);
            position: relative;
            overflow: hidden;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }

        #myBar::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            animation: barShine 2s infinite;
        }

        @keyframes barShine {
            to { left: 100%; }
        }

        /* Ad Info Section - Compact Flat Design */
        .ad-info-section {
            background: var(--white);
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border-color);
        }

        .ad-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .ad-title i {
            color: var(--primary-color);
            font-size: 14px;
        }

        .ad-description-wrapper {
            max-height: 80px;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: var(--primary-color) #f1f1f1;
        }

        .ad-description-wrapper::-webkit-scrollbar {
            width: 4px;
        }

        .ad-description-wrapper::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .ad-description-wrapper::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 4px;
        }

        .ad-description-wrapper::-webkit-scrollbar-thumb:hover {
            background: #0a5229;
        }

        .ad-description {
            font-size: 13px;
            color: var(--text-muted);
            line-height: 1.5;
            padding-right: 8px;
        }

        .ad-meta {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid var(--border-color);
        }

        .ad-meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: var(--text-muted);
        }

        .ad-meta-item i {
            color: var(--primary-color);
            font-size: 12px;
        }

        .ad-meta-item .value {
            font-weight: 600;
            color: var(--text-dark);
        }

        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--overlay);
            z-index: 9998;
            animation: fadeIn 0.3s ease;
        }

        .review-modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.9);
            background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 28px;
            padding: 2.5rem;
            max-width: 520px;
            width: 90%;
            max-height: 85vh;
            overflow-y: auto;
            box-shadow: 0 25px 70px rgba(0, 0, 0, 0.35), 0 0 0 1px rgba(255, 255, 255, 0.8);
            z-index: 9999;
            animation: modalSlideIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
            border: 3px solid rgba(15, 116, 60, 0.1);
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translate(-50%, -50%) scale(0.8);
            }
            to {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1);
            }
        }

        .modal-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .modal-header h3 {
            background: linear-gradient(135deg, var(--primary-color) 0%, #0a5229 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 800;
            font-size: 26px;
            margin-bottom: 0.75rem;
            line-height: 1.3;
        }

        .modal-header p {
            color: #4B5563;
            font-size: 15px;
            font-weight: 500;
        }

        .star-rating {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin: 2rem 0;
        }

        .star-rating input[type="radio"] {
            display: none;
        }

        .star-rating label {
            cursor: pointer;
            font-size: 3.5rem;
            color: #FFD700;
            opacity: 0.3;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            filter: drop-shadow(0 4px 8px rgba(255, 165, 0, 0.3));
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .star-rating label:hover {
            color: #FFA500;
            opacity: 1;
            transform: scale(1.2) rotate(-8deg);
            filter: drop-shadow(0 6px 12px rgba(255, 165, 0, 0.5));
        }

        .star-rating label.active {
            color: #FF8C00;
            opacity: 1;
            animation: starPulse 0.4s ease;
            filter: drop-shadow(0 6px 15px rgba(255, 140, 0, 0.6));
        }

        @keyframes starPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }

        .comment-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 1rem;
        }

        .comment-chip {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 2px solid #dee2e6;
            border-radius: 30px;
            padding: 12px 24px;
            font-size: 15px;
            font-weight: 600;
            color: #495057;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            user-select: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .comment-chip:hover {
            background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
            border-color: #adb5bd;
            transform: translateY(-3px) scale(1.03);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .comment-chip.selected {
            background: linear-gradient(135deg, #0F743C 0%, #0a5229 100%);
            border-color: #0F743C;
            color: white;
            transform: scale(1.08);
            box-shadow: 0 6px 20px rgba(15, 116, 60, 0.4);
            animation: chipSelect 0.3s ease;
        }

        @keyframes chipSelect {
            0% { transform: scale(1); }
            50% { transform: scale(1.15); }
            100% { transform: scale(1.08); }
        }

        .comment-chip i {
            margin-right: 6px;
        }

        .modal-actions {
            display: flex;
            gap: 12px;
            margin-top: 2rem;
        }

        .btn-cancel {
            flex: 1;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            color: #495057;
            border: 2px solid #dee2e6;
            padding: 16px 24px;
            border-radius: 14px;
            font-size: 17px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);\n        }

        .btn-cancel:hover {
            background: #E5E7EB;
            transform: translateY(-2px);
        }

        .btn-submit {
            flex: 2;
            background: linear-gradient(135deg, #0F743C 0%, #0a5229 100%);
            color: white;
            border: none;
            padding: 16px 36px;
            border-radius: 14px;
            font-size: 17px;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(15, 116, 60, 0.5), inset 0 2px 4px rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .btn-submit::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s;
        }

        .btn-submit:hover:not(:disabled)::before {
            left: 100%;
        }

        .btn-submit:hover:not(:disabled) {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(15, 116, 60, 0.5);
        }

        .btn-submit:disabled {
            background: #D1D5DB;
            cursor: not-allowed;
            box-shadow: none;
            opacity: 0.6;
        }

        .completion-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 20px;
            border-radius: 50px;
            color: var(--white);
            font-weight: 700;
            font-size: 14px;
        }

        .spinner {
            width: 18px;
            height: 18px;
            border: 3px solid rgba(15, 116, 60, 0.2);
            border-top-color: var(--primary-color);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .btn-submit .spinner {
            border-color: rgba(255, 255, 255, 0.3);
            border-top-color: white;
        }

        @media (max-width: 767px) {
            .review-modal {
                padding: 2rem 1.5rem;
                width: 95%;
            }

            .modal-header h3 {
                font-size: 20px;
            }

            .star-rating {
                gap: 8px;
            }

            .star-rating label {
                font-size: 2.5rem;
            }

            .modal-actions {
                flex-direction: column;
            }

            .btn-cancel, .btn-submit {
                width: 100%;
            }
        }

        @media (max-width: 576px) {
            .star-rating label {
                font-size: 2rem;
            }

            .progress-container {
                padding: 4px;
            }

            #myProgress {
                height: 40px;
            }

            #myBar {
                line-height: 40px;
                font-size: 14px;
            }
        }

        .content-wrapper {
            padding-top: 60px;
            min-height: 100vh;
            padding: 60px 12px 12px 12px;
        }

        .advertise-wrapper {
            max-width: 100%;
            margin: 0 auto;
            background: var(--white);
            border-radius: 12px;
            padding: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border-color);
        }

        .adFram {
            border: 0;
            width: 100%;
            min-height: 400px;
            border-radius: 8px;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .adBody {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 300px;
            padding: 1rem;
        }

        .iframe-container {
            position: relative;
            width: 100%;
            padding-bottom: 56.25%;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .iframe-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .ad-image {
            width: 100%;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s ease;
        }

        .ad-image:hover {
            transform: scale(1.005);
        }

        @media only screen and (max-width: 768px) {
            .content-wrapper {
                padding-top: 56px;
                padding: 56px 8px 8px 8px;
            }

            .advertise-wrapper {
                margin: 0;
                padding: 10px;
            }

            .adFram {
                min-height: 300px;
            }

            .adBody {
                min-height: 200px;
                padding: 0.75rem;
            }

            .ad-info-section {
                padding: 10px 12px;
                margin-bottom: 10px;
            }

            .ad-title {
                font-size: 14px;
            }

            .ad-description {
                font-size: 12px;
            }

            .ad-description-wrapper {
                max-height: 60px;
            }

            .ad-meta {
                gap: 12px;
                flex-wrap: wrap;
            }

            .ad-meta-item {
                font-size: 11px;
            }
        }

        /* Pause Overlay Styles */
        #pauseOverlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.9);
            z-index: 10000;
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.3s ease;
        }

        .pause-content {
            text-align: center;
            padding: 3rem;
            max-width: 500px;
        }

        .pause-icon {
            font-size: 80px;
            color: var(--warning-color);
            margin-bottom: 1.5rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }

        .pause-content h2 {
            color: white;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .pause-content p {
            color: #ccc;
            font-size: 16px;
            margin-bottom: 1.5rem;
        }

        .warning-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, var(--error-color) 0%, #c0392b 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 14px;
            box-shadow: 0 4px 15px rgba(218, 62, 47, 0.4);
        }

        /* Blocked Container Styles */
        .blocked-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            padding: 2rem;
        }

        .blocked-content {
            text-align: center;
            background: rgba(255, 255, 255, 0.05);
            padding: 3rem;
            border-radius: 24px;
            max-width: 450px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }

        .blocked-icon {
            font-size: 80px;
            color: var(--error-color);
            margin-bottom: 1.5rem;
        }

        .blocked-content h2 {
            color: white;
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .blocked-content p {
            color: #aaa;
            font-size: 16px;
            margin-bottom: 2rem;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, var(--primary-color) 0%, #0a5229 100%);
            color: white;
            padding: 14px 28px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(15, 116, 60, 0.4);
        }

        .back-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(15, 116, 60, 0.5);
            color: white;
        }

        /* Video Playing Indicator */
        .video-status {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 12px 20px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 1000;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .video-status.playing {
            background: linear-gradient(135deg, var(--primary-color) 0%, #0a5229 100%);
        }

        .video-status.paused {
            background: linear-gradient(135deg, var(--warning-color) 0%, #e67e22 100%);
        }

        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #4ade80;
            animation: blink 1s infinite;
        }

        .video-status.paused .status-dot {
            background: white;
            animation: none;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
    </style>
</head>

<body>
    <!-- Fixed Header with Progress -->
    <div class="ptc-header">
        <div class="container">
            <div class="progress-container">
                <div id="myProgress">
                    <div id="myBar">0%</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Area -->
    <div class="content-wrapper">
        <!-- Ad Info Section with Title & Description -->
        <div class="ad-info-section">
            <div class="ad-title">
                <i class="fas fa-bullhorn"></i>
                <span>{{ $ptc->title ?? 'বিজ্ঞাপন' }}</span>
            </div>
            @if($ptc->description)
            <div class="ad-description-wrapper">
                <div class="ad-description">{{ $ptc->description }}</div>
            </div>
            @endif
            <div class="ad-meta">
                <div class="ad-meta-item">
                    <i class="fas fa-clock"></i>
                    <span>সময়:</span>
                    <span class="value">{{ $ptc->duration }}s</span>
                </div>
                <div class="ad-meta-item">
                    <i class="fas fa-coins"></i>
                    <span>আয়:</span>
                    <span class="value">৳{{ showAmount(($userPlan->ptc_view_amount ?? null) ?? $ptc->amount ?? 0) }}</span>
                </div>
                @if($ptc->ads_type == 4)
                <div class="ad-meta-item">
                    <i class="fab fa-youtube"></i>
                    <span>ইউটিউব ভিডিও</span>
                </div>
                @elseif($ptc->ads_type == 2)
                <div class="ad-meta-item">
                    <i class="fas fa-image"></i>
                    <span>ছবি বিজ্ঞাপন</span>
                </div>
                @elseif($ptc->ads_type == 1)
                <div class="ad-meta-item">
                    <i class="fas fa-link"></i>
                    <span>ওয়েবসাইট</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Ad Content -->
        <div class="advertise-wrapper">
            @if ($ptc->ads_type == 1)
                <iframe src="{{ $ptc->ads_body }}" class="adFram"></iframe>
            @elseif($ptc->ads_type == 2)
                <img src="{{ getImage(fileManager()->ptc()->path . '/' . $ptc->ads_body) }}" class="ad-image" alt="{{ $ptc->title }}">
            @elseif($ptc->ads_type == 3)
                <div class="adBody">
                    {!! clean($ptc->ads_body) !!}
                </div>
            @else
                <div class="iframe-container">
                    <div id="youtube-player"></div>
                </div>
                <!-- Video Status Indicator -->
                <div class="video-status" id="videoStatus">
                    <span class="status-dot"></span>
                    <span id="statusText">ভিডিও লোড হচ্ছে...</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Modal Overlay -->
    <div class="modal-overlay" id="modalOverlay"></div>

    <!-- Review Modal -->
    <div class="review-modal" id="reviewModal">
        <form action="" id="confirm-form" method="post">
            @csrf
            <input type="hidden" name="rating" id="ratingInput" required>

            <div class="modal-header">
                <h3>বিজ্ঞাপনটি কেমন লাগলো?</h3>
                <p>আপনার মতামত আমাদের জানান এবং টাকা পান</p>
            </div>

            <div class="star-rating" id="starRating">
                <input type="radio" name="star" id="star5" value="5">
                <label for="star5" data-rating="5"><i class="fas fa-star"></i></label>
                <input type="radio" name="star" id="star4" value="4">
                <label for="star4" data-rating="4"><i class="fas fa-star"></i></label>
                <input type="radio" name="star" id="star3" value="3">
                <label for="star3" data-rating="3"><i class="fas fa-star"></i></label>
                <input type="radio" name="star" id="star2" value="2">
                <label for="star2" data-rating="2"><i class="fas fa-star"></i></label>
                <input type="radio" name="star" id="star1" value="1">
                <label for="star1" data-rating="1"><i class="fas fa-star"></i></label>
            </div>

            <div class="mb-3">
                <label class="form-label" style="font-weight: 600; color: #374151; margin-bottom: 0.5rem; display: block;">মন্তব্য (ঐচ্ছিক)</label>
                <input type="hidden" name="comment" id="commentInput">
                <div class="comment-chips">
                    <div class="comment-chip" data-comment="খুব ভালো বিজ্ঞাপন">
                        <i class="fas fa-thumbs-up"></i>খুব ভালো
                    </div>
                    <div class="comment-chip" data-comment="আকর্ষণীয় কন্টেন্ট">
                        <i class="fas fa-star"></i>আকর্ষণীয়
                    </div>
                    <div class="comment-chip" data-comment="তথ্যবহুল বিজ্ঞাপন">
                        <i class="fas fa-info-circle"></i>তথ্যবহুল
                    </div>
                    <div class="comment-chip" data-comment="প্রাসঙ্গিক এবং উপযোগী">
                        <i class="fas fa-check-circle"></i>প্রাসঙ্গিক
                    </div>
                    <div class="comment-chip" data-comment="সাধারণ মানের">
                        <i class="fas fa-minus-circle"></i>সাধারণ
                    </div>
                    <div class="comment-chip" data-comment="আরও উন্নতি প্রয়োজন">
                        <i class="fas fa-exclamation-circle"></i>উন্নতি প্রয়োজন
                    </div>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-cancel" id="cancelBtn">
                    <i class="fas fa-times me-2"></i>বাতিল
                </button>
                <button type="submit" id="submitBtn" class="btn-submit" disabled>
                    <i class="fas fa-paper-plane me-2"></i>কাজ জমা দিন
                </button>
            </div>
        </form>
    </div>
    @if($ptc->ads_type == 4)
    <!-- YouTube IFrame API -->
    <script src="https://www.youtube.com/iframe_api"></script>
    @endif

    <script>
        (function($, document) {
            "use strict";

            let selectedRating = 0;
            let adCompleted = false;
            let progressInterval = null;
            let currentWidth = 0;
            let ytPlayer = null;
            let videoWatchedTime = 0;
            let requiredWatchTime = {{ $ptc->duration }};
            let isYouTubeAd = {{ $ptc->ads_type == 4 ? 'true' : 'false' }};

            // YouTube Player Ready Callback
            window.onYouTubeIframeAPIReady = function() {
                if (!isYouTubeAd) return;

                @php
                    // Extract YouTube video ID from URL
                    $youtubeUrl = $ptc->ads_body;
                    preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $youtubeUrl, $matches);
                    $videoId = $matches[1] ?? '';
                @endphp

                ytPlayer = new YT.Player('youtube-player', {
                    height: '100%',
                    width: '100%',
                    videoId: '{{ $videoId }}',
                    playerVars: {
                        'autoplay': 1,
                        'controls': 1,
                        'rel': 0,
                        'modestbranding': 1,
                        'playsinline': 1
                    },
                    events: {
                        'onReady': onPlayerReady,
                        'onStateChange': onPlayerStateChange
                    }
                });
            };

            function onPlayerReady(event) {
                event.target.playVideo();
                updateVideoStatus('playing', 'ভিডিও চালু হচ্ছে');
            }

            function onPlayerStateChange(event) {
                var statusEl = $('#videoStatus');
                var statusText = $('#statusText');

                switch(event.data) {
                    case YT.PlayerState.PLAYING:
                        updateVideoStatus('playing', 'ভিডিও চলছে');
                        FraudPrevention.videoPlaying = true;
                        if (!progressInterval) {
                            startProgress();
                        }
                        break;
                    case YT.PlayerState.PAUSED:
                        updateVideoStatus('paused', 'ভিডিও বিরতি');
                        FraudPrevention.videoPlaying = false;
                        pauseProgress();
                        break;
                    case YT.PlayerState.ENDED:
                        updateVideoStatus('playing', 'ভিডিও শেষ হয়েছে');
                        break;
                    case YT.PlayerState.BUFFERING:
                        updateVideoStatus('paused', 'ভিডিও বাফারিং...');
                        break;
                }
            }

            function updateVideoStatus(state, text) {
                var statusEl = $('#videoStatus');
                statusEl.removeClass('playing paused').addClass(state);
                $('#statusText').text(text);
            }

            // Progress bar animation with pause/resume support
            function startProgress() {
                if (progressInterval) return;

                var elem = document.getElementById("myBar");
                var duration = requiredWatchTime;
                var increment = 100 / duration;

                progressInterval = setInterval(function() {
                    // Check if paused by fraud prevention
                    if (FraudPrevention.isPaused) {
                        return;
                    }

                    // For YouTube, check if video is actually playing
                    if (isYouTubeAd && ytPlayer && typeof ytPlayer.getPlayerState === 'function') {
                        if (ytPlayer.getPlayerState() !== YT.PlayerState.PLAYING) {
                            return;
                        }
                    }

                    if (currentWidth >= 100) {
                        clearInterval(progressInterval);
                        progressInterval = null;
                        elem.style.background = 'linear-gradient(90deg, #28a745 0%, #20c997 100%)';
                        elem.style.color = 'white';
                        elem.innerHTML = '<i class="fas fa-check-circle me-2"></i>সম্পূর্ণ হয়েছে!';
                        adCompleted = true;

                        // Hide video status
                        $('#videoStatus').fadeOut();

                        // Show review modal after 800ms
                        setTimeout(showReviewModal, 800);
                    } else {
                        currentWidth += increment;
                        if (currentWidth > 100) currentWidth = 100;
                        elem.style.width = currentWidth + '%';
                        var emoji = currentWidth < 33 ? '⏳' : currentWidth < 66 ? '⚡' : '🚀';
                        elem.innerHTML = emoji + ' ' + Math.floor(currentWidth) + '%';
                    }
                }, 1000);
            }

            function pauseProgress() {
                // Progress pauses automatically via the interval check
            }

            function resumeProgress() {
                if (!progressInterval && !adCompleted) {
                    startProgress();
                }
            }

            // Show review modal
            function showReviewModal() {
                $('#modalOverlay').fadeIn(300);
                $('#reviewModal').css('display', 'block');
                $('body').css('overflow', 'hidden');
            }

            // Hide review modal
            function hideReviewModal() {
                $('#modalOverlay').fadeOut(300);
                $('#reviewModal').fadeOut(300);
                $('body').css('overflow', 'auto');
            }

            // Cancel button
            $('#cancelBtn').on('click', function() {
                if (confirm('আপনি কি নিশ্চিত? রিভিউ না দিলে টাকা পাবেন না।')) {
                    hideReviewModal();
                    window.location.href = '{{ route('user.ptc.index') }}';
                }
            });

            // Comment chip functionality
            $('.comment-chip').on('click', function() {
                var comment = $(this).data('comment');

                // Toggle selection
                if ($(this).hasClass('selected')) {
                    $(this).removeClass('selected');
                    $('#commentInput').val('');
                } else {
                    $('.comment-chip').removeClass('selected');
                    $(this).addClass('selected');
                    $('#commentInput').val(comment);
                }
            });

            // Star rating functionality
            $('#starRating label').on('click', function() {
                selectedRating = $(this).data('rating');
                $('#ratingInput').val(selectedRating);

                // Remove all active classes
                $('#starRating label').removeClass('active').css('color', '#E5E7EB');

                // Add active class to selected and previous stars
                $(this).addClass('active').prevAll('label').addClass('active');
                $('#starRating label.active').css('color', '#F99E2B');

                // Enable submit button
                $('#submitBtn').prop('disabled', false);
                $('#confirm-form').attr('action', '{{ route('user.ptc.confirm', encrypt($ptc->id . '|' . auth()->user()->id)) }}');
            });

            // Hover effect for stars
            $('#starRating label').on('mouseenter', function() {
                var tempRating = $(this).data('rating');
                $('#starRating label').each(function() {
                    if ($(this).data('rating') >= tempRating) {
                        $(this).css('color', '#F99E2B');
                    } else {
                        $(this).css('color', '#E5E7EB');
                    }
                });
            });

            $('#starRating').on('mouseleave', function() {
                $('#starRating label').css('color', '#E5E7EB');
                $('#starRating label.active').css('color', '#F99E2B');
            });

            // Form submission with loading state and security data
            $('#confirm-form').on('submit', function(e) {
                if (!adCompleted) {
                    e.preventDefault();
                    alert('দয়া করে বিজ্ঞাপনটি সম্পূর্ণ দেখুন!');
                    return false;
                }

                if (selectedRating === 0) {
                    e.preventDefault();
                    alert('দয়া করে একটি রেটিং নির্বাচন করুন!');
                    return false;
                }

                // Add security data to form
                var securityData = FraudPrevention.getSecurityData();

                // Add hidden fields for security validation
                if (!$('#security_token').length) {
                    $(this).append('<input type="hidden" name="security_token" id="security_token" value="' + securityData.token + '">');
                    $(this).append('<input type="hidden" name="device_fingerprint" id="device_fingerprint" value="' + securityData.fingerprint + '">');
                    $(this).append('<input type="hidden" name="watch_time" id="watch_time" value="' + securityData.watchTime + '">');
                    $(this).append('<input type="hidden" name="tab_switches" id="tab_switches" value="' + securityData.tabSwitches + '">');
                    $(this).append('<input type="hidden" name="client_timestamp" id="client_timestamp" value="' + securityData.timestamp + '">');
                }

                // Show loading state
                $('#submitBtn').prop('disabled', true).html('<span class="spinner me-2"></span>জমা হচ্ছে...');
            });

            // Close modal on overlay click
            $('#modalOverlay').on('click', function() {
                if (confirm('আপনি কি নিশ্চিত? রিভিউ না দিলে টাকা পাবেন না।')) {
                    hideReviewModal();
                    window.location.href = '{{ route('user.ptc.index') }}';
                }
            });

            // Start progress on page load
            window.onload = function() {
                // For non-YouTube ads, start progress immediately
                if (!isYouTubeAd) {
                    startProgress();
                }
                // For YouTube ads, progress starts when video plays (handled by onPlayerStateChange)
            };

        })(jQuery, document);
    </script>
    <script src="{{ asset('assets/global/js/bootstrap.bundle.min.js') }}"></script>
</body>

</html>
