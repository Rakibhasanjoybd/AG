# WhatsApp Customer Care System - Quick Setup Script
# Run this in PowerShell from the AGCO root directory

Write-Host "============================================" -ForegroundColor Green
Write-Host "WhatsApp Customer Care System Setup" -ForegroundColor Green
Write-Host "============================================" -ForegroundColor Green
Write-Host ""

# Change to core directory
Set-Location -Path ".\core"

Write-Host "Step 1: Running database migration..." -ForegroundColor Yellow
php artisan migrate --path=database/migrations/2026_01_11_000002_create_whatsapp_contacts_table.php
Write-Host "✓ Migration completed!" -ForegroundColor Green
Write-Host ""

Write-Host "Step 2: Creating WhatsApp images directory..." -ForegroundColor Yellow
$whatsappDir = ".\public\assets\images\whatsapp"
if (-not (Test-Path $whatsappDir)) {
    New-Item -ItemType Directory -Path $whatsappDir -Force | Out-Null
    Write-Host "✓ Directory created: $whatsappDir" -ForegroundColor Green
} else {
    Write-Host "✓ Directory already exists" -ForegroundColor Green
}
Write-Host ""

Write-Host "Step 3: Clearing cache..." -ForegroundColor Yellow
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
Write-Host "✓ Cache cleared!" -ForegroundColor Green
Write-Host ""

Write-Host "============================================" -ForegroundColor Green
Write-Host "Setup Complete!" -ForegroundColor Green
Write-Host "============================================" -ForegroundColor Green
Write-Host ""
Write-Host "Next Steps:" -ForegroundColor Cyan
Write-Host "1. Access admin panel: /admin/whatsapp-contacts" -ForegroundColor White
Write-Host "2. Add your first WhatsApp contact" -ForegroundColor White
Write-Host "3. Users will see the floating WhatsApp button" -ForegroundColor White
Write-Host ""
Write-Host "Optional: Import sample data from:" -ForegroundColor Cyan
Write-Host "add_sample_whatsapp_contacts.sql" -ForegroundColor White
Write-Host ""
Write-Host "For full documentation, see:" -ForegroundColor Cyan
Write-Host "WHATSAPP_CUSTOMER_CARE_GUIDE.md" -ForegroundColor White
Write-Host ""

# Return to root directory
Set-Location -Path ".."

Write-Host "Press any key to exit..." -ForegroundColor Gray
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
