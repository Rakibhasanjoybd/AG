# DEPLOYMENT CHECKLIST & QUICK START

## IMMEDIATE ACTION ITEMS (Do These First)

### 1. PREPARE YOUR .ENV FILE
Create a file locally with your shared hosting credentials:

```
Location: core/.env (on shared hosting)

Template:
---
APP_NAME="AGCO"
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://yourdomain.com
DEBUGBAR_ENABLED=false

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=notice

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=agcoweb_live
DB_USERNAME=agco_user
DB_PASSWORD=YOUR_DB_PASSWORD

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=mail.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="AGCO"
---
```

### 2. EXPORT YOUR DATABASE
```bash
# Local command to backup database
mysqldump -u root -p agco > agco_backup.sql

# Then upload agco_backup.sql to shared hosting
```

### 3. UPLOAD FILES
Use FileZilla SFTP:
- Host: your.hosting.com
- Username: cPanel username
- Password: cPanel password
- Port: 22 (SFTP)

Upload to `public_html/`:
- `index.php`
- `core/` directory (entire folder)

---

## 15-MINUTE DEPLOYMENT PLAN

**Assuming you have:**
- ✅ Hosting account with SSH access
- ✅ Domain pointed to hosting
- ✅ SSL certificate installed
- ✅ PHP 8.0.2+ installed
- ✅ MySQL database created

### MINUTE 1-3: SSH INTO SERVER
```bash
# Open terminal/PowerShell and SSH in
ssh cpanel_username@your.hosting.com

# Password: your cPanel password
# Accept the key (yes)

# Navigate to public_html
cd ~/public_html
```

### MINUTE 4-5: UPLOAD & EXTRACT
```bash
# If files not uploaded yet via SFTP
# Assuming you uploaded a compressed file:
tar -xzf agco.tar.gz
rm agco.tar.gz

# Or if uploading via SFTP (do this first in FileZilla)
# Then continue here...
```

### MINUTE 6-8: SET PERMISSIONS
```bash
# Critical permissions
chmod -R 755 core/storage
chmod -R 755 core/bootstrap/cache
chmod 600 core/.env
chmod 644 .htaccess

# Create logs directory
mkdir -p logs
chmod 755 logs
```

### MINUTE 9-10: GENERATE APP KEY
```bash
cd core

# If APP_KEY not in .env, generate it:
php artisan key:generate
```

### MINUTE 11-12: IMPORT DATABASE
```bash
# Assuming you uploaded agco_backup.sql to public_html
cd ~/public_html
mysql -u agco_user -p agcoweb_live < agco_backup.sql
# (Enter your database password)
```

### MINUTE 13-14: CACHE & OPTIMIZE
```bash
cd core

# Cache everything
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize
```

### MINUTE 15: TEST
```bash
# Exit SSH
exit

# Open browser
https://yourdomain.com

# Check if site loads
```

---

## COMMANDS REFERENCE FOR SSH

### DATABASE
```bash
# Import database
mysql -u agco_user -p agcoweb_live < backup.sql

# Backup database
mysqldump -u agco_user -p agcoweb_live > backup.sql

# Access MySQL
mysql -u agco_user -p agcoweb_live
```

### LARAVEL COMMANDS
```bash
cd ~/public_html/core

# Cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Migrations & Seeding
php artisan migrate --force
php artisan db:seed --force

# Storage link (for uploaded files)
php artisan storage:link

# Check Laravel version
php artisan --version

# Tinker (test database)
php artisan tinker
```

### FILE PERMISSIONS
```bash
# View permissions
ls -la

# Change to 644 (files)
chmod 644 filename

# Change to 755 (directories)
chmod 755 directoryname

# Recursive for directories
chmod -R 755 core/storage

# Change owner (if needed)
chown -R username:username ~/public_html
```

### FILE OPERATIONS
```bash
# Create directory
mkdir directoryname

# Upload file (from local to server)
scp localfile username@host:~/public_html/

# Download file (from server to local)
scp username@host:~/public_html/file .

# Create file
touch filename
cat > filename << 'EOF'
file content here
EOF

# Edit file
nano filename  # then Ctrl+X to save

# Delete
rm filename
rm -r directoryname  # recursive
```

### SERVER INFO
```bash
# Check disk space
df -h

# Check file size
du -sh directory

# Check PHP version
php -v

# Check PHP extensions
php -m

# Check installed packages
composer show

# Verify database connection
php artisan tinker
DB::connection()->getPdo();
exit()
```

---

## FILE STRUCTURE YOU SHOULD HAVE

After deployment, your `public_html` should look like:

```
/home/username/public_html/
├── index.php                 (from root)
├── .htaccess                 (from root)
├── mix-manifest.json         (optional)
├── manifest.json             (optional)
├── sw.js                     (optional)
├── core/                     (entire Laravel app)
│   ├── .env                  (YOU CREATE THIS)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── routes/
│   ├── resources/
│   ├── storage/              (755 permissions)
│   ├── vendor/
│   ├── composer.json
│   ├── artisan
│   └── ...
├── public/                   (if used)
│   ├── css/
│   ├── js/
│   └── ...
├── logs/                     (create if not exists, 755)
│   ├── php_errors.log
│   ├── cron.log
│   └── laravel-*.log
└── assets/                   (if exists)
```

---

## .HTACCESS FOR APACHE (ROOT)

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

# Disable Directory Listing
Options -Indexes

# Security Headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-XSS-Protection "1; mode=block"
</IfModule>

# PHP Settings
php_flag display_errors Off
php_flag log_errors On
php_value upload_max_filesize 50M
php_value post_max_size 50M
```

---

## CRON JOB SETUP (cPanel)

1. Login to cPanel
2. Find "Cron Jobs"
3. Add new Cron Job:

```
Min: *
Hour: *
Day: *
Month: *
Weekday: *
Command: /usr/bin/php /home/username/public_html/core/artisan schedule:run >> /home/username/public_html/logs/cron.log 2>&1
```

This runs every minute automatically.

---

## MOST COMMON ERRORS & FIXES

### Error: 500 Internal Server Error
```bash
# Check error log
tail -f ~/public_html/core/storage/logs/laravel-*.log

# Check if APP_KEY is set
grep APP_KEY ~/public_html/core/.env

# Fix permissions
chmod -R 755 ~/public_html/core/storage
```

### Error: Database Connection Refused
```bash
# Verify .env credentials
cat ~/public_html/core/.env

# Test connection
php artisan tinker
DB::connection()->getPdo();
exit()

# Verify database exists
mysql -u agco_user -p
SHOW DATABASES;
```

### Error: File Permissions Issues
```bash
# Fix all permissions
chmod -R 755 ~/public_html/core/storage
chmod -R 755 ~/public_html/core/bootstrap/cache
chmod 600 ~/public_html/core/.env
chmod -R 644 ~/public_html/core/config
```

### Error: Composer Command Not Found
```bash
# Check if composer is installed
which composer

# If not installed, or use:
php composer.phar install
```

---

## FINAL CHECKLIST BEFORE GOING LIVE

- [ ] Domain points to hosting
- [ ] SSL certificate installed (HTTPS working)
- [ ] Database created and imported
- [ ] Files uploaded with correct permissions
- [ ] .env file created with correct credentials
- [ ] APP_KEY generated in .env
- [ ] Site loads without errors (https://yourdomain.com)
- [ ] Login works
- [ ] Database queries work
- [ ] Email configuration tested
- [ ] Cron job set up
- [ ] Error logs are empty/healthy
- [ ] All caches cleared and recached
- [ ] Site appears fast and responsive
- [ ] Images/assets load correctly

---

## AFTER LAUNCH TASKS

1. **Monitor for 48 hours**
   - Check error logs daily
   - Test all features
   - Monitor email sending
   - Watch database performance

2. **Setup Monitoring**
   - Use cPanel uptime monitor
   - Setup Google Search Console
   - Setup Google Analytics

3. **Backup Strategy**
   - Setup automatic daily backups
   - Download backup copies locally
   - Test restore process

4. **Regular Maintenance**
   - Check for PHP/Laravel updates monthly
   - Review error logs weekly
   - Monitor disk space
   - Test backup restoration quarterly

---

## NEED HELP?

Common support channels:
1. **Hosting Support**: Contact your hosting provider (cPanel, WHM, etc.)
2. **Laravel Docs**: https://laravel.com/docs
3. **PHP Docs**: https://www.php.net/docs.php
4. **Check Logs First**: Always check `core/storage/logs/laravel-*.log`

---

**Your site will be live when you see it loading at https://yourdomain.com without errors! 🚀**
