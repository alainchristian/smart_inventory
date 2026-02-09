# Laravel Development Server - Firewall Setup
# Run this as Administrator

Write-Host "============================================" -ForegroundColor Cyan
Write-Host "Laravel Server - Firewall Configuration" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

# Check if running as administrator
$isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)

if (-not $isAdmin) {
    Write-Host "ERROR: This script must be run as Administrator!" -ForegroundColor Red
    Write-Host ""
    Write-Host "Right-click this file and select 'Run with PowerShell as Administrator'" -ForegroundColor Yellow
    Write-Host ""
    pause
    exit 1
}

Write-Host "Creating firewall rule for Laravel Dev Server..." -ForegroundColor Yellow

# Remove existing rule if it exists
$existingRule = Get-NetFirewallRule -DisplayName "Laravel Dev Server Port 8000" -ErrorAction SilentlyContinue
if ($existingRule) {
    Write-Host "Removing existing rule..." -ForegroundColor Yellow
    Remove-NetFirewallRule -DisplayName "Laravel Dev Server Port 8000"
}

# Create new firewall rule
try {
    New-NetFirewallRule `
        -DisplayName "Laravel Dev Server Port 8000" `
        -Description "Allow inbound connections to Laravel development server on port 8000" `
        -Direction Inbound `
        -Action Allow `
        -Protocol TCP `
        -LocalPort 8000 `
        -Profile Private `
        -Enabled True | Out-Null

    Write-Host ""
    Write-Host "SUCCESS! Firewall rule created." -ForegroundColor Green
    Write-Host ""
    Write-Host "Port 8000 is now allowed through Windows Firewall." -ForegroundColor Green
    Write-Host "Your phone should be able to connect to:" -ForegroundColor Cyan
    Write-Host "  http://192.168.1.66:8000" -ForegroundColor White
    Write-Host ""

} catch {
    Write-Host ""
    Write-Host "ERROR: Failed to create firewall rule" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
    Write-Host ""
}

Write-Host "You can verify the rule in:" -ForegroundColor Yellow
Write-Host "  Control Panel > Windows Defender Firewall > Advanced Settings > Inbound Rules" -ForegroundColor White
Write-Host ""

pause
