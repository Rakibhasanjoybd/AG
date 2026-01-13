# Business Logic Layer Analysis Report

**Application:** AGCO Laravel Platform  
**Analysis Date:** January 4, 2026  
**Scope:** Services, Traits, Lib, and Business Logic Layer

---

## Table of Contents
1. [Executive Summary](#executive-summary)
2. [Directory Structure Overview](#directory-structure-overview)
3. [Services Layer](#services-layer)
4. [Traits Layer](#traits-layer)
5. [Library (Lib) Classes](#library-classes)
6. [Business Logic Analysis](#business-logic-analysis)
7. [Financial Transaction Handling](#financial-transaction-handling)
8. [Commission Calculation System](#commission-calculation-system)
9. [Plan/Investment Logic](#planinvestment-logic)
10. [Wallet/Balance System](#walletbalance-system)
11. [Referral/MLM System](#referralmlm-system)
12. [Security Concerns & Vulnerabilities](#security-concerns)
13. [Race Conditions Analysis](#race-conditions-analysis)
14. [Duplicate/Conflicting Business Rules](#duplicateconflicting-rules)
15. [Recommendations](#recommendations)

---

## Executive Summary

The AGCO platform is a PTC (Pay-To-Click) earning system with multi-level referral commissions. Business logic is distributed across controllers, helper functions, and models rather than being centralized in service classes. The architecture shows both mature fraud protection (RedPackSecurityService) and legacy code with potential vulnerabilities.

### Key Findings
- **1 Service Class:** `RedPackSecurityService` - Fraud detection for Red Pack claims
- **2 Traits:** `FileInfo`, `SupportTicketManager`
- **6 Library Classes:** Captcha, FileManager, FormProcessor, CurlRequest, GoogleAuthenticator, ClientInfo
- **Critical Business Logic** is primarily in helper functions and controllers
- **Multiple Race Condition Risks** identified in balance operations
- **Duplicate Commission Logic** with commented-out alternate implementation

---

## Directory Structure Overview

```
core/app/
├── Services/
│   └── RedPackSecurityService.php     # Fraud detection for Red Packs
├── Traits/
│   ├── FileInfo.php                   # File path configurations
│   └── SupportTicketManager.php       # Support ticket handling
├── Lib/
│   ├── Captcha.php                    # Google reCAPTCHA & custom captcha
│   ├── ClientInfo.php                 # IP/Browser detection
│   ├── CurlRequest.php                # HTTP client wrapper
│   ├── FileManager.php                # File upload/management
│   ├── FormProcessor.php              # Dynamic form processing
│   └── GoogleAuthenticator.php        # 2FA implementation
└── Http/
    ├── Controllers/                   # Contains most business logic
    └── Helpers/helpers.php            # Global helper functions (in vendor/cd/)
```

---

## Services Layer

### RedPackSecurityService (`Services/RedPackSecurityService.php`)

**Purpose:** Comprehensive fraud detection for Red Pack (free task) claims.

**Features:**
| Feature | Description |
|---------|-------------|
| VPN/Proxy Detection | Uses IPHub API to detect VPN/Proxy/Tor |
| Duplicate IP Check | Prevents multiple users from same IP within configurable hours |
| Duplicate Device Check | Uses device fingerprinting |
| IP Claim Limits | Configurable max claims per IP per 24 hours |
| Device Claim Limits | Configurable max claims per device per 24 hours |
| Country Restrictions | Whitelist/blacklist country support |
| Account Age Check | Flags accounts less than 7 days old |
| Fraud Score System | Cumulative scoring with configurable threshold |
| Rapid Claim Detection | Detects multiple claims within 5 minutes |
| Unusual Hour Detection | Flags claims at 2-5 AM Bangladesh time |
| Previous Fraud History | Tracks blocked claim history |

**Fraud Score Breakdown:**
```php
Duplicate IP:           25 points
Duplicate Device:       25 points
IP Limit Exceeded:      20 points
Device Limit Exceeded:  20 points
Country Restricted:     30 points (blocks immediately)
New Account (<7 days):  15 points
Rapid Claims:           15 points
Unusual Hours:          5 points
VPN/Proxy Detected:     40 points
Previous Fraud:         10 points per incident (max 30)
```

**Quality Assessment:** ⭐⭐⭐⭐ (4/5)
- Well-structured with clear separation of concerns
- Good logging and traceability
- Uses dependency injection

---

## Traits Layer

### FileInfo Trait (`Traits/FileInfo.php`)

**Purpose:** Centralized file path and size configurations for uploads.

**Configured Paths:**
```php
withdrawVerify   -> 'assets/images/verify/withdraw'
depositVerify    -> 'assets/images/verify/deposit'
verify           -> 'assets/verify'
default          -> 'assets/images/default.png'
withdrawMethod   -> 'assets/images/withdraw/method' (800x800)
ticket           -> 'assets/support'
logoIcon         -> 'assets/images/logoIcon'
extensions       -> 'assets/images/extensions' (36x36)
seo              -> 'assets/images/seo' (1180x600)
ptc              -> 'assets/images/ptc'
userProfile      -> 'assets/images/user/profile' (350x300)
adminProfile     -> 'assets/admin/images/profile' (400x400)
vipTask          -> 'assets/images/vip_tasks' (400x300)
spotlight        -> 'assets/images/spotlights' (800x400)
tutorial         -> 'assets/images/tutorials' (400x225)
audioPlayer      -> 'assets/images/audio' (100x100)
walletHeader     -> 'assets/images/wallet-header' (600x600)
redPack          -> 'assets/images/red_packs' (400x400)
redPackTask      -> 'assets/images/red_pack_tasks' (400x300)
```

### SupportTicketManager Trait (`Traits/SupportTicketManager.php`)

**Purpose:** Shared support ticket functionality for user/admin interfaces.

**Key Methods:**
- `supportTicket()` - List user's tickets
- `openSupportTicket()` - Show create form
- `storeSupportTicket()` - Create new ticket with attachments
- `viewTicket()` - View ticket with messages
- `replyTicket()` - Add reply to ticket

**Security:** Validates file extensions (jpg, png, jpeg, pdf, doc, docx)

---

## Library Classes

### Captcha (`Lib/Captcha.php`)
- Google reCAPTCHA v2 integration
- Custom captcha with HMAC verification
- Generates 6-digit rotating captcha images

### FileManager (`Lib/FileManager.php`)
- Image upload with resize
- Thumbnail generation
- Old file cleanup
- Uses Intervention/Image library

### FormProcessor (`Lib/FormProcessor.php`)
- Dynamic form generation from database
- Automatic validation rule generation
- File extension validation
- Supports: text, select, radio, textarea, checkbox, file

### GoogleAuthenticator (`Lib/GoogleAuthenticator.php`)
- Standard TOTP 2FA implementation
- QR code generation for authenticator apps

---

## Business Logic Analysis

### Location of Business Logic

Business logic is **NOT centralized in services** but distributed across:

| Location | Logic Type |
|----------|------------|
| `vendor/cd/app/Http/Helpers/helpers.php` | Commission calculations, utility functions |
| `Http/Controllers/User/*` | User actions, balance operations |
| `Http/Controllers/Admin/*` | Admin operations, approvals |
| `Http/Controllers/Gateway/*` | Payment processing |
| `Models/*` | Some business rules (RedPack, User) |

---

## Financial Transaction Handling

### Transaction Model Structure
```php
// core/app/Models/Transaction.php
class Transaction extends Model {
    protected $guarded = ['id'];
    
    // Fields: user_id, amount, post_balance, charge, 
    //         trx_type (+/-), details, remark, trx
}
```

### Balance Update Locations

#### 1. Deposit Completion (`Gateway/PaymentController.php:134-148`)
```php
public static function userDataUpdate($deposit, $isManual = null) {
    if ($deposit->status == 0 || $deposit->status == 2) {
        $deposit->status = 1;
        $deposit->save();

        $user = User::find($deposit->user_id);
        $user->balance += $deposit->amount;  // ⚠️ No lock
        $user->save();

        // Transaction record created
        levelCommission($user, $deposit->amount, 'deposit_commission', $deposit->trx);
    }
}
```

#### 2. Withdrawal Submission (`User/WithdrawController.php:95-100`)
```php
$user->balance -= $withdraw->amount;  // ⚠️ No lock
$user->save();
```

#### 3. PTC Viewing (`User/PtcController.php:115-135`)
```php
$user->balance += $ptc->amount;  // ⚠️ No lock
$user->save();
// Then calls levelCommission()
```

#### 4. Plan Purchase (`User/UserController.php:186-208`)
```php
$user->balance -= $plan->price;  // ⚠️ No lock
$user->daily_limit = $plan->daily_limit;
$user->expire_date = now()->addDays($plan->validity);
$user->plan_id = $plan->id;
$user->save();
```

#### 5. Balance Transfer (`User/UserController.php:289-340`)
```php
$user->balance -= $afterCharge;  // ⚠️ No lock
$user->save();

$receiver->balance += $request->amount;  // ⚠️ No lock
$receiver->save();
```

#### 6. Red Pack Task Completion (`User/RedPackController.php:318-332`)
```php
$user->balance += (float) $completion->reward_amount;  // ⚠️ No lock
$user->save();
```

#### 7. VIP Task Completion (`User/UserController.php:488-500`)
```php
$user->balance += $task->reward_amount;  // ⚠️ No lock
$user->save();
```

---

## Commission Calculation System

### Commission Formula (`helpers.php:630-720`)

```php
function levelCommission($referee, $amount, $commissionType, $trx) {
    $general = gs();
    if (!$general->$commissionType) {
        return false;  // Check if commission type enabled
    }

    $i = 1;
    $level = Referral::where('commission_type', $commissionType)->get();
    $tempReferee = $referee;

    while ($i <= $level->count()) {
        $referer = $tempReferee->refBy;
        if (!$referer) break;

        $plan = $referer->plan;
        if (!$plan) { $tempReferee = $referer; $i++; continue; }
        if ($i > $plan->ref_level) { $tempReferee = $referer; $i++; continue; }

        $commission = Referral::where('commission_type', $commissionType)
            ->where('level', $i)->first();
        if (!$commission) break;

        // ⭐ COMMISSION FORMULA
        $com = ($amount * $commission->percent) / 100;
        
        $referer->balance += $com;  // ⚠️ No lock
        $referer->save();

        // Log transactions and commission
        $tempReferee = $referer;
        $i++;
    }
}
```

### Commission Types
| Type | Trigger | Stored In |
|------|---------|-----------|
| `deposit_commission` | After successful deposit | `Referral.commission_type` |
| `plan_subscribe_commission` | After plan purchase | `Referral.commission_type` |
| `ptc_view_commission` | After PTC ad view | `Referral.commission_type` |

### Commission Rules
1. User must have a plan with `ref_level >= current_level`
2. Commission percent is stored per level in `referrals` table
3. Commission = `amount × percent / 100`
4. Traverses up the referral chain

### ⚠️ Commented-Out Alternative Logic
```php
// Lines 535-625: Different commission calculation
// Uses plan_price capping:
if ($amount < $referer->plan_price) {
    $com = ($amount * $commission->percent) / 100;
} else {
    $com = ($referer->plan_price * $commission->percent) / 100;
}
```
**Issue:** Two different commission formulas exist - one active, one commented. May indicate business rule uncertainty.

---

## Plan/Investment Logic

### Plan Model (`Models/Plan.php`)
```php
class Plan extends Model {
    // Fields: name, price, daily_limit, ref_level, validity, status
}
```

### Plan Purchase Flow (`UserController::buyPlan`)
1. Validate plan exists and is active
2. Check user has sufficient balance
3. Check user doesn't have same running plan
4. Deduct balance
5. Set daily_limit, expire_date, plan_id
6. Create transaction
7. **Trigger `levelCommission()` for `plan_subscribe_commission`**

### Running Plan Logic (`User::runningPlan`)
```php
public function runningPlan(): Attribute {
    if ($this->plan && $this->expire_date > now()) {
        $running = true;
    } else {
        $running = false;
    }
    return new Attribute(get: fn () => $running);
}
```

---

## Wallet/Balance System

### User Balance Fields
```php
'balance'                    // Main wallet
'hold_balance'               // Deprecated field (cast to decimal:8)
'referral_commission_hold'   // Held referral commissions
'upgrade_commission_hold'    // Held upgrade commissions
'ptc_commission_hold'        // Held PTC commissions
```

### Hold Wallet System (`Models/HoldWalletTransaction.php`)
```php
// Fields: user_id, from_user_id, commission_type, hold_amount, 
//         available_date, is_transferred, transferred_at

scopeAvailableForTransfer()  // Where available_date <= today
scopePending()               // Where available_date > today
```

### Hold Wallet Transfer (`UserController::holdWalletTransfer`)
```php
public function holdWalletTransfer() {
    $availableTransactions = HoldWalletTransaction::where('user_id', $user->id)
        ->where('is_transferred', 0)
        ->where('available_date', '<=', now()->toDateString())
        ->get();

    foreach ($availableTransactions as $tx) {
        $totalAmount += $tx->hold_amount;
        $tx->is_transferred = 1;
        $tx->transferred_at = now();
        $tx->save();
    }

    $user->balance += $totalAmount;
    // Reduce hold balances by type
    $user->save();
}
```

### Withdrawal Fee Calculation (`WithdrawController.php:49-52`)
```php
$charge = $method->fixed_charge + ($request->amount * $method->percent_charge / 100);
$afterCharge = $request->amount - $charge;
$finalAmount = $afterCharge * $method->rate;
```

---

## Referral/MLM System

### Referral Registration Flow (`RegisterController.php:128-165`)
1. Find referrer by `referral_code` or username (legacy support)
2. Generate unique 4-5 digit referral code for new user
3. Set `ref_by = referrer.id`
4. Apply registration bonus (if configured)
5. Apply default plan (if configured)

### Referral Code Generation
```php
protected function generateUniqueReferralCode() {
    do {
        $code = str_pad(random_int(1000, 99999), 4, '0', STR_PAD_LEFT);
    } while (User::where('referral_code', $code)->exists());
    return $code;
}
```

### Referral Link
```php
public function referralLink(): Attribute {
    return new Attribute(
        get: fn () => route('user.register') . '?ref=' . $this->referral_code,
    );
}
```

### Commission Levels
- Stored in `referrals` table
- Fields: `level`, `percent`, `commission_type`
- User's plan `ref_level` limits how many levels they receive

---

## Security Concerns

### 🔴 Critical: No Database Locks on Balance Operations

**Risk Level:** HIGH

All balance operations use `$user->balance += $amount; $user->save();` without:
- Database transactions
- Pessimistic locking (`lockForUpdate()`)
- Atomic operations

**Affected Operations:**
1. Deposit approval
2. Withdrawal submission
3. PTC viewing rewards
4. Plan purchases
5. Balance transfers
6. Commission distributions
7. Red Pack rewards
8. VIP task rewards

**Exploit Scenario:**
```
T1: Read balance = 100
T2: Read balance = 100
T1: Add 50 → balance = 150
T2: Add 30 → balance = 130 (Expected: 180)
```

### 🔴 Critical: Double-Spend on Withdrawals

**Location:** `WithdrawController.php:72-100`

```php
// First check
if ($withdraw->amount > $user->balance) {
    return back()->withNotify($notify);
}

// ... multiple operations ...

// Later deduction
$user->balance -= $withdraw->amount;
$user->save();
```

**Exploit:** Rapid withdrawal requests between check and deduction.

### 🟡 Medium: Commission Logic Bypass

**Issue:** In `levelCommission()`, if referer has no plan:
```php
if (!$plan) {
    $tempReferee = $referer;
    $i++;
    continue;  // Skip without blocking chain
}
```

This allows the chain to continue, potentially bypassing intended restrictions.

### 🟡 Medium: Unsigned Amount Inputs

**Location:** Multiple controllers

Amounts are validated as `numeric|gt:0` but not checked against negative overflow or extreme values.

### 🟢 Low: IP Spoofing

**Location:** `helpers.php:475-500`

`getRealIP()` trusts headers like `HTTP_X_FORWARDED_FOR` which can be spoofed.

---

## Race Conditions Analysis

### Identified Race Conditions

| ID | Location | Type | Risk |
|----|----------|------|------|
| RC1 | Balance updates everywhere | Lost Update | HIGH |
| RC2 | Withdrawal check-then-deduct | TOCTOU | HIGH |
| RC3 | PTC remain decrement | Lost Update | MEDIUM |
| RC4 | Red Pack claimed_count | Lost Update | MEDIUM |
| RC5 | Commission distribution | Lost Update | HIGH |
| RC6 | User daily limit checks | TOCTOU | LOW |

### RC1: Balance Lost Update
```php
// PtcController.php:115
$user->balance += $ptc->amount;  // ⚠️ No atomicity
$user->save();
```

### RC2: Withdrawal TOCTOU
```php
// Check at line 72
if ($withdraw->amount > $user->balance) { return; }
// ... gap ...
// Deduct at line 95
$user->balance -= $withdraw->amount;
```

### RC3: PTC Counter Race
```php
// PtcController.php:109-111
$ptc->increment('showed');
$ptc->decrement('remain');  // Two separate operations
```

### RC5: Commission Chain Race
```php
// helpers.php:676
$referer->balance += $com;  // Each referrer's balance not locked
$referer->save();
```

---

## Duplicate/Conflicting Rules

### 1. Two Commission Formulas

**Active (lines 630-720):**
```php
$com = ($amount * $commission->percent) / 100;
```

**Commented (lines 535-625):**
```php
if ($amount < $referer->plan_price) {
    $com = ($amount * $commission->percent) / 100;
} else {
    $com = ($referer->plan_price * $commission->percent) / 100;
}
```

**Issue:** Unclear which is correct business rule.

### 2. User Registration Validation

**Email:** Auto-generated as `{mobile}@agco.app`
- Not validated against existing
- But unique constraint likely exists

### 3. Plan Subscription Check

**UserController.php:188:**
```php
if ($user->runningPlan && $user->plan_id == $plan->id) {
    // Can't subscribe to same plan
}
```
But no check prevents downgrading during active subscription.

### 4. PTC Duplicate View Prevention

**Two identical checks:**
```php
// Line 21-25 (index query)
->whereDoesntHave('views', function($q){
    $q->where('user_id', auth()->user()->id)->whereDate('view_date', Date('Y-m-d'));
})

// Line 59-63 (confirmation check)
if ($viewads->where('ptc_id',$ptc->id)->first()) {
    // Can't see before 24 hours
}
```

---

## Recommendations

### Immediate Actions (Critical)

#### 1. Implement Database Transactions
```php
DB::transaction(function () use ($user, $amount) {
    $user = User::lockForUpdate()->find($user->id);
    $user->balance += $amount;
    $user->save();
});
```

#### 2. Use Atomic Increments
```php
// Instead of:
$user->balance += $amount;
$user->save();

// Use:
$user->increment('balance', $amount);
```

#### 3. Fix Withdrawal Race Condition
```php
DB::transaction(function () use ($withdraw) {
    $user = User::lockForUpdate()->find($withdraw->user_id);
    if ($user->balance < $withdraw->amount) {
        throw new InsufficientBalanceException();
    }
    $user->decrement('balance', $withdraw->amount);
});
```

### Short-Term (1-2 Weeks)

1. **Centralize Balance Operations** into a `WalletService` class
2. **Add Commission Service** class to replace helper function
3. **Implement Transaction Logging** middleware
4. **Add Rate Limiting** on financial endpoints

### Medium-Term (1 Month)

1. **Create `CommissionService`** with proper locking
2. **Implement Event-Driven** balance updates
3. **Add Balance Audit Trail** table
4. **Create Financial Reconciliation** reports

### Long-Term (3 Months)

1. **Migrate to Queue-Based** transaction processing
2. **Implement Double-Entry** bookkeeping
3. **Add Fraud Detection** ML layer
4. **Create API Rate Limiter** per user

---

## Appendix: Key File Locations

| File | Purpose |
|------|---------|
| `core/vendor/cd/app/Http/Helpers/helpers.php` | Commission logic, utilities |
| `core/app/Http/Controllers/User/UserController.php` | User dashboard, transfers |
| `core/app/Http/Controllers/User/WithdrawController.php` | Withdrawal processing |
| `core/app/Http/Controllers/User/PtcController.php` | PTC viewing, earnings |
| `core/app/Http/Controllers/User/RedPackController.php` | Free tasks, rewards |
| `core/app/Http/Controllers/Gateway/PaymentController.php` | Deposit processing |
| `core/app/Http/Controllers/Admin/ReferralController.php` | Commission settings |
| `core/app/Http/Controllers/Admin/PlanController.php` | Subscription plans |
| `core/app/Services/RedPackSecurityService.php` | Fraud detection |
| `core/app/Models/User.php` | User model, balances |
| `core/app/Models/Transaction.php` | Transaction records |
| `core/app/Models/Referral.php` | Commission levels |
| `core/app/Models/CommissionLog.php` | Commission history |

---

*Report generated by automated business logic analysis*
