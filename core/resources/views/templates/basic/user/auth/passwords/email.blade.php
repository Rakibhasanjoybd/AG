<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="theme-color" content="#DA3E2F">
    <title>{{ $general->siteName(__($pageTitle ?? 'পাসওয়ার্ড রিকভারি')) }}</title>
    @include('partials.seo')
    <link href="{{ asset('assets/global/css/all.min.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        html,body{height:100%;-webkit-tap-highlight-color:transparent}
        body{font-family:'Noto Sans Bengali',sans-serif;background:#F3F3F1;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:16px}

        .reset-card{width:100%;max-width:400px;background:#fff;border-radius:16px;overflow:hidden}

        .reset-header{background:#DA3E2F;padding:24px 20px;text-align:center}
        .header-icon{width:64px;height:64px;background:#fff;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;margin-bottom:12px}
        .header-icon i{font-size:28px;color:#DA3E2F}
        .reset-header h1{color:#fff;font-size:20px;font-weight:700;margin-bottom:4px}
        .reset-header p{color:rgba(255,255,255,0.9);font-size:12px}

        .reset-body{padding:20px}

        .info-box{background:#FFF9F0;border:1px solid #F99E2B;border-radius:10px;padding:12px;margin-bottom:16px;display:flex;align-items:flex-start;gap:10px}
        .info-box i{color:#F99E2B;font-size:18px;flex-shrink:0;margin-top:2px}
        .info-box p{color:#333;font-size:12px;line-height:1.5;margin:0}

        .form-group{margin-bottom:16px}
        .form-label{display:block;font-size:12px;font-weight:600;color:#333;margin-bottom:6px}

        .input-box{position:relative}
        .input-box i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#0F743C;font-size:14px}
        .input-box input{width:100%;padding:12px 12px 12px 40px;border:2px solid #e8e8e8;border-radius:10px;font-size:14px;font-family:inherit;transition:border-color 0.2s}
        .input-box input:focus{outline:none;border-color:#0F743C}

        .btn-submit{width:100%;padding:14px;background:#0F743C;color:#fff;border:none;border-radius:10px;font-size:15px;font-weight:700;font-family:inherit;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:background 0.2s;margin-bottom:16px}
        .btn-submit:active{background:#0d6334}
        .btn-submit i{font-size:16px}

        .back-link{display:block;text-align:center;color:#999;font-size:12px;text-decoration:none}
        .back-link:hover{color:#0F743C}
        .back-link i{margin-right:4px}
    </style>
</head>
<body>
    <div class="reset-card">
        <div class="reset-header">
            <div class="header-icon">
                <i class="fas fa-unlock-alt"></i>
            </div>
            <h1>{{ __($pageTitle) }}</h1>
            <p>আপনার অ্যাকাউন্ট পুনরুদ্ধার করুন</p>
        </div>

        <div class="reset-body">
            <div class="info-box">
                <i class="fas fa-info-circle"></i>
                <p>@lang('আপনার অ্যাকাউন্ট পুনরুদ্ধার করতে ইমেইল বা ইউজারনেম দিন। আমরা আপনাকে একটি ভেরিফিকেশন কোড পাঠাবো।')</p>
            </div>

            <form method="POST" action="{{ route('user.password.email') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">@lang('ইমেইল বা ইউজারনেম')</label>
                    <div class="input-box">
                        <i class="fas fa-user"></i>
                        <input type="text" name="value" value="{{ old('value') }}" placeholder="@lang('আপনার ইমেইল বা ইউজারনেম')" required autofocus="off">
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i>
                    @lang('কোড পাঠান')
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
</body>
</html>
