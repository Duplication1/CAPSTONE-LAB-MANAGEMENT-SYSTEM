@echo off
REM Health Monitor Agent Uninstallation Script

setlocal enabledelayedexpansion

echo ============================================
echo   Lab Health Monitor Agent Uninstaller
echo ============================================
echo.

REM Check if running as administrator
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo ERROR: This script must be run as Administrator
    echo Please right-click and select "Run as administrator"
    pause
    exit /b 1
)

echo This will completely remove the Lab Health Monitor Agent from this computer.
echo.
set /p CONFIRM=Are you sure you want to continue? (y/N): 

if /i "!CONFIRM!" neq "y" (
    echo Uninstallation cancelled.
    pause
    exit /b 0
)

echo.
echo [1/5] Stopping application...

REM Stop the application if running
taskkill /f /im "health-monitor.exe" >nul 2>&1
taskkill /f /im "Lab Health Monitor.exe" >nul 2>&1

echo ✓ Application stopped

echo.
echo [2/5] Removing scheduled tasks...

REM Remove scheduled task
schtasks /query /tn "Lab Health Monitor" >nul 2>&1
if %errorLevel% equ 0 (
    schtasks /delete /tn "Lab Health Monitor" /f >nul
    echo ✓ Scheduled task removed
) else (
    echo ✓ No scheduled task found
)

echo.
echo [3/5] Removing shortcuts...

REM Remove desktop shortcut
set SHORTCUT_PATH=%PUBLIC%\Desktop\Lab Health Monitor.lnk
if exist "%SHORTCUT_PATH%" (
    del "%SHORTCUT_PATH%" >nul 2>&1
    echo ✓ Desktop shortcut removed
)

REM Remove Start Menu folder
set START_MENU_DIR=%PROGRAMDATA%\Microsoft\Windows\Start Menu\Programs\Lab Health Monitor
if exist "%START_MENU_DIR%" (
    rmdir /s /q "%START_MENU_DIR%" >nul 2>&1
    echo ✓ Start Menu shortcuts removed
)

echo.
echo [4/5] Removing application files...

REM Set installation directory
set INSTALL_DIR=C:\Program Files\Lab Health Monitor

if exist "%INSTALL_DIR%" (
    rmdir /s /q "%INSTALL_DIR%" >nul 2>&1
    if exist "%INSTALL_DIR%" (
        echo WARNING: Some files in %INSTALL_DIR% could not be removed
        echo Please remove them manually after reboot
    ) else (
        echo ✓ Application files removed
    )
) else (
    echo ✓ No application files found
)

echo.
echo [5/5] Cleaning up configuration...

set CONFIG_DIR=%PROGRAMDATA%\Lab Health Monitor

set /p KEEP_CONFIG=Keep configuration and logs for future installations? (y/N): 

if /i "!KEEP_CONFIG!" neq "y" (
    if exist "%CONFIG_DIR%" (
        rmdir /s /q "%CONFIG_DIR%" >nul 2>&1
        echo ✓ Configuration and logs removed
    )
) else (
    echo ✓ Configuration and logs preserved in %CONFIG_DIR%
)

echo.
echo ============================================
echo   Uninstallation completed successfully!
echo ============================================
echo.
echo The Lab Health Monitor Agent has been removed from this computer.

if /i "!KEEP_CONFIG!" equ "y" (
    echo.
    echo Configuration files preserved in: %CONFIG_DIR%
    echo You can safely delete this folder if no longer needed.
)

echo.
echo Press any key to exit...
pause >nul