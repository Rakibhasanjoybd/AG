@extends($activeTemplate.'layouts.master_mobile')
@section('main-content')

<!-- Page Header -->
<div class="page-header-simple">
    <h2><i class="fas fa-lock me-2"></i>পাসওয়ার্ড পরিবর্তন</h2>
    <p>আপনার অ্যাকাউন্ট সুরক্ষিত রাখুন</p>
</div>

<!-- Password Form -->
<div class="section-block">
    <div class="security-icon">
        <i class="fas fa-shield-alt"></i>
    </div>

    <form action="" method="post">
        @csrf
        <div class="form-group">
            <label class="form-lbl">@lang('Current Password')</label>
            <div class="input-wrap">
                <i class="fas fa-key"></i>
                <input type="password" class="form-input" name="current_password" required autocomplete="current-password" placeholder="বর্তমান পাসওয়ার্ড">
            </div>
        </div>

        <div class="form-group">
            <label class="form-lbl">@lang('New Password')</label>
            <div class="input-wrap">
                <i class="fas fa-lock"></i>
                <input type="password" class="form-input" name="password" required autocomplete="new-password" placeholder="নতুন পাসওয়ার্ড">
            </div>
            @if($general->secure_password)
            <div class="password-rules">
                <p class="rule error lower"><i class="fas fa-circle"></i> ১টি ছোট হাতের অক্ষর</p>
                <p class="rule error capital"><i class="fas fa-circle"></i> ১টি বড় হাতের অক্ষর</p>
                <p class="rule error number"><i class="fas fa-circle"></i> ১টি সংখ্যা</p>
                <p class="rule error special"><i class="fas fa-circle"></i> ১টি বিশেষ চিহ্ন</p>
                <p class="rule error minimum"><i class="fas fa-circle"></i> কমপক্ষে ৬ অক্ষর</p>
            </div>
            @endif
        </div>

        <div class="form-group">
            <label class="form-lbl">@lang('Confirm Password')</label>
            <div class="input-wrap">
                <i class="fas fa-lock"></i>
                <input type="password" class="form-input" name="password_confirmation" required autocomplete="new-password" placeholder="পাসওয়ার্ড নিশ্চিত করুন">
            </div>
        </div>

        <button type="submit" class="btn-submit">
            <i class="fas fa-save me-2"></i>@lang('Update Password')
        </button>
    </form>
</div>

<div class="info-card">
    <div class="info-icon"><i class="fas fa-info-circle"></i></div>
    <div class="info-content">
        <strong>নিরাপত্তা টিপস</strong>
        <p>শক্তিশালী পাসওয়ার্ড ব্যবহার করুন এবং কারো সাথে শেয়ার করবেন না।</p>
    </div>
</div>

<div style="height: 20px;"></div>
@endsection

@push('style')
<style>
.page-header-simple{background:var(--purple);padding:24px 20px;text-align:center}
.page-header-simple h2{color:var(--white);font-size:20px;font-weight:700;margin-bottom:6px;display:flex;align-items:center;justify-content:center}
.page-header-simple p{color:rgba(255,255,255,0.7);font-size:13px;margin:0}

.section-block{background:var(--white);margin:16px;border-radius:20px;padding:20px;box-shadow:0 2px 10px rgba(0,0,0,0.04)}

.security-icon{width:70px;height:70px;background:#f3e8ff;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:28px;color:var(--purple)}

.form-group{margin-bottom:16px}
.form-lbl{display:block;font-size:12px;color:var(--gray);margin-bottom:8px;font-weight:500}
.input-wrap{position:relative}
.input-wrap i{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--gray);font-size:14px}
.form-input{width:100%;padding:14px 14px 14px 42px;border:1.5px solid #e5e7eb;border-radius:12px;font-size:14px;font-family:inherit;background:var(--white);transition:all 0.2s}
.form-input:focus{outline:none;border-color:var(--purple);box-shadow:0 0 0 3px rgba(82,0,106,0.1)}

.password-rules{background:#f8f9fa;border-radius:10px;padding:12px;margin-top:10px}
.rule{font-size:11px;color:var(--gray);margin:4px 0;display:flex;align-items:center;gap:8px}
.rule i{font-size:6px}
.rule.success{color:#16a34a}
.rule.success i{color:#16a34a}

.btn-submit{width:100%;padding:14px;background:var(--purple);color:var(--white);border:none;border-radius:14px;font-size:15px;font-weight:700;font-family:inherit;cursor:pointer;margin-top:8px;display:flex;align-items:center;justify-content:center}

.info-card{display:flex;gap:12px;background:var(--white);margin:0 16px;padding:14px;border-radius:14px;border-left:4px solid var(--gold)}
.info-icon{width:36px;height:36px;background:#fef9c3;border-radius:10px;display:flex;align-items:center;justify-content:center;color:var(--gold);font-size:16px;flex-shrink:0}
.info-content{font-size:12px;color:var(--gray)}
.info-content strong{display:block;color:var(--dark-text);margin-bottom:4px}
.info-content p{margin:0}
</style>
@endpush
@push('script-lib')
<script src="{{ asset('assets/global/js/secure_password.js') }}"></script>
@endpush
@push('script')
<script>
    (function ($) {
        "use strict";
        @if($general->secure_password)
            $('input[name=password]').on('input',function(){
                secure_password($(this));
            });

            $('[name=password]').focus(function () {
                $(this).closest('.form-group').addClass('hover-input-popup');
            });

            $('[name=password]').focusout(function () {
                $(this).closest('.form-group').removeClass('hover-input-popup');
            });

        @endif
    })(jQuery);
</script>
@endpush
