@extends($activeTemplate.'layouts.master_mobile')
@section('main-content')

<!-- Page Header -->
<div class="page-header-simple" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
    <div class="header-content">
        <div class="header-icon">
            <i class="fas fa-ad"></i>
        </div>
        <h1 class="header-title">@lang('আমার বিজ্ঞাপন')</h1>
        <p class="header-subtitle">@lang('আপনার সকল বিজ্ঞাপন দেখুন')</p>
    </div>
</div>

<!-- Create Button -->
<div class="section-block">
    <a href="{{ route('user.ptc.create') }}" class="create-btn">
        <i class="fas fa-plus"></i>
        @lang('নতুন বিজ্ঞাপন তৈরি করুন')
    </a>
</div>

<!-- Ads List -->
<div class="section-block">
    @forelse($ads as $ptc)
    <div class="ad-card">
        <div class="ad-header">
            <div class="ad-info">
                <h4 class="ad-title">{{strLimit($ptc->title, 25)}}</h4>
                <div class="ad-type">
                    @php echo $ptc->typeBadge @endphp
                </div>
            </div>
            <div class="ad-status">
                @php echo $ptc->statusBadge; @endphp
            </div>
        </div>
        <div class="ad-stats">
            <div class="stat-item">
                <span class="stat-label">@lang('সময়')</span>
                <span class="stat-value">{{$ptc->duration}} @lang('সে.')</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">@lang('সর্বোচ্চ')</span>
                <span class="stat-value">{{$ptc->max_show}}</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">@lang('দেখা হয়েছে')</span>
                <span class="stat-value">{{$ptc->showed}}</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">@lang('বাকি')</span>
                <span class="stat-value highlight">{{$ptc->remain}}</span>
            </div>
        </div>
        <div class="ad-footer">
            <div class="ad-amount">
                <span class="amount-label">@lang('মোট')</span>
                <span class="amount-value">{{ showAmount($ptc->amount) }} {{$general->cur_text}}</span>
            </div>
            <div class="ad-actions">
                @if ($ptc->status == 3)
                    <button class="action-btn disabled" disabled><i class="fas fa-pen"></i></button>
                @else
                    <a class="action-btn edit" href="{{route('user.ptc.edit',$ptc->id)}}"><i class="fas fa-pen"></i></a>
                @endif
                @if($ptc->status == 1 || $ptc->status == 0)
                    @if($ptc->status == 1)
                        <a class="action-btn danger" href="{{route('user.ptc.status',$ptc->id)}}"><i class="fas fa-eye-slash"></i></a>
                    @else
                        <a class="action-btn success" href="{{route('user.ptc.status',$ptc->id)}}"><i class="fas fa-eye"></i></a>
                    @endif
                @else
                    <button class="action-btn danger disabled" disabled><i class="fas fa-eye-slash"></i></button>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="empty-state">
        <div class="empty-icon">
            <i class="fas fa-ad"></i>
        </div>
        <h4>@lang('কোনো বিজ্ঞাপন নেই')</h4>
        <p>{{ __($emptyMessage) }}</p>
    </div>
    @endforelse
    
    @if($ads->hasPages())
    <div class="pagination-wrap">
        {{ $ads->links() }}
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
        margin-bottom: 15px;
    }
    .section-block:first-of-type { margin-top: 20px; }
    .create-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 100%;
        padding: 14px;
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        color: white;
        border-radius: 12px;
        font-weight: 600;
        text-decoration: none;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .create-btn:hover {
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(139, 92, 246, 0.4);
    }
    .ad-card {
        background: white;
        border-radius: 16px;
        padding: 16px;
        margin-bottom: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    .ad-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 15px;
    }
    .ad-title {
        font-size: 1rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 5px;
    }
    .ad-type { font-size: 0.8rem; }
    .ad-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
        padding: 12px;
        background: #f8fafc;
        border-radius: 10px;
        margin-bottom: 15px;
    }
    .stat-item { text-align: center; }
    .stat-label {
        display: block;
        font-size: 0.75rem;
        color: #666;
        margin-bottom: 3px;
    }
    .stat-value {
        font-weight: 600;
        color: #333;
        font-size: 0.9rem;
    }
    .stat-value.highlight { color: #8b5cf6; }
    .ad-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .amount-label {
        font-size: 0.8rem;
        color: #666;
    }
    .amount-value {
        font-weight: 700;
        color: #10b981;
        display: block;
    }
    .ad-actions { display: flex; gap: 8px; }
    .action-btn {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }
    .action-btn.edit { background: #e0e7ff; color: #6366f1; }
    .action-btn.success { background: #d1fae5; color: #10b981; }
    .action-btn.danger { background: #fee2e2; color: #ef4444; }
    .action-btn.disabled { background: #e5e7eb; color: #9ca3af; cursor: not-allowed; }
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
    .pagination-wrap { margin-top: 15px; }
</style>
@endpush
