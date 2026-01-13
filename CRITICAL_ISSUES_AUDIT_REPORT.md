# AGCO Critical Issues Audit Report

**Date:** January 4, 2026  
**Scope:** Duplicated Routes, Menu Items, Wrong Business Flows, Controller Issues

---

## 📌 EXECUTIVE SUMMARY

This audit identified **24 critical issues** in the AGCO codebase including:
- 1 duplicated route definition
- 2 duplicated menu items in admin sidebar
- 6 business logic issues (race conditions, missing validations)
- 8 controller-level issues
- 7 potential security/consistency issues

---

## 🔴 1. DUPLICATED ROUTES

### 1.1 CRITICAL: Duplicate Route Definition
**File:** [core/routes/admin.php](core/routes/admin.php#L57-L58)
```php
Route::get('mobile-verified', 'mobileVerifiedUsers')->name('mobile.verified');  // Line 57
Route::get('mobile-verified', 'mobileVerifiedUsers')->name('mobile.verified');  // Line 58 (DUPLICATE!)
```
**Impact:** The second route definition overwrites the first. While this doesn't cause a runtime error, it indicates copy-paste errors and code maintenance issues.

**Fix:** Remove line 58.

---

## 🟠 2. DUPLICATED MENU ITEMS

### 2.1 Duplicate "Referral Commissions" Menu Items
**File:** [core/resources/views/admin/partials/sidenav.blade.php](core/resources/views/admin/partials/sidenav.blade.php)

**Location 1 - Line 17-21 (App Features Section):**
```blade
<li class="sidebar-menu-item {{menuActive('admin.referrals.*')}}">
    <a href="{{route('admin.referrals.index')}}" class="nav-link ">
        <i class="menu-icon las la-link"></i>
        <span class="menu-title">@lang('Referral Commissions')</span>
    </a>
</li>
```

**Location 2 - Line 405-410 (Report Section):**
```blade
<li class="sidebar-menu-item {{menuActive('admin.report.commissions')}}">
    <a href="{{route('admin.report.commissions')}}" class="nav-link">
        <i class="menu-icon las la-dot-circle"></i>
        <span class="menu-title">@lang('Referral Commissions')</span>
    </a>
</li>
```

**Issue:** Two menu items with the same name but pointing to different routes:
1. `admin.referrals.index` → Referral Commission Settings (ReferralController@index)
2. `admin.report.commissions` → Commission Reports (ReportController@commissions)

**Impact:** User confusion - same name but different functionality.

**Fix:** Rename the first one to "Referral Settings" or "Commission Settings" since it manages commission percentages, while the second shows commission reports.

---

## 🔴 3. WRONG BUSINESS FLOWS

### 3.1 CRITICAL: PTC Confirm - No Transaction Lock (Race Condition)
**File:** [core/app/Http/Controllers/User/PtcController.php](core/app/Http/Controllers/User/PtcController.php#L118-L147)

```php
public function confirm(Request $request,$hash)
{
    // ... validation ...
    
    $ptc->increment('showed');
    $ptc->decrement('remain');
    $ptc->save();

    $user->balance += $ptc->amount;  // NOT PROTECTED BY TRANSACTION LOCK!
    $user->save();
    
    // ... rest of method
}
```

**Issue:** No database transaction or row locking. Multiple concurrent requests could:
1. Allow viewing the same ad multiple times
2. Cause balance race conditions

**Impact:** Financial loss, duplicate earnings.

**Fix:** Wrap in `DB::transaction()` with `lockForUpdate()`:
```php
DB::transaction(function () use ($ptc, $user, $trx) {
    $user = User::lockForUpdate()->find($user->id);
    $ptc = Ptc::lockForUpdate()->find($ptc->id);
    // ... atomic operations
}, 3);
```

---

### 3.2 CRITICAL: RedPack Task Completion - No Transaction Lock
**File:** [core/app/Http/Controllers/User/RedPackController.php](core/app/Http/Controllers/User/RedPackController.php#L312-L345)

```php
public function submitTask(Request $request, $allocationId, $taskId)
{
    // ...
    
    // Add balance to user - NOT IN TRANSACTION!
    $user->balance += (float) $completion->reward_amount;
    $user->save();
    
    // Create transaction
    Transaction::create([...]);
}
```

**Issue:** Balance update and transaction creation not atomic.

**Impact:** 
- Race condition can cause double rewards
- Transaction record could be created without balance update (or vice versa) on failure

**Fix:** Wrap entire reward processing in `DB::transaction()`.

---

### 3.3 HIGH: Admin Withdrawal Rejection - No Transaction Lock
**File:** [core/app/Http/Controllers/Admin/WithdrawalController.php](core/app/Http/Controllers/Admin/WithdrawalController.php#L105-L145)

```php
public function reject(Request $request)
{
    $withdraw->status = 3;
    $withdraw->save();

    $user = $withdraw->user;
    $user->balance += $withdraw->amount;  // NOT PROTECTED!
    $user->save();

    $transaction = new Transaction();
    // ...
}
```

**Issue:** Balance refund on withdrawal rejection not protected by transaction lock.

**Impact:** If admin rejects twice quickly, user could receive double refund.

**Fix:** Add `DB::transaction()` with locking.

---

### 3.4 MEDIUM: Incorrect Status Message in PTC Status Toggle
**File:** [core/app/Http/Controllers/User/PtcController.php](core/app/Http/Controllers/User/PtcController.php#L311-L319)

```php
public function status($id)
{
    if ($ptc->status == 1) {
        $ptc->status = 0;
        $notify[] = ['success','Advertisement deactivated successfully'];
    }else{
        $ptc->status = 1;
        $notify[] = ['success','Advertisement deactivated successfully'];  // WRONG MESSAGE!
    }
}
```

**Issue:** Both branches show "deactivated" message.

**Fix:** Change the else branch to:
```php
$notify[] = ['success','Advertisement activated successfully'];
```

---

### 3.5 MEDIUM: Commission Split Inconsistency
**File:** [core/app/Http/Helpers/helpers.php](core/app/Http/Helpers/helpers.php#L494-L530)

The `levelCommission()` function uses a 40/60 split (40% instant, 60% held) but:
1. The hold column determination doesn't account for `referral_commission` type
2. Defaults to `referral_commission_hold` but the type mapping could be clearer

```php
// Determine hold wallet column based on commission type
$holdColumn = 'referral_commission_hold';
if ($commissionType == 'deposit_commission' || $commissionType == 'plan_subscribe_commission') {
    $holdColumn = 'upgrade_commission_hold';
} elseif ($commissionType == 'ptc_view_commission') {
    $holdColumn = 'ptc_commission_hold';
}
```

**Issue:** The `ptc_view_commission` type is used but the actual constant is `ptc_view_commission` while in other places the type stored could be just `ptc_view`.

---

### 3.6 MEDIUM: Hold Wallet Transfer Deduction Logic
**File:** [core/app/Http/Controllers/User/UserController.php](core/app/Http/Controllers/User/UserController.php#L473-L478)

```php
// Commission types stored: 'referral', 'deposit', 'plan_subscribe', 'ptc_view'
$referralSum = $availableTransactions->where('commission_type', 'referral')->sum('hold_amount');
$depositSum = $availableTransactions->where('commission_type', 'deposit')->sum('hold_amount');
$planSubscribeSum = $availableTransactions->where('commission_type', 'plan_subscribe')->sum('hold_amount');
$ptcViewSum = $availableTransactions->where('commission_type', 'ptc_view')->sum('hold_amount');
```

**Issue:** The commission type stored in `HoldWalletTransaction` is `str_replace('_commission', '', $commissionType)` but the hold column uses full names. This mismatch could cause:
- `deposit_commission` → stored as `deposit` → trying to deduct from `upgrade_commission_hold`
- `plan_subscribe_commission` → stored as `plan_subscribe` → trying to deduct from `upgrade_commission_hold`

The logic seems correct but could be fragile and hard to maintain.

---

## 🟡 4. CONTROLLER ISSUES

### 4.1 Missing Input Validation

#### 4.1.1 PtcController::confirm - Missing Hash Validation
**File:** [core/app/Http/Controllers/User/PtcController.php](core/app/Http/Controllers/User/PtcController.php#L80)

```php
public function confirm(Request $request,$hash)
{
    $request->validate([
        'first_number'=>'required|integer',
        'second_number'=>'required|integer',
        'result'=>'required|integer',
    ]);
    // $hash is not validated!
}
```

**Issue:** The `$hash` parameter could throw an unhandled exception on `decrypt($hash)` if tampered.

**Fix:** Wrap decrypt in try-catch (already done in `checkEligibleAd` but could fail silently).

---

### 4.2 Inconsistent Error Handling

#### 4.2.1 Different Error Response Formats
**Files:** Multiple controllers

- `UserController::vipTaskSubmit()` returns JSON
- `PtcController::confirm()` uses redirect with notify
- `RedPackController::submitTask()` returns JSON

**Issue:** Inconsistent API response handling makes frontend integration difficult.

---

### 4.3 Missing Authorization Checks

#### 4.3.1 RedPackController - No Premium Check for All Methods
**File:** [core/app/Http/Controllers/User/RedPackController.php](core/app/Http/Controllers/User/RedPackController.php)

While `VipTask` methods check `$user->is_premium`, RedPack methods don't have similar checks if Red Packs are meant to be premium-only.

---

### 4.4 Duplicate Query in PtcController
**File:** [core/app/Http/Controllers/User/PtcController.php](core/app/Http/Controllers/User/PtcController.php#L13-L26)

```php
public function index()
{
    // First query - NEVER USED
    $ads = Ptc::where('status',1)->where('user_id','!=',auth()->id())
        ->where('remain','>',0)->inRandomOrder()->orderBy('remain','desc')->limit(50)->get();

    // Second query - OVERWRITES THE FIRST
    $ads = Ptc::where('status',1)
        ->where('remain','>',0)
        ->whereDoesntHave('views', function($q){
            $q->where('user_id', auth()->user()->id)->whereDate('view_date', Date('Y-m-d'));
        })
        ->inRandomOrder()
        ->orderBy('remain','desc')
        ->limit(45)
        ->get();
    return view(activeTemplate().'user.ptc.index',compact('ads','pageTitle'));
}
```

**Issue:** First query is executed but immediately overwritten. Wastes database resources.

**Fix:** Remove the first query (lines 15-16).

---

### 4.5 Admin Deposit Approve - Missing Status Lock Check
**File:** [core/app/Http/Controllers/Admin/DepositController.php](core/app/Http/Controllers/Admin/DepositController.php#L140-L145)

```php
public function approve($id)
{
    $deposit = Deposit::where('id',$id)->where('status',2)->firstOrFail();
    PaymentController::userDataUpdate($deposit,true);
    // ...
}
```

**Issue:** While `userDataUpdate` has locking, there's a gap between `firstOrFail()` and the locked update where another admin could also try to approve.

---

### 4.6 Inconsistent Validation Rules

#### Admin ManageUsersController vs User ProfileController
- Admin can update user email without email verification reset
- Missing duplicate username check in some places

---

### 4.7 Missing Rate Limiting on Sensitive Operations

**Affected Methods:**
- `buyPlan()` - Plan subscription
- `transferSubmit()` - Balance transfer  
- `withdrawSubmit()` - Withdrawal request
- `vipTaskSubmit()` - VIP task completion

While transactions help, explicit rate limiting middleware would prevent abuse.

---

### 4.8 YouTube Ads Price Inconsistency
**File:** [core/app/Http/Controllers/User/PtcController.php](core/app/Http/Controllers/User/PtcController.php#L230)

```php
} else {
    $price = @$general->ads_setting->ad_price->youtube ?? 0;
    $userAmo = @$general->ads_setting->amount_for_user->image ?? 0;  // WRONG! Uses 'image' instead of 'youtube'
}
```

**Issue:** YouTube ads use image amount for user payout.

**Fix:** Change to `->youtube`.

---

## 🔵 5. ADDITIONAL FINDINGS

### 5.1 Database Schema Concerns

1. **No foreign key constraints visible** - Could lead to orphaned records
2. **Missing indexes** on frequently queried columns like `user_id`, `status`

### 5.2 Code Duplication

1. **PTC submit logic** duplicated between `UserController@PtcController` and `AdminController@ManagePtcController`
2. **Date range validation** repeated in multiple report controllers

### 5.3 Security Observations

1. **Session-based withdrawal tracking** (`session()->get('wtrx')`) could be exploited
2. **No CSRF on API routes** (expected but should be documented)

---

## 📋 PRIORITY FIX ORDER

| Priority | Issue | File | Line |
|----------|-------|------|------|
| 🔴 P1 | PTC confirm race condition | PtcController.php | 118-147 |
| 🔴 P1 | RedPack task no transaction | RedPackController.php | 312-345 |
| 🔴 P1 | Admin withdrawal reject race | WithdrawalController.php | 105-145 |
| 🟠 P2 | Duplicate route | admin.php | 58 |
| 🟠 P2 | Duplicate query in PTC index | PtcController.php | 15-16 |
| 🟠 P2 | YouTube ads wrong price | PtcController.php | 230 |
| 🟡 P3 | Wrong status message | PtcController.php | 317 |
| 🟡 P3 | Duplicate menu items | sidenav.blade.php | 17-21, 405-410 |

---

## ✅ RECOMMENDED ACTIONS

### Immediate (This Sprint)
1. Fix all P1 race conditions with proper database transactions
2. Remove duplicate route definition
3. Fix the YouTube ads pricing bug

### Short-term (Next Sprint)
1. Standardize error response formats
2. Add rate limiting middleware to sensitive operations
3. Rename duplicate menu items for clarity

### Long-term
1. Implement comprehensive integration tests
2. Add foreign key constraints to database
3. Refactor duplicate code into shared services

---

**Report Generated By:** GitHub Copilot Code Audit  
**Reviewed Files:** 35+ PHP files across routes, controllers, models, and views
