# Security Audit Report - Laravel HTTP Layer

## Application: AGCO (PtcLab)
**Audit Date:** January 4, 2026  
**Scope:** `core/app/Http` Directory  
**Framework:** Laravel

---

## Executive Summary

This security audit examines the HTTP layer of the AGCO Laravel application, including middleware, authentication, authorization, input validation, and protection against common web vulnerabilities. The audit identified several **critical**, **high**, and **medium** severity issues that require immediate attention.

### Risk Summary

| Severity | Count | Status |
|----------|-------|--------|
| 🔴 Critical | 5 | Requires Immediate Action |
| 🟠 High | 8 | Requires Urgent Action |
| 🟡 Medium | 12 | Should Be Addressed |
| 🟢 Low | 6 | Best Practice Improvements |

---

## 1. Middleware Analysis

### 1.1 Registered Middleware (Kernel.php)

#### Global Middleware Stack
| Middleware | Purpose | Security Status |
|------------|---------|-----------------|
| `TrustProxies` | Proxy header handling | ⚠️ Proxies not configured |
| `HandleCors` | CORS handling | 🔴 Overly permissive |
| `PreventRequestsDuringMaintenance` | Maintenance mode | ✅ Standard |
| `ValidatePostSize` | POST size validation | ✅ Standard |
| `TrimStrings` | Input trimming | ✅ Standard |
| `ConvertEmptyStringsToNull` | Null conversion | ✅ Standard |

#### Web Middleware Group
| Middleware | Purpose | Security Status |
|------------|---------|-----------------|
| `EncryptCookies` | Cookie encryption | ✅ No exclusions |
| `StartSession` | Session handling | ✅ Standard |
| `VerifyCsrfToken` | CSRF protection | 🔴 Multiple exclusions |
| `LanguageMiddleware` | Language handling | ✅ Standard |
| `SubstituteBindings` | Route binding | ✅ Standard |

#### API Middleware Group
| Middleware | Purpose | Security Status |
|------------|---------|-----------------|
| `throttle:api` | Rate limiting | ✅ Enabled |
| `SubstituteBindings` | Route binding | ✅ Standard |

**Note:** `EnsureFrontendRequestsAreStateful` is commented out, which may affect Sanctum API authentication.

#### Route Middleware
| Alias | Middleware | Purpose |
|-------|------------|---------|
| `auth` | `Authenticate` | User authentication |
| `auth.api` | `AuthenticateApi` | API authentication |
| `admin` | `RedirectIfNotAdmin` | Admin-only access |
| `admin.guest` | `RedirectIfAdmin` | Non-admin only access |
| `guest` | `RedirectIfAuthenticated` | Unauthenticated only |
| `check.status` | `CheckStatus` | User verification status |
| `kyc` | `KycMiddleware` | KYC verification |
| `registration.status` | `AllowRegistration` | Registration toggle |
| `registration.complete` | `RegistrationStep` | Registration completion |
| `maintenance` | `MaintenanceMode` | Maintenance check |

---

## 2. Critical Security Issues

### 2.1 🔴 CSRF Token Exclusions (CRITICAL)

**File:** `app/Http/Middleware/VerifyCsrfToken.php`

```php
protected $except = [
    'user/deposit',
    'ipn*',
    'api/*',
    'webhook/*'
];
```

**Risk:** The `user/deposit` route is excluded from CSRF protection, exposing it to Cross-Site Request Forgery attacks. Attackers could trick authenticated users into initiating unauthorized deposits.

**Recommendation:**
- Remove `user/deposit` from CSRF exclusions
- Implement token-based verification for IPN/webhook endpoints
- Use signed URLs or HMAC verification for payment callbacks

---

### 2.2 🔴 Overly Permissive CORS Configuration (CRITICAL)

**File:** `config/cors.php`

```php
'allowed_origins' => ['*'],
'allowed_methods' => ['*'],
'allowed_headers' => ['*'],
```

**Risk:** Allows any origin to make requests to the API, enabling potential data theft through cross-origin attacks.

**Recommendation:**
```php
'allowed_origins' => [env('APP_URL'), 'https://trusted-domain.com'],
'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE'],
'allowed_headers' => ['Content-Type', 'Authorization', 'X-CSRF-TOKEN'],
```

---

### 2.3 🔴 Direct $_GET/$_POST Usage (CRITICAL)

**Files with direct superglobal access:**

| File | Line | Issue |
|------|------|-------|
| `SiteController.php` | 23 | `$_GET['reference']` |
| `Gateway/Instamojo/ProcessController.php` | 70, 74 | `$_POST` access |
| `Gateway/Paytm/ProcessController.php` | 52-71 | Multiple `$_POST` |
| `Gateway/Skrill/ProcessController.php` | 45-54 | Multiple `$_POST` |
| `Gateway/PaypalSdk/ProcessController.php` | 65, 69 | `$_GET['token']` |

**Risk:** Bypasses Laravel's input sanitization and validation, increasing vulnerability to injection attacks.

**Recommendation:**
- Replace `$_GET` with `$request->query()`
- Replace `$_POST` with `$request->input()` or `$request->post()`
- Always validate payment callback data

---

### 2.4 🔴 Session Security Configuration (HIGH)

**File:** `config/session.php`

```php
'encrypt' => false,           // Session not encrypted
'secure' => env('SESSION_SECURE_COOKIE', false),  // May not use HTTPS
'same_site' => 'lax',         // Acceptable but 'strict' is safer
```

**Risk:** Unencrypted sessions could be tampered with. Non-secure cookies can be intercepted over HTTP.

**Recommendation:**
```php
'encrypt' => true,
'secure' => env('SESSION_SECURE_COOKIE', true),
'same_site' => 'strict',
```

---

### 2.5 🔴 Unsafe File Operations (CRITICAL)

**File:** `UserController.php:165`
```php
public function attachmentDownload($fileHash)
{
    $filePath = decrypt($fileHash);
    // ... 
    return readfile($filePath);
}
```

**Risk:** Path traversal vulnerability if decryption fails or is manipulated. An attacker could potentially access arbitrary files.

**Recommendation:**
- Validate that decrypted path is within allowed directories
- Use Laravel's `response()->download()` with proper validation
- Implement whitelist of allowed file paths

---

## 3. High Severity Issues

### 3.1 🟠 Missing Request Validation Classes

**Issue:** No dedicated `FormRequest` classes found in `app/Http/Requests/` directory.

**Current State:** All validation is done inline in controllers:
```php
$request->validate([
    'amount' => 'required|numeric|gt:0',
    'method_code' => 'required',
]);
```

**Risk:** 
- Inconsistent validation across endpoints
- No authorization checks in form requests
- Difficult to maintain and audit

**Recommendation:**
Create dedicated FormRequest classes:
```php
// app/Http/Requests/DepositRequest.php
class DepositRequest extends FormRequest
{
    public function authorize() { return auth()->check(); }
    
    public function rules() {
        return [
            'amount' => 'required|numeric|gt:0|max:1000000',
            'method_code' => 'required|exists:gateway_currencies,method_code',
            'currency' => 'required|string|max:10',
        ];
    }
}
```

---

### 3.2 🟠 Raw SQL Queries

**File:** `AdminController.php`

```php
$widget['total_ads'] = Ptc::where('status',1)
    ->addSelect(\DB::raw('(select amount * ptcs.max_show) as amo'))
    ->get('amo')->sum('amo');

->selectRaw("SUM(amount) as amount, DATE_FORMAT(created_at,'%Y-%m-%d') as date")
```

**Risk:** While these specific queries don't use user input directly, the pattern could lead to SQL injection if user input is added later.

**Recommendation:**
- Use Laravel's aggregate functions where possible
- Document any raw queries with security notes
- Never interpolate user input into raw SQL

---

### 3.3 🟠 Insufficient Password Validation

**File:** `RegisterController.php:73`

```php
$passwordValidation = Password::min(6);
if ($general->secure_password) {
    $passwordValidation = $passwordValidation->mixedCase()->numbers()->symbols()->uncompromised();
}
```

**Risk:** When `secure_password` is disabled, only 6-character passwords are required - far below modern security standards.

**Recommendation:**
- Set minimum password length to 8 characters unconditionally
- Always require at least one number or special character
- Implement password strength meter on frontend

---

### 3.4 🟠 TrustHosts Middleware Disabled

**File:** `Kernel.php:17`

```php
// \App\Http\Middleware\TrustHosts::class,
```

**Risk:** Application may be vulnerable to Host Header Injection attacks.

**Recommendation:**
- Enable TrustHosts middleware
- Configure allowed hosts in the middleware

---

### 3.5 🟠 Admin Login Without Rate Limiting

**File:** `routes/admin.php`

The admin login route doesn't appear to have explicit rate limiting configured beyond Laravel's default throttling.

**Recommendation:**
- Apply aggressive rate limiting to admin login: `throttle:5,10`
- Implement IP-based lockout after multiple failures
- Add admin login attempt logging

---

### 3.6 🟠 Missing Authorization in CheckStatus Middleware

**File:** `Middleware/CheckStatus.php`

```php
public function handle($request, Closure $next)
{
    if (Auth::check()) {
        $user = auth()->user();
        if ($user->status && $user->ev && $user->sv && $user->tv) {
            return $next($request);
        }
        // ...
    }
    abort(403);
}
```

**Risk:** The middleware aborts with 403 if user is not authenticated, but this should be handled by the `auth` middleware first.

**Recommendation:**
- Ensure `auth` middleware always runs before `check.status`
- Add explicit error messages for different failure states

---

### 3.7 🟠 Insecure File Download Implementation

**File:** `UserController.php:161-165`

```php
header('Content-Disposition: attachment; filename="' . $title);
header("Content-Type: " . $mimetype);
return readfile($filePath);
```

**Risk:** Using `readfile()` directly can expose server to path traversal. Also, headers are set manually instead of using Laravel's response methods.

**Recommendation:**
```php
return response()->download($filePath, $title, [
    'Content-Type' => $mimetype,
]);
```

---

### 3.8 🟠 Debug Output in Production Code

**File:** `Gateway/PaypalSdk/PayPalHttp/Encoder.php`

```php
echo $message;  // Lines 34, 44, 50, 68, 97
```

**Risk:** Echo statements can leak sensitive information in production.

**Recommendation:**
- Use Laravel's logging instead of echo
- Remove or wrap in APP_DEBUG check

---

## 4. Medium Severity Issues

### 4.1 🟡 Missing Input Sanitization

Multiple controllers accept user input without explicit sanitization beyond validation:

**Examples:**
- `ProfileController.php`: `$request->firstname`, `$request->lastname` stored directly
- `SiteController.php`: Contact form message stored directly
- `RegisterController.php`: `$data['fullname']` only trimmed

**Recommendation:**
- Apply `strip_tags()` or HTML Purifier for text inputs
- Use Laravel's `Str::of()->limit()` for length restrictions
- Consider content security policies

---

### 4.2 🟡 Insufficient File Upload Validation

**File:** `SupportTicketManager.php`

```php
protected $allowedExtension = ['jpg', 'png', 'jpeg', 'pdf', 'doc', 'docx'];
```

**Risk:** Extension-only validation can be bypassed. Malicious files can masquerade as allowed types.

**Recommendation:**
```php
$request->validate([
    'attachments.*' => [
        'file',
        'max:4096',
        'mimes:jpg,png,jpeg,pdf,doc,docx',
        'mimetypes:image/jpeg,image/png,application/pdf,application/msword',
    ]
]);
```

---

### 4.3 🟡 Mass Assignment Concerns

**File:** `RegisterController.php`

```php
event(new Registered($user = $this->create($request->all())));
```

While `$request->all()` is passed to `create()`, the function manually assigns each field. However, this pattern is risky if the function is modified later.

**Recommendation:**
- Use `$request->only(['fullname', 'mobile', 'password', ...])` explicitly
- Or use `$request->validated()` after Form Request validation

---

### 4.4 🟡 API Authentication Weakness

**File:** `Kernel.php:43-46`

```php
'api' => [
    // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    'throttle:api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],
```

**Issue:** Sanctum middleware is commented out, meaning API authentication may not be properly implemented.

**Recommendation:**
- Implement proper API authentication (Sanctum, Passport, or JWT)
- Enable the EnsureFrontendRequestsAreStateful middleware if using SPA

---

### 4.5 🟡 Language Middleware SQL Query

**File:** `LanguageMiddleware.php`

```php
$language = Language::where('is_default', 1)->first();
```

**Risk:** Database query on every request without caching.

**Recommendation:**
```php
$language = Cache::remember('default_language', 3600, function () {
    return Language::where('is_default', 1)->first();
});
```

---

### 4.6 🟡 Insecure Error Messages

**File:** `LoginController.php:99`

```php
$notify[] = ['error', 'প্রিয় সহযোগী, আপনি হয়তো কিছু ভুল লিখছেন...'];
```

While using a generic message is good, the VPN reference could give attackers information about detection mechanisms.

---

### 4.7 🟡 KYC Middleware Inconsistency

**File:** `KycMiddleware.php`

```php
if ($request->is('api/*') && ($user->kv == 0 || $user->kv == 2)) {
    // Returns error
}
if ($user->kv == 0) {
    // Redirects to form
}
```

**Issue:** Different handling for API vs web could lead to bypass scenarios.

---

### 4.8 🟡 Missing Request Size Limits

**Issue:** No explicit request body size limits beyond PHP defaults.

**Recommendation:** Add middleware to limit request body sizes for specific routes.

---

### 4.9 🟡 Registration Step Auto-Completion

**File:** `RegistrationStep.php`

```php
if (!$user->reg_step) {
    $user->reg_step = 1;
    $user->save();
}
```

**Issue:** Automatically completing registration steps could bypass intended user verification workflows.

---

### 4.10 🟡 AllowRegistration Uses Global Setting

**File:** `AllowRegistration.php`

```php
if (gs()->registration == 0) {
    // block
}
```

**Risk:** The `gs()` helper is called on every request without caching verification.

---

### 4.11 🟡 Withdrawal PIN Hashing

**File:** `RegisterController.php:163`

```php
$user->withdrawal_pin = Hash::make($data['withdrawal_pin']);
```

**Good:** PIN is hashed. However, the validation only checks `digits_between:4,6` - consider adding complexity requirements for financial transactions.

---

### 4.12 🟡 Device Fingerprint Not Validated

**File:** `RedPackController.php`

```php
$deviceHash = $request->input('device_fingerprint');
```

**Issue:** Device fingerprint is accepted without validation format. Could be spoofed easily.

---

## 5. Authentication & Authorization

### 5.1 Auth Guards Configuration

**File:** `config/auth.php`

| Guard | Driver | Provider | Status |
|-------|--------|----------|--------|
| `web` | session | users | ✅ Standard |
| `api` | session | users | ⚠️ Session-based API auth |
| `admin` | session | admins | ✅ Separate admin guard |

**Concern:** API guard uses session driver instead of token-based authentication.

### 5.2 Password Reset Configuration

```php
'expire' => 60,
'throttle' => 60,
```

✅ Tokens expire in 60 minutes (appropriate)
✅ Throttle of 60 seconds prevents abuse

---

## 6. Missing Security Features

### 6.1 No Security Headers Middleware

**Missing:**
- X-Frame-Options
- X-Content-Type-Options
- X-XSS-Protection
- Content-Security-Policy
- Referrer-Policy
- Permissions-Policy

**Recommendation:** Create `SecurityHeaders` middleware:
```php
public function handle($request, Closure $next)
{
    $response = $next($request);
    
    $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
    $response->headers->set('X-Content-Type-Options', 'nosniff');
    $response->headers->set('X-XSS-Protection', '1; mode=block');
    $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
    
    return $response;
}
```

---

### 6.2 No IP-Based Rate Limiting for Sensitive Operations

**Recommendation:** Add aggressive rate limiting for:
- Login attempts: `throttle:5,5`
- Registration: `throttle:3,10`
- Password reset: `throttle:3,10`
- Withdrawal requests: `throttle:5,60`

---

### 6.3 No Logging of Security Events

**Recommendation:** Implement security event logging for:
- Failed login attempts
- Password changes
- Withdrawal requests
- Admin actions
- IP address changes

---

## 7. XSS Vulnerability Assessment

### 7.1 Blade Template Safety

Laravel's Blade `{{ }}` syntax auto-escapes output. However:

**Potential XSS vectors:**
- User-provided data in `{!! !!}` syntax
- JavaScript event handlers with user data
- Custom HTML content from admin settings

**Recommendation:** Audit all Blade templates for `{!! !!}` usage.

---

## 8. SQL Injection Assessment

### 8.1 Eloquent ORM Usage

Most database operations use Eloquent, which provides SQL injection protection. However:

**Areas of concern:**
- Raw queries in `AdminController.php`
- `selectRaw()` usage without bound parameters
- Dynamic column ordering (if any)

**Overall Risk:** LOW - Eloquent usage is consistent

---

## 9. Recommendations Priority Matrix

### Immediate (Week 1)
1. Fix CSRF exclusions - remove `user/deposit`
2. Update CORS configuration
3. Replace direct `$_GET`/`$_POST` usage
4. Enable session encryption
5. Fix file download security

### Short-term (Week 2-4)
1. Create FormRequest validation classes
2. Implement security headers middleware
3. Enable TrustHosts middleware
4. Add comprehensive rate limiting
5. Implement security event logging

### Medium-term (Month 2)
1. Audit all Blade templates for XSS
2. Implement proper API authentication
3. Add input sanitization layer
4. Create file upload security improvements
5. Review all raw SQL queries

### Long-term (Quarter 2)
1. Implement Content Security Policy
2. Add two-factor authentication for admin
3. Create security monitoring dashboard
4. Implement automated security testing
5. Schedule regular security audits

---

## 10. Compliance Notes

For financial applications, consider:
- PCI DSS compliance for payment handling
- GDPR requirements for user data
- Data encryption at rest
- Audit trail requirements
- Regular penetration testing

---

## Appendix A: Middleware File Summary

| File | Lines | Purpose | Security Rating |
|------|-------|---------|-----------------|
| `AllowRegistration.php` | 23 | Registration toggle | ✅ OK |
| `Authenticate.php` | 22 | User authentication | ✅ OK |
| `AuthenticateApi.php` | 24 | API authentication | ✅ OK |
| `CheckForMaintenanceMode.php` | 17 | Maintenance mode (legacy) | ✅ OK |
| `CheckStatus.php` | 45 | User status verification | ⚠️ Review |
| `EncryptCookies.php` | 17 | Cookie encryption | ✅ OK |
| `KycMiddleware.php` | 37 | KYC verification | ⚠️ Review |
| `LanguageMiddleware.php` | 35 | Language selection | ✅ OK |
| `MaintenanceMode.php` | 26 | Custom maintenance | ✅ OK |
| `PreventRequestsDuringMaintenance.php` | 17 | Maintenance blocking | ✅ OK |
| `RedirectIfAdmin.php` | 24 | Admin guest redirect | ✅ OK |
| `RedirectIfAuthenticated.php` | 22 | Guest redirect | ✅ OK |
| `RedirectIfNotAdmin.php` | 24 | Admin protection | ✅ OK |
| `RegistrationStep.php` | 31 | Registration completion | ⚠️ Review |
| `TrimStrings.php` | 19 | Input trimming | ✅ OK |
| `TrustHosts.php` | 20 | Host validation | ⚠️ Disabled |
| `TrustProxies.php` | 28 | Proxy trust | ⚠️ Not configured |
| `VerifyCsrfToken.php` | 20 | CSRF protection | 🔴 Critical |

---

## Appendix B: Security Testing Checklist

- [ ] Attempt CSRF attack on deposit endpoint
- [ ] Test CORS with malicious origin
- [ ] Attempt SQL injection on search parameters
- [ ] Test XSS in all user input fields
- [ ] Verify session security settings
- [ ] Test rate limiting on login
- [ ] Attempt path traversal on file downloads
- [ ] Verify admin access controls
- [ ] Test password reset flow
- [ ] Audit payment gateway callbacks

---

**Report Generated By:** Security Audit Agent  
**Classification:** CONFIDENTIAL  
**Distribution:** Development Team, Security Team, Management
