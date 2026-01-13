# MySQL Configuration Fix Script
# This script properly configures MySQL to prevent replication errors

Write-Host "===============================================" -ForegroundColor Cyan
Write-Host "MySQL Configuration Fix for XAMPP" -ForegroundColor Cyan
Write-Host "===============================================" -ForegroundColor Cyan
Write-Host ""

# Stop MySQL if running
Write-Host "Stopping MySQL..." -ForegroundColor Yellow
Get-Process mysqld -ErrorAction SilentlyContinue | Stop-Process -Force
Start-Sleep -Seconds 2

# Remove corrupted replication files
Write-Host "Removing corrupted replication files..." -ForegroundColor Yellow
Remove-Item "D:\xampp\mysql\data\master-*" -Force -ErrorAction SilentlyContinue
Remove-Item "D:\xampp\mysql\data\relay-*" -Force -ErrorAction SilentlyContinue

# Fix my.ini configuration
Write-Host "Fixing my.ini configuration..." -ForegroundColor Yellow
$configFile = "D:\xampp\mysql\bin\my.ini"
$content = Get-Content $configFile

# Check if skip-slave-start already exists
if ($content -notmatch "skip-slave-start") {
    $newContent = @()
    foreach ($line in $content) {
        $newContent += $line
        # Add skip-slave-start after server-id line
        if ($line -match "^server-id\s*=\s*1\s*$") {
            $newContent += "skip-slave-start"
            Write-Host "  Added skip-slave-start directive" -ForegroundColor Green
        }
    }
    $newContent | Set-Content $configFile
} else {
    Write-Host "  skip-slave-start already configured" -ForegroundColor Green
}

Write-Host ""
Write-Host "===============================================" -ForegroundColor Cyan
Write-Host "Configuration Fixed!" -ForegroundColor Green
Write-Host "===============================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Now starting MySQL..." -ForegroundColor Yellow
Write-Host ""

# Start MySQL
Start-Process -FilePath "D:\xampp\mysql\bin\mysqld.exe" -WindowStyle Hidden
Start-Sleep -Seconds 5

# Check if MySQL started
$mysqlProcess = Get-Process mysqld -ErrorAction SilentlyContinue
if ($mysqlProcess) {
    Write-Host "SUCCESS! MySQL is running (PID: $($mysqlProcess.Id))" -ForegroundColor Green
    
    # Check if port 3306 is listening
    $portCheck = netstat -ano | findstr ":3306" | findstr "LISTENING"
    if ($portCheck) {
        Write-Host "MySQL is listening on port 3306" -ForegroundColor Green
        Write-Host ""
        Write-Host "You can now access your application at: http://localhost/AGCO" -ForegroundColor Cyan
    }
} else {
    Write-Host "WARNING: MySQL process not found. Check XAMPP Control Panel." -ForegroundColor Red
    Start-Process "D:\xampp\xampp-control.exe"
}

Write-Host ""
Write-Host "Press any key to exit..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
