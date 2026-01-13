@echo off
echo ========================================
echo Starting XAMPP Services
echo ========================================
echo.

REM Start Apache
echo Starting Apache...
cd D:\xampp\apache\bin
start /B httpd.exe
timeout /t 3 /nobreak > nul

REM Start MySQL
echo Starting MySQL...
cd D:\xampp\mysql\bin
start /B mysqld.exe --console
timeout /t 5 /nobreak > nul

echo.
echo ========================================
echo Services Started!
echo ========================================
echo.
echo Checking status...
netstat -ano | findstr ":80 " | findstr "LISTENING"
netstat -ano | findstr ":3306" | findstr "LISTENING"
echo.
echo Press any key to exit...
pause > nul
