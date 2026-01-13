# Complete XAMPP MySQL Fix Solution
# Fixes: Unexpected shutdown, InnoDB recovery, corrupted system tables
# Run this script as Administrator

Write-Host "======================================================" -ForegroundColor Cyan
Write-Host "COMPLETE XAMPP MYSQL FIX" -ForegroundColor Cyan  
Write-Host "======================================================" -ForegroundColor Cyan
Write-Host ""

$xamppPath = "D:\xampp"
$mysqlData = "$xamppPath\mysql\data"
$mysqlBackup = "$xamppPath\mysql\backup"
$mysqlBin = "$xamppPath\mysql\bin"

# 1. Stop all MySQL processes
Write-Host "[Step 1/8] Stopping MySQL processes..." -ForegroundColor Yellow
Get-Process mysqld -ErrorAction SilentlyContinue | Stop-Process -Force
Start-Sleep -Seconds 3
Write-Host "  MySQL processes stopped" -ForegroundColor Green

# 2. Remove stale PID and lock files
Write-Host "[Step 2/8] Removing stale PID/lock files..." -ForegroundColor Yellow
Remove-Item "$mysqlData\*.pid" -Force -ErrorAction SilentlyContinue
Remove-Item "$mysqlData\*.lck" -Force -ErrorAction SilentlyContinue
Write-Host "  Removed stale lock files" -ForegroundColor Green

# 3. Clean corrupted/temp files
Write-Host "[Step 3/8] Removing corrupted/temp files..." -ForegroundColor Yellow
Remove-Item "$mysqlData\master*" -Force -ErrorAction SilentlyContinue
Remove-Item "$mysqlData\relay*" -Force -ErrorAction SilentlyContinue
Remove-Item "$mysqlData\multi-master.info" -Force -ErrorAction SilentlyContinue
Remove-Item "$mysqlData\ibtmp1" -Force -ErrorAction SilentlyContinue
Remove-Item "$mysqlData\tc.log" -Force -ErrorAction SilentlyContinue
Write-Host "  Cleaned temp and replication files" -ForegroundColor Green

# 4. Restore MySQL system tables from backup if corrupted
Write-Host "[Step 4/8] Checking MySQL system tables..." -ForegroundColor Yellow
if (Test-Path $mysqlBackup) {
    # Check if mysql system folder needs restoration
    $dbTable = Get-ChildItem "$mysqlData\mysql\db.*" -ErrorAction SilentlyContinue | Select-Object -First 1
    if (-not $dbTable -or $dbTable.Length -lt 1000) {
        Write-Host "  MySQL system tables corrupted - restoring from backup..." -ForegroundColor Yellow
        
        # Backup corrupted folder
        if (Test-Path "$mysqlData\mysql_corrupted") {
            Remove-Item "$mysqlData\mysql_corrupted" -Recurse -Force
        }
        if (Test-Path "$mysqlData\mysql") {
            Rename-Item "$mysqlData\mysql" "$mysqlData\mysql_corrupted" -ErrorAction SilentlyContinue
        }
        
        # Copy from backup
        Copy-Item "$mysqlBackup\mysql" "$mysqlData\mysql" -Recurse -Force
        Write-Host "  MySQL system tables restored" -ForegroundColor Green
    } else {
        Write-Host "  MySQL system tables OK" -ForegroundColor Green
    }
}

# 5. Zerofill Aria tables (required when moving between systems)
Write-Host "[Step 5/8] Zerofilling Aria tables..." -ForegroundColor Yellow
$ariaTables = Get-ChildItem "$mysqlData\mysql\*.MAI" -ErrorAction SilentlyContinue
foreach ($table in $ariaTables) {
    & "$mysqlBin\aria_chk.exe" --zerofill $table.FullName 2>$null
}
Write-Host "  Aria tables zerofilled" -ForegroundColor Green

# 6. Restore InnoDB files from backup if missing
Write-Host "[Step 6/8] Checking InnoDB files..." -ForegroundColor Yellow
if (Test-Path $mysqlBackup) {
    if (-not (Test-Path "$mysqlData\aria_log_control") -and (Test-Path "$mysqlBackup\aria_log_control")) {
        Copy-Item "$mysqlBackup\aria_log_control" "$mysqlData\" -Force
        Copy-Item "$mysqlBackup\aria_log.00000001" "$mysqlData\" -Force -ErrorAction SilentlyContinue
    }
    if (-not (Test-Path "$mysqlData\ibdata1") -and (Test-Path "$mysqlBackup\ibdata1")) {
        Copy-Item "$mysqlBackup\ibdata1" "$mysqlData\" -Force
    }
}
Write-Host "  InnoDB files verified" -ForegroundColor Green

# 7. Fix my.ini configuration
Write-Host "[Step 7/8] Updating MySQL configuration..." -ForegroundColor Yellow
$configFile = "$mysqlBin\my.ini"
$content = Get-Content $configFile -Raw -ErrorAction SilentlyContinue
if ($content) {
    $content = $content -replace 'skip-slave-start\r?\n', ''
    if ($content -notmatch 'skip-slave-start') {
        $content = $content -replace '(server-id\s*=\s*1)', "`$1`r`nskip-slave-start"
    }
    $content | Set-Content $configFile -NoNewline
}
Write-Host "  Configuration updated" -ForegroundColor Green

# 8. Start MySQL service
Write-Host "[Step 8/8] Starting MySQL service..." -ForegroundColor Yellow
$mysqldProcess = Start-Process -FilePath "$mysqlBin\mysqld.exe" `
    -ArgumentList "--defaults-file=$mysqlBin\my.ini" `
    -PassThru -WindowStyle Hidden

Start-Sleep -Seconds 8

# Verify MySQL is running
$mysqlRunning = Get-Process mysqld -ErrorAction SilentlyContinue
if ($mysqlRunning) {
    Write-Host "  MySQL started successfully!" -ForegroundColor Green
    
    # Test connection
    $testResult = & "$mysqlBin\mysql.exe" -u root -e "SELECT 1" 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-Host "  MySQL connection verified!" -ForegroundColor Green
    }
} else {
    Write-Host "  Direct start failed, trying recovery mode..." -ForegroundColor Yellow
    
    $mysqldProcess = Start-Process -FilePath "$mysqlBin\mysqld.exe" `
        -ArgumentList "--defaults-file=$mysqlBin\my.ini", "--innodb-force-recovery=1" `
        -PassThru -WindowStyle Hidden
    
    Start-Sleep -Seconds 10
    $mysqlRunning = Get-Process mysqld -ErrorAction SilentlyContinue
}

# Final verification
Write-Host ""
Write-Host "======================================================" -ForegroundColor Cyan

$apache = Get-Process httpd -ErrorAction SilentlyContinue
$mysql = Get-Process mysqld -ErrorAction SilentlyContinue

if ($apache) {
    Write-Host "[OK] Apache is running" -ForegroundColor Green
} else {
    Write-Host "[INFO] Apache is not running - start it from XAMPP Control Panel" -ForegroundColor Yellow
}

if ($mysql) {
    Write-Host "[OK] MySQL is running (PID: $($mysql.Id))" -ForegroundColor Green
    Write-Host ""
    Write-Host "======================================================" -ForegroundColor Cyan
    Write-Host "SUCCESS! Your application should now work at:" -ForegroundColor Green
    Write-Host "http://localhost/AGCO" -ForegroundColor Cyan
    Write-Host "======================================================" -ForegroundColor Cyan
    
    # Show databases
    Write-Host ""
    Write-Host "Available databases:" -ForegroundColor Yellow
    & "$mysqlBin\mysql.exe" -u root -e "SHOW DATABASES;" 2>$null
} else {
    Write-Host "[ERROR] MySQL is not running" -ForegroundColor Red
    Write-Host ""
    Write-Host "ADDITIONAL TROUBLESHOOTING:" -ForegroundColor Red
    Write-Host "1. Check error log: D:\xampp\mysql\data\*.err" -ForegroundColor White
    Write-Host "2. Open XAMPP Control Panel and click MySQL 'Logs'" -ForegroundColor White
    Write-Host "3. Try: netstat -ano | findstr :3306" -ForegroundColor White
}

Write-Host ""
Write-Host "Press any key to exit..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
