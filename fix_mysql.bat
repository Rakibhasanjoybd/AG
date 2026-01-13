@echo off
echo ================================================
echo MySQL Fix Script for XAMPP
echo ================================================
echo.

REM Stop any running MySQL process
taskkill /F /IM mysqld.exe /T 2>nul

echo Waiting for MySQL to stop...
timeout /t 2 /nobreak > nul

REM Clean up corrupted replication files
echo Cleaning up corrupted files...
del /F /Q "D:\xampp\mysql\data\master-*" 2>nul
del /F /Q "D:\xampp\mysql\data\relay-*" 2>nul

echo.
echo ================================================
echo Starting MySQL from XAMPP Control Panel
echo ================================================
echo.
echo Please:
echo 1. In XAMPP Control Panel, click START next to MySQL
echo 2. Wait for the green indicator
echo 3. If it fails, click on "Logs" button to see errors
echo.

start "" "D:\xampp\xampp-control.exe"

echo.
pause
