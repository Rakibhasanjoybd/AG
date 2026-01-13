# =====================================================
# AGCO SHARED HOSTING DEPLOYMENT PREPARATION SCRIPT
# =====================================================
# This script prepares your project for deployment
# Run this BEFORE uploading to shared hosting
# =====================================================

param(
    [string]$Action = "help"
)

# Colors for output
$Green = "Green"
$Red = "Red"
$Yellow = "Yellow"
$Cyan = "Cyan"

function Write-Header {
    param([string]$Text)
    Write-Host "`n" -ForegroundColor $Cyan
    Write-Host "═══════════════════════════════════════════════════════" -ForegroundColor $Cyan
    Write-Host "  $Text" -ForegroundColor $Cyan
    Write-Host "═══════════════════════════════════════════════════════" -ForegroundColor $Cyan
}

function Write-Success {
    param([string]$Text)
    Write-Host "✓ $Text" -ForegroundColor $Green
}

function Write-Error {
    param([string]$Text)
    Write-Host "✗ $Text" -ForegroundColor $Red
}

function Write-Warning {
    param([string]$Text)
    Write-Host "⚠ $Text" -ForegroundColor $Yellow
}

function Show-Help {
    Write-Header "AGCO DEPLOYMENT PREPARATION - HELP"
    Write-Host @"
USAGE:
  .\Deploy-Prep.ps1 -Action <action>

AVAILABLE ACTIONS:
  help              Show this help message
  check             Check PHP and dependencies
  backup            Create database backup
  cleanup           Clean up unnecessary files
  prepare-env       Create example .env file
  compress          Create deployment package
  full              Run all preparation steps
  setup-ssl         Setup SSL certificate info

EXAMPLES:
  .\Deploy-Prep.ps1 -Action check
  .\Deploy-Prep.ps1 -Action backup
  .\Deploy-Prep.ps1 -Action full

"@
}

function Check-Requirements {
    Write-Header "CHECKING REQUIREMENTS"
    
    # Check PHP
    Write-Host "Checking PHP..."
    try {
        $phpVersion = php -v 2>&1 | Select-Object -First 1
        if ($phpVersion -match "PHP (\d+\.\d+\.\d+)") {
            $version = [version]$matches[1]
            if ($version -ge [version]"8.0.2") {
                Write-Success "PHP $($matches[1]) found (Required: 8.0.2+)"
            } else {
                Write-Error "PHP version too old: $($matches[1]) (Required: 8.0.2+)"
            }
        }
    } catch {
        Write-Error "PHP not found in PATH"
    }
    
    # Check Composer
    Write-Host "Checking Composer..."
    try {
        $composerVersion = composer --version 2>&1
        Write-Success "Composer found: $composerVersion"
    } catch {
        Write-Error "Composer not found. Install from https://getcomposer.org"
    }
    
    # Check MySQL
    Write-Host "Checking MySQL..."
    try {
        $mysqlVersion = mysql --version 2>&1
        Write-Success "MySQL found: $mysqlVersion"
    } catch {
        Write-Warning "MySQL not found in PATH. This is OK - just needed for local testing"
    }
    
    # Check project structure
    Write-Host "Checking project structure..."
    $requiredDirs = @("core", "assets", "core/app", "core/routes", "core/storage")
    foreach ($dir in $requiredDirs) {
        if (Test-Path $dir) {
            Write-Success "Directory found: $dir"
        } else {
            Write-Error "Directory missing: $dir"
        }
    }
}

function Create-Backup {
    Write-Header "DATABASE BACKUP"
    
    # Check if database exists
    $dbName = Read-Host "Enter your database name"
    $dbUser = Read-Host "Enter database username"
    $dbPass = Read-Host "Enter database password" -AsSecureString
    $dbPassPlain = [System.Runtime.InteropServices.Marshal]::PtrToStringAuto([System.Runtime.InteropServices.Marshal]::SecureStringToCoTaskMemUnicode($dbPass))
    
    $backupPath = "backups"
    if (-not (Test-Path $backupPath)) {
        New-Item -ItemType Directory -Path $backupPath | Out-Null
        Write-Success "Created backups directory"
    }
    
    $timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
    $backupFile = "$backupPath\agco_backup_$timestamp.sql"
    
    Write-Host "Creating database backup..."
    try {
        $env:MYSQL_PWD = $dbPassPlain
        mysqldump -u $dbUser $dbName > $backupFile
        Write-Success "Backup created: $backupFile"
        Write-Host "Size: $((Get-Item $backupFile).Length / 1MB -as [int]) MB"
    } catch {
        Write-Error "Failed to create backup: $_"
    } finally {
        Remove-Item env:MYSQL_PWD -ErrorAction SilentlyContinue
    }
}

function Prepare-Environment {
    Write-Header "CREATING .ENV TEMPLATE"
    
    $envPath = "core\.env.example"
    if (-not (Test-Path $envPath)) {
        Write-Error "File not found: $envPath"
        return
    }
    
    Write-Host "Creating deployment .env template..."
    
    $envContent = @"
# ========================================
# DEPLOYMENT ENVIRONMENT CONFIGURATION
# ========================================
# Update these values for your shared hosting

APP_NAME="AGCO"
APP_ENV=production
APP_KEY=base64:GENERATE_THIS_ON_SERVER
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Performance
DEBUGBAR_ENABLED=false

# Logging
LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=notice

# Database - Update for your hosting
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

# Cache & Session
BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Mail - Configure for your hosting
MAIL_MAILER=smtp
MAIL_HOST=mail.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="`${APP_NAME}"

# Payment Gateways (if used)
# STRIPE_PUBLIC_KEY=
# STRIPE_SECRET_KEY=
# RAZORPAY_PUBLIC_KEY=
# RAZORPAY_SECRET_KEY=

# API Services (if used)
# AWS_ACCESS_KEY_ID=
# AWS_SECRET_ACCESS_KEY=
# AWS_DEFAULT_REGION=us-east-1
# AWS_BUCKET=
"@
    
    $envDeployPath = "core\.env.production"
    Set-Content -Path $envDeployPath -Value $envContent -Encoding UTF8
    Write-Success "Created: $envDeployPath"
    Write-Host "Update this file with your hosting credentials before deployment!"
}

function Cleanup-Project {
    Write-Header "CLEANING UP PROJECT"
    
    $itemsToRemove = @(
        "core\node_modules",
        "core\.git",
        "core\.env",
        ".env",
        "*.log",
        "backups\*.sql",
        "core\storage\logs\*",
        "core\storage\debugbar\*"
    )
    
    Write-Host "Files to clean (will not actually delete):"
    foreach ($item in $itemsToRemove) {
        $found = Get-ChildItem -Path $item -ErrorAction SilentlyContinue
        if ($found) {
            Write-Host "  - Found: $item"
        }
    }
    
    Write-Host "`nNote: Manual cleanup recommended for sensitive data:"
    Write-Host "  1. Delete core/node_modules (if not needed)"
    Write-Host "  2. Delete .git folder (if version control not needed)"
    Write-Host "  3. Clear core/storage/logs"
    Write-Host "  4. Remove any local development files"
}

function Create-Deployment-Package {
    Write-Header "CREATING DEPLOYMENT PACKAGE"
    
    $timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
    $packageName = "agco_deployment_$timestamp.zip"
    
    Write-Host "Creating compressed package..."
    Write-Warning "This may take a few minutes..."
    
    try {
        # Create temporary list of files
        $filesToInclude = @(
            "core",
            "index.php",
            ".htaccess",
            "manifest.json",
            "mix-manifest.json"
        )
        
        # Compress
        foreach ($file in $filesToInclude) {
            if (Test-Path $file) {
                Write-Host "Adding: $file"
            }
        }
        
        Write-Host "To create the actual package, use:"
        Write-Host "  Compress-Archive -Path core, index.php, .htaccess, manifest.json, mix-manifest.json -DestinationPath $packageName"
        Write-Success "Package ready for creation"
    } catch {
        Write-Error "Failed to create package: $_"
    }
}

function Show-SSL-Setup {
    Write-Header "SSL CERTIFICATE SETUP GUIDE"
    
    Write-Host @"
STEPS TO SETUP SSL ON SHARED HOSTING:

1. LOGIN TO cPANEL
   - Visit: https://your.hosting.com/cpanel
   - Username: Your cPanel username
   - Password: Your cPanel password

2. FIND SSL CERTIFICATE SECTION
   - Look for "AutoSSL" or "SSL/TLS"
   - Or search for "SSL" in cPanel

3. INSTALL FREE SSL (Let's Encrypt)
   - Click "AutoSSL"
   - Select your domain
   - Click "Install"
   - Wait 5-10 minutes for installation

4. VERIFY SSL INSTALLATION
   - Visit: https://yourdomain.com
   - Check for green lock in browser
   - No warnings should appear

5. FORCE HTTPS (Add to .htaccess)
   # Redirect all HTTP to HTTPS
   RewriteEngine On
   RewriteCond %{HTTPS} off
   RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

ALTERNATIVE: BUY SSL CERTIFICATE
   - From services like:
     * Comodo
     * Sectigo
     * Symantec
   - Upload via cPanel SSL interface

"@
}

function Show-Deployment-Checklist {
    Write-Header "PRE-DEPLOYMENT CHECKLIST"
    
    Write-Host @"
BEFORE UPLOADING TO SHARED HOSTING:

DATABASE PREPARATION:
  ☐ Backup your local database
  ☐ Export database to SQL file
  ☐ Save backup in 'backups' folder

ENVIRONMENT SETUP:
  ☐ Create .env file with hosting credentials
  ☐ Have APP_KEY ready (will be generated on server)
  ☐ Have database credentials
  ☐ Have mail server credentials

FILE PREPARATION:
  ☐ Clean up node_modules (if on disk)
  ☐ Remove .git folder
  ☐ Remove local .env file with local passwords
  ☐ Verify all project files present
  ☐ Compress project for uploading

HOSTING SETUP:
  ☐ Create hosting account
  ☐ Point domain to hosting
  ☐ Create MySQL database
  ☐ Create database user with all privileges
  ☐ Get SSH access
  ☐ Install SSL certificate

UPLOAD:
  ☐ Upload core/ directory via SFTP
  ☐ Upload index.php
  ☐ Upload .htaccess
  ☐ Upload database backup SQL file
  ☐ Verify file permissions (644/755)

FINAL SETUP ON SERVER:
  ☐ Create .env file
  ☐ Generate APP_KEY
  ☐ Import database
  ☐ Run migrations
  ☐ Cache configuration
  ☐ Setup cron job
  ☐ Test site access

"@
}

# Main execution
switch ($Action.ToLower()) {
    "help" { Show-Help }
    "check" { Check-Requirements }
    "backup" { Create-Backup }
    "cleanup" { Cleanup-Project }
    "prepare-env" { Prepare-Environment }
    "compress" { Create-Deployment-Package }
    "ssl" { Show-SSL-Setup }
    "checklist" { Show-Deployment-Checklist }
    "full" {
        Check-Requirements
        Prepare-Environment
        Cleanup-Project
        Show-Deployment-Checklist
    }
    default {
        Write-Error "Unknown action: $Action"
        Show-Help
    }
}

Write-Host "`n" -ForegroundColor $Cyan
Write-Host "Deployment Preparation Script Completed!" -ForegroundColor $Cyan
Write-Host "Next Steps:" -ForegroundColor $Cyan
Write-Host "  1. Review the deployment guides"
Write-Host "  2. Prepare your hosting account"
Write-Host "  3. Upload files via SFTP"
Write-Host "  4. Follow DEPLOYMENT_CHECKLIST_QUICK_START.md"
Write-Host ""
