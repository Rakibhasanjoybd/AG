# PowerShell script to add GitHub Secrets for auto-deploy
# Prerequisites: GitHub CLI (gh) must be installed and authenticated
# Install: https://cli.github.com/

param(
    [string]$Owner = "",
    [string]$Repo = "",
    [string]$DeployHost = "",
    [string]$DeployUser = "",
    [string]$DeployPort = "22",
    [string]$DeployPath = "",
    [string]$DeployKeyPath = ""
)

function Show-Usage {
    Write-Host "
GitHub Secrets Setup for Auto-Deploy
=====================================

Usage:
  .\setup-secrets.ps1 -Owner 'username' -Repo 'repo-name' -DeployHost 'example.com' `
    -DeployUser 'deploy' -DeployPort '22' -DeployPath '/var/www/html/agco' `
    -DeployKeyPath 'C:\Users\YourName\.ssh\deploy_key'

Or run interactively (just run the script with no parameters):
  .\setup-secrets.ps1

Prerequisites:
  1. Install GitHub CLI: https://cli.github.com/
  2. Authenticate: gh auth login
  3. Generate SSH key: ssh-keygen -t ed25519 -f deploy_key -N ''

" -ForegroundColor Cyan
}

# If no params provided, prompt interactively
if ([string]::IsNullOrEmpty($Owner)) {
    Show-Usage
    
    Write-Host "Interactive Setup" -ForegroundColor Green
    $Owner = Read-Host "GitHub username or org"
    $Repo = Read-Host "Repository name"
    $DeployHost = Read-Host "Server hostname or IP (e.g., example.com)"
    $DeployUser = Read-Host "SSH username (e.g., deploy)"
    $DeployPort = Read-Host "SSH port (default 22)" 
    if ([string]::IsNullOrEmpty($DeployPort)) { $DeployPort = "22" }
    $DeployPath = Read-Host "Server path (e.g., /var/www/html/agco)"
    $DeployKeyPath = Read-Host "Path to deploy_key private key (e.g., $env:USERPROFILE\.ssh\deploy_key)"
}

# Verify inputs
if ([string]::IsNullOrEmpty($Owner) -or [string]::IsNullOrEmpty($Repo)) {
    Write-Host "Error: Owner and Repo are required." -ForegroundColor Red
    Show-Usage
    exit 1
}

# Check if GitHub CLI is installed
if (-not (Get-Command gh -ErrorAction SilentlyContinue)) {
    Write-Host "❌ GitHub CLI not found. Install it: https://cli.github.com/" -ForegroundColor Red
    exit 1
}

# Read SSH private key
if ([string]::IsNullOrEmpty($DeployKeyPath)) {
    $DeployKeyPath = "$env:USERPROFILE\.ssh\deploy_key"
}

if (-not (Test-Path $DeployKeyPath)) {
    Write-Host "❌ SSH key not found at: $DeployKeyPath" -ForegroundColor Red
    Write-Host "   Generate one with: ssh-keygen -t ed25519 -f $DeployKeyPath -N ''" -ForegroundColor Yellow
    exit 1
}

$DeployKey = Get-Content $DeployKeyPath -Raw

Write-Host ""
Write-Host "Adding GitHub Secrets..." -ForegroundColor Cyan
Write-Host "Repository: $Owner/$Repo" -ForegroundColor Gray
Write-Host ""

# Add secrets using GitHub CLI
$secrets = @{
    "DEPLOY_HOST" = $DeployHost
    "DEPLOY_USER" = $DeployUser
    "DEPLOY_PORT" = $DeployPort
    "DEPLOY_PATH" = $DeployPath
    "DEPLOY_SSH_KEY" = $DeployKey
}

foreach ($secretName in $secrets.Keys) {
    $secretValue = $secrets[$secretName]
    try {
        Write-Host "  Adding $secretName..." -NoNewline
        $secretValue | gh secret set $secretName --repo "$Owner/$Repo" 2>&1
        Write-Host " ✅" -ForegroundColor Green
    } catch {
        Write-Host " ❌" -ForegroundColor Red
        Write-Host "    Error: $_"
        exit 1
    }
}

Write-Host ""
Write-Host "✅ All secrets added successfully!" -ForegroundColor Green
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Green
Write-Host "  1. Verify secrets: gh secret list --repo $Owner/$Repo" -ForegroundColor Gray
Write-Host "  2. Upload deploy_key.pub to server ~/.ssh/authorized_keys" -ForegroundColor Gray
Write-Host "  3. Push a commit to 'main' branch to trigger auto-deploy" -ForegroundColor Gray
Write-Host "  4. Watch the workflow: gh run list --repo $Owner/$Repo" -ForegroundColor Gray
Write-Host ""
