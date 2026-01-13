@echo off
REM Auto-Deploy SSH Key Generator for Windows
REM Generates ED25519 SSH key for GitHub Actions deployment

setlocal enabledelayedexpansion

echo.
echo ========================================
echo GitHub Actions Auto-Deploy Key Generator
echo ========================================
echo.

REM Check if ssh-keygen is available
where ssh-keygen >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: ssh-keygen not found.
    echo Please install OpenSSH Client:
    echo   Settings ^> Apps ^> Optional Features ^> Add OpenSSH Client
    echo Or download Git for Windows: https://git-scm.com/download/win
    pause
    exit /b 1
)

set KEY_PATH=%USERPROFILE%\.ssh\deploy_key

echo Generating ED25519 SSH key...
echo Key will be saved to: %KEY_PATH%
echo.

REM Create .ssh directory if it doesn't exist
if not exist "%USERPROFILE%\.ssh" (
    mkdir "%USERPROFILE%\.ssh"
    echo Created .ssh directory
)

REM Generate the key (non-interactive, no passphrase)
ssh-keygen -t ed25519 -f "%KEY_PATH%" -N "" -C "github-actions-deploy"

if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Failed to generate SSH key
    pause
    exit /b 1
)

echo.
echo ========================================
echo ✅ Key generated successfully!
echo ========================================
echo.
echo Private key:  %KEY_PATH%
echo Public key:   %KEY_PATH%.pub
echo.
echo Next steps:
echo.
echo 1. Copy the PRIVATE KEY content:
echo    [Will display below - copy everything between BEGIN and END]
echo.
type "%KEY_PATH%"
echo.
echo 2. PASTE the above into GitHub Secrets: DEPLOY_SSH_KEY
echo.
echo 3. Copy the PUBLIC KEY to your server ~/.ssh/authorized_keys
echo    Run this to display it:
echo    type "%KEY_PATH%.pub"
echo.
echo 4. Then run: .\scripts\setup-secrets.ps1
echo    Or manually add these secrets to GitHub:
echo      DEPLOY_HOST, DEPLOY_USER, DEPLOY_PORT, DEPLOY_PATH
echo.
pause
