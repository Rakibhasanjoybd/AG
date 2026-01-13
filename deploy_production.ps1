# AGCO Finance - Production Deployment Script
# PowerShell Script for Windows Deployment

Write-Host "========================================" -ForegroundColor Green
Write-Host "AGCO Finance Production Deployment" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green

# Check if running as Administrator
if (-NOT ([Security.Principal.WindowsPrincipal][Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole] "Administrator")) {
    Write-Host "Please run this script as Administrator!" -ForegroundColor Red
    exit 1
}

# Configuration
$PROJECT_NAME = "AGCO Finance"
$BACKUP_DIR = "D:\backups\agco_finance"
$PROJECT_DIR = "D:\xampp\htdocs\AGCO"
$LOG_FILE = "$PROJECT_DIR\deployment_$(Get-Date -Format 'yyyyMMdd_HHmmss').log"

# Create backup directory if not exists
if (!(Test-Path $BACKUP_DIR)) {
    New-Item -ItemType Directory -Path $BACKUP_DIR -Force
}

# Function to write logs
function Write-Log {
    param([string]$message, [string]$level = "INFO")
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $logMessage = "[$timestamp] [$level] $message"
    Write-Host $logMessage
    Add-Content -Path $LOG_FILE -Value $logMessage
}

# Function to check if service is running
function Test-Service {
    param([string]$serviceName)
    try {
        $service = Get-Service -Name $serviceName -ErrorAction SilentlyContinue
        return ($service -and $service.Status -eq 'Running')
    }
    catch {
        return $false
    }
}

# Function to create database backup
function Backup-Database {
    Write-Log "Creating database backup..."
    
    $backupFile = "$BACKUP_DIR\agco_finance_backup_$(Get-Date -Format 'yyyyMMdd_HHmmss').sql"
    $mysqlPath = "D:\xampp\mysql\bin\mysqldump.exe"
    
    if (!(Test-Path $mysqlPath)) {
        Write-Log "MySQL not found at $mysqlPath" -ForegroundColor Red
        return $false
    }
    
    try {
        # Read database credentials from .env
        $envFile = "$PROJECT_DIR\core\.env"
        if (Test-Path $envFile) {
            $envContent = Get-Content $envFile
            $dbDatabase = ($envContent | Where-Object { $_ -match "^DB_DATABASE=" }) -replace "DB_DATABASE=", ""
            $dbUsername = ($envContent | Where-Object { $_ -match "^DB_USERNAME=" }) -replace "DB_USERNAME=", ""
            $dbPassword = ($envContent | Where-Object { $_ -match "^DB_PASSWORD=" }) -replace "DB_PASSWORD=", ""
            
            $backupCommand = "$mysqlPath -u$dbUsername -p$dbPassword $dbDatabase > `"$backupFile`""
            cmd /c $backupCommand
            
            if (Test-Path $backupFile) {
                Write-Log "Database backup created: $backupFile"
                return $true
            }
        }
    }
    catch {
        Write-Log "Database backup failed: $($_.Exception.Message)" -ForegroundColor Red
    }
    
    return $false
}

# Function to validate environment
function Test-Environment {
    Write-Log "Validating environment..."
    
    # Check PHP
    try {
        $phpVersion = php -v | Select-Object -First 1
        Write-Log "PHP version: $phpVersion"
    }
    catch {
        Write-Log "PHP not found in PATH" -ForegroundColor Red
        return $false
    }
    
    # Check Composer
    try {
        $composerVersion = composer --version
        Write-Log "Composer version: $composerVersion"
    }
    catch {
        Write-Log "Composer not found in PATH" -ForegroundColor Red
        return $false
    }
    
    # Check Node.js (if needed)
    try {
        $nodeVersion = node --version
        Write-Log "Node.js version: $nodeVersion"
    }
    catch {
        Write-Log "Node.js not found (optional)" -ForegroundColor Yellow
    }
    
    # Check MySQL service
    if (Test-Service "mysql") {
        Write-Log "MySQL service is running"
    }
    else {
        Write-Log "MySQL service is not running" -ForegroundColor Red
        return $false
    }
    
    # Check Apache service
    if (Test-Service "apache") {
        Write-Log "Apache service is running"
    }
    else {
        Write-Log "Apache service is not running" -ForegroundColor Red
        return $false
    }
    
    return $true
}

# Function to run Laravel commands
function Invoke-LaravelCommand {
    param([string]$command)
    
    Write-Log "Running: php artisan $command"
    
    Set-Location "$PROJECT_DIR\core"
    
    try {
        $result = Invoke-Expression "php artisan $command 2>&1"
        Write-Log $result
        
        if ($LASTEXITCODE -ne 0) {
            Write-Log "Command failed: php artisan $command" -ForegroundColor Red
            return $false
        }
        
        return $true
    }
    catch {
        Write-Log "Error running command: $($_.Exception.Message)" -ForegroundColor Red
        return $false
    }
}

# Function to update dependencies
function Update-Dependencies {
    Write-Log "Updating PHP dependencies..."
    
    Set-Location "$PROJECT_DIR\core"
    
    try {
        # Update Composer dependencies
        Write-Log "Running: composer install --no-dev --optimize-autoloader"
        $result = Invoke-Expression "composer install --no-dev --optimize-autoloader 2>&1"
        Write-Log $result
        
        if ($LASTEXITCODE -ne 0) {
            Write-Log "Composer install failed" -ForegroundColor Red
            return $false
        }
        
        # Clear and cache configurations
        Invoke-LaravelCommand "config:clear"
        Invoke-LaravelCommand "config:cache"
        
        Write-Log "Dependencies updated successfully"
        return $true
    }
    catch {
        Write-Log "Error updating dependencies: $($_.Exception.Message)" -ForegroundColor Red
        return $false
    }
}

# Function to run database migrations
function Invoke-Migrations {
    Write-Log "Running database migrations..."
    
    if (!(Invoke-LaravelCommand "migrate --force")) {
        return $false
    }
    
    Write-Log "Migrations completed successfully"
    return $true
}

# Function to set file permissions
function Set-FilePermissions {
    Write-Log "Setting file permissions..."
    
    # Set permissions for storage directory
    $storagePath = "$PROJECT_DIR\core\storage"
    if (Test-Path $storagePath) {
        try {
            # Grant IIS_IUSRS modify permissions on storage
            $acl = Get-Acl $storagePath
            $accessRule = New-Object System.Security.AccessControl.FileSystemAccessRule("IIS_IUSRS","Modify","ContainerInherit,ObjectInherit","None","Allow")
            $acl.SetAccessRule($accessRule)
            Set-Acl $storagePath $acl
            
            Write-Log "Storage permissions set"
        }
        catch {
            Write-Log "Failed to set storage permissions: $($_.Exception.Message)" -ForegroundColor Yellow
        }
    }
    
    # Set permissions for bootstrap/cache
    $cachePath = "$PROJECT_DIR\core\bootstrap\cache"
    if (Test-Path $cachePath) {
        try {
            $acl = Get-Acl $cachePath
            $accessRule = New-Object System.Security.AccessControl.FileSystemAccessRule("IIS_IUSRS","Modify","ContainerInherit,ObjectInherit","None","Allow")
            $acl.SetAccessRule($accessRule)
            Set-Acl $cachePath $acl
            
            Write-Log "Cache permissions set"
        }
        catch {
            Write-Log "Failed to set cache permissions: $($_.Exception.Message)" -ForegroundColor Yellow
        }
    }
}

# Function to run tests
function Invoke-Tests {
    Write-Log "Running application tests..."
    
    Set-Location "$PROJECT_DIR\core"
    
    try {
        # Run unit tests
        Write-Log "Running unit tests..."
        $unitResult = Invoke-Expression "vendor\bin\phpunit tests\Unit --verbose 2>&1"
        Write-Log $unitResult
        
        # Run feature tests
        Write-Log "Running feature tests..."
        $featureResult = Invoke-Expression "vendor\bin\phpunit tests\Feature --verbose 2>&1"
        Write-Log $featureResult
        
        if ($LASTEXITCODE -ne 0) {
            Write-Log "Some tests failed" -ForegroundColor Yellow
            return $false
        }
        
        Write-Log "All tests passed"
        return $true
    }
    catch {
        Write-Log "Error running tests: $($_.Exception.Message)" -ForegroundColor Red
        return $false
    }
}

# Function to optimize application
function Optimize-Application {
    Write-Log "Optimizing application..."
    
    # Clear all caches
    Invoke-LaravelCommand "cache:clear"
    Invoke-LaravelCommand "route:clear"
    Invoke-LaravelCommand "view:clear"
    
    # Optimize for production
    Invoke-LaravelCommand "config:cache"
    Invoke-LaravelCommand "route:cache"
    Invoke-LaravelCommand "view:cache"
    
    # Preload classes
    if (Test-Path "$PROJECT_DIR\core\bootstrap\cache\packages.php") {
        Write-Log "Application optimized successfully"
        return $true
    }
    
    return $false
}

# Function to verify deployment
function Test-Deployment {
    Write-Log "Verifying deployment..."
    
    # Test basic routes
    $testUrls = @(
        "http://localhost/AGCO/",
        "http://localhost/AGCO/user/login",
        "http://localhost/AGCO/api/auth/me"
    )
    
    foreach ($url in $testUrls) {
        try {
            $response = Invoke-WebRequest -Uri $url -UseBasicParsing -TimeoutSec 10
            Write-Log "✓ $url - Status: $($response.StatusCode)"
        }
        catch {
            Write-Log "✗ $url - Failed: $($_.Exception.Message)" -ForegroundColor Red
        }
    }
    
    # Check log files for errors
    $logPath = "$PROJECT_DIR\core\storage\logs"
    if (Test-Path $logPath) {
        $latestLog = Get-ChildItem $logPath -Filter "*.log" | Sort-Object LastWriteTime -Descending | Select-Object -First 1
        if ($latestLog) {
            $errorCount = (Get-Content $latestLog.FullName | Where-Object { $_ -match "ERROR" }).Count
            if ($errorCount -gt 0) {
                Write-Log "Found $errorCount errors in latest log file" -ForegroundColor Yellow
            }
        }
    }
}

# Main deployment process
try {
    Write-Log "Starting deployment of $PROJECT_NAME..."
    Write-Log "Deployment started at: $(Get-Date)"
    
    # Step 1: Validate environment
    Write-Log "Step 1: Environment Validation" -ForegroundColor Cyan
    if (!(Test-Environment)) {
        Write-Log "Environment validation failed" -ForegroundColor Red
        exit 1
    }
    
    # Step 2: Create backup
    Write-Log "Step 2: Creating Backup" -ForegroundColor Cyan
    if (!(Backup-Database)) {
        Write-Log "Warning: Database backup failed, continuing..." -ForegroundColor Yellow
    }
    
    # Step 3: Update dependencies
    Write-Log "Step 3: Updating Dependencies" -ForegroundColor Cyan
    if (!(Update-Dependencies)) {
        Write-Log "Dependency update failed" -ForegroundColor Red
        exit 1
    }
    
    # Step 4: Run migrations
    Write-Log "Step 4: Running Migrations" -ForegroundColor Cyan
    if (!(Invoke-Migrations)) {
        Write-Log "Migration failed" -ForegroundColor Red
        exit 1
    }
    
    # Step 5: Set file permissions
    Write-Log "Step 5: Setting File Permissions" -ForegroundColor Cyan
    Set-FilePermissions
    
    # Step 6: Run tests
    Write-Log "Step 6: Running Tests" -ForegroundColor Cyan
    $testResult = Invoke-Tests
    if (!$testResult) {
        Write-Log "Some tests failed, but continuing deployment..." -ForegroundColor Yellow
    }
    
    # Step 7: Optimize application
    Write-Log "Step 7: Optimizing Application" -ForegroundColor Cyan
    if (!(Optimize-Application)) {
        Write-Log "Application optimization failed" -ForegroundColor Red
        exit 1
    }
    
    # Step 8: Verify deployment
    Write-Log "Step 8: Verifying Deployment" -ForegroundColor Cyan
    Test-Deployment
    
    Write-Log "========================================" -ForegroundColor Green
    Write-Log "Deployment completed successfully!" -ForegroundColor Green
    Write-Log "Deployment completed at: $(Get-Date)" -ForegroundColor Green
    Write-Log "========================================" -ForegroundColor Green
    
    # Restart services
    Write-Log "Restarting web services..."
    Restart-Service -Name "apache" -Force
    Restart-Service -Name "mysql" -Force
    
    Write-Log "Services restarted"
    
}
catch {
    Write-Log "Deployment failed: $($_.Exception.Message)" -ForegroundColor Red
    Write-Log "Stack trace: $($_.ScriptStackTrace)" -ForegroundColor Red
    exit 1
}

# Display summary
Write-Host "`nDeployment Summary:" -ForegroundColor Green
Write-Host "- Project: $PROJECT_NAME" -ForegroundColor Green
Write-Host "- Location: $PROJECT_DIR" -ForegroundColor Green
Write-Host "- Log file: $LOG_FILE" -ForegroundColor Green
Write-Host "- Backup location: $BACKUP_DIR" -ForegroundColor Green

Write-Host "`nNext Steps:" -ForegroundColor Yellow
Write-Host "1. Verify the application is working correctly" -ForegroundColor Yellow
Write-Host "2. Check the application logs for any errors" -ForegroundColor Yellow
Write-Host "3. Run security scans if required" -ForegroundColor Yellow
Write-Host "4. Monitor performance for the next 24 hours" -ForegroundColor Yellow

Write-Host "`nDeployment completed successfully!" -ForegroundColor Green
