@echo off
title PharmCare Offline Desktop Application
setlocal EnableDelayedExpansion

set "APPDATA_DIR=%APPDATA%\PharmCare"
set "APP_DIR=%~dp0"
if "%APP_DIR:~-1%"=="\" set "APP_DIR=%APP_DIR:~0,-1%"

REM 1. Locate PHP binary
set "PHP_BIN="
if exist "%APP_DIR%\php\php.exe" (
    set "PHP_BIN=%APP_DIR%\php\php.exe"
) else if exist "C:\xampp\php\php.exe" (
    set "PHP_BIN=C:\xampp\php\php.exe"
) else (
    where php >nul 2>&1
    if not errorlevel 1 set "PHP_BIN=php"
)

if "%PHP_BIN%"=="" (
    echo ERROR: PHP execution binary not found.
    echo Please ensure PHP 8.2+ is installed or bundled in the application directory.
    pause
    exit /b 1
)

REM Add PHP directory to PATH for this process
for %%I in ("%PHP_BIN%") do set "PHP_DIR=%%~dpI"
set "PATH=%PHP_DIR%;%PATH%"

REM 2. Ensure writable data directory exists
if not exist "%APPDATA_DIR%" mkdir "%APPDATA_DIR%"

REM First-run: copy .env.example if .env missing
if not exist "%APPDATA_DIR%\.env" (
    echo ========================================================
    echo   PHARMCARE - FIRST TIME SETUP
    echo ========================================================
    echo.
    copy "%APP_DIR%\.env.example" "%APPDATA_DIR%\.env" > nul
)

REM Copy .env to project root
copy "%APPDATA_DIR%\.env" "%APP_DIR%\.env" > nul 2>&1

REM 3. Run bootstrap
echo [1/3] Bootstrapping application...
pushd "%APP_DIR%"
"%PHP_BIN%" artisan app:bootstrap
if !errorlevel! neq 0 (
    echo.
    echo ERROR: Bootstrap failed. Please contact support.
    popd
    pause
    exit /b 1
)
popd

REM Save updated .env
copy "%APP_DIR%\.env" "%APPDATA_DIR%\.env" > nul 2>&1

REM Ensure writable storage directory exists
if not exist "%APPDATA_DIR%\storage\app\public" mkdir "%APPDATA_DIR%\storage\app\public"

REM 4. Kill any existing process on port 8000
for /f "tokens=5" %%a in ('netstat -aon ^| findstr :8000 ^| findstr LISTENING') do taskkill /PID %%a /F >nul 2>&1

REM 5. Dynamically write and execute background launcher with exact PHP_BIN path
echo [2/3] Starting server in background...
(
    echo Set WshShell = CreateObject^("WScript.Shell"^)
    echo WshShell.CurrentDirectory = "%APP_DIR%"
    echo WshShell.Run "cmd /c ""%PHP_BIN%"" artisan serve --host=127.0.0.1 --port=8000", 0, False
    echo Set WshShell = Nothing
) > "%APPDATA_DIR%\launch_server.vbs"

wscript "%APPDATA_DIR%\launch_server.vbs"

REM 6. Launch browser
echo [3/3] Launching PharmCare...
ping 127.0.0.1 -n 4 > nul
start "" "http://127.0.0.1:8000"

echo.
echo PharmCare is now running in the background.
echo You may close this window.
ping 127.0.0.1 -n 5 > nul
