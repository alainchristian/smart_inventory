@echo off
echo ============================================
echo Smart Inventory - HTTPS Setup via ngrok
echo ============================================
echo.
echo This script helps you set up HTTPS for camera access.
echo.
echo REQUIREMENTS:
echo   1. ngrok installed (download from https://ngrok.com/download)
echo   2. Laravel server running in another terminal
echo.
echo ============================================
echo.

REM Check if ngrok is installed
where ngrok >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: ngrok not found!
    echo.
    echo Please install ngrok:
    echo   1. Download from: https://ngrok.com/download
    echo   2. Extract ngrok.exe
    echo   3. Add to PATH or place in this folder
    echo.
    pause
    exit /b 1
)

echo ngrok is installed. Starting tunnel...
echo.
echo IMPORTANT: Keep this window open!
echo.
echo After ngrok starts:
echo   1. Copy the HTTPS URL (https://xxxxx.ngrok-free.app)
echo   2. Run: update-env-ngrok.bat [paste-url-here]
echo   3. Test on your phone
echo.
echo Starting ngrok...
echo ============================================
echo.

ngrok http 8000
