@echo off
REM Computer Registration Script for Health Monitor
REM This script registers a computer in the health monitoring database

setlocal enabledelayedexpansion

echo ============================================
echo   Computer Registration for Health Monitor
echo ============================================
echo.

REM Get computer information
set COMPUTER_NAME=%COMPUTERNAME%
set IP_ADDRESS=
set MAC_ADDRESS=

echo Gathering computer information...

REM Get IP address
for /f "tokens=2 delims=:" %%i in ('ipconfig ^| find "IPv4 Address"') do (
    set IP_ADDRESS=%%i
    set IP_ADDRESS=!IP_ADDRESS: =!
    goto :ip_done
)
:ip_done

REM Get MAC address
for /f "tokens=2 delims=:" %%i in ('getmac /fo list ^| find "Physical Address"') do (
    set MAC_ADDRESS=%%i
    set MAC_ADDRESS=!MAC_ADDRESS: =!
    set MAC_ADDRESS=!MAC_ADDRESS:-=:!
    goto :mac_done
)
:mac_done

REM Generate unique API key
for /f %%i in ('powershell -Command "[guid]::NewGuid().ToString()"') do set API_KEY=%%i

echo Computer Name: %COMPUTER_NAME%
echo IP Address: %IP_ADDRESS%
echo MAC Address: %MAC_ADDRESS%
echo API Key: %API_KEY%

REM Create PowerShell script for database registration
set PS_SCRIPT=%TEMP%\register_computer.ps1

(
echo $serverUrl = "http://localhost/CAPSTONE-LAB-MANAGEMENT-SYSTEM"
echo $computerName = "%COMPUTER_NAME%"
echo $ipAddress = "%IP_ADDRESS%"
echo $macAddress = "%MAC_ADDRESS%"
echo $apiKey = "%API_KEY%"
echo.
echo # Create registration data
echo $registrationData = @{
echo     computer_name = $computerName
echo     ip_address = $ipAddress
echo     mac_address = $macAddress
echo     location = "Lab Computer"
echo     lab_room = "LAB-AUTO"
echo     operating_system = (Get-WmiObject Win32_OperatingSystem).Caption
echo     api_key = $apiKey
echo } ^| ConvertTo-Json
echo.
echo # Register computer via API
echo try {
echo     Write-Host "Registering computer with health monitoring system..."
echo     
echo     # First, try to insert directly into database if possible
echo     $insertSql = "INSERT IGNORE INTO computers (computer_name, ip_address, mac_address, location, lab_room, operating_system, status, api_key) VALUES ('$computerName', '$ipAddress', '$macAddress', 'Lab Computer', 'LAB-AUTO', '" + (Get-WmiObject Win32_OperatingSystem).Caption + "', 'online', '$apiKey')"
echo     
echo     Write-Host "Computer registration prepared"
echo     Write-Host "API Key: $apiKey"
echo     
echo     # Save config for the monitoring agent
echo     $configPath = "$env:PROGRAMDATA\Lab Health Monitor\config.json"
echo     $configDir = Split-Path $configPath -Parent
echo     if (!(Test-Path $configDir)) {
echo         New-Item -ItemType Directory -Path $configDir -Force
echo     }
echo     
echo     $config = @{
echo         server = @{
echo             url = $serverUrl
echo             apiKey = $apiKey
echo         }
echo         computer = @{
echo             name = $computerName
echo             location = "Lab Computer"
echo             room = "LAB-AUTO"
echo         }
echo         monitoring = @{
echo             interval = 30000
echo             enableTemperature = $true
echo             enableProcesses = $true
echo             enableNetwork = $true
echo         }
echo         thresholds = @{
echo             cpu = 85
echo             memory = 90
echo             disk = 95
echo             temperature = 80
echo         }
echo         autoStart = $true
echo         minimizeToTray = $true
echo         notifications = $true
echo     } ^| ConvertTo-Json -Depth 3
echo     
echo     $config ^| Out-File -FilePath $configPath -Encoding UTF8
echo     Write-Host "✓ Configuration saved to $configPath"
echo     
echo     Write-Host "✓ Computer registration completed"
echo     Write-Host ""
echo     Write-Host "IMPORTANT: Add this computer to the database manually:"
echo     Write-Host "SQL: $insertSql"
echo     Write-Host ""
echo     Write-Host "Or run this in phpMyAdmin or your MySQL client."
echo     
echo } catch {
echo     Write-Host "Registration failed: $_" -ForegroundColor Red
echo     exit 1
echo }
) > "%PS_SCRIPT%"

echo.
echo Registering computer...
powershell -ExecutionPolicy Bypass -File "%PS_SCRIPT%"

if %errorLevel% neq 0 (
    echo ERROR: Computer registration failed
    pause
    exit /b 1
)

echo.
echo ============================================
echo   Computer Registration Complete!
echo ============================================
echo.
echo The computer has been prepared for health monitoring.
echo.
echo NEXT STEPS:
echo 1. Add this computer to the database using the SQL command shown above
echo 2. Or use phpMyAdmin to add the computer record
echo 3. Then run the health monitor agent
echo.

REM Cleanup
del "%PS_SCRIPT%" 2>nul

pause