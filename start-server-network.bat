@echo off
echo ============================================
echo Starting Laravel Server (Network Accessible)
echo ============================================
echo.
echo Your PC IP: 192.168.1.66
echo Server will be accessible at: http://192.168.1.66:8000
echo.
echo ⚠️  IMPORTANT: CAMERA ACCESS REQUIRES HTTPS!
echo.
echo HTTP (http://...) will NOT work for phone camera.
echo Modern browsers block camera over HTTP for security.
echo.
echo For camera scanning to work:
echo   USE: start-with-ngrok.bat (provides HTTPS)
echo   OR: Set up SSL certificate (see ENABLE_HTTPS.md)
echo.
echo This HTTP server is only useful for:
echo   - Desktop browser access
echo   - Testing basic functionality
echo   - Development without camera features
echo.
echo Make sure:
echo - Phone is on same WiFi network
echo - Windows Firewall allows port 8000
echo.
echo Press Ctrl+C to stop server
echo ============================================
echo.

cd /d "%~dp0"
php artisan serve --host=0.0.0.0 --port=8000
