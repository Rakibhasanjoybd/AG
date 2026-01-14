<?php
// Setup Basic Tables Script
// This creates the essential tables before running Laravel migrations

header('Content-Type: text/plain');
echo "=== AGCO Basic Tables Setup ===\n\n";

// Load environment
$envFile = __DIR__ . '/core/.env';
if (!file_exists($envFile)) {
    echo "❌ .env file not found\n";
    exit;
}

// Parse .env
$env = [];
$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    if (strpos($line, '#') === 0) continue;
    if (strpos($line, '=') === false) continue;
    list($key, $value) = explode('=', $line, 2);
    $env[trim($key)] = trim($value);
}

try {
    $dsn = "mysql:host={$env['DB_HOST']};port={$env['DB_PORT']};dbname={$env['DB_DATABASE']};charset=utf8mb4";
    $pdo = new PDO($dsn, $env['DB_USERNAME'], $env['DB_PASSWORD']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connected to database\n\n";
    
    // Create migrations table first
    echo "Creating migrations table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS migrations (
            id int unsigned NOT NULL AUTO_INCREMENT,
            migration varchar(255) NOT NULL,
            batch int NOT NULL,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "✅ migrations table created\n\n";
    
    // Create users table
    echo "Creating users table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id bigint unsigned NOT NULL AUTO_INCREMENT,
            username varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            email_verified_at timestamp NULL DEFAULT NULL,
            password varchar(255) NOT NULL,
            remember_token varchar(100) DEFAULT NULL,
            firstname varchar(255) DEFAULT NULL,
            lastname varchar(255) DEFAULT NULL,
            mobile varchar(255) DEFAULT NULL,
            mobile_verified_at timestamp NULL DEFAULT NULL,
            address text,
            city varchar(255) DEFAULT NULL,
            state varchar(255) DEFAULT NULL,
            zip varchar(255) DEFAULT NULL,
            country varchar(255) DEFAULT NULL,
            balance decimal(28,8) NOT NULL DEFAULT '0.00000000',
            image varchar(255) DEFAULT NULL,
            status tinyint(1) NOT NULL DEFAULT '1',
            kyc_status tinyint(1) NOT NULL DEFAULT '0',
            ver_code varchar(255) DEFAULT NULL,
            ver_code_send_at timestamp NULL DEFAULT NULL,
            two_factor_status tinyint(1) NOT NULL DEFAULT '0',
            two_factor_secret varchar(255) DEFAULT NULL,
            email_verified tinyint(1) NOT NULL DEFAULT '0',
            sms_verified tinyint(1) NOT NULL DEFAULT '0',
            created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY users_username_unique (username),
            UNIQUE KEY users_email_unique (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "✅ users table created\n\n";
    
    // Create general_settings table
    echo "Creating general_settings table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS general_settings (
            id bigint unsigned NOT NULL AUTO_INCREMENT,
            sitename varchar(255) NOT NULL,
            cur_text varchar(255) NOT NULL,
            cur_sym varchar(255) NOT NULL,
            base_color varchar(255) DEFAULT NULL,
            secondary_color varchar(255) DEFAULT NULL,
            timezone varchar(255) NOT NULL,
            bcp tinyint(1) NOT NULL DEFAULT '0',
            registration tinyint(1) NOT NULL DEFAULT '1',
            ev tinyint(1) NOT NULL DEFAULT '0',
            email_verification tinyint(1) NOT NULL DEFAULT '0',
            sv tinyint(1) NOT NULL DEFAULT '0',
            sms_verification tinyint(1) NOT NULL DEFAULT '0',
            sn tinyint(1) NOT NULL DEFAULT '1',
            sms_notification tinyint(1) NOT NULL DEFAULT '1',
            en tinyint(1) NOT NULL DEFAULT '1',
            email_notification tinyint(1) NOT NULL DEFAULT '1',
            pn tinyint(1) NOT NULL DEFAULT '1',
            push_notification tinyint(1) NOT NULL DEFAULT '1',
            strong_pass tinyint(1) NOT NULL DEFAULT '0',
            agent tinyint(1) NOT NULL DEFAULT '0',
            blog tinyint(1) NOT NULL DEFAULT '0',
            deposit tinyint(1) NOT NULL DEFAULT '1',
            withdraw tinyint(1) NOT NULL DEFAULT '1',
            invest tinyint(1) NOT NULL DEFAULT '0',
            transfer tinyint(1) NOT NULL DEFAULT '0',
            trade tinyint(1) NOT NULL DEFAULT '0',
            kyc tinyint(1) NOT NULL DEFAULT '0',
            google_recaptcha tinyint(1) NOT NULL DEFAULT '0',
            social_login tinyint(1) NOT NULL DEFAULT '0',
            cookie tinyint(1) NOT NULL DEFAULT '1',
            forum tinyint(1) NOT NULL DEFAULT '0',
            knowledge_base tinyint(1) NOT NULL DEFAULT '0',
            stack_exchange tinyint(1) NOT NULL DEFAULT '0',
            support_ticket tinyint(1) NOT NULL DEFAULT '1',
            crons tinyint(1) NOT NULL DEFAULT '0',
            maintenance_mode tinyint(1) NOT NULL DEFAULT '0',
            secure_socket tinyint(1) NOT NULL DEFAULT '0',
            allow_signup tinyint(1) NOT NULL DEFAULT '1',
            allow_kyc_during_signup tinyint(1) NOT NULL DEFAULT '0',
            multilingual tinyint(1) NOT NULL DEFAULT '0',
            language varchar(255) NOT NULL DEFAULT 'en',
            theme varchar(255) NOT NULL DEFAULT 'default',
            active_template varchar(255) NOT NULL DEFAULT 'default',
            modules json DEFAULT NULL,
            version varchar(255) NOT NULL DEFAULT '1.0',
            build varchar(255) NOT NULL DEFAULT '1',
            last_update timestamp NULL DEFAULT NULL,
            force_ssl tinyint(1) NOT NULL DEFAULT '0',
            created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "✅ general_settings table created\n\n";
    
    // Create languages table
    echo "Creating languages table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS languages (
            id bigint unsigned NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            code varchar(255) NOT NULL,
            is_default tinyint(1) NOT NULL DEFAULT '0',
            status tinyint(1) NOT NULL DEFAULT '1',
            created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "✅ languages table created\n\n";
    
    // Insert default language
    echo "Inserting default language...\n";
    $pdo->exec("
        INSERT IGNORE INTO languages (name, code, is_default, status) 
        VALUES ('English', 'en', 1, 1)
    ");
    echo "✅ Default language inserted\n\n";
    
    // Insert default general settings
    echo "Inserting default general settings...\n";
    $pdo->exec("
        INSERT IGNORE INTO general_settings (
            sitename, cur_text, cur_sym, timezone, bcp, registration, ev, 
            email_verification, sv, sms_verification, sn, sms_notification, 
            en, email_notification, pn, push_notification, strong_pass, 
            agent, blog, deposit, withdraw, invest, transfer, trade, kyc, 
            google_recaptcha, social_login, cookie, forum, knowledge_base, 
            stack_exchange, support_ticket, crons, maintenance_mode, 
            secure_socket, allow_signup, allow_kyc_during_signup, multilingual, 
            language, theme, active_template, version, build, force_ssl
        ) VALUES (
            'AGCO', 'USD', '$', 'UTC', 0, 1, 0, 0, 0, 0, 1, 1, 1, 1, 1, 1, 
            0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 1, 0, 0, 0, 1, 0, 
            0, 'en', 'default', 'default', '1.0', '1', 0
        )
    ");
    echo "✅ Default general settings inserted\n\n";
    
    // Create additional essential tables
    echo "Creating transactions table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS transactions (
            id bigint unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint unsigned NOT NULL,
            amount decimal(28,8) NOT NULL,
            post_balance decimal(28,8) NOT NULL,
            charge decimal(28,8) NOT NULL DEFAULT '0.00000000',
            trx_type varchar(255) NOT NULL,
            trx varchar(255) NOT NULL,
            details text,
            remark varchar(255) DEFAULT NULL,
            created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY transactions_user_id_foreign (user_id),
            KEY transactions_trx_type_index (trx_type),
            KEY transactions_trx_index (trx),
            KEY transactions_remark_index (remark)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "✅ transactions table created\n\n";
    
    echo "Creating deposits table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS deposits (
            id bigint unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint unsigned NOT NULL,
            method_code varchar(255) NOT NULL,
            method_currency varchar(255) NOT NULL,
            amount decimal(28,8) NOT NULL,
            charge decimal(28,8) NOT NULL DEFAULT '0.00000000',
            rate decimal(28,8) NOT NULL DEFAULT '0.00000000',
            final_amount decimal(28,8) NOT NULL,
            detail text,
            btc_wallet varchar(255) DEFAULT NULL,
            trx varchar(255) NOT NULL,
            try_count int NOT NULL DEFAULT '0',
            status tinyint NOT NULL DEFAULT '0',
            admin_feedback varchar(255) DEFAULT NULL,
            created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY deposits_user_id_foreign (user_id),
            KEY deposits_trx_index (trx),
            KEY deposits_status_index (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "✅ deposits table created\n\n";
    
    echo "Creating withdrawals table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS withdrawals (
            id bigint unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint unsigned NOT NULL,
            method_id bigint unsigned DEFAULT NULL,
            amount decimal(28,8) NOT NULL,
            charge decimal(28,8) NOT NULL DEFAULT '0.00000000',
            final_amount decimal(28,8) NOT NULL,
            after_charge decimal(28,8) NOT NULL,
            trx varchar(255) NOT NULL,
            status tinyint NOT NULL DEFAULT '0',
            admin_feedback text,
            created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY withdrawals_user_id_foreign (user_id),
            KEY withdrawals_trx_index (trx),
            KEY withdrawals_status_index (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "✅ withdrawals table created\n\n";
    
    echo "Creating withdraw_methods table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS withdraw_methods (
            id bigint unsigned NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            image varchar(255) DEFAULT NULL,
            min_limit decimal(28,8) NOT NULL DEFAULT '0.00000000',
            max_limit decimal(28,8) NOT NULL DEFAULT '0.00000000',
            fixed_charge decimal(28,8) NOT NULL DEFAULT '0.00000000',
            percent_charge decimal(28,8) NOT NULL DEFAULT '0.00000000',
            rate decimal(28,8) NOT NULL DEFAULT '0.00000000',
            currency varchar(255) NOT NULL,
            description text,
            status tinyint(1) NOT NULL DEFAULT '1',
            created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY withdraw_methods_status_index (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "✅ withdraw_methods table created\n\n";
    
    echo "Creating commission_logs table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS commission_logs (
            id bigint unsigned NOT NULL AUTO_INCREMENT,
            to_id bigint unsigned NOT NULL,
            from_id bigint unsigned NOT NULL,
            level int NOT NULL DEFAULT '1',
            commission decimal(28,8) NOT NULL DEFAULT '0.00000000',
            main_balance decimal(28,8) NOT NULL DEFAULT '0.00000000',
            type varchar(255) NOT NULL,
            title varchar(255) NOT NULL,
            created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY commission_logs_to_id_foreign (to_id),
            KEY commission_logs_from_id_foreign (from_id),
            KEY commission_logs_type_index (type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "✅ commission_logs table created\n\n";
    
    echo "Creating ptc_views table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS ptc_views (
            id bigint unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint unsigned NOT NULL,
            ptc_id bigint unsigned NOT NULL,
            view_time timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            ip_address varchar(255) DEFAULT NULL,
            created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY ptc_views_user_id_foreign (user_id),
            KEY ptc_views_ptc_id_foreign (ptc_id),
            KEY ptc_views_view_time_index (view_time)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "✅ ptc_views table created\n\n";
    
    echo "Creating plans table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS plans (
            id bigint unsigned NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            price decimal(28,8) NOT NULL,
            daily_commission decimal(28,8) NOT NULL DEFAULT '0.00000000',
            duration_days int NOT NULL DEFAULT '0',
            min_invest decimal(28,8) NOT NULL DEFAULT '0.00000000',
            max_invest decimal(28,8) NOT NULL DEFAULT '0.00000000',
            fixed_amount decimal(28,8) NOT NULL DEFAULT '0.00000000',
            percentage decimal(28,8) NOT NULL DEFAULT '0.00000000',
            interest_status tinyint(1) NOT NULL DEFAULT '0',
            repeat_time tinyint(1) NOT NULL DEFAULT '0',
            capital_back_status tinyint(1) NOT NULL DEFAULT '0',
            lifetime_status tinyint(1) NOT NULL DEFAULT '0',
            description text,
            featured tinyint(1) NOT NULL DEFAULT '0',
            status tinyint(1) NOT NULL DEFAULT '1',
            created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY plans_status_index (status),
            KEY plans_featured_index (featured)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "✅ plans table created\n\n";
    
    echo "Adding plan_id column to users table...\n";
    $pdo->exec("
        ALTER TABLE users ADD COLUMN IF NOT EXISTS plan_id bigint unsigned DEFAULT NULL AFTER status
    ");
    echo "✅ plan_id column added\n\n";
    
    echo "Adding ref_by column to users table...\n";
    $pdo->exec("
        ALTER TABLE users ADD COLUMN IF NOT EXISTS ref_by bigint unsigned DEFAULT NULL AFTER plan_id
    ");
    echo "✅ ref_by column added\n\n";
    
    echo "Creating frontend table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS frontend (
            id bigint unsigned NOT NULL AUTO_INCREMENT,
            data_keys varchar(255) NOT NULL,
            data_values text,
            created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY frontend_data_keys_index (data_keys)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "✅ frontend table created\n\n";
    
    echo "Creating admin_notifications table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admin_notifications (
            id bigint unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint unsigned DEFAULT NULL,
            title varchar(255) NOT NULL,
            notification text NOT NULL,
            type varchar(255) NOT NULL DEFAULT 'info',
            read_status tinyint(1) NOT NULL DEFAULT '0',
            created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY admin_notifications_user_id_foreign (user_id),
            KEY admin_notifications_read_status_index (read_status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "✅ admin_notifications table created\n\n";
    
    echo "Creating user_notifications table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user_notifications (
            id bigint unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint unsigned NOT NULL,
            title varchar(255) NOT NULL,
            notification text NOT NULL,
            type varchar(255) NOT NULL DEFAULT 'info',
            read_status tinyint(1) NOT NULL DEFAULT '0',
            created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_notifications_user_id_foreign (user_id),
            KEY user_notifications_read_status_index (read_status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "✅ user_notifications table created\n\n";
    
    echo "Creating support_tickets table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS support_tickets (
            id bigint unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint unsigned NOT NULL,
            ticket_id varchar(255) NOT NULL,
            subject varchar(255) NOT NULL,
            message text NOT NULL,
            status tinyint(1) NOT NULL DEFAULT '0',
            priority varchar(255) NOT NULL DEFAULT 'medium',
            last_reply timestamp NULL DEFAULT NULL,
            created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY support_tickets_user_id_foreign (user_id),
            KEY support_tickets_status_index (status),
            KEY support_tickets_ticket_id_index (ticket_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "✅ support_tickets table created\n\n";
    
    echo "Creating ptcs table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS ptcs (
            id bigint unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint unsigned DEFAULT NULL,
            title varchar(255) NOT NULL,
            url varchar(255) NOT NULL,
            amount decimal(28,8) NOT NULL DEFAULT '0.00000000',
            timer int NOT NULL DEFAULT '0',
            status tinyint(1) NOT NULL DEFAULT '1',
            created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY ptcs_user_id_foreign (user_id),
            KEY ptcs_status_index (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "✅ ptcs table created\n\n";
    
    echo "Creating admins table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admins (
            id bigint unsigned NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            username varchar(255) NOT NULL,
            password varchar(255) NOT NULL,
            image varchar(255) DEFAULT NULL,
            status tinyint(1) NOT NULL DEFAULT '1',
            role varchar(255) NOT NULL DEFAULT 'admin',
            created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY admins_email_unique (email),
            UNIQUE KEY admins_username_unique (username)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "✅ admins table created\n\n";
    
    echo "=== Basic Tables Setup Complete ===\n";
    echo "✅ All essential tables created\n";
    echo "✅ Ready for Laravel migrations\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
