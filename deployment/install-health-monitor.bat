@echo off
REM Health Monitor Agent Installation Script
REM This script will install the Lab Health Monitor on lab computers

setlocal enabledelayedexpansion

echo ============================================
echo   Lab Health Monitor Agent Installer
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

echo [1/6] Checking system requirements...

REM Check Windows version
ver | find "10" >nul
if %errorLevel% neq 0 (
    ver | find "11" >nul
    if %errorLevel% neq 0 (
        echo WARNING: Windows 10 or 11 recommended
    )
)

REM Check if Node.js is installed
node --version >nul 2>&1
if %errorLevel% neq 0 (
    echo.
    echo Node.js not found. Please install Node.js first:
    echo https://nodejs.org/
    echo.
    pause
    exit /b 1
) else (
    echo ✓ Node.js found
)

echo.
echo [2/6] Creating installation directory...

REM Set installation directory
set INSTALL_DIR=C:\Program Files\Lab Health Monitor
set CONFIG_DIR=%PROGRAMDATA%\Lab Health Monitor
set LOG_DIR=%CONFIG_DIR%\logs

REM Create directories
if not exist "%INSTALL_DIR%" mkdir "%INSTALL_DIR%"
if not exist "%CONFIG_DIR%" mkdir "%CONFIG_DIR%"
if not exist "%LOG_DIR%" mkdir "%LOG_DIR%"

echo ✓ Directory created: %INSTALL_DIR%

echo.
echo [3/6] Copying application files...

REM Copy all application files (from parent directory)
xcopy /E /I /H /Y "%~dp0..\health-monitor-agent\*" "%INSTALL_DIR%\" >nul
if %errorLevel% neq 0 (
    echo ERROR: Failed to copy application files
    echo Source path: %~dp0..\health-monitor-agent\
    echo Target path: %INSTALL_DIR%\
    pause
    exit /b 1
)

echo ✓ Application files copied

echo.
echo [4/6] Installing dependencies...

cd "%INSTALL_DIR%"
call npm install --silent
if %errorLevel% neq 0 (
    echo ERROR: Failed to install dependencies
    echo Please check your internet connection
    pause
    exit /b 1
)

echo ✓ Dependencies installed

echo.
echo [5/6] Configuring application...

REM Generate unique API key for this computer
set API_KEY=
for /f %%i in ('powershell -Command "[System.Guid]::NewGuid().ToString()"') do set API_KEY=%%i

REM Create default configuration
set CONFIG_FILE=%CONFIG_DIR%\config.json

if not exist "%CONFIG_FILE%" (
    echo Creating default configuration...
    (
    echo {
    echo   "server": {
    echo     "url": "http://localhost/CAPSTONE-LAB-MANAGEMENT-SYSTEM",
    echo     "apiKey": "%API_KEY%"
    echo   },
    echo   "computer": {
    echo     "name": "%COMPUTERNAME%",
    echo     "location": "Lab Computer",
    echo     "room": "AUTO-ASSIGNED"
    echo   },
    echo   "monitoring": {
    echo     "interval": 30000,
    echo     "enableTemperature": true,
    echo     "enableProcesses": true,
    echo     "enableNetwork": true
    echo   },
    echo   "thresholds": {
    echo     "cpu": 85,
    echo     "memory": 90,
    echo     "disk": 95,
    echo     "temperature": 80
    echo   },
    echo   "autoStart": true,
    echo   "minimizeToTray": true,
    echo   "notifications": true
    echo }
    ) > "%CONFIG_FILE%"
    
    echo ✓ Generated unique API key: %API_KEY%
    echo ✓ Computer will auto-register when health monitor starts
)

REM Set permissions on config directory
icacls "%CONFIG_DIR%" /grant Users:M >nul 2>&1

echo ✓ Configuration created

echo.
echo [6/6] Creating Windows service and shortcuts...

REM Create Windows service using NSSM (if available) or Task Scheduler
schtasks /query /tn "Lab Health Monitor" >nul 2>&1
if %errorLevel% equ 0 (
    echo Removing existing scheduled task...
    schtasks /delete /tn "Lab Health Monitor" /f >nul
)

echo Creating scheduled task for auto-start...
schtasks /create /tn "Lab Health Monitor" /tr "\"%INSTALL_DIR%\start-monitor.bat\"" /sc onstart /ru SYSTEM /rl highest /f >nul
if %errorLevel% neq 0 (
    echo WARNING: Could not create auto-start task
    echo The application will need to be started manually
)

REM Create desktop shortcut
set SHORTCUT_PATH=%PUBLIC%\Desktop\Lab Health Monitor.lnk
powershell -Command "$WshShell = New-Object -comObject WScript.Shell; $Shortcut = $WshShell.CreateShortcut('%SHORTCUT_PATH%'); $Shortcut.TargetPath = '%INSTALL_DIR%\start-monitor.bat'; $Shortcut.WorkingDirectory = '%INSTALL_DIR%'; $Shortcut.Description = 'Lab Health Monitor Agent'; $Shortcut.Save()"

REM Create Start Menu shortcut
set START_MENU_DIR=%PROGRAMDATA%\Microsoft\Windows\Start Menu\Programs\Lab Health Monitor
if not exist "%START_MENU_DIR%" mkdir "%START_MENU_DIR%"
set START_SHORTCUT=%START_MENU_DIR%\Lab Health Monitor.lnk
powershell -Command "$WshShell = New-Object -comObject WScript.Shell; $Shortcut = $WshShell.CreateShortcut('%START_SHORTCUT%'); $Shortcut.TargetPath = '%INSTALL_DIR%\start-monitor.bat'; $Shortcut.WorkingDirectory = '%INSTALL_DIR%'; $Shortcut.Description = 'Lab Health Monitor Agent'; $Shortcut.Save()"

echo ✓ Shortcuts created

echo.
echo ============================================
echo   Installation completed successfully!
echo ============================================
echo.
echo Installation directory: %INSTALL_DIR%
echo Configuration directory: %CONFIG_DIR%
echo.
echo The Health Monitor Agent will start automatically with Windows.
echo You can also start it manually from:
echo - Desktop shortcut
echo - Start Menu
echo - Command: "%INSTALL_DIR%\health-monitor.exe"
echo.
echo Configuration file: %CONFIG_FILE%
echo Edit this file to customize server settings and thresholds.
echo.
echo Starting the application now...

REM Start the application
start "" "%INSTALL_DIR%\start-monitor.bat"

echo.
echo Press any key to exit...
pause >nul