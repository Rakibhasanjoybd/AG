<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="theme-color" content="#0F743C">
    <title>{{ $general->siteName(__($pageTitle ?? 'নতুন পাসওয়ার্ড')) }}</title>
    @include('partials.seo')
    <link href="{{ asset('assets/global/css/all.min.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        html,body{height:100%;-webkit-tap-highlight-color:transparent}
        body{font-family:'Noto Sans Bengali',sans-serif;background:#F3F3F1;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:16px}

        .reset-card{width:100%;max-width:400px;background:#fff;border-radius:16px;overflow:hidden}

        .reset-header{background:#0F743C;padding:24px 20px;text-align:center}
        .header-icon{width:64px;height:64px;background:#fff;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;margin-bottom:12px}
        .header-icon i{font-size:28px;color:#0F743C}
        .reset-header h1{color:#fff;font-size:20px;font-weight:700;margin-bottom:4px}
        .reset-header p{color:rgba(255,255,255,0.9);font-size:12px}

        .reset-body{padding:20px}

        .success-box{background:#f0fdf4;border:1px solid #0F743C;border-radius:10px;padding:12px;margin-bottom:16px;display:flex;align-items:flex-start;gap:10px}
        .success-box i{color:#0F743C;font-size:18px;flex-shrink:0;margin-top:2px}
        .success-box p{color:#333;font-size:12px;line-height:1.5;margin:0}

        .form-group{margin-bottom:14px}
        .form-label{display:block;font-size:12px;font-weight:600;color:#333;margin-bottom:6px}

        .input-box{position:relative}
        .input-box i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#0F743C;font-size:14px}
        .input-box input{width:100%;padding:12px 12px 12px 40px;border:2px solid #e8e8e8;border-radius:10px;font-size:14px;font-family:inherit;transition:border-color 0.2s}
        .input-box input:focus{outline:none;border-color:#0F743C}

        .pass-hints{background:#FFF9F0;border:1px solid #F99E2B;border-radius:8px;padding:10px;margin-top:8px}
        .pass-hints p{font-size:10px;color:#DA3E2F;margin-bottom:3px;display:flex;align-items:center;gap:5px}
        .pass-hints p:last-child{margin-bottom:0}
        .pass-hints p::before{content:'×';font-weight:bold;font-size:12px}
        .pass-hints p.ok{color:#0F743C}
        .pass-hints p.ok::before{content:'✓'}

        .btn-submit{width:100%;padding:14px;background:#0F743C;color:#fff;border:none;border-radius:10px;font-size:15px;font-weight:700;font-family:inherit;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:background 0.2s;margin-top:6px}
        .btn-submit:active{background:#0d6334}
        .btn-submit i{font-size:16px}

        .back-link{display:block;text-align:center;color:#999;font-size:12px;text-decoration:none;margin-top:16px}
        .back-link:hover{color:#0F743C}
        .back-link i{margin-right:4px}
    </style>
</head>
<body>
    <div class="reset-card">
        <div class="reset-header">
            <div class="header-icon">
                <i class="fas fa-key"></i>
            </div>
            <h1>{{ __($pageTitle) }}</h1>
            <p>নতুন পাসওয়ার্ড সেট করুন</p>
        </div>

        <div class="reset-body">
            <div class="success-box">
                <i class="fas fa-check-circle"></i>
                <p>@lang('আপনার অ্যাকাউন্ট যাচাই হয়েছে। এখন নতুন পাসওয়ার্ড দিন। শক্তিশালী পাসওয়ার্ড ব্যবহার করুন।')</p>
            </div>

            <form method="POST" action="{{ route('user.password.update') }}">
                @csrf
                <input type="hidden" name="email" value="{{ $email }}">
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="form-group">
                    <label class="form-label">@lang('নতুন পাসওয়ার্ড')</label>
                    <div class="input-box">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" placeholder="@lang('নতুন পাসওয়ার্ড লিখুন')" required>
                    </div>
                    @if($general->secure_password)
                    <div class="pass-hints">
                        <p class="lower">@lang('১টি ছোট অক্ষর')</p>
                        <p class="capital">@lang('১টি বড় অক্ষর')</p>
                        <p class="number">@lang('১টি সংখ্যা')</p>
                        <p class="special">@lang('১টি বিশেষ চিহ্ন')</p>
                        <p class="minimum">@lang('কমপক্ষে ৬ অক্ষর')</p>
                    </div>
                    @endif
                </div>

                <div class="form-group">
                    <label class="form-label">@lang('পাসওয়ার্ড নিশ্চিত করুন')</label>
                    <div class="input-box">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password_confirmation" placeholder="@lang('আবার পাসওয়ার্ড লিখুন')" required>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-check"></i>
                    @lang('পাসওয়ার্ড পরিবর্তন করুন')
                </button>
            </form>

            <a href="{{ route('user.login') }}" class="back-link">
                <i class="fas fa-arrow-left"></i>
                @lang('লগইন পেজে ফিরুন')
            </a>
        </div>
    </div>

    <script src="{{ asset('assets/global/js/jquery-3.6.0.min.js') }}"></script>
    @include('partials.notify')

    @if($general->secure_password)
    <script src="{{ asset('assets/global/js/secure_password.js') }}"></script>
    <script>
        (function($){"use strict";
            $('input[name=password]').on('input',function(){
                var p=$(this).val();
                $('.pass-hints p').removeClass('ok');
                if(/[a-z]/.test(p))$('.lower').addClass('ok');
                if(/[A-Z]/.test(p))$('.capital').addClass('ok');
                if(/[0-9]/.test(p))$('.number').addClass('ok');
                if(/[^a-zA-Z0-9]/.test(p))$('.special').addClass('ok');
                if(p.length>=6)$('.minimum').addClass('ok');
            });
        })(jQuery);
    </script>
    @endif
</body>
</html>
