<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="theme-color" content="#0F743C">
    <title>{{ $general->siteName(__($pageTitle ?? 'নীতিমালা')) }}</title>
    @include('partials.seo')
    <link href="{{ asset('assets/global/css/all.min.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        html,body{height:100%;-webkit-tap-highlight-color:transparent}
        body{font-family:'Noto Sans Bengali',sans-serif;background:#F3F3F1;min-height:100vh}

        /* Header */
        .policy-header{background:#0F743C;padding:16px;position:sticky;top:0;z-index:100}
        .header-inner{max-width:600px;margin:0 auto;display:flex;align-items:center;gap:12px}
        .back-btn{width:36px;height:36px;background:rgba(255,255,255,0.15);border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;text-decoration:none;font-size:16px;flex-shrink:0}
        .back-btn:hover{background:rgba(255,255,255,0.25)}
        .header-title{color:#fff;font-size:16px;font-weight:700;flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}

        /* Content */
        .policy-wrapper{max-width:600px;margin:0 auto;padding:16px}

        .policy-card{background:#fff;border-radius:16px;overflow:hidden}

        .policy-meta{background:#f8f8f6;padding:14px 16px;border-bottom:1px solid #e8e8e8;display:flex;align-items:center;gap:10px}
        .meta-icon{width:40px;height:40px;background:#0F743C;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
        .meta-icon i{color:#fff;font-size:18px}
        .meta-info h1{font-size:15px;font-weight:700;color:#333;margin-bottom:2px}
        .meta-info p{font-size:11px;color:#666}

        .policy-content{padding:16px}

        /* Content Styling */
        .policy-content h1,.policy-content h2,.policy-content h3,.policy-content h4{color:#0F743C;font-weight:700;margin:20px 0 12px;line-height:1.4}
        .policy-content h1{font-size:20px;border-bottom:2px solid #0F743C;padding-bottom:10px;margin-top:0}
        .policy-content h2{font-size:17px;border-left:3px solid #F99E2B;padding-left:10px}
        .policy-content h3{font-size:15px;color:#C7662B}
        .policy-content h4{font-size:14px;color:#DA3E2F}

        .policy-content p{color:#333;line-height:1.7;margin-bottom:12px;font-size:13px}
        .policy-content ul,.policy-content ol{color:#333;line-height:1.7;margin-bottom:12px;padding-left:20px;font-size:13px}
        .policy-content li{margin-bottom:6px}

        .policy-content a{color:#0F743C;text-decoration:none;font-weight:600}
        .policy-content a:hover{text-decoration:underline}

        .policy-content strong{color:#DA3E2F;font-weight:700}

        .policy-content blockquote{border-left:3px solid #C7662B;background:#FFF9F0;padding:12px 14px;margin:14px 0;border-radius:8px;font-style:italic;color:#666;font-size:13px}

        .policy-content table{width:100%;border-collapse:collapse;margin:14px 0;font-size:12px;border-radius:8px;overflow:hidden;border:1px solid #e8e8e8}
        .policy-content th{background:#0F743C;color:#fff;padding:10px;text-align:left;font-weight:600}
        .policy-content td{padding:10px;border-bottom:1px solid #e8e8e8}
        .policy-content tr:last-child td{border-bottom:none}
        .policy-content tr:nth-child(even){background:#f8f8f6}

        .policy-content img{max-width:100%;height:auto;border-radius:8px;margin:10px 0}

        /* Footer */
        .policy-footer{padding:16px;text-align:center;border-top:1px solid #e8e8e8}
        .footer-links{display:flex;gap:12px;justify-content:center;flex-wrap:wrap}
        .footer-links a{color:#666;font-size:11px;text-decoration:none;display:flex;align-items:center;gap:4px}
        .footer-links a:hover{color:#0F743C}
        .footer-links i{font-size:12px}

        /* Read Progress */
        .progress-bar{position:fixed;top:0;left:0;width:0;height:3px;background:linear-gradient(90deg,#F99E2B,#DA3E2F);z-index:1000;transition:width 0.1s}

        @media(max-width:400px){
            .policy-wrapper{padding:12px}
            .policy-content{padding:14px}
            .policy-content h1{font-size:18px}
            .policy-content h2{font-size:15px}
            .policy-content p,.policy-content li{font-size:12px}
        }
    </style>
</head>
<body>
    <div class="progress-bar" id="progressBar"></div>

    <div class="policy-header">
        <div class="header-inner">
            <a href="{{ route('home') }}" class="back-btn">
                <i class="fas fa-arrow-left"></i>
            </a>
            <span class="header-title">{{ __(@$policy->data_values->title ?? $pageTitle) }}</span>
        </div>
    </div>

    <div class="policy-wrapper">
        <div class="policy-card">
            <div class="policy-meta">
                <div class="meta-icon">
                    <i class="fas fa-file-contract"></i>
                </div>
                <div class="meta-info">
                    <h1>{{ __(@$policy->data_values->title ?? $pageTitle) }}</h1>
                    <p>@lang('সর্বশেষ আপডেট'): {{ @$policy->updated_at ? $policy->updated_at->format('d M, Y') : date('d M, Y') }}</p>
                </div>
            </div>

            <div class="policy-content">
                @php echo $policy->data_values->details @endphp
            </div>

            <div class="policy-footer">
                <div class="footer-links">
                    <a href="{{ route('home') }}"><i class="fas fa-home"></i> @lang('হোম')</a>
                    <a href="{{ route('user.login') }}"><i class="fas fa-sign-in-alt"></i> @lang('লগইন')</a>
                    <a href="{{ route('user.register') }}"><i class="fas fa-user-plus"></i> @lang('রেজিস্টার')</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('scroll',function(){
            var winScroll=document.body.scrollTop||document.documentElement.scrollTop;
            var height=document.documentElement.scrollHeight-document.documentElement.clientHeight;
            var scrolled=(winScroll/height)*100;
            document.getElementById('progressBar').style.width=scrolled+'%';
        });
    </script>
</body>
</html>
