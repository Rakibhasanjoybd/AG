@php
    $policyPages = getContent('policy_pages.element', false, null, true);
    $registerCaption = getContent('register.content', true);
@endphp
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="theme-color" content="#F99E2B">
    <title>{{ $general->siteName(__($pageTitle ?? 'রেজিস্টার')) }}</title>
    @include('partials.seo')
    <link href="{{ asset('assets/global/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/global/css/all.min.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        html,body{height:100%;-webkit-tap-highlight-color:transparent}
        body{font-family:'Noto Sans Bengali',sans-serif;background:#F3F3F1;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:16px}

        .register-card{width:100%;max-width:440px;background:#fff;border-radius:16px;overflow:hidden;max-height:95vh;display:flex;flex-direction:column}

        /* Header */
        .register-header{background:#F99E2B;padding:20px;display:flex;align-items:center;gap:12px;flex-shrink:0}
        .header-icon{width:50px;height:50px;background:#fff;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
        .header-icon img{width:34px;height:34px;object-fit:contain}
        .header-info h1{color:#fff;font-size:18px;font-weight:700;margin-bottom:2px}
        .header-info p{color:rgba(255,255,255,0.9);font-size:11px}

        /* Body */
        .register-body{padding:18px;overflow-y:auto;flex:1;-webkit-overflow-scrolling:touch}

        /* Referral Box */
        .referral-box{background:#f0fdf4;border:1px solid #0F743C;border-radius:10px;padding:12px;margin-bottom:14px}
        .referral-box .form-label{color:#0F743C;display:flex;align-items:center;gap:6px}
        .referral-box .form-label i{font-size:12px}
        .referral-box input{background:#fff;border-color:#86efac}
        .referral-box input:read-only{background:#F3F3F1;cursor:not-allowed}
        .referral-hint{font-size:10px;color:#666;margin-top:4px}

        /* Form */
        .form-group{margin-bottom:12px}
        .form-label{display:block;font-size:11px;font-weight:600;color:#333;margin-bottom:5px}
        .form-label .req{color:#DA3E2F}

        .input-box{position:relative}
        .input-box i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#0F743C;font-size:13px}
        .input-box input,.input-box select{width:100%;padding:11px 12px 11px 36px;border:2px solid #e8e8e8;border-radius:10px;font-size:13px;font-family:inherit;background:#fff;transition:border-color 0.2s}
        .input-box input:focus,.input-box select:focus{outline:none;border-color:#0F743C}
        .input-box.no-icon input{padding-left:12px}

        .form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}

        .mobile-row{display:flex}
        .country-code{background:#F3F3F1;border:2px solid #e8e8e8;border-right:none;border-radius:10px 0 0 10px;padding:11px 10px;font-size:12px;font-weight:600;color:#333;min-width:58px;text-align:center;display:flex;align-items:center;justify-content:center}
        .mobile-row input{border-radius:0 10px 10px 0;padding-left:12px}

        .error-text{font-size:10px;color:#DA3E2F;margin-top:3px}
        .hint-text{font-size:10px;color:#666;margin-top:3px}

        /* Password Hints */
        .pass-hints{background:#FFF9F0;border:1px solid #F99E2B;border-radius:8px;padding:10px;margin-top:6px;display:none}
        .pass-hints.show{display:block}
        .pass-hints p{font-size:10px;color:#DA3E2F;margin-bottom:2px;display:flex;align-items:center;gap:5px}
        .pass-hints p::before{content:'×';font-weight:bold;font-size:12px}
        .pass-hints p.ok{color:#0F743C}
        .pass-hints p.ok::before{content:'✓'}

        /* Agree */
        .agree-row{display:flex;align-items:flex-start;gap:8px;margin-bottom:14px}
        .agree-row input{width:16px;height:16px;margin-top:2px;accent-color:#0F743C;flex-shrink:0}
        .agree-row label{font-size:11px;color:#555;line-height:1.4}
        .agree-row a{color:#DA3E2F;font-weight:600;text-decoration:none}

        /* Submit */
        .btn-submit{width:100%;padding:13px;background:#0F743C;color:#fff;border:none;border-radius:10px;font-size:14px;font-weight:700;font-family:inherit;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:background 0.2s}
        .btn-submit:active{background:#0d6334}
        .btn-submit i{font-size:15px}

        /* Divider */
        .divider{display:flex;align-items:center;margin:16px 0}
        .divider::before,.divider::after{content:'';flex:1;height:1px;background:#e8e8e8}
        .divider span{padding:0 12px;color:#999;font-size:11px}

        /* Links */
        .alt-link{text-align:center;font-size:12px;color:#666}
        .alt-link a{color:#DA3E2F;font-weight:700;text-decoration:none}

        .back-link{display:block;text-align:center;color:#999;font-size:11px;text-decoration:none;margin-top:12px;padding-bottom:4px}
        .back-link:hover{color:#0F743C}

        /* Modal */
        .modal-content{border-radius:16px;overflow:hidden}
        .modal-header{background:#F99E2B;color:#fff;padding:14px 16px}
        .modal-body{padding:20px;text-align:center}
        .modal-body i{font-size:40px;color:#0F743C;margin-bottom:12px}
        .modal-footer{justify-content:center;padding:12px 16px;gap:10px}
        .modal-footer .btn{padding:10px 20px;font-size:13px;border-radius:8px}

        @media(max-width:400px){
            .form-row{grid-template-columns:1fr}
            .register-body{padding:14px}
        }
    </style>
</head>
<body>
    <div class="register-card">
        <div class="register-header">
            <div class="header-icon">
                <img src="{{ getImage(getFilePath('logoIcon') .'/logo.png') }}" alt="{{ $general->site_name ?? 'AGCO' }}">
            </div>
            <div class="header-info">
                <h1>{{ __(@$registerCaption->data_values->heading ?? 'নতুন অ্যাকাউন্ট') }}</h1>
                <p>আজই যোগ দিন এবং আয় শুরু করুন</p>
            </div>
        </div>

        <div class="register-body">
            <form class="verify-gcaptcha" action="{{ route('user.register') }}" method="post">
                @csrf

                <div class="referral-box">
                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label"><i class="fas fa-gift"></i> @lang('রেফারেল কোড')</label>
                        <div class="input-box no-icon">
                            @if (session()->get('reference') != null || request()->get('ref'))
                                <input type="text" name="referral_code" value="{{ session()->get('reference') ?? request()->get('ref') }}" readonly>
                            @else
                                <input type="text" name="referral_code" placeholder="@lang('রেফারেল কোড থাকলে লিখুন')" value="{{ old('referral_code') }}">
                            @endif
                        </div>
                        <div class="referral-hint">@lang('ঐচ্ছিক - না থাকলে খালি রাখুন')</div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">@lang('পুরো নাম') <span class="req">*</span></label>
                    <div class="input-box">
                        <i class="fas fa-user"></i>
                        <input type="text" name="fullname" placeholder="@lang('আপনার পুরো নাম')" value="{{ old('fullname') }}" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">@lang('দেশ') <span class="req">*</span></label>
                        <div class="input-box">
                            <i class="fas fa-globe"></i>
                            <select name="country" required>
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
                        <label class="form-label">@lang('মোবাইল') <span class="req">*</span></label>
                        <div class="mobile-row">
                            <span class="country-code mobile-code"></span>
                            <input type="hidden" name="mobile_code">
                            <input type="hidden" name="country_code">
                            <input type="number" name="mobile" class="checkUser" placeholder="@lang('XXXXXXXXXX')" value="{{ old('mobile') }}" required>
                        </div>
                        <div class="error-text mobileExist"></div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">@lang('পাসওয়ার্ড') <span class="req">*</span></label>
                        <div class="input-box">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" placeholder="@lang('পাসওয়ার্ড')" required>
                        </div>
                        @if ($general->secure_password)
                        <div class="pass-hints" id="passHints">
                            <p class="lower">@lang('১টি ছোট অক্ষর')</p>
                            <p class="capital">@lang('১টি বড় অক্ষর')</p>
                            <p class="number">@lang('১টি সংখ্যা')</p>
                            <p class="special">@lang('১টি বিশেষ চিহ্ন')</p>
                            <p class="minimum">@lang('কমপক্ষে ৬ অক্ষর')</p>
                        </div>
                        @endif
                    </div>

                    <div class="form-group">
                        <label class="form-label">@lang('উত্তোলন পিন') <span class="req">*</span></label>
                        <div class="input-box">
                            <i class="fas fa-shield-alt"></i>
                            <input type="password" name="withdrawal_pin" placeholder="@lang('৪-৬ ডিজিট')" maxlength="6" minlength="4" required pattern="[0-9]{4,6}">
                        </div>
                        <div class="hint-text">@lang('টাকা উত্তোলনে এই পিন লাগবে')</div>
                    </div>
                </div>

                <x-captcha></x-captcha>

                @if ($general->agree)
                <div class="agree-row">
                    <input type="checkbox" id="agree" name="agree" @checked(old('agree')) required>
                    <label for="agree">
                        @lang('আমি সম্মত')
                        @foreach ($policyPages as $policy)
                            <a href="{{ route('policy.pages', [slug($policy->data_values->title), $policy->id]) }}" target="_blank">{{ __($policy->data_values->title) }}</a>@if (!$loop->last), @endif
                        @endforeach
                    </label>
                </div>
                @endif

                <button type="submit" class="btn-submit">
                    <i class="fas fa-user-plus"></i>
                    @lang('রেজিস্টার করুন')
                </button>
            </form>

            <div class="divider"><span>অথবা</span></div>

            <p class="alt-link">
                @lang('ইতিমধ্যে অ্যাকাউন্ট আছে?') <a href="{{ route('user.login') }}">@lang('লগইন করুন')</a>
            </p>

            <a href="{{ route('home') }}" class="back-link">
                <i class="fas fa-arrow-left"></i> @lang('হোম পেজে ফিরুন')
            </a>
        </div>
    </div>

    <div class="modal fade" id="existModalCenter" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('আপনি আমাদের সাথে আছেন')</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <i class="fas fa-user-check"></i>
                    <h6>@lang('আপনার ইতিমধ্যে অ্যাকাউন্ট আছে')</h6>
                    <p class="text-muted small">@lang('অনুগ্রহ করে লগইন করুন')</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('বন্ধ')</button>
                    <a href="{{ route('user.login') }}" class="btn" style="background:#0F743C;color:#fff">@lang('লগইন')</a>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/global/js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('assets/global/js/bootstrap.bundle.min.js') }}"></script>
    @include('partials.notify')

    @if ($general->secure_password)
    <script src="{{ asset('assets/global/js/secure_password.js') }}"></script>
    @endif

    <script>
        "use strict";
        (function($){
            var detectedCountryCode='{{ $detectedCountry ?? "" }}';
            var forcedCountryCode='{{ $forcedCountryCode ?? "" }}';
            var allowedCountryCodes=@json($allowedCountryCodes ?? []);

            function getCookie(n){var m=document.cookie.match(new RegExp('(^| )'+n+'=([^;]+)'));return m?decodeURIComponent(m[2]):'';}
            function setCookie(n,v,d){var e='';if(d){var dt=new Date();dt.setTime(dt.getTime()+(d*24*60*60*1000));e='; expires='+dt.toUTCString();}document.cookie=n+'='+encodeURIComponent(v||'')+e+'; path=/';}

            var cookieCC=getCookie('user_country_code');
            var initCC='';
            if(forcedCountryCode){initCC=forcedCountryCode;}
            else if(cookieCC&&(!allowedCountryCodes.length||allowedCountryCodes.includes(cookieCC))){initCC=cookieCC;}
            else if(detectedCountryCode){initCC=detectedCountryCode;}
            if(initCC){$('select[name=country] option[data-code="'+initCC+'"]').prop('selected',true);}

            $('select[name=country]').change(function(){
                var opt=$(this).find(':selected');
                $('input[name=mobile_code]').val(opt.data('mobile_code'));
                $('input[name=country_code]').val(opt.data('code'));
                $('.mobile-code').text('+'+opt.data('mobile_code'));
                var sc=opt.data('code');
                if(!forcedCountryCode&&sc){setCookie('user_country_code',sc,365);}
            }).change();

            @if ($general->secure_password)
            $('input[name=password]').on('focus',function(){$('#passHints').addClass('show');})
            .on('blur',function(){$('#passHints').removeClass('show');})
            .on('input',function(){
                var p=$(this).val();
                $('.pass-hints p').removeClass('ok');
                if(/[a-z]/.test(p))$('.lower').addClass('ok');
                if(/[A-Z]/.test(p))$('.capital').addClass('ok');
                if(/[0-9]/.test(p))$('.number').addClass('ok');
                if(/[^a-zA-Z0-9]/.test(p))$('.special').addClass('ok');
                if(p.length>=6)$('.minimum').addClass('ok');
            });
            @endif

            $('input[name=withdrawal_pin]').on('input',function(){this.value=this.value.replace(/[^0-9]/g,'');});

            $('.checkUser').on('focusout',function(){
                var url='{{ route('user.checkUser') }}';
                var val=$(this).val();
                var token='{{ csrf_token() }}';
                if($(this).attr('name')=='mobile'){
                    var mobile=$('.mobile-code').text().substr(1)+val;
                    $.post(url,{mobile:mobile,_token:token},function(r){
                        if(r.data!=false&&r.type=='mobile'){$('#existModalCenter').modal('show');}
                        else if(r.data!=false){$('.'+r.type+'Exist').text('এই '+r.type+' ইতিমধ্যে ব্যবহৃত');}
                        else{$('.'+r.type+'Exist').text('');}
                    });
                }
            });

            var urlP=new URLSearchParams(window.location.search);
            var ref=urlP.get('ref');
            if(ref&&$('input[name=referral_code]').val()==''){$('input[name=referral_code]').val(ref).attr('readonly',true);}
        })(jQuery);
    </script>
</body>
</html>
