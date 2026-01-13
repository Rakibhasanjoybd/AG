@extends($activeTemplate.'layouts.master_mobile')
@section('main-content')

<!-- Page Header -->
<div class="page-header-simple" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
    <div class="header-content">
        <div class="header-icon">
            <i class="fas fa-edit"></i>
        </div>
        <h1 class="header-title">@lang('বিজ্ঞাপন সম্পাদনা')</h1>
        <p class="header-subtitle">@lang('বিজ্ঞাপনের তথ্য আপডেট করুন')</p>
    </div>
</div>

<!-- Back Button -->
<div class="section-block">
    <a href="{{ route('user.ptc.ads') }}" class="back-btn">
        <i class="fas fa-arrow-left"></i>
        @lang('আমার বিজ্ঞাপনসমূহ')
    </a>
</div>

<!-- Form -->
<div class="section-block">
    <div class="form-card">
        <form role="form" method="POST" action="{{ route('user.ptc.update',$ptc->id) }}" enctype="multipart/form-data">
            @csrf
            
            <div class="form-group">
                <label class="form-label">@lang('বিজ্ঞাপনের শিরোনাম')</label>
                <input type="text" name="title" class="form-control" value="{{ $ptc->title }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">@lang('সময়কাল')</label>
                <div class="input-with-addon">
                    <input type="number" name="duration" class="form-control" value="{{ $ptc->duration }}" required>
                    <span class="addon">@lang('সেকেন্ড')</span>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">@lang('বিজ্ঞাপনের ধরন')</label>
                <input type="hidden" name="ads_type" value="{{$ptc->ads_type}}">
                <div class="type-badge">
                    @php echo $ptc->typeBadge @endphp
                </div>
            </div>

            @if($ptc->ads_type == 1)
            <div class="form-group">
                <label class="form-label">@lang('লিংক') <span class="required">*</span></label>
                <input type="text" name="website_link" class="form-control" value="{{ $ptc->ads_body }}" placeholder="@lang('http://example.com')">
            </div>
            @elseif($ptc->ads_type == 2)
            <div class="form-group">
                <label class="form-label">@lang('নতুন ব্যানার')</label>
                <input type="file" class="form-control" name="banner_image">
            </div>
            <div class="form-group">
                <label class="form-label">@lang('বর্তমান ব্যানার') <span class="required">*</span></label>
                <div class="current-banner">
                    <img src="{{ getImage(getFilePath('ptc').'/'.$ptc->ads_body) }}" alt="Banner">
                </div>
            </div>
            @elseif($ptc->ads_type == 3)
            <div class="form-group">
                <label class="form-label">@lang('স্ক্রিপ্ট') <span class="required">*</span></label>
                <textarea name="script" class="form-control" rows="4">{{ $ptc->ads_body }}</textarea>
            </div>
            @else
            <div class="form-group">
                <label class="form-label">@lang('ইউটিউব এম্বেড লিংক') <span class="required">*</span></label>
                <input type="text" name="youtube" class="form-control" value="{{ $ptc->ads_body }}">
            </div>
            @endif

            <button type="submit" class="submit-btn">
                <i class="fas fa-save"></i>
                @lang('আপডেট করুন')
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
    .back-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #8b5cf6;
        font-weight: 500;
        text-decoration: none;
        font-size: 0.95rem;
    }
    .back-btn:hover { color: #7c3aed; }
    .form-card {
        background: white;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    .form-group { margin-bottom: 18px; }
    .form-label {
        display: block;
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
        font-size: 0.9rem;
    }
    .required { color: #ef4444; }
    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        font-size: 1rem;
        transition: border-color 0.2s;
    }
    .form-control:focus {
        outline: none;
        border-color: #8b5cf6;
    }
    .input-with-addon {
        display: flex;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        overflow: hidden;
    }
    .input-with-addon .form-control {
        border: none;
        border-radius: 0;
    }
    .input-with-addon .addon {
        padding: 12px;
        background: #f3f4f6;
        color: #666;
        font-size: 0.85rem;
        white-space: nowrap;
        display: flex;
        align-items: center;
    }
    .type-badge {
        padding: 10px;
        background: #f8fafc;
        border-radius: 8px;
    }
    .current-banner {
        border-radius: 10px;
        overflow: hidden;
        border: 2px solid #e5e7eb;
    }
    .current-banner img {
        width: 100%;
        display: block;
    }
    .submit-btn {
        width: 100%;
        padding: 15px;
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
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
    }
    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(139, 92, 246, 0.4);
    }
</style>
@endpush
