@extends($activeTemplate.'layouts.master_mobile')
@section('main-content')

<!-- Page Header -->
<div class="page-header-simple" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
    <div class="header-content">
        <div class="header-icon">
            <i class="fas fa-coins"></i>
        </div>
        <h1 class="header-title">@lang('PTC আয়')</h1>
        <p class="header-subtitle">@lang('আপনার দৈনিক আয়ের রিপোর্ট')</p>
    </div>
</div>

<!-- Earnings List -->
<div class="section-block">
    @forelse($viewads as $view)
    <div class="earning-card">
        <div class="earning-date">
            <div class="date-icon">
                <i class="fas fa-calendar-day"></i>
            </div>
            <span>{{ showDateTime($view->date, 'd M, Y') }}</span>
        </div>
        <div class="earning-stats">
            <div class="stat-item">
                <span class="stat-label">@lang('মোট ক্লিক')</span>
                <span class="stat-value">{{ $view->total_clicks }}</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">@lang('মোট আয়')</span>
                <span class="stat-value earn">{{ showAmount($view->total_earned) }} {{ $general->cur_text }}</span>
            </div>
        </div>
    </div>
    @empty
    <div class="empty-state">
        <div class="empty-icon">
            <i class="fas fa-coins"></i>
        </div>
        <h4>@lang('কোনো আয় নেই')</h4>
        <p>{{ __($emptyMessage) }}</p>
    </div>
    @endforelse
    
    <div class="pagination-wrap">
        {{paginateLinks($viewads)}}
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
        margin-top: 20px;
    }
    .earning-card {
        background: white;
        border-radius: 16px;
        padding: 16px;
        margin-bottom: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    .earning-date {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 15px;
        padding-bottom: 12px;
        border-bottom: 1px solid #f0f0f0;
    }
    .date-icon {
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .date-icon i { color: white; font-size: 0.9rem; }
    .earning-date span {
        font-weight: 600;
        color: #333;
    }
    .earning-stats {
        display: flex;
        justify-content: space-between;
    }
    .stat-item { text-align: center; }
    .stat-label {
        display: block;
        font-size: 0.8rem;
        color: #666;
        margin-bottom: 5px;
    }
    .stat-value {
        font-weight: 700;
        font-size: 1.1rem;
        color: #333;
    }
    .stat-value.earn { color: #10b981; }
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
