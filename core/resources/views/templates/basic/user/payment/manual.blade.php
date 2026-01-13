@extends($activeTemplate.'layouts.master_mobile')
@section('main-content')

<!-- Compact Flat Header -->
<div class="manual-header-flat">
    <div class="header-content">
        <div class="header-icon">
            <i class="fas fa-file-invoice-dollar"></i>
        </div>
        <div class="header-text">
            <h1>{{__($pageTitle)}}</h1>
            <p>পেমেন্ট তথ্য প্রদান করুন</p>
        </div>
    </div>
</div>

<!-- Payment Summary Compact -->
<div class="summary-compact">
    <div class="sum-item">
        <span>অনুরোধকৃত</span>
        <strong>{{ showAmount($data['amount']) }} {{__($general->cur_text)}}</strong>
    </div>
    <div class="sum-divider"></div>
    <div class="sum-item total">
        <span>প্রদেয় মোট</span>
        <strong>{{showAmount($data['final_amo'])}} {{$data['method_currency']}}</strong>
    </div>
</div>

<!-- Payment Form -->
<div class="manual-form-flat">
    <!-- Instructions -->
    <div class="inst-flat">
        <div class="inst-header">
            <i class="fas fa-lightbulb"></i>
            <span>পেমেন্ট নির্দেশনা</span>
        </div>
        <div class="inst-content">
            @php echo $data->gateway->description @endphp
        </div>
    </div>

    <!-- Form -->
    <form action="{{ route('user.deposit.manual.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <x-viser-form identifier="id" identifierValue="{{ $gateway->form_id }}"></x-viser-form>
        
        <button type="submit" class="btn-manual">
            <span>পেমেন্ট সম্পন্ন করুন</span>
            <i class="fas fa-arrow-right"></i>
        </button>
    </form>
</div>

<!-- Help Info -->
<div class="help-flat">
    <i class="fas fa-headset"></i>
    <span>সমস্যা হলে সাপোর্টে যোগাযোগ করুন</span>
</div>

<div class="spacing"></div>
@endsection

@push('style')
<style>
/* ========================================
   MANUAL DEPOSIT - COMPACT FLAT DESIGN
   ======================================== */

:root {
    --manual-primary: #52006A;
    --manual-success: #16a34a;
    --manual-warning: #f59e0b;
    --manual-bg: #f5f5f5;
    --manual-white: #ffffff;
    --manual-text: #1a1a1a;
    --manual-text-light: #666666;
    --manual-border: #d0d0d0;
}

/* Bangla Font */
body, input, select, button, textarea {
    font-family: 'Hind Siliguri', 'Noto Sans Bengali', 'SolaimanLipi', -apple-system, BlinkMacSystemFont, sans-serif;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

/* Header */
.manual-header-flat {
    background: var(--manual-primary);
    padding: 16px;
}

.header-content {
    display: flex;
    align-items: center;
    gap: 12px;
}

.header-icon {
    width: 40px;
    height: 40px;
    background: var(--manual-white);
    color: var(--manual-primary);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}

.header-text h1 {
    font-size: 18px;
    font-weight: 700;
    color: var(--manual-white);
    margin: 0 0 2px 0;
    line-height: 1.3;
}

.header-text p {
    font-size: 13px;
    color: rgba(255, 255, 255, 0.9);
    margin: 0;
    line-height: 1;
}

/* Summary Compact */
.summary-compact {
    display: flex;
    align-items: center;
    background: var(--manual-white);
    margin: 12px;
    padding: 14px 16px;
    border: 1px solid var(--manual-border);
    border-radius: 8px;
}

.sum-item {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.sum-item span {
    font-size: 11px;
    color: var(--manual-text-light);
    font-weight: 500;
}

.sum-item strong {
    font-size: 16px;
    font-weight: 700;
    color: var(--manual-text);
}

.sum-item.total strong {
    color: var(--manual-success);
    font-size: 18px;
}

.sum-divider {
    width: 1px;
    height: 40px;
    background: var(--manual-border);
    margin: 0 12px;
}

/* Manual Form */
.manual-form-flat {
    padding: 12px;
}

/* Instructions */
.inst-flat {
    background: var(--manual-white);
    border: 1px solid var(--manual-border);
    border-radius: 8px;
    overflow: hidden;
    margin-bottom: 14px;
}

.inst-header {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 14px;
    background: #fef3c7;
    border-bottom: 1px solid var(--manual-border);
    font-size: 13px;
    font-weight: 600;
    color: var(--manual-text);
}

.inst-header i {
    color: var(--manual-warning);
    font-size: 14px;
}

.inst-content {
    padding: 14px;
    font-size: 13px;
    color: var(--manual-text);
    line-height: 1.6;
}

.inst-content p {
    margin: 0 0 10px;
}

.inst-content p:last-child {
    margin: 0;
}

.inst-content strong {
    color: var(--manual-primary);
    font-weight: 600;
}

.inst-content ul, .inst-content ol {
    margin: 8px 0;
    padding-left: 20px;
}

.inst-content li {
    margin: 4px 0;
}

/* Form Fields */
.manual-form-flat form {
    background: var(--manual-white);
    border: 1px solid var(--manual-border);
    border-radius: 8px;
    padding: 16px;
}

.form-group {
    margin-bottom: 14px;
}

.form-group label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: var(--manual-text);
    margin-bottom: 6px;
}

.form-control, .form--control {
    width: 100%;
    padding: 12px;
    border: 1.5px solid var(--manual-border);
    border-radius: 6px;
    font-size: 14px;
    font-family: inherit;
    background: var(--manual-white);
    color: var(--manual-text);
}

.form-control:focus, .form--control:focus {
    outline: none;
    border-color: var(--manual-primary);
}

textarea.form-control, textarea.form--control {
    min-height: 90px;
    resize: vertical;
}

input[type="file"].form-control, input[type="file"].form--control {
    padding: 10px;
    font-size: 13px;
}

select.form-control, select.form--control {
    cursor: pointer;
}

/* Button */
.btn-manual {
    width: 100%;
    padding: 14px 20px;
    background: var(--manual-primary);
    color: var(--manual-white);
    border: none;
    border-radius: 6px;
    font-size: 15px;
    font-weight: 700;
    font-family: inherit;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    margin-top: 6px;
}

.btn-manual:active {
    opacity: 0.9;
}

.btn-manual i {
    font-size: 14px;
}

/* Help */
.help-flat {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 12px;
    margin: 0 12px;
    background: var(--manual-white);
    border: 1px solid var(--manual-border);
    border-radius: 6px;
    font-size: 12px;
    color: var(--manual-text-light);
}

.help-flat i {
    color: var(--manual-primary);
    font-size: 14px;
}

/* Utility */
.spacing {
    height: 20px;
}

/* Responsive */
@media (min-width: 768px) {
    .manual-header-flat {
        padding: 20px 24px;
    }

    .header-icon {
        width: 48px;
        height: 48px;
        font-size: 22px;
    }

    .header-text h1 {
        font-size: 20px;
    }

    .header-text p {
        font-size: 14px;
    }

    .summary-compact,
    .manual-form-flat,
    .help-flat {
        max-width: 500px;
        margin-left: auto;
        margin-right: auto;
    }
}
</style>
@endpush
