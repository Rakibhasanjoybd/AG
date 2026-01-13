@extends($activeTemplate.'layouts.master_mobile')
@section('main-content')
@php
    $notifications = $user->userNotifications()->latest()->paginate(20);
@endphp

<!-- Page Header -->
<div class="page-header">
    <h2><i class="fas fa-bell me-2"></i>নোটিফিকেশন</h2>
    @if($user->unreadNotificationsCount() > 0)
    <form action="{{ route('user.notifications.mark.read') }}" method="POST">
        @csrf
        <button type="submit" class="mark-read-btn">
            <i class="fas fa-check-double"></i>
        </button>
    </form>
    @endif
</div>

<!-- Notifications List -->
<div class="notif-list">
    @forelse($notifications as $notification)
    <div class="notif-item {{ !$notification->is_read ? 'unread' : '' }}">
        <div class="notif-icon {{ $notification->type ?? 'default' }}">
            @if($notification->icon)
                <i class="{{ $notification->icon }}"></i>
            @else
                @switch($notification->type)
                    @case('commission')
                        <i class="fas fa-coins"></i>
                        @break
                    @case('deposit')
                        <i class="fas fa-arrow-down"></i>
                        @break
                    @case('withdraw')
                        <i class="fas fa-arrow-up"></i>
                        @break
                    @case('task')
                        <i class="fas fa-tasks"></i>
                        @break
                    @default
                        <i class="fas fa-bell"></i>
                @endswitch
            @endif
        </div>
        <div class="notif-content">
            <h4>{{ $notification->title }}</h4>
            @if($notification->message)
            <p>{{ Str::limit($notification->message, 60) }}</p>
            @endif
            <span class="notif-time">{{ $notification->created_at->diffForHumans() }}</span>
        </div>
        @if(!$notification->is_read)
        <span class="unread-dot"></span>
        @endif
    </div>
    @empty
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-bell-slash"></i></div>
        <h4>কোনো নোটিফিকেশন নেই</h4>
        <p>এখানে আপনার সব নোটিফিকেশন দেখাবে</p>
    </div>
    @endforelse
</div>

@if($notifications->hasPages())
<div class="pagination-wrap">
    {{ $notifications->links() }}
</div>
@endif

<div style="height: 20px;"></div>
@endsection

@push('style')
<style>
.page-header{background:var(--purple);padding:20px;display:flex;justify-content:space-between;align-items:center}
.page-header h2{color:var(--white);font-size:18px;font-weight:700;margin:0;display:flex;align-items:center}
.mark-read-btn{width:40px;height:40px;background:var(--gold);border:none;border-radius:12px;color:var(--purple);font-size:14px;cursor:pointer}

.notif-list{padding:16px}
.notif-item{display:flex;align-items:flex-start;gap:14px;background:var(--white);padding:14px;border-radius:14px;margin-bottom:10px;position:relative;box-shadow:0 2px 8px rgba(0,0,0,0.04)}
.notif-item.unread{background:#f3e8ff;border-left:3px solid var(--purple)}
.notif-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;background:#f3e8ff;color:var(--purple)}
.notif-icon.commission{background:#dcfce7;color:#16a34a}
.notif-icon.deposit{background:#dbeafe;color:#2563eb}
.notif-icon.withdraw{background:#fce7f3;color:var(--crimson)}
.notif-icon.task{background:#fff7ed;color:var(--orange)}
.notif-content{flex:1;min-width:0}
.notif-content h4{font-size:14px;font-weight:600;color:var(--dark-text);margin-bottom:4px}
.notif-content p{font-size:12px;color:var(--gray);margin-bottom:6px;line-height:1.4}
.notif-time{font-size:10px;color:var(--gray)}
.unread-dot{width:8px;height:8px;background:var(--crimson);border-radius:50%;position:absolute;top:16px;right:14px}

.empty-state{text-align:center;padding:50px 20px;background:var(--white);border-radius:16px}
.empty-icon{width:70px;height:70px;background:#f3e8ff;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:28px;color:var(--purple)}
.empty-state h4{font-size:16px;font-weight:700;color:var(--dark-text);margin-bottom:6px}
.empty-state p{font-size:13px;color:var(--gray);margin:0}

.pagination-wrap{padding:0 16px;display:flex;justify-content:center}
</style>
@endpush
