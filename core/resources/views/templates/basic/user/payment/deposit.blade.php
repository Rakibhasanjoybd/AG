@extends($activeTemplate.'layouts.master_mobile')
@section('main-content')

<!-- Compact Header -->
<div class="dep-header">
    <div class="dep-header-top">
        <a href="{{ route('user.home') }}" class="dep-back">
            <i class="las la-arrow-left"></i>
        </a>
        <span class="dep-title">ব্যালেন্স জমা</span>
        <div class="dep-spacer"></div>
    </div>
    <div class="dep-balance">
        <span class="dep-bal-label">বর্তমান ব্যালেন্স</span>
        <span class="dep-bal-amount">৳{{ showAmount(auth()->user()->balance) }}</span>
    </div>
</div>

<!-- Form Section -->
<div class="dep-form-wrap">
    <form action="{{route('user.deposit.insert')}}" method="post" id="depositForm">
        @csrf
        <input type="hidden" name="method_code" id="method_code">
        <input type="hidden" name="currency" id="currency">

        <!-- Payment Method -->
        <div class="dep-card">
            <div class="dep-card-head">
                <i class="las la-credit-card"></i>
                <span>পেমেন্ট মেথড</span>
            </div>
            <div class="dep-card-body">
                <div class="dep-select-wrap">
                    <select name="gateway" id="gateway" class="dep-select" required>
                        <option value="">মেথড নির্বাচন করুন</option>
                        @foreach($gatewayCurrency as $data)
                        <option value="{{$data->method_code}}" 
                            data-method_code="{{$data->method_code}}"
                            data-currency="{{$data->currency}}"
                            data-rate="{{$data->rate}}"
                            data-min="{{$data->min_amount}}"
                            data-max="{{$data->max_amount}}"
                            data-fixed_charge="{{$data->fixed_charge}}"
                            data-percent_charge="{{$data->percent_charge}}"
                            data-crypto="{{ $data->method->crypto ?? 0 }}"
                            @selected(old('gateway') == $data->method_code)>
                            {{$data->name}}
                        </option>
                        @endforeach
                    </select>
                    <i class="las la-angle-down"></i>
                </div>
                
                <!-- Gateway Info (Shows after selection) -->
                <div class="dep-gateway-info" id="gatewayInfo" style="display:none;">
                    <div class="dgi-row">
                        <span>সর্বনিম্ন</span>
                        <strong>৳<span id="minAmount">0</span></strong>
                    </div>
                    <div class="dgi-row">
                        <span>সর্বোচ্চ</span>
                        <strong>৳<span id="maxAmount">0</span></strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Amount Input -->
        <div class="dep-card">
            <div class="dep-card-head">
                <i class="las la-coins"></i>
                <span>জমার পরিমাণ</span>
            </div>
            <div class="dep-card-body">
                <div class="dep-amount-input">
                    <span class="dep-currency-symbol">৳</span>
                    <input type="number" step="any" name="amount" id="amount" 
                        placeholder="পরিমাণ লিখুন" 
                        value="{{ old('amount', $prefilledAmount ?? '') }}" 
                        autocomplete="off" required>
                </div>
                
                <!-- Quick Amounts -->
                <div class="dep-quick-amounts" id="quickAmounts" style="display:none;">
                    <button type="button" class="dqa-btn" data-amount="100">৳১০০</button>
                    <button type="button" class="dqa-btn" data-amount="500">৳৫০০</button>
                    <button type="button" class="dqa-btn" data-amount="1000">৳১০০০</button>
                    <button type="button" class="dqa-btn" data-amount="2000">৳২০০০</button>
                </div>
            </div>
        </div>

        <!-- Calculation Summary -->
        <div class="dep-card dep-summary" id="summaryCard" style="display:none;">
            <div class="dep-card-head">
                <i class="las la-calculator"></i>
                <span>হিসাব বিবরণ</span>
            </div>
            <div class="dep-card-body">
                <div class="dep-sum-row">
                    <span>জমার পরিমাণ</span>
                    <strong>৳<span id="depositAmount">0</span></strong>
                </div>
                <div class="dep-sum-row charge-row">
                    <span>চার্জ</span>
                    <strong class="text-orange">৳<span id="chargeAmount">0</span></strong>
                </div>
                <div class="dep-sum-row rate-row" id="rateRow" style="display:none;">
                    <span>রেট</span>
                    <strong>1৳ = <span id="rateValue">0</span> <span id="rateCurrency"></span></strong>
                </div>
                <div class="dep-sum-divider"></div>
                <div class="dep-sum-row total-row">
                    <span>মোট প্রদেয়</span>
                    <strong class="text-primary">৳<span id="payableAmount">0</span></strong>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <button type="button" class="dep-submit-btn" id="submitBtn" onclick="showConfirmModal()">
            <i class="las la-wallet"></i>
            <span>জমা দিন</span>
            <i class="las la-arrow-right"></i>
        </button>

        <!-- Security Note -->
        <div class="dep-security">
            <i class="las la-shield-alt"></i>
            <span>সকল লেনদেন নিরাপদ ও এনক্রিপ্টেড</span>
        </div>
    </form>
</div>

<!-- Confirmation Modal -->
<div class="dep-modal-overlay" id="confirmModal">
    <div class="dep-modal">
        <div class="dep-modal-icon">
            <i class="las la-info-circle"></i>
        </div>
        <h3 class="dep-modal-title">জমার নির্দেশনা</h3>
        
        <div class="dep-modal-content">
            <div class="dep-modal-summary">
                <div class="dms-row">
                    <span>পেমেন্ট মেথড</span>
                    <strong id="modalMethod">-</strong>
                </div>
                <div class="dms-row">
                    <span>পরিমাণ</span>
                    <strong id="modalAmount">৳0</strong>
                </div>
                <div class="dms-row">
                    <span>চার্জ</span>
                    <strong id="modalCharge">৳0</strong>
                </div>
                <div class="dms-divider"></div>
                <div class="dms-row dms-total">
                    <span>মোট প্রদেয়</span>
                    <strong id="modalTotal">৳0</strong>
                </div>
            </div>
            
            <div class="dep-modal-notice">
                <i class="las la-clock"></i>
                <p>আপনার তথ্য সঠিক হলে কিছুক্ষনের মধ্যেই ব্যালেন্স যুক্ত করে দেয়া হবে।</p>
            </div>
        </div>
        
        <div class="dep-modal-actions">
            <button type="button" class="dep-modal-btn cancel" onclick="hideConfirmModal()">
                <i class="las la-times"></i>
                বাতিল
            </button>
            <button type="button" class="dep-modal-btn confirm" onclick="submitDeposit()">
                <i class="las la-check"></i>
                জমা দিন
            </button>
        </div>
    </div>
</div>

<div class="dep-spacing"></div>
@endsection

@push('style')
<style>
/* ========================================
   FLAT COMPACT DEPOSIT UI
   Clean, User-Friendly Design
   ======================================== */

:root {
    --dep-primary: #52006A;
    --dep-primary-light: #7a1a9a;
    --dep-success: #10b981;
    --dep-warning: #f59e0b;
    --dep-orange: #ff6b35;
    --dep-bg: #f4f4f4;
    --dep-card: #ffffff;
    --dep-text: #1a1a1a;
    --dep-text-light: #666666;
    --dep-border: #e0e0e0;
}

* { box-sizing: border-box; }

body, input, select, button {
    font-family: 'Hind Siliguri', sans-serif;
}

/* Header */
.dep-header {
    background: var(--dep-primary);
    padding: 12px 16px 16px;
}

.dep-header-top {
    display: flex;
    align-items: center;
    margin-bottom: 12px;
}

.dep-back {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,0.15);
    border-radius: 8px;
    color: #fff;
    font-size: 18px;
    text-decoration: none;
}

.dep-title {
    flex: 1;
    text-align: center;
    font-size: 16px;
    font-weight: 700;
    color: #fff;
}

.dep-spacer { width: 32px; }

.dep-balance {
    background: rgba(255,255,255,0.1);
    border-radius: 10px;
    padding: 12px;
    text-align: center;
}

.dep-bal-label {
    display: block;
    font-size: 12px;
    color: rgba(255,255,255,0.8);
    margin-bottom: 4px;
}

.dep-bal-amount {
    font-size: 24px;
    font-weight: 700;
    color: #fff;
}

/* Form Wrapper */
.dep-form-wrap {
    padding: 12px;
    background: var(--dep-bg);
    min-height: calc(100vh - 140px);
}

/* Cards */
.dep-card {
    background: var(--dep-card);
    border-radius: 10px;
    margin-bottom: 12px;
    border: 1px solid var(--dep-border);
}

.dep-card-head {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 12px;
    border-bottom: 1px solid var(--dep-border);
    background: #fafafa;
    border-radius: 10px 10px 0 0;
}

.dep-card-head i {
    font-size: 16px;
    color: var(--dep-primary);
}

.dep-card-head span {
    font-size: 13px;
    font-weight: 700;
    color: var(--dep-text);
}

.dep-card-body {
    padding: 12px;
}

/* Select */
.dep-select-wrap {
    position: relative;
}

.dep-select {
    width: 100%;
    padding: 12px 36px 12px 12px;
    border: 1.5px solid var(--dep-border);
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    color: var(--dep-text);
    background: #fff;
    appearance: none;
    cursor: pointer;
}

.dep-select:focus {
    outline: none;
    border-color: var(--dep-primary);
}

.dep-select-wrap i {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--dep-text-light);
    pointer-events: none;
}

/* Gateway Info */
.dep-gateway-info {
    display: flex;
    gap: 12px;
    margin-top: 10px;
    padding: 10px;
    background: #f9f9f9;
    border-radius: 6px;
}

.dgi-row {
    flex: 1;
    text-align: center;
}

.dgi-row span {
    display: block;
    font-size: 11px;
    color: var(--dep-text-light);
    margin-bottom: 2px;
}

.dgi-row strong {
    font-size: 13px;
    color: var(--dep-text);
}

/* Amount Input */
.dep-amount-input {
    position: relative;
    display: flex;
    align-items: center;
}

.dep-currency-symbol {
    position: absolute;
    left: 12px;
    font-size: 18px;
    font-weight: 700;
    color: var(--dep-primary);
}

.dep-amount-input input {
    width: 100%;
    padding: 14px 12px 14px 36px;
    border: 1.5px solid var(--dep-border);
    border-radius: 8px;
    font-size: 20px;
    font-weight: 700;
    color: var(--dep-text);
    background: #fff;
}

.dep-amount-input input:focus {
    outline: none;
    border-color: var(--dep-primary);
}

.dep-amount-input input::placeholder {
    font-size: 14px;
    font-weight: 400;
    color: var(--dep-text-light);
}

/* Quick Amounts */
.dep-quick-amounts {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
    margin-top: 10px;
}

.dqa-btn {
    padding: 8px 4px;
    border: 1.5px solid var(--dep-border);
    border-radius: 6px;
    background: #fff;
    font-size: 12px;
    font-weight: 700;
    color: var(--dep-text);
    cursor: pointer;
    transition: all 0.2s;
    font-family: inherit;
}

.dqa-btn:hover, .dqa-btn.active {
    background: var(--dep-primary);
    border-color: var(--dep-primary);
    color: #fff;
}

/* Summary */
.dep-summary .dep-card-body {
    padding: 10px 12px;
}

.dep-sum-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 6px 0;
    font-size: 13px;
}

.dep-sum-row span {
    color: var(--dep-text-light);
}

.dep-sum-row strong {
    color: var(--dep-text);
    font-weight: 700;
}

.text-orange { color: var(--dep-orange) !important; }
.text-primary { color: var(--dep-primary) !important; }

.dep-sum-divider {
    height: 1px;
    background: var(--dep-border);
    margin: 6px 0;
}

.total-row {
    padding-top: 8px;
}

.total-row span {
    font-weight: 700;
    color: var(--dep-text);
}

.total-row strong {
    font-size: 16px;
}

/* Submit Button */
.dep-submit-btn {
    width: 100%;
    padding: 14px;
    background: var(--dep-primary);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-bottom: 12px;
    font-family: inherit;
    transition: all 0.2s;
}

.dep-submit-btn:hover {
    background: var(--dep-primary-light);
}

.dep-submit-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.dep-submit-btn i {
    font-size: 18px;
}

/* Security */
.dep-security {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 10px;
    background: rgba(16, 185, 129, 0.1);
    border-radius: 8px;
    font-size: 12px;
    color: var(--dep-success);
    font-weight: 600;
}

.dep-spacing { height: 30px; }

/* Modal */
.dep-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    padding: 20px;
}

.dep-modal-overlay.show {
    display: flex;
}

.dep-modal {
    background: #fff;
    border-radius: 14px;
    width: 100%;
    max-width: 340px;
    padding: 20px;
    animation: modalSlide 0.3s ease;
}

@keyframes modalSlide {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.dep-modal-icon {
    width: 56px;
    height: 56px;
    background: rgba(82, 0, 106, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 12px;
}

.dep-modal-icon i {
    font-size: 28px;
    color: var(--dep-primary);
}

.dep-modal-title {
    text-align: center;
    font-size: 18px;
    font-weight: 700;
    color: var(--dep-text);
    margin: 0 0 16px 0;
}

.dep-modal-content {
    margin-bottom: 16px;
}

.dep-modal-summary {
    background: #f9f9f9;
    border-radius: 10px;
    padding: 12px;
    margin-bottom: 12px;
}

.dms-row {
    display: flex;
    justify-content: space-between;
    padding: 6px 0;
    font-size: 13px;
}

.dms-row span {
    color: var(--dep-text-light);
}

.dms-row strong {
    color: var(--dep-text);
    font-weight: 700;
}

.dms-divider {
    height: 1px;
    background: var(--dep-border);
    margin: 6px 0;
}

.dms-total strong {
    color: var(--dep-primary);
    font-size: 15px;
}

.dep-modal-notice {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 12px;
    background: rgba(249, 158, 11, 0.1);
    border-radius: 8px;
    border-left: 3px solid var(--dep-warning);
}

.dep-modal-notice i {
    font-size: 20px;
    color: var(--dep-warning);
    flex-shrink: 0;
    margin-top: 2px;
}

.dep-modal-notice p {
    margin: 0;
    font-size: 13px;
    color: var(--dep-text);
    line-height: 1.5;
}

.dep-modal-actions {
    display: flex;
    gap: 10px;
}

.dep-modal-btn {
    flex: 1;
    padding: 12px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    font-family: inherit;
    transition: all 0.2s;
}

.dep-modal-btn.cancel {
    background: #f0f0f0;
    color: var(--dep-text);
}

.dep-modal-btn.cancel:hover {
    background: #e0e0e0;
}

.dep-modal-btn.confirm {
    background: var(--dep-primary);
    color: #fff;
}

.dep-modal-btn.confirm:hover {
    background: var(--dep-primary-light);
}

.dep-modal-btn i {
    font-size: 16px;
}

/* Responsive */
@media (min-width: 768px) {
    .dep-form-wrap {
        max-width: 480px;
        margin: 0 auto;
    }
}
</style>
@endpush

@push('script')
<script>
(function($) {
    "use strict";
    
    var selectedGateway = null;
    var calculatedData = {
        amount: 0,
        charge: 0,
        payable: 0
    };

    // Gateway Selection
    $('#gateway').on('change', function() {
        var $selected = $(this).find('option:selected');
        
        if (!$(this).val()) {
            $('#gatewayInfo').hide();
            $('#quickAmounts').hide();
            $('#summaryCard').hide();
            $('#method_code').val('');
            $('#currency').val('');
            selectedGateway = null;
            return;
        }

        selectedGateway = {
            method_code: $selected.data('method_code'),
            currency: $selected.data('currency'),
            rate: parseFloat($selected.data('rate')) || 1,
            min: parseFloat($selected.data('min')) || 0,
            max: parseFloat($selected.data('max')) || 0,
            fixed_charge: parseFloat($selected.data('fixed_charge')) || 0,
            percent_charge: parseFloat($selected.data('percent_charge')) || 0,
            crypto: parseInt($selected.data('crypto')) || 0,
            name: $selected.text().trim()
        };

        // Set hidden fields immediately
        $('#method_code').val(selectedGateway.method_code);
        $('#currency').val(selectedGateway.currency);

        // Show gateway info
        $('#minAmount').text(selectedGateway.min.toFixed(0));
        $('#maxAmount').text(selectedGateway.max.toFixed(0));
        $('#gatewayInfo').show();
        $('#quickAmounts').show();

        // Calculate if amount exists
        calculateAmount();
    });

    // Amount Input
    $('#amount').on('input', function() {
        calculateAmount();
    });

    // Quick Amount Buttons
    $('.dqa-btn').on('click', function() {
        var amount = $(this).data('amount');
        $('#amount').val(amount);
        $('.dqa-btn').removeClass('active');
        $(this).addClass('active');
        calculateAmount();
    });

    // Calculate Amount
    function calculateAmount() {
        if (!selectedGateway) return;

        var amount = parseFloat($('#amount').val()) || 0;
        
        if (amount <= 0) {
            $('#summaryCard').hide();
            return;
        }

        var charge = selectedGateway.fixed_charge + (amount * selectedGateway.percent_charge / 100);
        var payable = amount + charge;

        calculatedData = {
            amount: amount,
            charge: charge,
            payable: payable
        };

        // Update Summary
        $('#depositAmount').text(amount.toFixed(2));
        $('#chargeAmount').text(charge.toFixed(2));
        $('#payableAmount').text(payable.toFixed(2));

        // Rate conversion
        if (selectedGateway.currency != '{{ $general->cur_text ?? "BDT" }}') {
            $('#rateValue').text(selectedGateway.rate);
            $('#rateCurrency').text(selectedGateway.currency);
            $('#rateRow').show();
        } else {
            $('#rateRow').hide();
        }

        $('#summaryCard').show();
    }

    // Show Confirmation Modal
    window.showConfirmModal = function() {
        // Validate
        if (!selectedGateway) {
            alert('অনুগ্রহ করে পেমেন্ট মেথড নির্বাচন করুন');
            return;
        }

        var amount = parseFloat($('#amount').val()) || 0;
        
        if (amount <= 0) {
            alert('অনুগ্রহ করে পরিমাণ লিখুন');
            return;
        }

        if (amount < selectedGateway.min) {
            alert('সর্বনিম্ন পরিমাণ: ৳' + selectedGateway.min);
            return;
        }

        if (amount > selectedGateway.max) {
            alert('সর্বোচ্চ পরিমাণ: ৳' + selectedGateway.max);
            return;
        }

        // Update modal content
        $('#modalMethod').text(selectedGateway.name);
        $('#modalAmount').text('৳' + calculatedData.amount.toFixed(2));
        $('#modalCharge').text('৳' + calculatedData.charge.toFixed(2));
        $('#modalTotal').text('৳' + calculatedData.payable.toFixed(2));

        $('#confirmModal').addClass('show');
    };

    // Hide Modal
    window.hideConfirmModal = function() {
        $('#confirmModal').removeClass('show');
    };

    // Submit Form
    window.submitDeposit = function() {
        $('#confirmModal').removeClass('show');
        
        // Disable button and show loading
        var $btn = $('#submitBtn');
        $btn.prop('disabled', true);
        $btn.find('span').text('অপেক্ষা করুন...');
        $btn.find('i:last-child').removeClass('la-arrow-right').addClass('la-spinner la-spin');

        // Submit form
        $('#depositForm').submit();
    };

    // Close modal on overlay click
    $('#confirmModal').on('click', function(e) {
        if (e.target === this) {
            hideConfirmModal();
        }
    });

    // Initialize if gateway pre-selected
    if ($('#gateway').val()) {
        $('#gateway').trigger('change');
    }

})(jQuery);
</script>
@endpush

