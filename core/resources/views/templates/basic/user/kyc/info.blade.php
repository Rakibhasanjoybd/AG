@extends($activeTemplate.'layouts.master_mobile')
@section('main-content')

<!-- Page Header -->
<div class="page-header-simple" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">
    <div class="header-content">
        <div class="header-icon">
            <i class="fas fa-id-card"></i>
        </div>
        <h1 class="header-title">@lang('KYC তথ্য')</h1>
        <p class="header-subtitle">@lang('আপনার জমাকৃত KYC তথ্য')</p>
    </div>
</div>

<!-- KYC Data -->
<div class="section-block">
    @if($user->kyc_data)
    <div class="kyc-card">
        @foreach($user->kyc_data as $val)
        @continue(!$val->value)
        <div class="kyc-item">
            <span class="kyc-label">{{__($val->name)}}</span>
            <span class="kyc-value">
                @if($val->type == 'checkbox')
                    {{ implode(',',$val->value) }}
                @elseif($val->type == 'file')
                    <a href="{{ route('user.attachment.download',encrypt(getFilePath('verify').'/'.$val->value)) }}" class="file-link">
                        <i class="fas fa-file-download"></i> @lang('ডাউনলোড')
                    </a>
                @else
                    {{__($val->value)}}
                @endif
            </span>
        </div>
        @endforeach
    </div>
    @else
    <div class="empty-state">
        <div class="empty-icon">
            <i class="fas fa-id-card"></i>
        </div>
        <h4>@lang('KYC তথ্য পাওয়া যায়নি')</h4>
        <p>@lang('আপনি এখনও KYC সম্পন্ন করেননি')</p>
    </div>
    @endif
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
        margin-top: 20px;
    }
    .kyc-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    .kyc-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px;
        border-bottom: 1px solid #f0f0f0;
    }
    .kyc-item:last-child {
        border-bottom: none;
    }
    .kyc-label {
        font-weight: 600;
        color: #333;
        font-size: 0.95rem;
    }
    .kyc-value {
        color: #666;
        font-size: 0.9rem;
        text-align: right;
    }
    .file-link {
        color: #3b82f6;
        text-decoration: none;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .file-link:hover {
        color: #1d4ed8;
    }
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        background: white;
        border-radius: 16px;
    }
    .empty-icon {
        width: 70px;
        height: 70px;
        background: #f3f4f6;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
    }
    .empty-icon i { font-size: 1.8rem; color: #9ca3af; }
    .empty-state h4 { color: #666; margin-bottom: 5px; }
    .empty-state p { color: #999; font-size: 0.9rem; }
</style>
@endpush
