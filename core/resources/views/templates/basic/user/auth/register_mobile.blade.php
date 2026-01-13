@extends($activeTemplate.'layouts.app_mobile')
@section('content')
@php
    $policyPages = getContent('policy_pages.element', false, null, true);
@endphp
<div class="auth-page">
    <div class="auth-header">
        <img src="{{ getImage(getFilePath('logoIcon') .'/logo.png') }}" alt="@lang('Logo')" class="auth-logo">
        <h1 class="auth-title">@lang('Create Account')</h1>
        <p class="auth-subtitle">@lang('Join us and start earning today')</p>
    </div>

    <div class="auth-form-container">
        <form class="auth-form verify-gcaptcha" action="{{ route('user.register') }}" method="post">
            @csrf

            <div class="form-group">
                <label class="form-label">@lang('Full Name')</label>
                <input type="text" name="fullname" class="form-control" placeholder="@lang('Enter your full name')" value="{{ old('fullname') }}" required>
                <div class="form-helper">
                    <i class="fas fa-user"></i>
                    <p>@lang('Enter your real name as per your ID for verification')</p>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">@lang('Phone Number (BD)')</label>
                <div class="input-group">
                    <span class="input-prefix">+880</span>
                    <input type="text" name="mobile" class="form-control checkUser" placeholder="@lang('1XXXXXXXXX')" value="{{ old('mobile') }}" required>
                </div>
                <input type="hidden" name="mobile_code" value="880">
                <input type="hidden" name="country_code" value="BD">
                <input type="hidden" name="country" value="Bangladesh">
                <small class="text-danger mobileExist"></small>
                <div class="form-helper">
                    <i class="fas fa-phone"></i>
                    <p>@lang('Enter your 10-digit Bangladesh mobile number (without 0)')</p>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">@lang('Password')</label>
                <input type="password" name="password" class="form-control" placeholder="@lang('Create a strong password')" required>
                @if($general->secure_password)
                <div class="form-helper">
                    <i class="fas fa-shield-alt"></i>
                    <p>@lang('Password must contain: 1 uppercase, 1 lowercase, 1 number, 1 special character, minimum 6 characters')</p>
                </div>
                @endif
            </div>

            <div class="form-group">
                <label class="form-label">@lang('Confirm Password')</label>
                <input type="password" name="password_confirmation" class="form-control" placeholder="@lang('Re-enter your password')" required>
            </div>

            <div class="form-group">
                <label class="form-label">@lang('Referral Code') <span class="text-muted">(@lang('Optional'))</span></label>
                <input type="text" name="referBy" class="form-control" placeholder="@lang('Enter referral code if you have one')" value="{{ session()->get('reference') ?? old('referBy') }}" {{ session()->get('reference') ? 'readonly' : '' }}>
                <div class="form-helper">
                    <i class="fas fa-gift"></i>
                    <p>@lang('Enter a referral code to get bonus rewards on signup')</p>
                </div>
            </div>

            @if($general->agree)
            <div class="form-group">
                <div class="checkbox-group">
                    <input type="checkbox" name="agree" id="agree" @checked(old('agree')) required>
                    <label for="agree">
                        @lang('I agree to the')
                        @foreach($policyPages as $policy)
                            <a href="{{ route('policy.pages', [slug($policy->data_values->title), $policy->id]) }}" target="_blank">{{ __($policy->data_values->title) }}</a>@if(!$loop->last), @endif
                        @endforeach
                    </label>
                </div>
            </div>
            @endif

            <x-captcha></x-captcha>

            <button type="submit" class="auth-btn">
                <i class="fas fa-user-plus"></i> @lang('Create Account')
            </button>

            <div class="auth-footer">
                <p>@lang('Already have an account?') <a href="{{ route('user.login') }}">@lang('Login')</a></p>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="existModalCenter" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 16px;">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Account Exists')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-user-check" style="font-size: 48px; color: var(--primary); margin-bottom: 16px;"></i>
                <p>@lang('You already have an account. Please login instead.')</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('Close')</button>
                <a href="{{ route('user.login') }}" class="btn btn-primary">@lang('Login Now')</a>
            </div>
        </div>
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
    .text-danger {
        color: #ef4444;
        font-size: 12px;
        margin-top: 4px;
        display: block;
    }
</style>
@endpush

@push('script')
<script>
(function($) {
    "use strict";

    $('.checkUser').on('focusout', function(e) {
        var url = '{{ route('user.checkUser') }}';
        var value = $(this).val();
        var token = '{{ csrf_token() }}';

        if ($(this).attr('name') == 'mobile') {
            var mobile = '880' + value;
            var data = { mobile: mobile, _token: token }
        }

        $.post(url, data, function(response) {
            if (response.data != false && response.type == 'mobile') {
                $('#existModalCenter').modal('show');
            } else if (response.data != false) {
                $(`.${response.type}Exist`).text(`${response.type} already exists`);
            } else {
                $(`.${response.type}Exist`).text('');
            }
        });
    });
})(jQuery);
</script>
@endpush
