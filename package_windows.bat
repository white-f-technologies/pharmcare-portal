@echo off
title PharmCare Windows Packaging Tool
echo ========================================================
echo   PHARMCARE WINDOWS STANDALONE PACKAGING SCRIPT
echo ========================================================

echo 1. Clearing application cache...
call php artisan config:clear
call php artisan route:clear
call php artisan view:clear

echo 2. Building front-end assets...
call npm run build

echo 3. Packaging client distribution ZIP...
call php vendor-tools/package_clean.php

echo 4. Compiling Inno Setup executable installer...
set "ISCC_PATH="
if exist "C:\Program Files (x86)\Inno Setup 6\ISCC.exe" set "ISCC_PATH=C:\Program Files (x86)\Inno Setup 6\ISCC.exe"
if exist "C:\Program Files\Inno Setup 6\ISCC.exe" set "ISCC_PATH=C:\Program Files\Inno Setup 6\ISCC.exe"
where ISCC >nul 2>&1
if not errorlevel 1 set "ISCC_PATH=ISCC"

if defined ISCC_PATH (
    echo Compiling installer with ISCC...
    "%ISCC_PATH%" installer.iss
    echo Setup executable check complete.
) else (
    echo Note: Inno Setup (ISCC.exe) not detected on PATH.
    echo Standalone ZIP package generated in dist\ directory.
)

echo ========================================================
echo   PACKAGING COMPLETE!
echo   Output files located in dist\ directory:
echo   - PharmCare_Standalone_v2.1.0_Client.zip
echo   - PharmCare_Setup_v2.1.0.exe (if ISCC present)
echo ========================================================
pause
