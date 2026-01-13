<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="theme-color" content="#C7662B">
    <title>{{ $general->siteName(__($pageTitle ?? 'কোড যাচাই')) }}</title>
    @include('partials.seo')
    <link href="{{ asset('assets/global/css/all.min.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        html,body{height:100%;-webkit-tap-highlight-color:transparent}
        body{font-family:'Noto Sans Bengali',sans-serif;background:#F3F3F1;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:16px}

        .verify-card{width:100%;max-width:400px;background:#fff;border-radius:16px;overflow:hidden}

        .verify-header{background:#C7662B;padding:24px 20px;text-align:center}
        .header-icon{width:64px;height:64px;background:#fff;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;margin-bottom:12px}
        .header-icon i{font-size:28px;color:#C7662B}
        .verify-header h1{color:#fff;font-size:20px;font-weight:700;margin-bottom:4px}
        .verify-header p{color:rgba(255,255,255,0.9);font-size:12px}

        .verify-body{padding:20px}

        .email-box{background:#f0fdf4;border:1px solid #0F743C;border-radius:10px;padding:12px;margin-bottom:16px;text-align:center}
        .email-box i{color:#0F743C;font-size:20px;margin-bottom:6px}
        .email-box p{color:#333;font-size:12px;margin:0}
        .email-box strong{color:#0F743C;font-weight:700}

        .code-inputs{display:flex;gap:8px;justify-content:center;margin-bottom:16px}
        .code-inputs input{width:44px;height:50px;border:2px solid #e8e8e8;border-radius:10px;text-align:center;font-size:20px;font-weight:700;font-family:inherit;transition:border-color 0.2s}
        .code-inputs input:focus{outline:none;border-color:#0F743C}

        .btn-submit{width:100%;padding:14px;background:#0F743C;color:#fff;border:none;border-radius:10px;font-size:15px;font-weight:700;font-family:inherit;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:background 0.2s;margin-bottom:16px}
        .btn-submit:active{background:#0d6334}
        .btn-submit i{font-size:16px}

        .hint-text{text-align:center;font-size:11px;color:#666;line-height:1.5;margin-bottom:12px}
        .hint-text a{color:#DA3E2F;font-weight:600;text-decoration:none}
        .hint-text a:hover{text-decoration:underline}

        .back-link{display:block;text-align:center;color:#999;font-size:12px;text-decoration:none}
        .back-link:hover{color:#0F743C}
        .back-link i{margin-right:4px}
    </style>
</head>
<body>
    <div class="verify-card">
        <div class="verify-header">
            <div class="header-icon">
                <i class="fas fa-envelope-open-text"></i>
            </div>
            <h1>@lang('কোড যাচাই করুন')</h1>
            <p>আপনার ইমেইলে পাঠানো কোড দিন</p>
        </div>

        <div class="verify-body">
            <div class="email-box">
                <i class="fas fa-check-circle"></i>
                <p>৬ ডিজিটের কোড পাঠানো হয়েছে: <strong>{{ showEmailAddress($email) }}</strong></p>
            </div>

            <form action="{{ route('user.password.verify.code') }}" method="POST" class="submit-form">
                @csrf
                <input type="hidden" name="email" value="{{ $email }}">

                <div class="code-inputs">
                    <input type="text" name="code[]" maxlength="1" required autofocus>
                    <input type="text" name="code[]" maxlength="1" required>
                    <input type="text" name="code[]" maxlength="1" required>
                    <input type="text" name="code[]" maxlength="1" required>
                    <input type="text" name="code[]" maxlength="1" required>
                    <input type="text" name="code[]" maxlength="1" required>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-check"></i>
                    @lang('যাচাই করুন')
                </button>
            </form>

            <p class="hint-text">
                @lang('কোড পাননি? Spam/Junk ফোল্ডার চেক করুন।')<br>
                <a href="{{ route('user.password.request') }}">@lang('আবার কোড পাঠান')</a>
            </p>

            <a href="{{ route('user.login') }}" class="back-link">
                <i class="fas fa-arrow-left"></i>
                @lang('লগইন পেজে ফিরুন')
            </a>
        </div>
    </div>

    <script src="{{ asset('assets/global/js/jquery-3.6.0.min.js') }}"></script>
    @include('partials.notify')

    <script>
        "use strict";
        (function($){
            var inputs=$('.code-inputs input');
            inputs.on('input',function(){
                var val=$(this).val().replace(/[^0-9]/g,'');
                $(this).val(val);
                if(val.length===1){$(this).next('input').focus();}
            });
            inputs.on('keydown',function(e){
                if(e.key==='Backspace'&&$(this).val()===''){ $(this).prev('input').focus();}
            });
            inputs.on('paste',function(e){
                e.preventDefault();
                var paste=(e.originalEvent.clipboardData||window.clipboardData).getData('text').replace(/[^0-9]/g,'');
                inputs.each(function(i){$(this).val(paste[i]||'');});
                inputs.filter(function(){return $(this).val()!=='';}).last().focus();
            });
        })(jQuery);
    </script>
</body>
</html>
