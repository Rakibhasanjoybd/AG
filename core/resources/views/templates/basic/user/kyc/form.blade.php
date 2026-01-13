@extends($activeTemplate.'layouts.master_mobile')
@section('main-content')

<!-- Page Header -->
<div class="page-header-simple" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">
    <div class="header-content">
        <div class="header-icon">
            <i class="fas fa-user-check"></i>
        </div>
        <h1 class="header-title">@lang('KYC ভেরিফিকেশন')</h1>
        <p class="header-subtitle">@lang('আপনার পরিচয় যাচাই করুন')</p>
    </div>
</div>

<!-- Info Card -->
<div class="section-block">
    <div class="info-card">
        <div class="info-icon">
            <i class="fas fa-info-circle"></i>
        </div>
        <div class="info-content">
            <span class="info-title">@lang('গুরুত্বপূর্ণ')</span>
            <span class="info-desc">@lang('KYC সম্পন্ন করলে আপনি সম্পূর্ণ সুবিধা পাবেন')</span>
        </div>
    </div>
</div>

<!-- Form -->
<div class="section-block">
    <div class="form-card">
        <form action="{{route('user.kyc.submit')}}" method="post" enctype="multipart/form-data">
            @csrf

            <x-viser-form identifier="act" identifierValue="kyc"></x-viser-form>

            <button type="submit" class="submit-btn">
                <i class="fas fa-paper-plane"></i>
                @lang('সাবমিট করুন')
            </button>
        </form>
    </div>
</div>

@endsection

@push('style')
<style>
    .page-header-simple {
        padding: 30px 20px;
        text-align: center;
        margin-bottom: 0;
    }
    .header-content { color: white; }
    .header-icon {
        width: 60px;
        height: 60px;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
    }
    .header-icon i { font-size: 1.5rem; color: white; }
    .header-title {
        font-size: 1.4rem;
        font-weight: 700;
        margin-bottom: 5px;
        color: white;
    }
    .header-subtitle {
        font-size: 0.9rem;
        opacity: 0.9;
        color: rgba(255,255,255,0.9);
        margin: 0;
    }
    .section-block {
        padding: 0 15px;
        margin-bottom: 15px;
    }
    .section-block:first-of-type { margin-top: 20px; }
    .info-card {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        border: 1px solid #93c5fd;
        border-radius: 16px;
        padding: 16px;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    .info-icon {
        width: 45px;
        height: 45px;
        background: #3b82f6;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .info-icon i { color: white; font-size: 1.2rem; }
    .info-content { display: flex; flex-direction: column; }
    .info-title {
        font-weight: 600;
        color: #1e40af;
        font-size: 0.95rem;
    }
    .info-desc {
        color: #1d4ed8;
        font-size: 0.85rem;
    }
    .form-card {
        background: white;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    .form-card .form-group {
        margin-bottom: 18px;
    }
    .form-card .form-group label {
        display: block;
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
        font-size: 0.9rem;
    }
    .form-card .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        font-size: 1rem;
        transition: border-color 0.2s;
    }
    .form-card .form-control:focus {
        outline: none;
        border-color: #3b82f6;
    }
    .submit-btn {
        width: 100%;
        padding: 15px;
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: transform 0.2s, box-shadow 0.2s;
        margin-top: 10px;
    }
    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
    }
</style>
@endpush
