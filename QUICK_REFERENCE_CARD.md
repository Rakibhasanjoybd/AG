# ⚡ QUICK REFERENCE CARD - PRINT & KEEP HANDY

## 📋 ESSENTIAL INFORMATION TO SAVE

```
YOUR HOSTING DETAILS:
├─ Domain: ___________________________
├─ Hosting Provider: __________________
├─ cPanel URL: ________________________
├─ cPanel Username: ____________________
├─ cPanel Password: ____________________
├─ Email: _____________________________
│
DATABASE:
├─ Database Name: _____________________
├─ Database Host: localhost
├─ Database Username: _________________
├─ Database Password: _________________
├─ MySQL Port: 3306
│
SSH/SFTP:
├─ Host: _____________________________
├─ Username: __________________________
├─ Password: ___________________________
├─ Port: 22 (SSH) or 21 (FTP)
│
EMAIL/SMTP:
├─ Mail Host: __________________________
├─ Mail Port: 465 or 587
├─ Mail Username: _______________________
├─ Mail Password: _______________________
└─ Encryption: SSL or TLS
```

---

## 🚀 DEPLOYMENT EXECUTION CHECKLIST

### PREPARATION (Before Upload)
```
☐ Backup database: mysqldump -u root -p agco > backup.sql
☐ Read DEPLOYMENT_CHECKLIST_QUICK_START.md
☐ Have .env file ready with credentials
☐ Have database backup ready
☐ Test SFTP connection works
☐ Verify SSH access available
```

### UPLOAD FILES
```
☐ Connect SFTP (FileZilla):
    Host: your.hosting.com:22
    Username: cPanel username
    Password: cPanel password
☐ Upload to: public_html/
    ├─ index.php
    ├─ .htaccess
    └─ core/ (entire directory)
☐ Verify upload complete
```

### SSH INTO SERVER
```
☐ ssh username@your.hosting.com
   (or ssh username@server-ip)
☐ Password: (your cPanel password)
☐ cd ~/public_html
```

### SET PERMISSIONS
```
☐ chmod -R 755 core/storage
☐ chmod -R 755 core/bootstrap/cache
☐ chmod 600 core/.env
☐ chmod 644 .htaccess
```

### CREATE .ENV
```
☐ Edit: core/.env
☐ Update:
    APP_URL=https://yourdomain.com
    DB_HOST=localhost
    DB_DATABASE=agcoweb_live
    DB_USERNAME=agco_user
    DB_PASSWORD=your_db_password
    MAIL_HOST=mail.yourdomain.com
    MAIL_USERNAME=your-email
```

### GENERATE KEY
```
☐ cd core
☐ php artisan key:generate
```

### IMPORT DATABASE
```
☐ mysql -u agco_user -p agcoweb_live < backup.sql
☐ (Enter database password when prompted)
```

### OPTIMIZE
```
☐ php artisan config:cache
☐ php artisan route:cache
☐ php artisan view:cache
```

### SETUP CRON
```
☐ Go to cPanel → Cron Jobs
☐ Add: /usr/bin/php /home/username/public_html/core/artisan schedule:run >> /home/username/public_html/logs/cron.log 2>&1
```

### TEST
```
☐ Open browser
☐ Go to: https://yourdomain.com
☐ Check that site loads
☐ No errors in browser
```

---

## 🔧 ESSENTIAL SSH COMMANDS

### Database
```bash
# Import database
mysql -u username -p database_name < backup.sql

# Backup database
mysqldump -u username -p database_name > backup.sql

# Access MySQL
mysql -u username -p
```

### Laravel
```bash
# Generate key (if needed)
php artisan key:generate

# Cache everything
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Run migrations
php artisan migrate --force

# Seed database
php artisan db:seed --force
```

### File Permissions
```bash
# View permissions
ls -la

# Change permissions (644 for files)
chmod 644 filename

# Change permissions (755 for dirs)
chmod 755 directory

# Recursive (755 for storage)
chmod -R 755 core/storage

# Change ownership
chown -R username:username ~/public_html
```

### File Operations
```bash
# Create directory
mkdir logs

# View file
cat filename
nano filename    # Edit file (Ctrl+X to save)

# Copy file
cp source destination

# Delete
rm filename
rm -r directory

# File size
du -sh directory

# Disk space
df -h
```

### Debugging
```bash
# View Laravel logs
tail -f core/storage/logs/laravel-*.log

# View error logs
tail -f ~/public_html/logs/php_errors.log

# View cron logs
tail -f logs/cron.log

# Test database connection
php artisan tinker
DB::connection()->getPdo();
exit()
```

---

## ⚠️ COMMON ERRORS & QUICK FIXES

### Error: 500 Internal Server Error
```
FIX:
1. tail -f core/storage/logs/laravel-*.log
2. Read actual error message
3. Common causes:
   - DB credentials wrong
   - .env file missing
   - file permissions wrong
```

### Error: Database Connection Failed
```
FIX:
1. Check .env credentials match
2. php artisan tinker
3. DB::connection()->getPdo();
4. Verify database exists & has data
```

### Error: Permission Denied
```
FIX:
chmod -R 755 core/storage
chmod -R 755 core/bootstrap/cache
chmod 600 core/.env
```

### Error: File Not Found (404)
```
FIX:
1. Check .htaccess exists
2. Check .htaccess has correct rewrite rules
3. Verify mod_rewrite enabled
4. php artisan cache:clear
```

### Site Very Slow
```
FIX:
1. php artisan config:cache
2. php artisan route:cache
3. Add gzip to .htaccess
4. Check database queries
```

---

## 📁 FILE STRUCTURE YOU NEED

```
After upload, your public_html should look like:
├── index.php           (from root)
├── .htaccess           (Apache)
├── manifest.json       (optional)
├── core/               (Laravel app)
│   ├── .env            (YOU CREATE)
│   ├── app/
│   ├── config/
│   ├── routes/
│   ├── storage/        (755)
│   ├── vendor/
│   ├── artisan
│   └── composer.json
├── logs/               (755, you create)
│   ├── php_errors.log
│   └── cron.log
└── assets/             (if exists)
```

---

## 🔐 CRITICAL SECURITY SETTINGS

```
.env File:
APP_ENV=production
APP_DEBUG=false

.htaccess (Root):
# Disable directory listing
Options -Indexes

# Protect .env
<Files .env>
    Deny from all
</Files>

# Security headers
Header always set X-Content-Type-Options "nosniff"
Header always set X-Frame-Options "SAMEORIGIN"
```

---

## 📊 HOSTING PROVIDER QUICK LINKS

```
Bluehost:      https://www.bluehost.com ($2.95)
SiteGround:    https://www.siteground.com ($2.99+)
Hostinger:     https://www.hostinger.com ($2.99)
Namecheap:     https://www.namecheap.com ($2.88)
GoDaddy:       https://www.godaddy.com ($4.99)
DreamHost:     https://www.dreamhost.com ($4.95)
```

---

## ✅ FINAL CHECKLIST

```
DEPLOYMENT COMPLETE WHEN:
✓ Site loads at https://yourdomain.com
✓ No 500 errors
✓ Login works
✓ Database queries execute
✓ Images load
✓ Error logs clean
✓ Performance acceptable
✓ All features work
```

---

## 🎯 QUICK DECISION TREE

```
I get 500 error
→ Check logs: tail -f core/storage/logs/laravel-*.log
→ Fix what error says

I can't connect to database
→ Check .env credentials match
→ Verify database exists
→ Verify user has privileges

My site is slow
→ php artisan config:cache
→ Check database performance

Files won't upload
→ Check file permissions
→ Check disk space

Nothing loads
→ Check if files uploaded correctly
→ Verify .htaccess exists
→ Check PHP version
```

---

## 📞 WHEN TO GET HELP

**Contact Hosting Support When:**
- PHP/server configuration issues
- Can't create database
- SSH access problems
- SSL certificate issues
- Disk space/resource limits

**Check Logs First When:**
- 500 error
- Database connection error
- Feature not working
- Emails not sending

**Read Guides When:**
- Not sure what to do
- Need detailed steps
- Want to understand process

---

## ⏱️ TIME ESTIMATES

```
Setup hosting account:      30-60 minutes
Upload files via SFTP:      10-30 minutes
SSH setup & config:         10-20 minutes
Database import:            5-15 minutes
Testing & troubleshooting:  15-30 minutes
━━━━━━━━━━━━━━━━━━━━━━━━━
TOTAL:                      1.5-2.5 hours
```

---

## 💾 BACKUP STRATEGY

```
BEFORE DEPLOYMENT:
☐ Backup local database
☐ Backup all project files
☐ Keep in safe location
☐ Test backup restoration

AFTER DEPLOYMENT:
☐ Setup automatic backups (daily)
☐ Download copies locally weekly
☐ Test restore process monthly
☐ Monitor backup storage space
```

---

## 🚀 YOU'RE READY!

This quick reference has:
✅ All essential commands
✅ Quick error fixes
✅ Hosting provider links
✅ Complete checklist
✅ File structures

**Print this page and keep it handy during deployment!**

---

**Need detailed help? Read the full deployment guides:**
- DEPLOYMENT_START_HERE.md
- SHARED_HOSTING_DEPLOYMENT_GUIDE.md
- DEPLOYMENT_CHECKLIST_QUICK_START.md
