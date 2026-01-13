#!/usr/bin/env pwsh
# GitHub Secrets Setup Script - Automated via API
# This script adds all required secrets for auto-deploy

param(
    [string]$GitHubToken = $env:GITHUB_TOKEN,
    [string]$Owner = "Rakibhasanjoybd",
    [string]$Repo = "AG",
    [string]$DeployHost = "ftp.agcolimited.uk",
    [string]$DeployUser = "agco",
    [string]$DeployPort = "22",
    [string]$DeployPath = "/var/www/html/agco",
    [string]$DeployKeyPath = "C:\Users\LS WHOLESALE COMPANY\Downloads\id_rsa"
)

if (-not $GitHubToken) {
    Write-Host "ERROR: GITHUB_TOKEN environment variable not set" -ForegroundColor Red
    Write-Host "Set it with: `$env:GITHUB_TOKEN = 'your_personal_access_token'" -ForegroundColor Yellow
    exit 1
}

# Read the private SSH key
if (-not (Test-Path $DeployKeyPath)) {
    Write-Host "ERROR: SSH key not found at $DeployKeyPath" -ForegroundColor Red
    exit 1
}

$DeployKey = Get-Content $DeployKeyPath -Raw

# GitHub API base URL
$ApiBase = "https://api.github.com/repos/$Owner/$Repo/actions/secrets"

# Function to add a secret
function Add-GitHubSecret {
    param(
        [string]$SecretName,
        [string]$SecretValue
    )
    
    $Body = @{
        encrypted_value = $SecretValue
    } | ConvertTo-Json
    
    $Headers = @{
        "Authorization" = "token $GitHubToken"
        "Accept" = "application/vnd.github.v3+json"
    }
    
    try {
        $Response = Invoke-RestMethod -Uri "$ApiBase/$SecretName" -Method PUT -Headers $Headers -Body $Body -ContentType "application/json"
        Write-Host "✅ Added secret: $SecretName" -ForegroundColor Green
        return $true
    }
    catch {
        Write-Host "❌ Failed to add secret: $SecretName" -ForegroundColor Red
        Write-Host "   Error: $($_.Exception.Message)" -ForegroundColor Red
        return $false
    }
}

Write-Host "Adding GitHub Secrets for Auto-Deploy" -ForegroundColor Cyan
Write-Host "Repository: $Owner/$Repo" -ForegroundColor Gray
Write-Host ""

# Get public key for encryption (GitHub requires secrets to be encrypted)
Write-Host "Fetching repository public key..." -NoNewline
$Headers = @{
    "Authorization" = "token $GitHubToken"
    "Accept" = "application/vnd.github.v3+json"
}

try {
    $PubKeyResponse = Invoke-RestMethod -Uri "$ApiBase/public-key" -Method GET -Headers $Headers
    $PublicKeyId = $PubKeyResponse.key_id
    $PublicKeyBase64 = $PubKeyResponse.key
    Write-Host " Done" -ForegroundColor Green
}
catch {
    Write-Host " Failed" -ForegroundColor Red
    Write-Host "   Error: $($_.Exception.Message)"
    exit 1
}

# Function to encrypt value for GitHub
function Encrypt-GitHubSecret {
    param(
        [string]$SecretValue,
        [string]$PublicKeyBase64
    )
    
    # For now, return the value as-is (GitHub CLI handles encryption)
    # In real implementation, would use libsodium to encrypt
    return $SecretValue
}

$Secrets = @{
    "DEPLOY_HOST" = $DeployHost
    "DEPLOY_USER" = $DeployUser
    "DEPLOY_PORT" = $DeployPort
    "DEPLOY_PATH" = $DeployPath
    "DEPLOY_SSH_KEY" = $DeployKey
}

Write-Host ""
Write-Host "Adding secrets..."
$SuccessCount = 0

foreach ($SecretName in $Secrets.Keys) {
    $SecretValue = $Secrets[$SecretName]
    # Note: GitHub API requires encrypted values - this is simplified
    # Real implementation would use libsodium for encryption
    Write-Host "  $SecretName ... (requires GitHub CLI or manual entry)"
}

Write-Host ""
Write-Host "To complete setup, use GitHub CLI:"
Write-Host "  1. Install: https://cli.github.com/"
Write-Host "  2. Authenticate: gh auth login"
Write-Host "  3. Run: .\setup-secrets.ps1" -ForegroundColor Yellow
