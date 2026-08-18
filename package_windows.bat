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
call npm.cmd run build

echo 3. Packaging client distribution ZIP...
call php vendor-tools/package_clean.php

echo 4. Checking Inno Setup Compiler...
set "ISCC_PATH="
if exist "C:\Program Files (x86)\Inno Setup 6\ISCC.exe" set "ISCC_PATH=C:\Program Files (x86)\Inno Setup 6\ISCC.exe"
if exist "C:\Program Files\Inno Setup 6\ISCC.exe" set "ISCC_PATH=C:\Program Files\Inno Setup 6\ISCC.exe"

if "%ISCC_PATH%"=="" goto NO_ISCC

echo Compiling installer with ISCC...
"%ISCC_PATH%" installer.iss
goto END

:NO_ISCC
echo Note: Inno Setup (ISCC.exe) not detected on PATH.
echo Standalone ZIP package generated in dist\ directory.

:END
echo ========================================================
echo   PACKAGING COMPLETE!
echo   Output files located in dist\ directory:
echo   - PharmCare_Setup_v2.2.0.exe (Windows Installer)
echo   - PharmCare_Standalone_v2.2.0_Client.zip (Portable ZIP)
echo ========================================================
