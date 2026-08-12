@echo off
title PharmCare Offline Desktop Application
setlocal EnableDelayedExpansion

set APPDATA_DIR=%APPDATA%\PharmCare

REM Locate PHP binary (check bundled php folder first, then PATH, then XAMPP)
if exist "%~dp0php\php.exe" (
    set "PATH=%~dp0php;!PATH!"
) else (
    where php >nul 2>&1
    if errorlevel 1 (
        if exist "C:\xampp\php\php.exe" (
            set "PATH=C:\xampp\php;!PATH!"
        ) else (
            echo ERROR: PHP execution binary not found.
            echo Please ensure PHP 8.2+ is installed or bundled in the application directory.
            pause
            exit /b 1
        )
    )
)

REM Ensure writable data directory exists
if not exist "%APPDATA_DIR%" mkdir "%APPDATA_DIR%"

REM First-run: copy .env.example to writable location
if not exist "%APPDATA_DIR%\.env" (
    echo ========================================================
    echo   PHARMCARE - FIRST TIME SETUP
    echo ========================================================
    echo.
    copy "%~dp0.env.example" "%APPDATA_DIR%\.env" > nul
)

REM Copy .env from writable location to project root (artisan needs it here)
copy "%APPDATA_DIR%\.env" "%~dp0.env" > nul 2>&1

REM Run bootstrap (generates key, migrations, seeds system data, creates admin)
echo [1/3] Bootstrapping application...
pushd "%~dp0"
php artisan app:bootstrap
if errorlevel 1 (
    echo.
    echo ERROR: Bootstrap failed. Please contact support.
    pause
    exit /b 1
)
popd

REM Save updated .env (with generated APP_KEY) back to writable location
copy "%~dp0.env" "%APPDATA_DIR%\.env" > nul 2>&1

REM Ensure writable storage/app/public directory exists before link creation
if not exist "%APPDATA_DIR%\storage\app\public" mkdir "%APPDATA_DIR%\storage\app\public"

REM Ensure storage directory link exists (public/storage -> APPDATA storage)
if exist "%~dp0public\storage" (
    dir /a:l "%~dp0public" 2>nul | findstr /i "storage" >nul
    if errorlevel 1 (
        rem It is a plain directory copied by setup, remove it to enable junction link
        rmdir /S /Q "%~dp0public\storage" >nul 2>&1
    )
)
if not exist "%~dp0public\storage" (
    mklink /J "%~dp0public\storage" "%APPDATA_DIR%\storage\app\public" >nul 2>&1
)

REM Kill any existing PHP server on port 8000
for /f "tokens=5" %%a in ('netstat -aon ^| findstr :8000 ^| findstr LISTENING') do taskkill /PID %%a /F >nul 2>&1

REM Start server hidden in background (no window)
echo [2/3] Starting server in background...
wscript "%~dp0start_server.vbs"

REM Wait for server to be ready
echo [3/3] Launching PharmCare...
timeout /t 3 > nul
start "" "http://127.0.0.1:8000"

echo.
echo PharmCare is now running in the background.
echo You may close this window.
timeout /t 5 > nul
