{{-- Red Bag Container (hidden, used for tracking only) --}}
<div id="red-bag-container" style="display: none;"></div>

{{-- Red Bag Modal --}}
<div class="modal fade" id="redBagModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content red-bag-modal-content">
            <div class="modal-body p-0">
                {{-- Opening Animation State --}}
                <div id="red-bag-opening" class="red-bag-opening">
                    <div class="red-bag-large">
                        <svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 48 48">
                            <path fill="#e53935" d="M43.444,38.672L43.444,38.672c0,0-1.556-0.667-2.333-4c-0.778-3.333-1.222-15-6.222-16c-2.111,2-7,3.111-10.889,3.111s-8.778-1.111-10.889-3.111c-5,1-5.444,12.667-6.222,16c-0.778,3.333-2.333,4-2.333,4l0,0C4.222,38.783,4,39.117,4,39.561c0,0.222,0,0.333,0.111,0.333c0.111,0.222,0.222,0.333,0.333,0.444l0,0c0,0,5.111,3.556,19.556,3.556s19.556-3.556,19.556-3.556l0,0c0.111-0.111,0.333-0.333,0.333-0.444c0-0.111,0.111-0.222,0.111-0.333C44,39.117,43.778,38.783,43.444,38.672z"></path>
                            <path fill="#e53935" d="M9.667,10.561c0,0,2.778,3.667,3.444,6.889c0.444,1.778,2.667,4.333,10.889,4.333s10.222-3.111,8.889-2.222c-0.222,0.111,3.556-1.778,5.556-6.667L9.667,10.561z"></path>
                            <path fill="#b71c1c" d="M38.444,11.783c2.222,5.111-1,7.778-7.778,7.778S9.556,13.339,9.556,10.006s4.444-6,14.444-6C30.778,4.006,37.111,8.783,38.444,11.783z"></path>
                            <rect width="9.321" height="8.032" x="24.402" y="9.941" fill="#fbc02d" transform="rotate(28.646 29.063 13.957)"></rect>
                            <path fill="#fbc02d" d="M35.88,18.8c-0.02,0.22-0.12,0.44-0.3,0.6c-2.46,2.32-7.73,3.38-11.58,3.38s-9.12-1.06-11.58-3.38c-0.4-0.38-0.41-1.01-0.03-1.42c0.38-0.4,1.01-0.41,1.41-0.03c1.71,1.61,6.09,2.83,10.2,2.83c2.57,0,5.24-0.48,7.3-1.23h0.01C33.12,19.5,34.66,19.25,35.88,18.8z"></path>
                            <path fill="#b71c1c" d="M30.67,19.56c-6.78,0-21.11-6.22-21.11-9.55c0-0.27,0.03-0.54,0.09-0.8c1.908,2.979,14.56,8.49,21.02,8.49c4.98,0,8.05-0.87,8.43-3.67C39.55,17.67,36.36,19.56,30.67,19.56z"></path>
                        </svg>
                    </div>
                    <p class="opening-text">রেড ব্যাগ খোলা হচ্ছে...</p>
                </div>

                {{-- Result State --}}
                <div id="red-bag-result" class="red-bag-result" style="display: none;">
                    <div class="result-box">
                        <div class="coins-decoration">
                            <div class="coin coin-1"></div>
                            <div class="coin coin-2"></div>
                        </div>
                        <div class="result-content">
                            <p id="result-message" class="result-message"></p>
                            <h2 id="result-amount" class="result-amount"></h2>
                        </div>
                    </div>

                    <div class="result-actions">
                        <button type="button" class="btn btn--base collect-btn" id="collectBtn" onclick="collectRedBag()">
                            সংগ্রহ করুন
                        </button>
                    </div>

                    <div class="referral-cta">
                        <p>তোমার বন্ধুকে এখনই AGCO তে যুক্ত করো, তাহলে ফ্রি মিলবে অসংখ্য Red Bag বোনাস</p>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="shareReferral()">
                            <i class="las la-share-alt"></i> বন্ধুদের আমন্ত্রণ করুন
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Red Bag Modal */
.red-bag-modal-content {
    background: #f8f8f8;
    border-radius: 16px;
    overflow: hidden;
    border: 3px solid #e53935;
}

.red-bag-opening {
    text-align: center;
    padding: 40px 20px;
}

.red-bag-large {
    animation: redBagShake 0.5s ease-in-out infinite;
}

@keyframes redBagShake {
    0%, 100% { transform: rotate(-5deg); }
    50% { transform: rotate(5deg); }
}

.opening-text {
    margin-top: 20px;
    font-size: 18px;
    color: #e53935;
    font-weight: bold;
}

/* Result Box */
.red-bag-result {
    padding: 20px;
}

.result-box {
    position: relative;
    background: #fff;
    border: 2px solid #e53935;
    border-radius: 12px;
    padding: 30px 20px;
    text-align: center;
    margin-bottom: 20px;
}

.coins-decoration {
    position: absolute;
    top: -15px;
    left: -10px;
}

.coin {
    width: 35px;
    height: 35px;
    background: linear-gradient(135deg, #ffc107, #ff9800);
    border-radius: 50%;
    position: absolute;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(255, 152, 0, 0.4);
}

.coin::before {
    content: '¥';
    color: #fff;
    font-weight: bold;
    font-size: 16px;
}

.coin-1 {
    top: 0;
    left: 0;
}

.coin-2 {
    top: 20px;
    left: 30px;
}

.result-message {
    font-size: 16px;
    color: #333;
    margin-bottom: 10px;
}

.result-amount {
    font-size: 32px;
    color: #e53935;
    font-weight: bold;
    margin: 0;
}

.result-amount.no-win {
    font-size: 18px;
    color: #666;
}

.result-actions {
    text-align: center;
    margin-bottom: 20px;
}

.collect-btn {
    background: linear-gradient(135deg, #e53935, #b71c1c);
    border: none;
    padding: 12px 40px;
    font-size: 18px;
    border-radius: 25px;
    color: white;
    font-weight: bold;
}

.collect-btn:hover {
    background: linear-gradient(135deg, #c62828, #8e0000);
    transform: scale(1.05);
}

.collect-btn.btn-secondary {
    background: linear-gradient(135deg, #6c757d, #495057);
}

.collect-btn.btn-secondary:hover {
    background: linear-gradient(135deg, #5a6268, #343a40);
}

.referral-cta {
    background: #fff3e0;
    border-radius: 10px;
    padding: 15px;
    text-align: center;
}

.referral-cta p {
    font-size: 13px;
    color: #e65100;
    margin-bottom: 10px;
}

/* Confetti Animation */
.confetti {
    position: absolute;
    width: 10px;
    height: 10px;
    background: #ffc107;
    animation: confettiFall 1s ease-out forwards;
}

@keyframes confettiFall {
    0% {
        transform: translateY(0) rotate(0deg);
        opacity: 1;
    }
    100% {
        transform: translateY(100px) rotate(720deg);
        opacity: 0;
    }
}

/* Win celebration */
.celebration {
    animation: celebrationPulse 0.5s ease-in-out;
}

@keyframes celebrationPulse {
    0% { transform: scale(0.8); opacity: 0; }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); opacity: 1; }
}
</style>

<script>
// Red Bag Global Variables
var redBagAvailable = false;
var redBagRemainingClaims = 0;
var deviceId = null;

// Generate device fingerprint
function generateDeviceId() {
    var canvas = document.createElement('canvas');
    var ctx = canvas.getContext('2d');
    ctx.textBaseline = 'top';
    ctx.font = '14px Arial';
    ctx.fillText('AGCO Red Bag', 2, 2);

    var fingerprint = [
        navigator.userAgent,
        navigator.language,
        screen.width + 'x' + screen.height,
        new Date().getTimezoneOffset(),
        canvas.toDataURL()
    ].join('|');

    return btoa(fingerprint).substring(0, 32);
}

// Check red bag availability
function checkRedBagAvailability() {
    deviceId = generateDeviceId();

    var xhr = new XMLHttpRequest();
    xhr.open('GET', '{{ route("user.red-bag.check") }}', true);
    xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
    xhr.setRequestHeader('Accept', 'application/json');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    redBagAvailable = response.available;
                    redBagRemainingClaims = response.remaining || 0;

                    var container = document.getElementById('red-bag-container');
                    var countBadge = document.getElementById('red-bag-count');
                    var favBadge = document.getElementById('favRedBagBadge');

                    if (redBagAvailable && redBagRemainingClaims > 0) {
                        if (container) container.style.display = 'block';
                        if (countBadge) countBadge.textContent = redBagRemainingClaims;
                        // Update fav menu visibility
                        if (typeof updateFavRedBagVisibility === 'function') {
                            updateFavRedBagVisibility(true, redBagRemainingClaims);
                        }
                        // Update service grid and modal badges
                        if (typeof updateRedBagServiceBadge === 'function') {
                            updateRedBagServiceBadge(true, redBagRemainingClaims);
                        }
                        animateRedBagNotification();
                    } else {
                        if (container) container.style.display = 'none';
                        // Hide fav menu
                        if (typeof updateFavRedBagVisibility === 'function') {
                            updateFavRedBagVisibility(false, 0);
                        }
                        // Hide service grid and modal badges
                        if (typeof updateRedBagServiceBadge === 'function') {
                            updateRedBagServiceBadge(false, 0);
                        }
                    }
                } catch(e) {
                    console.log('Red bag check error:', e);
                }
            }
        }
    };
    xhr.send();
}

function animateRedBagNotification() {
    var fab = document.getElementById('redBagFab');
    if (fab) {
        fab.classList.add('notify');
        setTimeout(function() { fab.classList.remove('notify'); }, 2500);
    }
}

function openRedBagModal() {
    if (!redBagAvailable || redBagRemainingClaims <= 0) {
        return;
    }

    document.getElementById('red-bag-opening').style.display = 'block';
    document.getElementById('red-bag-result').style.display = 'none';

    // Use Bootstrap modal if available, otherwise simple show
    var modalEl = document.getElementById('redBagModal');
    if (modalEl && window.bootstrap && bootstrap.Modal) {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    } else if (typeof jQuery !== 'undefined' && jQuery.fn && jQuery.fn.modal) {
        jQuery(modalEl).modal('show');
    } else if (modalEl) {
        modalEl.classList.add('show');
        modalEl.style.display = 'block';
        document.body.classList.add('modal-open');
    }

    // Claim red bag after animation
    setTimeout(claimRedBag, 1500);
}

function claimRedBag() {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '{{ route("user.red-bag.claim") }}', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
    xhr.setRequestHeader('Accept', 'application/json');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            var response;
            try {
                response = JSON.parse(xhr.responseText);
            } catch(e) {
                response = { success: false, is_winner: false, message: 'কিছু ভুল হয়েছে', amount: 0 };
            }
            showRedBagResult(response);
        }
    };
    xhr.send('device_id=' + encodeURIComponent(deviceId) + '&_token={{ csrf_token() }}');
}

function showRedBagResult(response) {
    document.getElementById('red-bag-opening').style.display = 'none';
    var resultDiv = document.getElementById('red-bag-result');
    resultDiv.style.display = 'block';
    resultDiv.classList.add('celebration');

    document.getElementById('result-message').textContent = response.message || '';

    var amountEl = document.getElementById('result-amount');
    var collectBtn = document.getElementById('collectBtn');

    if (response.is_winner && response.amount > 0) {
        amountEl.textContent = '৳ ' + response.formatted_amount;
        amountEl.classList.remove('no-win');
        // Show "Collect" button for winners
        collectBtn.textContent = 'সংগ্রহ করুন';
        collectBtn.classList.remove('btn-secondary');
        collectBtn.classList.add('btn--base');
        createConfetti();
    } else {
        amountEl.textContent = '🍀';
        amountEl.classList.add('no-win');
        // Show "OK" button for non-winners (nothing to collect)
        collectBtn.textContent = 'ঠিক আছে';
        collectBtn.classList.remove('btn--base');
        collectBtn.classList.add('btn-secondary');
    }

    // Update remaining claims
    redBagRemainingClaims = response.remaining_claims || 0;
    var countBadge = document.getElementById('red-bag-count');
    if (countBadge) countBadge.textContent = redBagRemainingClaims;

    if (redBagRemainingClaims <= 0) {
        redBagAvailable = false;
    }
    
    // Update service grid and modal badges
    if (typeof updateRedBagServiceBadge === 'function') {
        updateRedBagServiceBadge(redBagAvailable, redBagRemainingClaims);
    }
    
    // Update fav menu
    if (typeof updateFavRedBagVisibility === 'function') {
        updateFavRedBagVisibility(redBagAvailable, redBagRemainingClaims);
    }
}

function collectRedBag() {
    var modalEl = document.getElementById('redBagModal');
    if (modalEl && window.bootstrap && bootstrap.Modal) {
        var instance = bootstrap.Modal.getInstance(modalEl) || bootstrap.Modal.getOrCreateInstance(modalEl);
        instance.hide();
    } else if (typeof jQuery !== 'undefined' && jQuery.fn && jQuery.fn.modal) {
        jQuery(modalEl).modal('hide');
    } else if (modalEl) {
        modalEl.classList.remove('show');
        modalEl.style.display = 'none';
        document.body.classList.remove('modal-open');
    }

    if (redBagRemainingClaims <= 0) {
        var container = document.getElementById('red-bag-container');
        if (container) container.style.display = 'none';
    }

    // Refresh balance display if exists
    if (typeof refreshBalance === 'function') {
        refreshBalance();
    }
}

function shareReferral() {
    var referralLink = '{{ auth()->check() ? route("home") . "?ref=" . auth()->user()->username : route("home") }}';

    if (navigator.share) {
        navigator.share({
            title: 'AGCO Finance',
            text: 'AGCO তে যোগ দাও এবং Red Bag বোনাস পাও!',
            url: referralLink
        });
    } else {
        navigator.clipboard.writeText(referralLink).then(function() {
            alert('রেফারেল লিংক কপি হয়েছে!');
        });
    }
}

function createConfetti() {
    var colors = ['#e53935', '#ffc107', '#4caf50', '#2196f3', '#9c27b0'];
    var resultBox = document.querySelector('.result-box');
    if (!resultBox) return;

    for (var i = 0; i < 20; i++) {
        (function(index) {
            setTimeout(function() {
                var confetti = document.createElement('div');
                confetti.className = 'confetti';
                confetti.style.left = Math.random() * 100 + '%';
                confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.animationDuration = (Math.random() * 0.5 + 0.5) + 's';
                resultBox.appendChild(confetti);

                setTimeout(function() { confetti.remove(); }, 1000);
            }, index * 50);
        })(i);
    }
}

// Manual trigger for fav menu
function showRedBagFromMenu() {
    if (redBagAvailable && redBagRemainingClaims > 0) {
        openRedBagModal();
    } else {
        // Re-check availability
        checkRedBagAvailability();
        setTimeout(function() {
            if (redBagAvailable && redBagRemainingClaims > 0) {
                openRedBagModal();
            } else {
                alert('রেড ব্যাগ এখন উপলব্ধ নয়। পরে আবার চেষ্টা করুন।');
            }
        }, 800);
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    @if(auth()->check())
    setTimeout(function() {
        checkRedBagAvailability();
    }, 1500);
    // Check every 5 minutes
    setInterval(checkRedBagAvailability, 300000);
    @endif
});
</script>
