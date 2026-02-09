@echo off
setlocal enabledelayedexpansion

echo ============================================
echo Update APP_URL with ngrok HTTPS URL
echo ============================================
echo.

REM Check if URL provided as argument
if "%~1"=="" (
    echo USAGE: update-env-ngrok.bat [ngrok-url]
    echo.
    echo Example:
    echo   update-env-ngrok.bat https://abc123.ngrok-free.app
    echo.
    echo Steps:
    echo   1. Start ngrok: start-with-ngrok.bat
    echo   2. Copy HTTPS URL from ngrok output
    echo   3. Run this script with that URL
    echo.
    pause
    exit /b 1
)

set NGROK_URL=%~1

REM Remove trailing slash if present
if "!NGROK_URL:~-1!"=="/" set NGROK_URL=!NGROK_URL:~0,-1!

REM Validate URL format
echo !NGROK_URL! | findstr /R /C:"^https://.*\.ngrok.*\.app$" >nul
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Invalid ngrok URL format!
    echo.
    echo Expected format: https://xxxxx.ngrok-free.app
    echo You provided: !NGROK_URL!
    echo.
    pause
    exit /b 1
)

echo Updating .env file...
echo New APP_URL: !NGROK_URL!
echo.

REM Backup .env
copy .env .env.backup.%date:~-4,4%%date:~-10,2%%date:~-7,2%_%time:~0,2%%time:~3,2%%time:~6,2% >nul 2>&1

REM Update APP_URL in .env
powershell -Command "(Get-Content .env) -replace '^APP_URL=.*', 'APP_URL=!NGROK_URL!' | Set-Content .env"

if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Failed to update .env file
    pause
    exit /b 1
)

echo ✓ .env updated successfully
echo.
echo Clearing Laravel caches...

call php artisan config:clear
call php artisan config:cache
call php artisan route:clear

echo.
echo ============================================
echo SUCCESS! Configuration updated.
echo ============================================
echo.
echo APP_URL is now: !NGROK_URL!
echo.
echo NEXT STEPS:
echo   1. Refresh your browser (Ctrl+F5)
echo   2. Go to transfer page
echo   3. Click "Enable Phone Scanner"
echo   4. QR code should show HTTPS URL
echo   5. Scan with phone - camera should work!
echo.
echo If you restart ngrok, you must run this script
echo again with the new URL.
echo.
pause
