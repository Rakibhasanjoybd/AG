@php
    $user = auth()->user();
@endphp
<style>
/* Floating Notification Button */
.floating-notify-btn {
    position: fixed;
    bottom: 100px;
    right: 20px;
    width: 48px;
    height: 48px;
    background: var(--crimson);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
    font-size: 18px;
    text-decoration: none;
    z-index: 1000;
    box-shadow: 0 4px 15px rgba(205, 17, 59, 0.4);
    transition: all 0.3s ease;
}
.floating-notify-btn:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 20px rgba(205, 17, 59, 0.5);
    color: var(--white);
}
.floating-notify-btn:active {
    transform: scale(0.95);
}
.floating-notify-badge {
    position: absolute;
    top: -6px;
    right: -6px;
    min-width: 20px;
    height: 20px;
    background: var(--gold);
    color: var(--purple);
    font-size: 11px;
    font-weight: 700;
    padding: 0 6px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}
</style>

<!-- Floating Notification Button -->
<a href="{{ route('user.notifications') }}" class="floating-notify-btn">
    <i class="fas fa-bell" aria-hidden="true"></i>
    <span class="visually-hidden">Notifications</span>
    @if($user->unreadNotificationsCount() > 0)
        <span class="floating-notify-badge">{{ $user->unreadNotificationsCount() > 9 ? '9+' : $user->unreadNotificationsCount() }}</span>
    @endif
</a>
