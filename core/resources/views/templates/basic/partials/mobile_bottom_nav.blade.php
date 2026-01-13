@php
    $currentRoute = Route::currentRouteName();
    $isTaskPage = in_array($currentRoute, ['user.ptc.index', 'user.ptc.show', 'user.ptc.ads', 'user.ptc.create']);
    $isPlanPage = str_contains($currentRoute, 'plan') || str_contains(request()->url(), 'plan');
@endphp
<style>
@@import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap');

/* Prevent black extension/bar issue */
body{overflow-x:hidden;position:relative}
body::after{content:none!important}
html,body{background:#F3F3F1!important;min-height:100vh}

/* Add padding to body to prevent content being hidden behind nav */
body{padding-bottom:80px!important}

/* Bottom Navigation - Enhanced UX */
.bottom-nav{position:fixed;bottom:0;left:0;right:0;margin:0 auto;width:100%;max-width:480px;background:#fff;display:flex;justify-content:space-between;align-items:flex-end;padding:0 8px;box-shadow:0 -4px 20px rgba(0,0,0,0.1);z-index:1000;height:70px;border-radius:20px 20px 0 0;font-family:'Hind Siliguri',sans-serif}

.nav-item{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;text-decoration:none;color:#6b7280;font-size:10px;font-weight:600;padding:10px 10px;transition:all 0.25s ease;position:relative;min-width:52px}
.nav-item i{font-size:22px;transition:all 0.25s ease}
.nav-item span{font-size:10px;font-weight:700;letter-spacing:-0.2px;font-family:'Hind Siliguri',sans-serif}

/* Hover effect */
.nav-item:active{transform:scale(0.92)}

/* Active States */
.nav-item.active{color:#0F743C}
.nav-item.active i{transform:scale(1.1)}

.nav-item.home-nav.active{color:#0F743C}
.nav-item.plan-nav.active{color:#F99E2B}
.nav-item.promo-nav.active{color:#DA3E2F}
.nav-item.my-nav.active{color:#C7662B}

/* Active top bar indicator */
.nav-item.active::before{content:'';position:absolute;top:0;left:50%;transform:translateX(-50%);width:24px;height:3px;background:currentColor;border-radius:0 0 3px 3px}

/* Center Work Button with Label */
.task-wrap{display:flex;flex-direction:column;align-items:center;margin-top:-28px;text-decoration:none}
.task-btn{width:56px;height:56px;background:linear-gradient(135deg,#F99E2B 0%,#FF8C00 100%);border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 6px 20px rgba(249,158,43,0.5);transition:all 0.25s ease;border:3px solid #fff}
.task-btn i{color:#fff;font-size:24px}
.task-wrap:active .task-btn{transform:scale(0.92);box-shadow:0 4px 12px rgba(249,158,43,0.4)}
.task-wrap.active .task-btn{background:linear-gradient(135deg,#0F743C 0%,#0d6334 100%);box-shadow:0 6px 20px rgba(15,116,60,0.5)}
.task-label{font-size:10px;font-weight:700;color:#F99E2B;margin-top:6px;letter-spacing:-0.2px;font-family:'Hind Siliguri',sans-serif}
.task-wrap.active .task-label{color:#0F743C}

/* Pulse animation for task button */
@@keyframes taskGlow{0%,100%{box-shadow:0 6px 20px rgba(249,158,43,0.5)}50%{box-shadow:0 6px 28px rgba(249,158,43,0.7)}}
.task-btn{animation:taskGlow 2.5s ease-in-out infinite}
.task-wrap.active .task-btn{animation:none}

/* Safe area for notch phones */
@@supports(padding-bottom:env(safe-area-inset-bottom)){.bottom-nav{padding-bottom:env(safe-area-inset-bottom)}}

/* Red Bag Floating Action Button - Clean Design */
.red-bag-fab{position:fixed;bottom:90px;right:16px;z-index:999;display:none}
.red-bag-fab-btn{width:58px;height:58px;background:linear-gradient(145deg,#e53935 0%,#c62828 100%);border-radius:16px;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 20px rgba(229,57,53,0.4),0 2px 8px rgba(0,0,0,0.1);cursor:pointer;position:relative;transition:all 0.3s cubic-bezier(0.4,0,0.2,1);border:none;outline:none}
.red-bag-fab-btn:active{transform:scale(0.94)}
.red-bag-fab-btn svg{width:36px;height:36px;filter:drop-shadow(0 2px 3px rgba(0,0,0,0.2))}
.red-bag-fab-badge{position:absolute;top:-6px;right:-6px;background:linear-gradient(135deg,#ffc107 0%,#ff9800 100%);color:#fff;font-size:12px;font-weight:800;min-width:22px;height:22px;border-radius:11px;display:flex;align-items:center;justify-content:center;padding:0 6px;box-shadow:0 2px 8px rgba(255,152,0,0.5);font-family:'Hind Siliguri',sans-serif}

/* Red Bag Animations */
@@keyframes redBagFloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-6px)}}
@@keyframes redBagGlow{0%,100%{box-shadow:0 4px 20px rgba(229,57,53,0.4),0 2px 8px rgba(0,0,0,0.1)}50%{box-shadow:0 6px 30px rgba(229,57,53,0.6),0 4px 12px rgba(0,0,0,0.15)}}
@@keyframes badgePop{0%{transform:scale(0.5)}50%{transform:scale(1.2)}100%{transform:scale(1)}}
.red-bag-fab.active .red-bag-fab-btn{animation:redBagFloat 2s ease-in-out infinite,redBagGlow 2s ease-in-out infinite}
.red-bag-fab.active .red-bag-fab-badge{animation:badgePop 0.4s ease-out}

/* Notification Ring Effect */
.red-bag-fab-btn::before{content:'';position:absolute;width:100%;height:100%;border-radius:16px;background:rgba(229,57,53,0.3);opacity:0;transition:all 0.3s}
.red-bag-fab.notify .red-bag-fab-btn::before{animation:ringPulse 1s ease-out 2}
@@keyframes ringPulse{0%{transform:scale(1);opacity:0.8}100%{transform:scale(1.5);opacity:0}}
</style>

{{-- Red Bag FAB (Floating Action Button) --}}
<div class="red-bag-fab" id="redBagFab">
    <button class="red-bag-fab-btn" id="redBagFabBtn" onclick="showRedBagFromMenu()" type="button" aria-label="Red Bag">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48">
            <path fill="#fff" d="M43.444,38.672c0,0-1.556-0.667-2.333-4c-0.778-3.333-1.222-15-6.222-16c-2.111,2-7,3.111-10.889,3.111s-8.778-1.111-10.889-3.111c-5,1-5.444,12.667-6.222,16c-0.778,3.333-2.333,4-2.333,4C4.222,38.783,4,39.117,4,39.561c0,0.222,0,0.333,0.111,0.333c0.111,0.222,0.222,0.333,0.333,0.444c0,0,5.111,3.556,19.556,3.556s19.556-3.556,19.556-3.556c0.111-0.111,0.333-0.333,0.333-0.444c0-0.111,0.111-0.222,0.111-0.333C44,39.117,43.778,38.783,43.444,38.672z"/>
            <path fill="#ffcdd2" d="M38.444,11.783c2.222,5.111-1,7.778-7.778,7.778S9.556,13.339,9.556,10.006s4.444-6,14.444-6C30.778,4.006,37.111,8.783,38.444,11.783z"/>
            <rect fill="#ffc107" width="9.321" height="8.032" x="24.402" y="9.941" transform="rotate(28.646 29.063 13.957)"/>
            <path fill="#ffc107" d="M35.88,18.8c-0.02,0.22-0.12,0.44-0.3,0.6c-2.46,2.32-7.73,3.38-11.58,3.38s-9.12-1.06-11.58-3.38c-0.4-0.38-0.41-1.01-0.03-1.42c0.38-0.4,1.01-0.41,1.41-0.03c1.71,1.61,6.09,2.83,10.2,2.83c2.57,0,5.24-0.48,7.3-1.23C33.12,19.5,34.66,19.25,35.88,18.8z"/>
        </svg>
        <span class="red-bag-fab-badge" id="redBagFabBadge">0</span>
    </button>
</div>

<nav class="bottom-nav">
    <a href="{{ route('user.home') }}" class="nav-item home-nav {{ $currentRoute == 'user.home' ? 'active' : '' }}">
        <i class="fas fa-home"></i>
        <span>হোম</span>
    </a>
    <a href="{{ route('plans') }}" class="nav-item plan-nav {{ $isPlanPage ? 'active' : '' }}">
        <i class="fas fa-box"></i>
        <span>প্ল্যান</span>
    </a>
    <a href="{{ route('user.ptc.index') }}" class="task-wrap {{ $isTaskPage ? 'active' : '' }}">
        <div class="task-btn">
            <i class="fas fa-bolt"></i>
        </div>
        <span class="task-label">কাজ</span>
    </a>
    <a href="{{ route('user.referred') }}" class="nav-item promo-nav {{ in_array($currentRoute, ['user.referred', 'user.commissions']) ? 'active' : '' }}">
        <i class="fas fa-bullhorn"></i>
        <span>প্রমোশন</span>
    </a>
    <a href="{{ route('user.profile.setting') }}" class="nav-item my-nav {{ $currentRoute == 'user.profile.setting' ? 'active' : '' }}">
        <i class="fas fa-user"></i>
        <span>প্রোফাইল</span>
    </a>
</nav>
<script>
// Show/hide Red Bag FAB based on availability
function updateFavRedBagVisibility(available, count) {
    var fab = document.getElementById('redBagFab');
    var badge = document.getElementById('redBagFabBadge');

    if (fab) {
        if (available && count > 0) {
            fab.style.display = 'block';
            fab.classList.add('active');
            if (badge) badge.textContent = count;
            // Trigger notification animation
            fab.classList.add('notify');
            setTimeout(function() {
                fab.classList.remove('notify');
            }, 2500);
        } else {
            fab.style.display = 'none';
            fab.classList.remove('active');
        }
    }
}
</script>
