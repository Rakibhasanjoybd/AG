# SPECIFIC HOSTING PROVIDER GUIDES

## Popular Shared Hosting Providers Setup

### 1. BLUEHOST (Most Popular & WordPress-Friendly)

#### Create Account
1. Visit: https://www.bluehost.com
2. Choose your plan
3. Enter domain name
4. Complete payment

#### Initial Setup
1. Login to cPanel at: `https://your-domain.com/cpanel`
2. Or: `https://your-ip:2083`

#### Setup Steps
```
1. AUTO SSL (SSL/TLS)
   - Click "AutoSSL"
   - It's usually already installed
   - If not, click "Manage" → "Issue"

2. CREATE MYSQL DATABASE
   - Go to "MySQL Databases"
   - Database name: agcoweb_live
   - Username: agco_user (auto-prefixed)
   - Password: [Generate 16+ chars]
   - Create Database & User
   - Note all credentials

3. UPLOAD FILES
   - Use "File Manager" in cPanel
   - Or use SFTP (FileZilla)
   - Upload to: public_html/

4. CONFIGURE PHP
   - Go to "MultiPHP Manager"
   - Select your domain
   - Choose PHP 8.1 or 8.2
   - Check Extensions tab for required extensions

5. SETUP CRON JOB
   - Go to "Cron Jobs"
   - Add: /usr/bin/php /home/username/public_html/core/artisan schedule:run >> /home/username/public_html/logs/cron.log 2>&1
```

#### Bluehost SSH Access
```bash
# Connect via SSH (if enabled)
ssh username@your-domain.com

# Or use their Terminal in cPanel
# Go to: Advanced → Terminal
```

#### Bluehost Mail Setup
```env
# Bluehost usually provides:
MAIL_MAILER=smtp
MAIL_HOST=mail.yourdomain.com
MAIL_PORT=465 or 587
MAIL_USERNAME=your-email@yourdomain.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=ssl or tls
```

---

### 2. SiteGround (Premium Quality)

#### Create Account
1. Visit: https://www.siteground.com
2. Choose plan (StartUp, GrowBig, GoGeek)
3. Register domain or point existing domain

#### Initial Setup
1. Login to cPanel at: `https://cpanel.your-domain.com` or IP

#### Setup Steps
```
1. SITE TOOLS
   - Go to "Site Tools" for the domain
   - Or use traditional cPanel

2. SSL/TLS
   - Scroll to "SSL/TLS"
   - Click "Manage" for your domain
   - Usually auto-installed (Let's Encrypt)

3. MYSQL DATABASES
   - Under "DATABASES"
   - Click "MySQL Databases"
   - Create database: agcoweb_live
   - Create user with password
   - Add user to database with ALL privileges

4. FILE MANAGER
   - Upload to: /home/yourdomain.com/public_html/
   - Or use SFTP for faster upload

5. PHP VERSION
   - Click "PHP Settings"
   - Select PHP 8.1 or 8.2
   - Check extensions enabled

6. CRON JOBS
   - Under "ADVANCED"
   - Click "Cron Jobs"
   - Add cron job
```

#### SiteGround SSH Access
```bash
# SiteGround requires SSH key setup
# 1. Generate SSH key in Site Tools
# 2. Or use SFTP credentials for SCP
ssh username@IP_ADDRESS
```

#### SiteGround Mail
```env
MAIL_MAILER=smtp
MAIL_HOST=mail.yourdomain.com
MAIL_PORT=465
MAIL_USERNAME=your-email@yourdomain.com
MAIL_PASSWORD=email-password
MAIL_ENCRYPTION=ssl
```

---

### 3. HOSTINGER (Budget-Friendly)

#### Create Account
1. Visit: https://www.hostinger.com
2. Choose plan (Premium, Business, etc.)
3. Complete payment

#### Initial Setup
1. Access hPanel at: https://hpanel.hostinger.com
2. Select your domain

#### Setup Steps
```
1. WORDPRESS/CMS
   - This is WordPress-focused, but can use for Laravel
   - May need to disable auto-WordPress installation

2. SSL
   - Usually auto-installed via Let's Encrypt
   - Check in: "Manage" → "SSL/TLS"

3. CREATE DATABASE
   - Go to "Databases"
   - Click "Create Database"
   - Name: agcoweb_live
   - User: agco_user
   - Password: [generate strong password]
   - Assign privileges

4. FILE MANAGER
   - Upload via File Manager
   - Or use SFTP/SSH

5. PHP VERSION
   - Go to "Settings"
   - Select PHP 8.1 or 8.2
   - Verify extensions

6. CRON JOBS
   - In "Tools"
   - Add cron job
```

#### Hostinger SSH
```bash
# SSH access details in hPanel
# Usually provided in Account Details
ssh u123456@yourdomain.com
```

#### Hostinger Mail
```env
# Check with your domain email provider
MAIL_MAILER=smtp
MAIL_HOST=mail.yourdomain.com
MAIL_PORT=465 or 587
MAIL_USERNAME=your-email@yourdomain.com
MAIL_PASSWORD=email-password
MAIL_ENCRYPTION=ssl or tls
```

---

### 4. NAMECHEAP HOSTING (Domain + Hosting Bundle)

#### Create Account
1. Visit: https://www.namecheap.com
2. Choose hosting plan
3. Set up domain

#### Initial Setup
1. Login to cPanel: `https://cpanel.yourdomain.com`
2. Or IP-based access

#### Setup Steps
```
1. SSL/TLS
   - Click "SSL/TLS Status"
   - Should show free AutoSSL
   - If not, click to install

2. MySQL DATABASES
   - Go to "MySQL Databases"
   - Create: agcoweb_live
   - User: agco_user
   - Password: [strong password]

3. FILE UPLOAD
   - Use File Manager or SFTP
   - Upload to public_html/

4. PHP VERSION
   - Select PHP in "MultiPHP Manager"
   - Choose 8.1 or 8.2

5. CRON JOBS
   - Add scheduled task
```

#### Namecheap SSH
```bash
# SFTP/SSH usually available
ssh cpanel_user@your-ip
# Password is your cPanel password
```

#### Namecheap Mail
```env
# Use Namecheap's mail service
MAIL_MAILER=smtp
MAIL_HOST=mail.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=your-email@yourdomain.com
MAIL_PASSWORD=email-password
MAIL_ENCRYPTION=tls
```

---

### 5. GODADDY HOSTING (Integrated with Domain)

#### Create Account
1. Visit: https://www.godaddy.com
2. Buy hosting + domain or use existing

#### Initial Setup
1. Go to cPanel: `https://cpanel.your-domain.com`
2. Username: usually your GoDaddy username

#### Setup Steps
```
1. SSL (FREE WITH GODADDY)
   - Click "SSL/TLS"
   - Already installed (Let's Encrypt)

2. MYSQL DATABASES
   - "MySQL Databases"
   - Create database: agcoweb_live
   - Create user: agco_user
   - Assign privileges

3. UPLOAD FILES
   - File Manager in cPanel
   - Or FTP via FileZilla

4. PHP
   - Select appropriate PHP version
   - Usually 8.0+ available

5. CRON
   - Add cron job for schedule:run
```

#### GoDaddy SSH
```bash
# May require enabling
# Go to Security → SSH Keys
# Then: ssh username@your-ip
```

#### GoDaddy Mail
```env
# Use GoDaddy's business email
MAIL_MAILER=smtp
MAIL_HOST=pop.godaddy.com or smtp.godaddy.com
MAIL_PORT=465 or 587
MAIL_USERNAME=your-email@yourdomain.com
MAIL_PASSWORD=email-password
MAIL_ENCRYPTION=ssl or tls
```

---

### 6. DREAMHOST (Developer-Friendly)

#### Create Account
1. Visit: https://www.dreamhost.com
2. Choose plan
3. Setup domain

#### Initial Setup
1. Login to Panel: https://panel.dreamhost.com
2. Navigate to Web Hosting

#### Setup Steps
```
1. MANAGE DOMAIN
   - Click "Manage" next to domain
   - Go to "Database" tab
   - Create MySQL: agcoweb_live
   - Create user: agco_user
   - Add user to database

2. SSL
   - Usually auto-generated (Let's Encrypt)
   - Check "Domain Management" → "SSL"

3. FILE UPLOAD
   - Use SFTP or panel File Manager
   - Upload to: /home/username/yourdomain.com/

4. SSH (Recommended)
   - Go to "Users" → manage user
   - Ensure SSH access enabled
   - Connect with credentials

5. CRON
   - Go to "Cron Jobs"
   - Add: /usr/bin/php /home/username/yourdomain.com/core/artisan schedule:run
```

#### DreamHost SSH
```bash
# SSH enabled by default
ssh username@your-domain.com
# Or: ssh username@server.dreamhost.com
```

#### DreamHost Mail
```env
MAIL_MAILER=smtp
MAIL_HOST=mail.yourdomain.com
MAIL_PORT=465 (SSL)
MAIL_USERNAME=your-email@yourdomain.com
MAIL_PASSWORD=email-password
MAIL_ENCRYPTION=ssl
```

---

## UNIVERSAL SHARED HOSTING GUIDE

### If Your Provider is Not Listed Above

#### 1. First Login
- Check your hosting welcome email
- Find cPanel URL (usually `your-domain.com/cpanel` or IP:2083)
- Username & password provided

#### 2. Essential Information You Need
```
☐ cPanel username & password
☐ MySQL username & password (or ability to create)
☐ FTP/SFTP username & password
☐ SSH access (if available)
☐ Available PHP version
☐ Installed PHP extensions
☐ Server mail host/SMTP details
```

#### 3. Universal Setup Steps
```bash
# 1. Create Database
#    Go to "MySQL Databases" or similar
#    Database: agcoweb_live
#    User: agco_user
#    Password: [strong]

# 2. Upload Files
#    Via SFTP: FileZilla to public_html/
#    Or cPanel File Manager

# 3. SSH In (if available)
#    ssh username@your-domain.com
#    or ssh username@server-ip

# 4. Set Permissions
cd ~/public_html
chmod -R 755 core/storage
chmod -R 755 core/bootstrap/cache
chmod 600 core/.env

# 5. Generate App Key
cd core
php artisan key:generate

# 6. Import Database
mysql -u agco_user -p agcoweb_live < database.sql

# 7. Cache & Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 8. Add Cron Job
# Via cPanel Cron Jobs interface:
# /usr/bin/php /home/username/public_html/core/artisan schedule:run >> /home/username/public_html/logs/cron.log 2>&1
```

---

## COMPARISON CHART

| Provider | Starting Price | SSH | Free SSL | PHP 8+ | Recommendation |
|----------|---|---|---|---|---|
| Bluehost | $2.95/mo | Yes | Yes | Yes | Great for beginners |
| SiteGround | $2.99/mo | Yes | Yes | Yes | Best performance |
| Hostinger | $2.99/mo | Yes | Yes | Yes | Best value |
| Namecheap | $2.88/mo | Yes | Yes | Yes | Domain bundle |
| GoDaddy | $4.99/mo | Limited | Yes | Yes | Domain integration |
| DreamHost | $4.95/mo | Yes | Yes | Yes | Developer friendly |

---

## TROUBLESHOOTING BY PROVIDER

### Bluehost Issues
```
PHP Version Not Working?
→ Go to MultiPHP Manager
→ Uncheck conflicting versions
→ Select ONLY one PHP version

Cron Not Running?
→ Check in "Cron Jobs" if enabled
→ Verify command syntax
→ Check logs in your domain folder
```

### SiteGround Issues
```
File Permissions?
→ Use Site Tools → File Manager
→ Right-click file → Change Permissions
→ Directories: 755, Files: 644

Database Access?
→ Check credentials in Site Tools
→ May need to allow from any host
```

### Hostinger Issues
```
SSH Connection Fails?
→ Enable SSH in hPanel → Settings
→ May require SSH key setup
→ Check Account Details for credentials

PHP Extensions Missing?
→ Go to Settings → PHP Configuration
→ Enable required extensions
```

### GoDaddy Issues
```
SSL Not Working?
→ Force HTTPS in .htaccess
→ May need manual installation
→ Contact support if needed

Database Credentials?
→ Check cPanel or account details
→ May be prefixed with username
```

---

## FINAL PROVIDER-SPECIFIC TIPS

**Always Ask Your Hosting Provider:**
1. Maximum upload file size
2. MySQL version available
3. PHP memory limit
4. Email SMTP credentials
5. Number of MySQL databases allowed
6. SSH access available?
7. Backup frequency
8. Uptime guarantee

**Always Document:**
1. Database credentials
2. cPanel username/password
3. FTP credentials
4. SSH key (if applicable)
5. Email SMTP settings
6. Server IP address
7. Support contact info

---

**Ready to deploy? Choose your provider above and follow the specific steps! 🚀**
