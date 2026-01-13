# AGCO Database Schema Analysis Report

**Generated:** January 4, 2026  
**Database:** MariaDB 10.4.32  
**Application:** Laravel-based PTC/Mining Platform (AGCO/MegaMining)

---

## 1. Complete Table Inventory

### Core Tables (from agcoweb.sql)

| # | Table Name | Purpose | Primary Key | Foreign Keys |
|---|------------|---------|-------------|--------------|
| 1 | `admins` | Admin user accounts | `id` | None |
| 2 | `admin_notifications` | Admin notification queue | `id` | `user_id` (no FK constraint) |
| 3 | `admin_password_resets` | Admin password reset tokens | `id` | None |
| 4 | `announcements` | Scrolling announcements | `id` | None |
| 5 | `audio_players` | Audio content for users | `id` | None |
| 6 | `auto_payments` | API keys for auto payments | `id` | None |
| 7 | `commission_logs` | Referral commission history | `id` | `to_id`, `from_id` (no FK) |
| 8 | `daily_spotlights` | Featured content | `id` | None |
| 9 | `deposits` | User deposits | `id` | `user_id` (no FK constraint) |
| 10 | `extensions` | Third-party integrations | `id` | None |
| 11 | `forms` | Dynamic form configurations | `id` | None |
| 12 | `frontends` | CMS content storage | `id` | None |
| 13 | `gateways` | Payment gateway configurations | `id` | None |
| 14 | `gateway_currencies` | Gateway currency settings | `id` | None |
| 15 | `general_settings` | System configuration | `id` | None |
| 16 | `languages` | Localization support | `id` | None |
| 17 | `migrations` | Laravel migrations tracker | `id` | None |
| 18 | `notification_logs` | Notification history | `id` | `user_id` (no FK) |
| 19 | `notification_templates` | Email/SMS templates | `id` | None |
| 20 | `pages` | CMS pages | `id` | None |
| 21 | `password_resets` | User password reset tokens | None (no PK!) | None |
| 22 | `personal_access_tokens` | API tokens (Sanctum) | `id` | Polymorphic |
| 23 | `plans` | Subscription plans | `id` | None |
| 24 | `ptcs` | PTC advertisements | `id` | `user_id` (no FK) |
| 25 | `ptc_views` | Ad view tracking | `id` | `ptc_id`, `user_id` (no FK) |
| 26 | `referrals` | Referral commission structure | `id` | None |
| 27 | `support_attachments` | Ticket attachments | `id` | `support_message_id` (no FK) |
| 28 | `support_messages` | Ticket replies | `id` | `support_ticket_id` (no FK) |
| 29 | `support_tickets` | Support tickets | `id` | `user_id` (no FK) |
| 30 | `transactions` | All financial transactions | `id` | `user_id` (no FK) |
| 31 | `users` | User accounts | `id` | None |
| 32 | `user_logins` | Login history | `id` | `user_id` (no FK) |
| 33 | `user_notifications` | User notifications | `id` | `user_id` (FK ✓) |
| 34 | `video_tutorials` | Tutorial videos | `id` | None |
| 35 | `withdrawals` | Withdrawal requests | `id` | `method_id`, `user_id` (no FK) |
| 36 | `withdraw_methods` | Withdrawal methods | `id` | None |

### Migration-Created Tables (not in main SQL dump)

| # | Table Name | Purpose | Status |
|---|------------|---------|--------|
| 37 | `hold_wallet_transactions` | Hold wallet for commissions | Migration only |
| 38 | `vip_tasks` | VIP task products | Migration only |
| 39 | `vip_task_completions` | VIP task completion tracking | Migration only |
| 40 | `faqs` | FAQ entries | Migration only |
| 41 | `red_packs` | Red pack (reward) configuration | Migration only |
| 42 | `red_pack_tasks` | Tasks within red packs | Migration only |
| 43 | `red_pack_allocations` | User red pack assignments | Migration only |
| 44 | `red_pack_task_completions` | Task completion tracking | Migration only |
| 45 | `red_pack_claims` | Claim attempt logs | Migration only |
| 46 | `red_pack_shares` | Share tracking | Migration only |

---

## 2. Detailed Table Structures

### 2.1 Users Table

```sql
CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `plan_id` int(11) NOT NULL DEFAULT 0,
  `plan_price` decimal(18,2) NOT NULL DEFAULT 0.00,
  `isClick` int(11) DEFAULT NULL,
  `ref_by` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `daily_limit` int(11) NOT NULL DEFAULT 0,
  `firstname` varchar(40) DEFAULT NULL,
  `lastname` varchar(40) DEFAULT NULL,
  `username` varchar(40) NOT NULL,
  `email` varchar(40) NOT NULL,
  `withdraw_password` int(11) DEFAULT NULL,  -- ⚠️ ISSUE: Should be varchar for hashed PIN
  `country_code` varchar(40) DEFAULT NULL,
  `mobile` varchar(40) DEFAULT NULL,
  `balance` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `password` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL COMMENT 'contains full address',
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `kyc_data` text DEFAULT NULL,
  `kv` tinyint(1) NOT NULL DEFAULT 0,
  `ev` tinyint(1) NOT NULL DEFAULT 0,
  `sv` tinyint(1) NOT NULL DEFAULT 0,
  `reg_step` tinyint(1) NOT NULL DEFAULT 0,
  `ver_code` varchar(40) DEFAULT NULL,
  `ver_code_send_at` datetime DEFAULT NULL,
  `ts` tinyint(1) NOT NULL DEFAULT 0,
  `tv` tinyint(1) NOT NULL DEFAULT 1,
  `tsc` varchar(255) DEFAULT NULL,
  `ban_reason` varchar(255) DEFAULT NULL,
  `expire_date` datetime DEFAULT NULL,
  `remember_token` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`,`email`)
);
```

**Migration Additions (not in base SQL):**
- `hold_balance` decimal(28,8) - Hold wallet balance
- `referral_commission_hold` decimal(28,8)
- `upgrade_commission_hold` decimal(28,8)
- `ptc_commission_hold` decimal(28,8)
- `fullname` varchar(255) - Computed/stored fullname
- `is_premium` tinyint(1) - Premium user flag
- `referral_code` varchar(10) UNIQUE - Unique referral code
- `withdrawal_pin` varchar(255) - Hashed withdrawal PIN

### 2.2 Deposits Table

```sql
CREATE TABLE `deposits` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `method_code` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `amount` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `method_currency` varchar(40) DEFAULT NULL,
  `charge` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `rate` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `final_amo` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `detail` text DEFAULT NULL,
  `btc_amo` varchar(255) DEFAULT NULL,
  `btc_wallet` varchar(255) DEFAULT NULL,
  `trx` varchar(40) DEFAULT NULL,
  `try` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=>success, 2=>pending, 3=>cancel',
  `from_api` tinyint(1) NOT NULL DEFAULT 0,
  `admin_feedback` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);
```

### 2.3 Transactions Table

```sql
CREATE TABLE `transactions` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `amount` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `charge` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `post_balance` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `trx_type` varchar(40) DEFAULT NULL,
  `trx_logo` varchar(255) DEFAULT NULL,
  `trx_type_name` text DEFAULT NULL,
  `trx` varchar(40) DEFAULT NULL,
  `details` varchar(255) DEFAULT NULL,
  `remark` varchar(40) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);
```

### 2.4 Plans Table

```sql
CREATE TABLE `plans` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(40) NOT NULL,
  `price` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `image` varchar(255) DEFAULT NULL,
  `daily_limit` int(11) NOT NULL DEFAULT 0,
  `ads_rate` decimal(8,2) NOT NULL DEFAULT 0.00,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `ref_level` int(11) NOT NULL DEFAULT 0,
  `validity` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);
```

---

## 3. Schema Issues & Concerns

### 3.1 🔴 Critical Issues

#### Missing Foreign Key Constraints
Most tables have logical relationships but **NO foreign key constraints**:

| Child Table | Column | Should Reference |
|-------------|--------|------------------|
| `deposits` | `user_id` | `users.id` |
| `withdrawals` | `user_id` | `users.id` |
| `withdrawals` | `method_id` | `withdraw_methods.id` |
| `transactions` | `user_id` | `users.id` |
| `commission_logs` | `to_id`, `from_id` | `users.id` |
| `admin_notifications` | `user_id` | `users.id` |
| `notification_logs` | `user_id` | `users.id` |
| `support_tickets` | `user_id` | `users.id` |
| `support_messages` | `support_ticket_id` | `support_tickets.id` |
| `support_attachments` | `support_message_id` | `support_messages.id` |
| `ptcs` | `user_id` | `users.id` |
| `ptc_views` | `user_id`, `ptc_id` | `users.id`, `ptcs.id` |
| `user_logins` | `user_id` | `users.id` |

**Impact:** Orphaned records possible, data integrity at risk

#### password_resets Table Missing Primary Key
```sql
CREATE TABLE `password_resets` (
  `email` varchar(40) DEFAULT NULL,
  `token` varchar(40) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL
  -- NO PRIMARY KEY!
);
```
**Fix:** Add `id` column or make `email` primary key

#### Data Type Mismatch
In `users` table:
- `withdraw_password int(11)` should be `varchar(255)` for hashed PIN
- Migration adds `withdrawal_pin varchar(255)` - **DUPLICATE/CONFLICT**

### 3.2 🟡 Performance Issues

#### Missing Indexes

| Table | Missing Index On | Query Pattern |
|-------|------------------|---------------|
| `deposits` | `user_id` | User deposit history |
| `deposits` | `status` | Pending deposits |
| `deposits` | `trx` | Transaction lookup |
| `withdrawals` | `user_id` | User withdrawal history |
| `withdrawals` | `status` | Pending withdrawals |
| `transactions` | `user_id` | User transaction history |
| `transactions` | `trx` | Transaction lookup |
| `transactions` | `remark` | Transaction type queries |
| `commission_logs` | `to_id` | User commission history |
| `commission_logs` | `from_id` | Source tracking |
| `ptc_views` | `user_id` | User view history |
| `ptc_views` | `ptc_id` | Ad statistics |
| `ptc_views` | `view_date` | Daily limit checks |
| `ptcs` | `status` | Active ads query |
| `user_logins` | `user_id` | Login history |
| `support_tickets` | `user_id` | User tickets |
| `support_tickets` | `status` | Open ticket queries |

#### Recommended Compound Indexes

```sql
-- High-value indexes
ALTER TABLE deposits ADD INDEX idx_user_status (user_id, status);
ALTER TABLE withdrawals ADD INDEX idx_user_status (user_id, status);
ALTER TABLE transactions ADD INDEX idx_user_created (user_id, created_at);
ALTER TABLE ptc_views ADD INDEX idx_user_date (user_id, view_date);
ALTER TABLE ptcs ADD INDEX idx_status_plan (status, plan_id);
```

### 3.3 🟠 Normalization Issues

#### 1. Denormalized User Balance
- `users.balance` stored directly (acceptable for performance)
- No audit trail enforced at DB level
- Race condition risk on concurrent updates

#### 2. JSON/Text Columns for Structured Data
| Table | Column | Issue |
|-------|--------|-------|
| `users` | `address` | JSON stored as TEXT |
| `users` | `kyc_data` | JSON stored as TEXT |
| `general_settings` | `mail_config` | JSON stored as TEXT |
| `general_settings` | `sms_config` | JSON stored as TEXT |
| `gateways` | `gateway_parameters` | JSON stored as TEXT |
| `gateways` | `supported_currencies` | JSON stored as TEXT |
| `forms` | `form_data` | JSON stored as TEXT |
| `frontends` | `data_values` | LONGTEXT for JSON |

**Recommendation:** Use native JSON column type for better validation

#### 3. Redundant Data
- `users.plan_price` duplicates `plans.price`
- `commission_logs.details` duplicates calculable data
- `frontends` uses key-value pattern (EAV anti-pattern)

### 3.4 🟢 Minor Issues

#### Inconsistent Naming Conventions
- `ptcs` vs `ptc_views` (singular vs compound)
- `admins` vs `admin_notifications` (plural table, singular prefix)
- `trx` abbreviation used inconsistently

#### Nullable Columns That Shouldn't Be
- `admins.name` - Should be NOT NULL
- `admins.email` - Should be NOT NULL  
- `gateways.name` - Should be NOT NULL

---

## 4. Migration Analysis

### 4.1 Migration Files Found

| File | Tables/Changes | Status |
|------|----------------|--------|
| `2023_04_22_230555_create_auto_payments_table.php` | `auto_payments` | ✅ In DB |
| `2024_12_30_000001_add_hold_wallet_to_users_table.php` | Adds hold columns to users | ⚠️ May not be run |
| `2024_12_30_000002_create_announcements_table.php` | `announcements` | ✅ In DB |
| `2024_12_30_000003_create_video_tutorials_table.php` | `video_tutorials` | ✅ In DB |
| `2024_12_30_000004_create_faqs_table.php` | `faqs` | ❓ Unknown |
| `2024_12_30_000005_create_vip_tasks_table.php` | `vip_tasks` | ❓ Unknown |
| `2024_12_30_000006_create_vip_task_completions_table.php` | `vip_task_completions` | ❓ Unknown |
| `2024_12_30_000007_create_hold_wallet_transactions_table.php` | `hold_wallet_transactions` | ❓ Unknown |
| `2024_12_30_000008_create_daily_spotlights_table.php` | `daily_spotlights` | ✅ In DB |
| `2024_12_30_000009_create_audio_players_table.php` | `audio_players` | ✅ In DB |
| `2024_12_30_000010_create_user_notifications_table.php` | `user_notifications` | ✅ In DB |
| `2026_01_01_000001_add_wallet_header_to_general_settings.php` | Modifies settings | ❓ Unknown |
| `2026_01_01_000002_add_wallet_header_slideshow_to_general_settings.php` | Modifies settings | ❓ Unknown |
| `2026_01_03_000001_add_referral_code_and_withdrawal_pin_to_users.php` | Adds user columns | ❓ Unknown |
| `2026_01_03_000001_create_red_packs_table.php` | `red_packs` | ❓ Unknown |
| `2026_01_03_000002_create_red_pack_tasks_table.php` | `red_pack_tasks` | ❓ Unknown |
| `2026_01_03_000003_create_red_pack_allocations_table.php` | `red_pack_allocations` | ❓ Unknown |
| `2026_01_03_000004_create_red_pack_task_completions_table.php` | `red_pack_task_completions` | ❓ Unknown |
| `2026_01_03_000005_create_red_pack_claims_table.php` | `red_pack_claims` | ❓ Unknown |
| `2026_01_03_000006_create_red_pack_shares_table.php` | `red_pack_shares` | ❓ Unknown |

### 4.2 Migration vs SQL Dump Discrepancies

1. **`migrations` table shows only 2 migrations run:**
   - `2019_12_14_000001_create_personal_access_tokens_table`
   - `2023_04_22_230555_create_auto_payments_table`

2. **Tables in SQL dump but NOT tracked in migrations:**
   - All core tables (admins, users, deposits, etc.)
   - These were created via raw SQL, not Laravel migrations

3. **Migrations that need to be run:**
   ```bash
   php artisan migrate --status
   ```

### 4.3 Duplicate Migration Timestamps

⚠️ **Two migrations have the same timestamp:**
- `2026_01_03_000001_add_referral_code_and_withdrawal_pin_to_users.php`
- `2026_01_03_000001_create_red_packs_table.php`

This may cause issues when running migrations.

---

## 5. Data Integrity Concerns

### 5.1 Balance Consistency

| Risk | Description | Mitigation |
|------|-------------|------------|
| Race Condition | Concurrent balance updates | Use DB transactions + row locking |
| Drift | `users.balance` vs `SUM(transactions)` | Periodic reconciliation job |
| Negative Balance | No DB-level constraint | Add CHECK constraint |

```sql
-- Recommended constraint
ALTER TABLE users ADD CONSTRAINT chk_balance_non_negative CHECK (balance >= 0);
```

### 5.2 Orphaned Records Risk

Without FK constraints, these scenarios are possible:
- Deposits for deleted users
- Transactions without valid user
- Support messages without tickets
- Commission logs with invalid user IDs

### 5.3 Suggested Integrity Fixes

```sql
-- Add foreign keys with cascading
ALTER TABLE deposits 
  ADD CONSTRAINT fk_deposits_user 
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

ALTER TABLE withdrawals 
  ADD CONSTRAINT fk_withdrawals_user 
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

ALTER TABLE transactions 
  ADD CONSTRAINT fk_transactions_user 
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

ALTER TABLE ptc_views 
  ADD CONSTRAINT fk_ptcviews_user 
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  ADD CONSTRAINT fk_ptcviews_ptc 
  FOREIGN KEY (ptc_id) REFERENCES ptcs(id) ON DELETE CASCADE;
```

---

## 6. Security Concerns

### 6.1 Sensitive Data Storage

| Table | Column | Concern |
|-------|--------|---------|
| `admins` | `password` | ✅ Hashed (bcrypt) |
| `users` | `password` | ✅ Hashed (bcrypt) |
| `users` | `withdraw_password` | ⚠️ INT type - NOT secure! |
| `gateways` | `gateway_parameters` | ⚠️ API keys in plain JSON |
| `auto_payments` | `api_key`, `secret_key` | ⚠️ Unencrypted |
| `admin_password_resets` | `token` | ⚠️ Unhashed reset tokens |

### 6.2 Recommendations

1. **Encrypt sensitive gateway parameters** using Laravel's encryption
2. **Hash password reset tokens** before storage
3. **Remove/Fix `withdraw_password`** column - use migration's `withdrawal_pin` instead
4. **Add rate limiting** on reset token attempts

---

## 7. Performance Recommendations

### 7.1 Immediate Actions

```sql
-- Critical indexes to add NOW
ALTER TABLE deposits ADD INDEX idx_user_id (user_id);
ALTER TABLE withdrawals ADD INDEX idx_user_id (user_id);
ALTER TABLE transactions ADD INDEX idx_user_id (user_id);
ALTER TABLE transactions ADD INDEX idx_trx (trx);
ALTER TABLE ptc_views ADD INDEX idx_user_ptc_date (user_id, ptc_id, view_date);
ALTER TABLE user_logins ADD INDEX idx_user_id (user_id);
ALTER TABLE support_tickets ADD INDEX idx_user_status (user_id, status);
```

### 7.2 Query Optimization

1. **Paginate large result sets** (transactions, deposits)
2. **Use eager loading** for relationships in Eloquent
3. **Consider read replicas** for report queries
4. **Archive old records** (>6 months) to separate tables

### 7.3 Maintenance Tasks

```sql
-- Regular maintenance
OPTIMIZE TABLE transactions;
OPTIMIZE TABLE deposits;
OPTIMIZE TABLE ptc_views;
ANALYZE TABLE users;
```

---

## 8. Summary

### Tables: 36 core + 10 migration-only = 46 total

### Critical Issues to Fix:
1. ❌ No foreign key constraints on most tables
2. ❌ `password_resets` has no primary key
3. ❌ `withdraw_password` wrong data type
4. ❌ Missing essential indexes
5. ❌ Duplicate migration timestamps

### Data Integrity Score: **4/10**
- Many orphan risks
- No balance constraints
- Minimal referential integrity

### Performance Score: **5/10**
- Primary keys present
- Minimal secondary indexes
- JSON in TEXT columns

### Recommendations Priority:
1. **HIGH:** Add missing indexes
2. **HIGH:** Add foreign key constraints
3. **MEDIUM:** Run pending migrations
4. **MEDIUM:** Fix `withdraw_password` column
5. **LOW:** Refactor JSON columns to native type

---

*Report generated by AGCO Database Analysis Agent*
