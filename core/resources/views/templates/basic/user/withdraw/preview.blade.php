@extends($activeTemplate.'layouts.master_mobile')
@section('main-content')
@php
    $walletInfo = session()->get('withdraw_wallet');
@endphp

<!-- Page Header -->
<div class="page-header-simple">
    <div class="ph-icon"><i class="fas fa-check-circle"></i></div>
    <h2>উত্তোলন নিশ্চিত করুন</h2>
    <p>{{ $withdraw->method->name }}</p>
</div>

<!-- Summary Card -->
<div class="summary-card">
    @if($walletInfo)
    <div class="summary-wallet">
        <i class="fas fa-wallet"></i>
        <div>
            <span>{{ $walletInfo['method_name'] }}</span>
            <strong>{{ $walletInfo['number'] }}</strong>
        </div>
    </div>
    @endif

    <div class="summary-amounts">
        <div class="amount-row">
            <span>পরিমাণ</span>
            <strong>৳{{ showAmount($withdraw->amount) }}</strong>
        </div>
        <div class="amount-row small">
            <span>চার্জ</span>
            <strong>৳{{ showAmount($withdraw->charge) }}</strong>
        </div>
        <div class="amount-row total">
            <span>মোট পাবেন</span>
            <strong>৳{{ showAmount($withdraw->amount - $withdraw->charge) }}</strong>
        </div>
    </div>
</div>

<!-- Withdraw Form -->
<div class="form-section">
    <form action="{{route('user.withdraw.submit')}}" method="post" enctype="multipart/form-data">
        @csrf

        <!-- Withdrawal PIN -->
        <div class="form-group">
            <label class="form-lbl"><i class="fas fa-lock"></i> উত্তোলন পিন</label>
            <input type="password" name="withdrawal_pin" class="form-input pin-input" placeholder="পিন লিখুন" maxlength="6" minlength="4" required pattern="[0-9]{4,6}" inputmode="numeric">
            <small class="hint-text">রেজিস্ট্রেশনের সময় দেওয়া ৪-৬ ডিজিট পিন</small>
        </div>

        @if($withdraw->method->description)
        <div class="info-box">
            <i class="fas fa-info-circle"></i>
            <div>
                @php echo $withdraw->method->description; @endphp
            </div>
        </div>
        @endif

        <x-viser-form identifier="id" identifierValue="{{ $withdraw->method->form_id }}"></x-viser-form>

        @if(auth()->user()->ts)
        <div class="form-group">
            <label class="form-lbl"><i class="fas fa-key"></i> 2FA কোড</label>
            <input type="text" name="authenticator_code" class="form-input" placeholder="২ফ্যাক্টর কোড লিখুন" required>
        </div>
        @endif

        <button type="submit" class="btn-submit">
            <i class="fas fa-check-circle"></i> নিশ্চিত করুন
        </button>
    </form>
</div>

<div class="security-note">
    <i class="fas fa-shield-alt"></i>
    <p>ভুল তথ্য দিলে উত্তোলন বাতিল হতে পারে। সব তথ্য যাচাই করুন।</p>
</div>

<div style="height: 20px;"></div>
@endsection

@push('style')
<style>
.page-header-simple{background:#16a34a;padding:24px 20px;text-align:center}
.ph-icon{width:50px;height:50px;background:rgba(255,255,255,0.2);border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto 8px;font-size:20px;color:#fff}
.page-header-simple h2{color:#fff;font-size:20px;font-weight:600;margin-bottom:4px}
.page-header-simple p{color:rgba(255,255,255,0.9);font-size:12px;margin:0}

.summary-card{background:#fff;margin:16px;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.1)}
.summary-wallet{display:flex;align-items:center;gap:12px;padding:14px;background:#f9fafb;border-bottom:1px solid #e5e7eb}
.summary-wallet i{font-size:18px;color:#16a34a}
.summary-wallet span{display:block;font-size:11px;color:#6b7280}
.summary-wallet strong{display:block;font-size:14px;color:#1f2937;font-family:'Roboto Mono',monospace;margin-top:2px}

.summary-amounts{padding:14px}
.amount-row{display:flex;justify-content:space-between;padding:8px 0;font-size:13px}
.amount-row span{color:#6b7280}
.amount-row strong{color:#1f2937;font-size:14px}
.amount-row.small{font-size:12px;padding:6px 0}
.amount-row.small strong{font-size:13px;color:#f59e0b}
.amount-row.total{border-top:2px solid #e5e7eb;padding-top:12px;margin-top:8px}
.amount-row.total strong{color:#16a34a;font-size:18px;font-weight:700}

.form-section{background:#fff;margin:0 16px 16px;border-radius:12px;padding:18px;box-shadow:0 1px 3px rgba(0,0,0,0.1)}
.form-group{margin-bottom:16px}
.form-lbl{display:flex;align-items:center;gap:6px;font-size:13px;color:#1f2937;font-weight:600;margin-bottom:8px}
.form-lbl i{font-size:14px;color:#16a34a}

.form-input{width:100%;padding:12px;border:1px solid #e5e7eb;border-radius:8px;font-size:14px;background:#fff}
.form-input:focus{outline:none;border-color:#16a34a}
.pin-input{text-align:center;font-weight:600;letter-spacing:4px}
.hint-text{display:block;font-size:11px;color:#6b7280;margin-top:6px}

.info-box{display:flex;gap:10px;background:#f0f9ff;padding:12px;border-radius:8px;margin-bottom:16px;border-left:3px solid #3b82f6}
.info-box i{font-size:16px;color:#3b82f6;flex-shrink:0;margin-top:2px}
.info-box div{font-size:12px;color:#1e40af;line-height:1.5}

.btn-submit{width:100%;padding:14px;background:#16a34a;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;margin-top:12px;display:flex;align-items:center;justify-content:center;gap:6px}
.btn-submit:active{transform:scale(0.98)}

.security-note{display:flex;align-items:center;gap:10px;background:#fef3c7;margin:0 16px;padding:12px;border-radius:8px}
.security-note i{font-size:18px;color:#f59e0b;flex-shrink:0}
.security-note p{font-size:11px;color:#92400e;margin:0}

/* Form component styles for viser-form */
.form-section .form-control,.form-section .form--control{width:100%;padding:12px;border:1px solid #e5e7eb;border-radius:8px;font-size:14px;background:#fff}
.form-section .form-control:focus,.form-section .form--control:focus{outline:none;border-color:#16a34a}
.form-section textarea.form-control,.form-section textarea.form--control{min-height:100px;resize:vertical}
</style>
@endpush

@push('script')
<script>
// Only allow numbers in PIN input
document.querySelector('.pin-input').addEventListener('input', function() {
    this.value = this.value.replace(/[^0-9]/g, '');
});
</script>
@endpush
