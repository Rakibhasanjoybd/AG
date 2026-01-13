@php
    $loginCaption = getContent('login.content',true);
@endphp
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="theme-color" content="#0F743C">
    <title>{{ $general->siteName(__($pageTitle ?? 'লগইন')) }}</title>
    @include('partials.seo')
    <link href="{{ asset('assets/global/css/all.min.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        html,body{height:100%;-webkit-tap-highlight-color:transparent}
        body{font-family:'Noto Sans Bengali',sans-serif;background:#F3F3F1;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:16px}

        .login-card{width:100%;max-width:400px;background:#fff;border-radius:16px;overflow:hidden}

        /* Header */
        .login-header{background:#0F743C;padding:24px 20px;text-align:center}
        .header-icon{width:60px;height:60px;background:#fff;border-radius:12px;display:inline-flex;align-items:center;justify-content:center;margin-bottom:12px}
        .header-icon img{width:40px;height:40px;object-fit:contain}
        .login-header h1{color:#fff;font-size:20px;font-weight:700;margin-bottom:4px}
        .login-header p{color:rgba(255,255,255,0.9);font-size:12px}

        /* Body */
        .login-body{padding:20px}

        /* Alert */
        .alert-box{background:#FFF5F5;border:1px solid #DA3E2F;border-radius:8px;padding:10px 12px;margin-bottom:16px;display:none;align-items:center;gap:8px}
        .alert-box.show{display:flex}
        .alert-box i{color:#DA3E2F;font-size:16px;flex-shrink:0}
        .alert-box span{color:#DA3E2F;font-size:11px;line-height:1.4}

        /* Form */
        .form-group{margin-bottom:14px}
        .form-label{display:block;font-size:12px;font-weight:600;color:#333;margin-bottom:6px}
        .form-label .req{color:#DA3E2F}

        .input-box{position:relative}
        .input-box i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#0F743C;font-size:14px}
        .input-box input,.input-box select{width:100%;padding:12px 12px 12px 38px;border:2px solid #e8e8e8;border-radius:10px;font-size:14px;font-family:inherit;background:#fff;transition:border-color 0.2s}
        .input-box input:focus,.input-box select:focus{outline:none;border-color:#0F743C}

        .mobile-row{display:flex}
        .country-code{background:#F3F3F1;border:2px solid #e8e8e8;border-right:none;border-radius:10px 0 0 10px;padding:12px;font-size:13px;font-weight:600;color:#333;min-width:60px;text-align:center;display:flex;align-items:center;justify-content:center}
        .mobile-row input{border-radius:0 10px 10px 0;padding-left:12px}

        /* Options */
        .form-options{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px}
        .remember{display:flex;align-items:center;gap:6px;font-size:11px;color:#666}
        .remember input{width:16px;height:16px;accent-color:#0F743C}
        .forgot{font-size:11px;color:#DA3E2F;text-decoration:none;font-weight:600}
        .forgot:hover{text-decoration:underline}

        /* Submit */
        .btn-submit{width:100%;padding:14px;background:#0F743C;color:#fff;border:none;border-radius:10px;font-size:15px;font-weight:700;font-family:inherit;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:background 0.2s}
        .btn-submit:active{background:#0d6334}
        .btn-submit i{font-size:16px}

        /* Divider */
        .divider{display:flex;align-items:center;margin:18px 0}
        .divider::before,.divider::after{content:'';flex:1;height:1px;background:#e8e8e8}
        .divider span{padding:0 12px;color:#999;font-size:11px}

        /* Links */
        .alt-link{text-align:center;font-size:13px;color:#666}
        .alt-link a{color:#F99E2B;font-weight:700;text-decoration:none}
        .alt-link a:hover{text-decoration:underline}

        /* Footer */
        .login-footer{text-align:center;padding:0 20px 16px}
        .back-link{display:inline-flex;align-items:center;gap:6px;color:#999;font-size:12px;text-decoration:none}
        .back-link:hover{color:#0F743C}

        /* Captcha */
        .captcha-wrap{margin-bottom:14px}

        @media(max-width:360px){
            .login-body{padding:16px}
            .btn-submit{padding:12px}
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <div class="header-icon">
                <img src="{{ getImage(getFilePath('logoIcon') .'/logo.png') }}" alt="{{ $general->site_name ?? 'AGCO' }}">
            </div>
            <h1>{{ __(@$loginCaption->data_values->heading ?? 'স্বাগতম!') }}</h1>
            <p>আপনার অ্যাকাউন্টে লগইন করুন</p>
        </div>

        <div class="login-body">
            <div class="alert-box" id="countryMismatchAlert">
                <i class="fas fa-exclamation-triangle"></i>
                <span>@lang('আপনি হয়তো ভুল তথ্য দিচ্ছেন অথবা VPN ব্যবহার করছেন')</span>
            </div>

            <form class="verify-gcaptcha" action="{{ route('user.login') }}" method="post">
                @csrf

                <div class="form-group">
                    <label class="form-label">@lang('দেশ') <span class="req">*</span></label>
                    <div class="input-box">
                        <i class="fas fa-globe"></i>
                        <select name="country" id="loginCountry">
                            @foreach ($countries as $key => $country)
                                <option data-mobile_code="{{ $country->dial_code }}" value="{{ $country->country }}" data-code="{{ $key }}"
                                    @if(isset($detectedCountry) && $key == $detectedCountry) selected @endif>
                                    {{ __($country->country) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">@lang('মোবাইল নম্বর') <span class="req">*</span></label>
                    <div class="mobile-row">
                        <span class="country-code mobile-code"></span>
                        <input type="hidden" name="mobile_code" id="mobileCode">
                        <input type="hidden" name="country_code" id="countryCode">
                        <input type="number" name="mobile" id="mobileNumber" placeholder="@lang('XXXXXXXXXX')" required value="{{ old('mobile') }}">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">@lang('পাসওয়ার্ড') <span class="req">*</span></label>
                    <div class="input-box">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" placeholder="@lang('পাসওয়ার্ড লিখুন')" required>
                    </div>
                </div>

                <div class="captcha-wrap">
                    <x-captcha></x-captcha>
                </div>

                <div class="form-options">
                    <label class="remember">
                        <input type="checkbox" name="remember" {{ old('mobile') !== null ? (old('remember') ? 'checked' : '') : 'checked' }}>
                        @lang('মনে রাখুন')
                    </label>
                    <a href="{{ route('user.password.request') }}" class="forgot">@lang('পাসওয়ার্ড ভুলে গেছেন?')</a>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-sign-in-alt"></i>
                    @lang('লগইন করুন')
                </button>
            </form>

            <div class="divider"><span>অথবা</span></div>

            <p class="alt-link">
                @lang('নতুন ইউজার?') <a href="{{ route('user.register') }}">@lang('রেজিস্টার করুন')</a>
            </p>
        </div>

        <div class="login-footer">
            <a href="{{ route('home') }}" class="back-link">
                <i class="fas fa-arrow-left"></i>
                @lang('হোম পেজে ফিরুন')
            </a>
        </div>
    </div>

    <script src="{{ asset('assets/global/js/jquery-3.6.0.min.js') }}"></script>
    @include('partials.notify')

    @php $googleCaptcha = loadReCaptcha(); @endphp
    @if($googleCaptcha)
    <script>
        function verifyCaptcha(){document.getElementById('g-recaptcha-error').innerHTML='';}
        (function($){"use strict";$('.verify-gcaptcha').on('submit',function(){var response=grecaptcha.getResponse();if(response.length==0){document.getElementById('g-recaptcha-error').innerHTML='<span class="text-danger">@lang("Captcha field is required.")</span>';return false;}return true;});})(jQuery);
    </script>
    @endif

    <script>
        "use strict";
        (function($){
            var detectedCountryCode='{{ $detectedCountry ?? "" }}';
            var forcedCountryCode='{{ $forcedCountryCode ?? "" }}';
            var allowedCountryCodes=@json($allowedCountryCodes ?? []);
            var userSelectedCountry='';
            var isInitialCountrySet=true;

            function getCookie(name){var match=document.cookie.match(new RegExp('(^| )'+name+'=([^;]+)'));return match?decodeURIComponent(match[2]):'';}
            function setCookie(name,value,days){var expires='';if(days){var date=new Date();date.setTime(date.getTime()+(days*24*60*60*1000));expires='; expires='+date.toUTCString();}document.cookie=name+'='+encodeURIComponent(value||'')+expires+'; path=/';}

            var cookieCountryCode=getCookie('user_country_code');
            var initialCountryCode='';

            if(forcedCountryCode){initialCountryCode=forcedCountryCode;}
            else if(cookieCountryCode&&(!allowedCountryCodes.length||allowedCountryCodes.includes(cookieCountryCode))){initialCountryCode=cookieCountryCode;}
            else if(detectedCountryCode){initialCountryCode=detectedCountryCode;}

            if(initialCountryCode){$('#loginCountry option[data-code="'+initialCountryCode+'"]').prop('selected',true);}

            $('#loginCountry').change(function(){
                var opt=$(this).find(':selected');
                $('#mobileCode').val(opt.data('mobile_code'));
                $('#countryCode').val(opt.data('code'));
                $('.mobile-code').text('+'+opt.data('mobile_code'));
                userSelectedCountry=opt.data('code');
                if(!forcedCountryCode&&userSelectedCountry){setCookie('user_country_code',userSelectedCountry,365);}
                if(isInitialCountrySet){isInitialCountrySet=false;$('#countryMismatchAlert').removeClass('show');}
                else if(detectedCountryCode&&userSelectedCountry!==detectedCountryCode){$('#countryMismatchAlert').addClass('show');}
                else{$('#countryMismatchAlert').removeClass('show');}
            }).change();

            $('form').on('submit',function(e){
                var mobile=$('#mobileNumber').val();
                if(mobile.length<10){e.preventDefault();alert('অনুগ্রহ করে সঠিক মোবাইল নম্বর দিন');return false;}
            });
        })(jQuery);
    </script>
</body>
</html>
