# Laravel Server Network Diagnostics
# This script checks if your server is properly configured for phone access

Write-Host "============================================" -ForegroundColor Cyan
Write-Host "Laravel Server Network Diagnostics" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

$allGood = $true

# 1. Check PC's IP Address
Write-Host "[1/6] Checking PC's IP Address..." -ForegroundColor Yellow
$wifiAdapter = Get-NetIPAddress -AddressFamily IPv4 -InterfaceAlias "Wi-Fi" -ErrorAction SilentlyContinue
if ($wifiAdapter) {
    $pcIP = $wifiAdapter.IPAddress
    Write-Host "  PC WiFi IP: $pcIP" -ForegroundColor Green

    if ($pcIP -like "192.168.*" -or $pcIP -like "10.*") {
        Write-Host "  Status: OK - Valid local network IP" -ForegroundColor Green
    } else {
        Write-Host "  WARNING: IP doesn't look like local network" -ForegroundColor Yellow
    }
} else {
    Write-Host "  ERROR: WiFi adapter not found or not connected" -ForegroundColor Red
    $allGood = $false
}
Write-Host ""

# 2. Check if server is running
Write-Host "[2/6] Checking if Laravel server is running..." -ForegroundColor Yellow
$serverProcess = Get-Process php -ErrorAction SilentlyContinue
if ($serverProcess) {
    Write-Host "  Status: PHP process is running" -ForegroundColor Green
} else {
    Write-Host "  WARNING: No PHP process found - server might not be running" -ForegroundColor Yellow
    $allGood = $false
}
Write-Host ""

# 3. Check if port 8000 is listening
Write-Host "[3/6] Checking if port 8000 is listening..." -ForegroundColor Yellow
$portCheck = netstat -an | Select-String ":8000.*LISTENING"
if ($portCheck) {
    $portLine = $portCheck[0].ToString().Trim()

    if ($portLine -match "0\.0\.0\.0:8000") {
        Write-Host "  Status: OK - Listening on all interfaces (0.0.0.0:8000)" -ForegroundColor Green
    } elseif ($portLine -match "127\.0\.0\.1:8000") {
        Write-Host "  ERROR: Listening on localhost only (127.0.0.1:8000)" -ForegroundColor Red
        Write-Host "  FIX: Restart server with: php artisan serve --host=0.0.0.0 --port=8000" -ForegroundColor Yellow
        $allGood = $false
    } else {
        Write-Host "  Port 8000 is listening: $portLine" -ForegroundColor Yellow
    }
} else {
    Write-Host "  ERROR: Port 8000 is not listening" -ForegroundColor Red
    Write-Host "  FIX: Start server with: php artisan serve --host=0.0.0.0 --port=8000" -ForegroundColor Yellow
    $allGood = $false
}
Write-Host ""

# 4. Check firewall rule
Write-Host "[4/6] Checking Windows Firewall..." -ForegroundColor Yellow
$firewallRule = Get-NetFirewallRule -DisplayName "*Laravel*8000*" -ErrorAction SilentlyContinue
if ($firewallRule) {
    Write-Host "  Status: OK - Firewall rule exists" -ForegroundColor Green
    if ($firewallRule.Enabled -eq $true) {
        Write-Host "  Rule is enabled" -ForegroundColor Green
    } else {
        Write-Host "  WARNING: Rule exists but is disabled" -ForegroundColor Yellow
    }
} else {
    Write-Host "  WARNING: No firewall rule found for Laravel" -ForegroundColor Yellow
    Write-Host "  FIX: Run setup-firewall.ps1 as Administrator" -ForegroundColor Yellow
    $allGood = $false
}
Write-Host ""

# 5. Test local connectivity
Write-Host "[5/6] Testing local connectivity..." -ForegroundColor Yellow
if ($pcIP) {
    try {
        $testConnection = Test-NetConnection -ComputerName $pcIP -Port 8000 -WarningAction SilentlyContinue
        if ($testConnection.TcpTestSucceeded) {
            Write-Host "  Status: OK - Port 8000 is accessible locally" -ForegroundColor Green
        } else {
            Write-Host "  ERROR: Cannot connect to port 8000 locally" -ForegroundColor Red
            $allGood = $false
        }
    } catch {
        Write-Host "  WARNING: Could not test connection" -ForegroundColor Yellow
    }
}
Write-Host ""

# 6. Check .env configuration
Write-Host "[6/6] Checking .env configuration..." -ForegroundColor Yellow
$envPath = Join-Path $PSScriptRoot ".env"
if (Test-Path $envPath) {
    $envContent = Get-Content $envPath
    $appUrlLine = $envContent | Where-Object { $_ -match "^APP_URL=" }

    if ($appUrlLine) {
        Write-Host "  Current APP_URL: $appUrlLine" -ForegroundColor Cyan

        if ($appUrlLine -match "localhost" -or $appUrlLine -match "127\.0\.0\.1") {
            Write-Host "  WARNING: APP_URL uses localhost" -ForegroundColor Yellow
            Write-Host "  RECOMMENDED: APP_URL=http://${pcIP}:8000" -ForegroundColor Yellow
        } elseif ($pcIP -and ($appUrlLine -match $pcIP)) {
            Write-Host "  Status: OK - APP_URL matches PC IP" -ForegroundColor Green
        } else {
            Write-Host "  Status: APP_URL is set" -ForegroundColor Cyan
        }
    }
} else {
    Write-Host "  WARNING: .env file not found" -ForegroundColor Yellow
}
Write-Host ""

# Summary
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "DIAGNOSTIC SUMMARY" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

if ($allGood) {
    Write-Host "ALL CHECKS PASSED!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Your server should be accessible from phone at:" -ForegroundColor Green
    if ($pcIP) {
        Write-Host "  http://${pcIP}:8000" -ForegroundColor White
    }
    Write-Host ""
    Write-Host "Next steps:" -ForegroundColor Cyan
    Write-Host "  1. Make sure phone is on same WiFi network" -ForegroundColor White
    Write-Host "  2. Open phone browser and test: http://${pcIP}:8000" -ForegroundColor White
    Write-Host "  3. If works, scan QR code on transfer page" -ForegroundColor White
} else {
    Write-Host "ISSUES FOUND - See errors above" -ForegroundColor Red
    Write-Host ""
    Write-Host "Common fixes:" -ForegroundColor Yellow
    Write-Host "  1. Restart server: php artisan serve --host=0.0.0.0 --port=8000" -ForegroundColor White
    Write-Host "  2. Run: setup-firewall.ps1 (as Administrator)" -ForegroundColor White
    Write-Host "  3. Make sure phone is on same WiFi network" -ForegroundColor White
}

Write-Host ""
pause
