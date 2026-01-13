@extends($activeTemplate.'layouts.master_mobile')
@section('main-content')
@php
    $user = auth()->user();
    $general = gs();
    $todayClicks = $user->clicks->where('view_date', Date('Y-m-d'))->count();
    $dailyLimit = ($user->plan && $user->expire_date && $user->expire_date >= now())
        ? (int) ($user->daily_limit ?? 0)
        : (int) ($general->free_user_ptc_limit ?? ($user->daily_limit ?? 0));
    $remainingClicks = $dailyLimit - $todayClicks;
    $earnAmount = 0;
    if ($user->plan && $user->expire_date && $user->expire_date >= now()) {
        $earnAmount = $user->plan->ptc_view_amount ?? 0;
    } else {
        $earnAmount = $general->free_user_ptc_earning ?? 0;
    }
@endphp

<!-- Compact Page Header -->
<div class="ptc-header">
    <div class="header-left">
        <h2><i class="fas fa-ad"></i> বিজ্ঞাপন দেখুন</h2>
    </div>
    <div class="header-right">
        <div class="stat-badge">
            <span class="stat-count">{{ $todayClicks }}/{{ $dailyLimit }}</span>
            <span class="stat-label">বাকি {{ $remainingClicks }}</span>
        </div>
    </div>
</div>

<!-- Task Grid - 3 Columns -->
<div class="task-grid">
    @forelse($ads as $ad)
    <a href="{{ route('user.ptc.show',encrypt($ad->id.'|'.auth()->user()->id)) }}" target="_blank" class="task-card">
        <div class="task-thumbnail">
            @if($ad->thumbnail)
                <img src="{{ getImage(getFilePath('ptc').'/'.$ad->thumbnail) }}" alt="{{ $ad->title }}" class="thumbnail-img">
            @else
                <div class="thumbnail-icon">
                    <i class="fas fa-play-circle"></i>
                </div>
            @endif
            <div class="task-amount">৳{{ showAmount($earnAmount) }}</div>
            <div class="task-duration">{{ $ad->duration }}s</div>
        </div>
        <div class="task-content">
            <h4 class="task-title">{{ Str::limit(__($ad->title), 20) }}</h4>
            <button class="task-view-btn">
                <i class="fas fa-eye"></i> দেখুন
            </button>
        </div>
    </a>
    @empty
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-tv"></i></div>
        <h4>কোনো বিজ্ঞাপন নেই</h4>
        @if(!empty($ptcDisabled ?? false) && !empty($ptcDisabledReason ?? null))
            <p>{{ __($ptcDisabledReason) }}</p>
        @else
            <p>{{ __($emptyMessage ?? 'Data not found') }}</p>
        @endif
        <a href="{{ route('user.home') }}" class="back-btn">
            <i class="fas fa-home me-2"></i>ড্যাশবোর্ডে ফিরুন
        </a>
    </div>
    @endforelse
</div>

@if($ads->count() > 0)
<div class="ptc-tips">
    <div class="tip-icon"><i class="fas fa-lightbulb"></i></div>
    <div class="tip-content">
        <strong>টিপস:</strong> প্রতিটি বিজ্ঞাপন সম্পূর্ণ দেখার পর আপনার ব্যালেন্সে টাকা জমা হবে।
    </div>
</div>
@endif

<div style="height: 20px;"></div>
@endsection

@push('style')
<style>
/* Compact Header */
.ptc-header{background:#0F743C;padding:16px 20px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 2px 8px rgba(0,0,0,0.1)}
.header-left h2{color:var(--white);font-size:18px;font-weight:700;margin:0;display:flex;align-items:center;gap:8px}
.header-left h2 i{font-size:20px}
.header-right .stat-badge{background:rgba(255,255,255,0.15);padding:8px 14px;border-radius:20px;text-align:center}
.stat-badge .stat-count{display:block;color:var(--white);font-size:15px;font-weight:700;line-height:1.2}
.stat-badge .stat-label{display:block;color:rgba(255,255,255,0.9);font-size:10px;margin-top:2px}

/* Task Grid - 3 Columns */
.task-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;padding:16px}
.task-card{display:block;background:var(--white);border-radius:12px;overflow:hidden;text-decoration:none;box-shadow:0 2px 8px rgba(0,0,0,0.06);transition:all 0.2s}
.task-card:active{transform:scale(0.96)}

/* Square Thumbnail */
.task-thumbnail{position:relative;width:100%;padding-top:100%;background:linear-gradient(135deg,#0F743C 0%,#1a9d56 100%);overflow:hidden}
.thumbnail-img{position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover}
.thumbnail-icon{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:50px;height:50px;background:rgba(255,255,255,0.2);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--white);font-size:24px}
.task-amount{position:absolute;top:8px;left:8px;background:#F99E2B;color:var(--white);padding:4px 8px;border-radius:6px;font-size:11px;font-weight:700}
.task-duration{position:absolute;top:8px;right:8px;background:rgba(0,0,0,0.5);color:var(--white);padding:4px 8px;border-radius:6px;font-size:10px;font-weight:600}

/* Task Content */
.task-content{padding:10px}
.task-title{font-size:12px;font-weight:600;color:#1a1a1a;margin:0 0 8px 0;line-height:1.3;height:32px;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical}
.task-view-btn{width:100%;padding:8px;background:#0F743C;color:var(--white);border:none;border-radius:8px;font-size:11px;font-weight:600;cursor:pointer;transition:all 0.2s;display:flex;align-items:center;justify-content:center;gap:4px}
.task-view-btn:active{background:#0d6334}

/* Empty State */
.empty-state{grid-column:1/-1;text-align:center;padding:50px 20px;background:var(--white);border-radius:16px}
.empty-icon{width:80px;height:80px;background:#e8f5e9;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:32px;color:#0F743C}
.empty-state h4{font-size:18px;font-weight:700;color:var(--dark-text);margin-bottom:8px}
.empty-state p{font-size:13px;color:var(--gray);margin-bottom:20px}
.back-btn{display:inline-flex;align-items:center;padding:12px 24px;background:#0F743C;color:var(--white);border-radius:12px;text-decoration:none;font-size:14px;font-weight:600}

/* Tips Section */
.ptc-tips{display:flex;align-items:flex-start;gap:12px;background:var(--white);margin:0 16px;padding:14px;border-radius:14px;border-left:4px solid #F99E2B}
.tip-icon{width:36px;height:36px;background:#fff3e0;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#F99E2B;font-size:16px;flex-shrink:0}
.tip-content{font-size:12px;color:var(--gray);line-height:1.5}
.tip-content strong{color:var(--dark-text)}

/* Responsive */
@media (max-width:380px){
.task-grid{grid-template-columns:repeat(2,1fr);gap:10px;padding:12px}
.task-title{font-size:11px}
.task-view-btn{font-size:10px;padding:6px}
}
</style>
@endpush
