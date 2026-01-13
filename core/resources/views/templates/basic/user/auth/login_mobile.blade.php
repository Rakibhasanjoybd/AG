@extends($activeTemplate.'layouts.app_mobile')
@section('content')
<div class="auth-page">
    <div class="auth-header">
        <img src="{{ getImage(getFilePath('logoIcon') .'/logo.png') }}" alt="@lang('Logo')" class="auth-logo">
        <h1 class="auth-title">@lang('Welcome Back!')</h1>
        <p class="auth-subtitle">@lang('Login to continue earning')</p>
    </div>

    <div class="auth-form-container">
        <form class="auth-form" action="{{ route('user.login') }}" method="post">
            @csrf

            <div class="form-group">
                <label class="form-label">@lang('Phone Number')</label>
                <div class="input-group">
                    <span class="input-prefix">+880</span>
                    <input type="text" name="username" class="form-control" placeholder="@lang('Enter your phone number')" value="{{ old('username') }}" required>
                </div>
                <div class="form-helper">
                    <i class="fas fa-info-circle"></i>
                    <p>@lang('Enter your registered Bangladesh phone number without country code')</p>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">@lang('Password')</label>
                <input type="password" name="password" class="form-control" placeholder="@lang('Enter your password')" required>
            </div>

            <div class="form-group">
                <div class="checkbox-group">
                    <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label for="remember">@lang('Remember me on this device')</label>
                </div>
            </div>

            <x-captcha></x-captcha>

            <button type="submit" class="auth-btn">
                <i class="fas fa-sign-in-alt"></i> @lang('Login')
            </button>

            <div class="auth-footer">
                <p><a href="{{ route('user.password.request') }}">@lang('Forgot Password?')</a></p>
                <p class="mt-2">@lang("Don't have an account?") <a href="{{ route('user.register') }}">@lang('Sign Up')</a></p>
            </div>
        </form>
    </div>
</div>
@endsection

@push('style')
<link rel="stylesheet" href="{{ asset($activeTemplateTrue . 'css/mobile-app.css') }}">
<style>
    body {
        background: var(--bg-gradient);
    }
    .auth-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .mt-2 {
        margin-top: 12px;
    }
</style>
@endpush
