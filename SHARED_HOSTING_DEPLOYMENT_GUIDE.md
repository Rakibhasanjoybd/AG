# 🚀 SHARED HOSTING DEPLOYMENT GUIDE - COMPLETE A to Z

## Overview
This guide will help you deploy your AGCO Laravel application to shared hosting in perfect condition.

---

## STEP 1: PRE-DEPLOYMENT CHECKLIST

### 1.1 System Requirements Check
- [x] PHP 8.0.2 or higher
- [x] MySQL 5.7+
- [x] OpenSSL PHP extension
- [x] PDO PHP extension
- [x] Mbstring PHP extension
- [x] Tokenizer PHP extension
- [x] XML PHP extension
- [x] Ctype PHP extension
- [x] JSON PHP extension
- [x] BCMath PHP extension
- [x] GD library (for image handling)

**How to check on shared hosting:**
1. Login to cPanel
2. Go to "Select PHP Version" in Software section
3. Verify PHP version is 8.0.2+
4. Check "Extensions" tab for all required extensions

### 1.2 Required Hosting Features
- SSH access (for running commands)
- Composer support
- Multiple MySQL databases
- File permissions management (644 for files, 755 for directories)
- Cron jobs support

---

## STEP 2: DOMAIN & HOSTING SETUP

### 2.1 Domain Pointer
1. Point your domain to your hosting account
2. Update DNS records:
   - A record → Your hosting IP
   - Wait 24-48 hours for DNS propagation

### 2.2 Create New Addon Domain (if needed)
1. Login to cPanel
2. Go to "Addon Domains"
3. Add your domain
4. Set public HTML folder (usually `/public_html/yourdomain.com`)

### 2.3 SSL Certificate
1. Go to "AutoSSL" in cPanel (most hosts provide free SSL)
2. Install SSL certificate for your domain
3. This is CRITICAL for production

---

## STEP 3: DATABASE SETUP

### 3.1 Create Database & User
1. Login to cPanel
2. Go to "MySQL Databases"
3. Create new database:
   ```
   Database Name: agcoweb_live
   ```
4. Create new user:
   ```
   Username: agco_user
   Password: [Generate Strong Password - 12+ chars, mixed case, numbers, special chars]
   ```
5. Assign user to database with ALL privileges

### 3.2 Import Database
1. Download backup from your local system:
   - File: `agcoweb_production_export.sql` (or your latest backup)

2. Option A - Using cPanel (easiest):
   - Go to "phpMyAdmin"
   - Select your new database
   - Click "Import"
   - Upload your `.sql` file
   - Click "Go"
   - Wait for completion (may take 5-10 minutes for large databases)

3. Option B - Using SSH (faster for large databases):
   ```bash
   mysql -u agco_user -p agcoweb_live < agcoweb_production_export.sql
   ```
   When prompted, enter your database password.

### 3.3 Verify Database Import
1. In phpMyAdmin, select your database
2. Check that all tables are present
3. Verify data integrity

---

## STEP 4: UPLOAD PROJECT FILES

### 4.1 File Structure on Shared Hosting
```
/home/username/
├── public_html/
│   ├── yourdomain.com/  (or root if single domain)
│   │   ├── index.php (entry point)
│   │   ├── web.config (for IIS if applicable)
│   │   ├── public/ (Laravel public folder)
│   │   └── .htaccess
│   └── core/ (Laravel app root)
│       ├── app/
│       ├── bootstrap/
│       ├── config/
│       ├── database/
│       ├── routes/
│       ├── resources/
│       ├── storage/
│       ├── vendor/
│       ├── .env
│       ├── composer.json
│       └── artisan
```

### 4.2 Upload Via FTP/SFTP (Using FileZilla or similar)

**Steps:**
1. Use SFTP for secure connection (preferred over FTP)
2. Connection details:
   ```
   Host: your.hosting.com
   Username: cpanel_username
   Password: cpanel_password
   Port: 22 (for SFTP) or 21 (for FTP)
   ```

3. Upload these directories to `public_html`:
   - `index.php` → root
   - `core/` → root
   - `.htaccess` → root (if using Apache)

4. File permissions after upload:
   ```
   Directories: 755
   Files: 644
   Exceptions:
   - storage/* : 755
   - bootstrap/cache/* : 755
   - .env : 600
   ```

### 4.3 Upload Via SSH (Recommended - Faster)

```bash
# Connect to server
ssh username@your.hosting.com

# Navigate to public_html
cd ~/public_html

# Upload files (from your local machine, run this):
# Create a compressed file first
tar -czf agco.tar.gz ./

# Then via SFTP or SCP, upload the tar file
# Then extract on server:
tar -xzf agco.tar.gz
rm agco.tar.gz
```

---

## STEP 5: LARAVEL CONFIGURATION

### 5.1 Create .env File
1. On shared hosting, create file: `/home/username/public_html/core/.env`

2. Copy from `.env.example` and update:

```dotenv
APP_NAME="AGCO"
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Performance
DEBUGBAR_ENABLED=false

# Logging
LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=notice

# Database
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=agcoweb_live
DB_USERNAME=agco_user
DB_PASSWORD=YOUR_DB_PASSWORD

# Cache & Session
BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=your.mailserver.com
MAIL_PORT=587
MAIL_USERNAME=your_email@yourdomain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"

# Pusher (if using)
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=

# AWS (if using S3 for storage)
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
```

### 5.2 Generate Application Key
```bash
# SSH into server
cd ~/public_html/core

# Generate key (if not already in .env)
php artisan key:generate

# This updates your .env file with APP_KEY
```

### 5.3 Fix Directory Permissions
```bash
# SSH into server
cd ~/public_html

# Set storage permissions
chmod -R 755 core/storage
chmod -R 755 core/bootstrap/cache

# Set .env permissions
chmod 600 core/.env

# Set config file permissions
chmod 644 core/config/*
```

---

## STEP 6: OPTIMIZE LARAVEL FOR PRODUCTION

### 6.1 Run Required Artisan Commands
```bash
# SSH into server
cd ~/public_html/core

# Clear caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations (if needed)
php artisan migrate --force

# Seed data (if needed)
php artisan db:seed --class=YourSeeder --force
```

### 6.2 Composer Install/Update
```bash
# SSH into server
cd ~/public_html/core

# If not already done
composer install --optimize-autoloader --no-dev
```

---

## STEP 7: WEB SERVER CONFIGURATION

### 7.1 Apache (.htaccess)
Create/update `/public_html/.htaccess`:

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

### 7.2 Nginx (nginx.conf)
If using Nginx, configure server block:

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /home/username/public_html;
    index index.php index.html index.htm;

    # Redirect HTTP to HTTPS
    if ($scheme != "https") {
        return 301 https://$server_name$request_uri;
    }

    # SSL Certificate (Let's Encrypt)
    listen 443 ssl;
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

---

## STEP 8: CRITICAL SECURITY CONFIGURATION

### 8.1 Protect Sensitive Files
Create `.htaccess` in `core/` directory:

```apache
<Files .env>
    Deny from all
</Files>

<Files .env.example>
    Deny from all
</Files>

<FilesMatch "^\.">
    Deny from all
</FilesMatch>
```

### 8.2 Disable Directory Listing
Add to main `.htaccess`:
```apache
Options -Indexes
```

### 8.3 Security Headers
Add to `.htaccess`:
```apache
# Security Headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "no-referrer-when-downgrade"
</IfModule>
```

### 8.4 PHP Security Settings
If you can modify php.ini or .htaccess:

```apache
php_flag display_errors Off
php_flag log_errors On
php_value error_log /home/username/public_html/logs/php_errors.log
php_value upload_max_filesize 50M
php_value post_max_size 50M
```

---

## STEP 9: EMAIL CONFIGURATION

### 9.1 Setup Mail Service
**Option A: Using Hosting Mail Server**
```env
MAIL_MAILER=smtp
MAIL_HOST=mail.yourdomain.com
MAIL_PORT=465 (or 587)
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=ssl (or tls)
```

**Option B: Using Third-Party Service**
- SendGrid
- Mailjet  
- AWS SES
- etc.

Update `.env` accordingly.

### 9.2 Test Mail Configuration
```bash
# SSH into server
cd ~/public_html/core

# Test mail sending
php artisan tinker
Mail::raw('Test email', function($m) { $m->to('your-email@example.com')->subject('Test'); });
exit()
```

---

## STEP 10: CRON JOBS (Task Scheduling)

### 10.1 Add Cron Job
1. Login to cPanel
2. Go to "Cron Jobs"
3. Add new cron job:

```bash
* * * * * /usr/bin/php /home/username/public_html/core/artisan schedule:run >> /home/username/public_html/logs/cron.log 2>&1
```

This runs Laravel's task scheduler every minute.

### 10.2 Create Logs Directory
```bash
mkdir -p ~/public_html/logs
chmod 755 ~/public_html/logs
```

---

## STEP 11: TESTING & VERIFICATION

### 11.1 Test Site Access
1. Open your browser
2. Navigate to `https://yourdomain.com`
3. Check that:
   - Site loads without errors
   - HTTPS works
   - SSL certificate is valid
   - Images load correctly
   - Database connections work

### 11.2 Check Error Logs
```bash
# View PHP errors
tail -f ~/public_html/logs/php_errors.log

# View Laravel logs
tail -f ~/public_html/core/storage/logs/laravel-*.log

# View cron logs
tail -f ~/public_html/logs/cron.log
```

### 11.3 Test Key Features
- [ ] User login/registration
- [ ] Database queries
- [ ] File uploads
- [ ] Email sending
- [ ] Payment processing (if applicable)
- [ ] API endpoints (if applicable)

---

## STEP 12: PERFORMANCE OPTIMIZATION

### 12.1 Enable Caching
```bash
# SSH into server
cd ~/public_html/core

# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize class loader
composer dump-autoload --optimize
```

### 12.2 Enable Gzip Compression
Add to `.htaccess`:
```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE text/javascript
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/json
    AddOutputFilterByType DEFLATE image/svg+xml
</IfModule>
```

### 12.3 Browser Caching
Add to `.htaccess`:
```apache
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

---

## STEP 13: DATABASE BACKUP & RECOVERY

### 13.1 Auto Backup Setup
```bash
# Create backup script
cat > ~/backup.sh << 'EOF'
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u agco_user -p agcoweb_live > ~/backups/agcoweb_$DATE.sql
gzip ~/backups/agcoweb_$DATE.sql
EOF

chmod +x ~/backup.sh
```

### 13.2 Add Backup to Cron
In cPanel Cron Jobs:
```bash
0 2 * * * /home/username/backup.sh
```
(Runs daily at 2 AM)

### 13.3 Manual Backup
```bash
# Via SSH
cd ~/public_html/core
php artisan backup:run

# Or manual MySQL dump
mysqldump -u agco_user -p agcoweb_live > ~/backups/manual_backup.sql
```

---

## STEP 14: MONITORING & MAINTENANCE

### 14.1 Monitor Error Logs
- Check daily: `~/public_html/core/storage/logs/`
- Fix any errors immediately
- Monitor disk space usage

### 14.2 Update Dependencies Regularly
```bash
# Check for updates
cd ~/public_html/core
composer outdated

# Update packages (carefully test on staging first)
composer update
```

### 14.3 Security Updates
- Keep PHP updated
- Keep Laravel updated
- Keep dependencies updated
- Monitor security advisories

---

## STEP 15: TROUBLESHOOTING COMMON ISSUES

### Issue: 500 Internal Server Error
**Solution:**
1. Check error logs: `~/public_html/core/storage/logs/laravel-*.log`
2. Enable debug mode temporarily in `.env`: `APP_DEBUG=true`
3. Check file permissions (should be 644/755)
4. Verify database connection
5. Check PHP version compatibility

### Issue: Database Connection Failed
**Solution:**
1. Verify `DB_HOST`, `DB_USERNAME`, `DB_PASSWORD` in `.env`
2. Test connection: `php artisan tinker` then `DB::connection()->getPdo();`
3. Check if database user has all privileges
4. Verify MySQL is running on shared hosting

### Issue: Images/Assets Not Loading
**Solution:**
1. Check `.htaccess` rewrite rules
2. Verify file permissions (644 for files)
3. Check storage symlink: `php artisan storage:link`
4. Clear cache: `php artisan cache:clear`

### Issue: Emails Not Sending
**Solution:**
1. Verify MAIL_* settings in `.env`
2. Test SMTP connection
3. Check if mail server is active
4. Review Laravel logs for errors
5. Whitelist domain in mail server

### Issue: Slow Site Performance
**Solution:**
1. Enable caching: `php artisan config:cache`
2. Check database query performance
3. Enable gzip compression
4. Use CDN for static assets
5. Monitor CPU/memory usage

---

## QUICK REFERENCE CHECKLIST

```
Pre-Deployment:
☐ Check hosting PHP version (8.0.2+)
☐ Verify required extensions installed
☐ Obtain SSH access
☐ Create SSL certificate

Database:
☐ Create MySQL database
☐ Create database user
☐ Import backup/data
☐ Verify tables and data

Files:
☐ Upload all project files
☐ Set correct permissions (644/755)
☐ Verify .env file exists
☐ Verify core/ directory structure

Configuration:
☐ Create and configure .env
☐ Generate APP_KEY
☐ Configure database credentials
☐ Configure mail settings
☐ Setup security headers

Optimization:
☐ Run composer install
☐ Cache configuration
☐ Cache routes
☐ Cache views
☐ Enable gzip compression

Testing:
☐ Test site access (HTTPS)
☐ Test database connection
☐ Test email sending
☐ Test file uploads
☐ Review error logs

Cron & Monitoring:
☐ Setup cron job
☐ Create logs directory
☐ Setup backup script
☐ Monitor error logs
```

---

## SUPPORT & HELP

If you encounter issues:

1. **Check logs first:**
   - Laravel logs: `core/storage/logs/laravel-*.log`
   - PHP errors: Check cPanel error log
   - Cron logs: `logs/cron.log`

2. **Test database:**
   ```bash
   php artisan tinker
   DB::connection()->getPdo(); // Should not error
   ```

3. **Common commands:**
   ```bash
   # Clear all caches
   php artisan cache:clear
   
   # Run migrations
   php artisan migrate --force
   
   # Seed database
   php artisan db:seed --force
   ```

4. **Restart services** (contact hosting support if needed)

---

**Deployment Status: READY TO DEPLOY**

Follow each step carefully and your site will be live perfectly! 🎉
