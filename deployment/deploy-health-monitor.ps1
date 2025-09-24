# Lab Health Monitor - Bulk Deployment Script
# This PowerShell script deploys the Health Monitor Agent to multiple computers in the lab network

param(
    [Parameter(Mandatory=$false)]
    [string[]]$ComputerNames = @(),
    
    [Parameter(Mandatory=$false)]
    [string]$ComputerListFile = "",
    
    [Parameter(Mandatory=$false)]
    [string]$Username = "",
    
    [Parameter(Mandatory=$false)]
    [string]$Password = "",
    
    [Parameter(Mandatory=$false)]
    [string]$InstallPath = "C:\Program Files\Lab Health Monitor",
    
    [Parameter(Mandatory=$false)]
    [string]$ServerUrl = "http://localhost/CAPSTONE-LAB-MANAGEMENT-SYSTEM",
    
    [Parameter(Mandatory=$false)]
    [switch]$TestConnection = $false,
    
    [Parameter(Mandatory=$false)]
    [switch]$UninstallOnly = $false,
    
    [Parameter(Mandatory=$false)]
    [switch]$Force = $false
)

# Script configuration
$ScriptPath = Split-Path -Parent $MyInvocation.MyCommand.Definition
$SourcePath = Join-Path $ScriptPath "health-monitor-agent"
$LogFile = Join-Path $ScriptPath "deployment-$(Get-Date -Format 'yyyy-MM-dd-HH-mm').log"

# Initialize logging
function Write-Log {
    param([string]$Message, [string]$Level = "INFO")
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $logMessage = "[$timestamp] [$Level] $Message"
    Write-Host $logMessage
    Add-Content -Path $LogFile -Value $logMessage
}

function Show-Usage {
    Write-Host @"
Lab Health Monitor - Bulk Deployment Script

Usage:
    .\deploy-health-monitor.ps1 [options]

Options:
    -ComputerNames <names>      : Comma-separated list of computer names
    -ComputerListFile <file>    : Text file containing computer names (one per line)
    -Username <username>        : Username for remote access
    -Password <password>        : Password for remote access
    -InstallPath <path>         : Installation path (default: C:\Program Files\Lab Health Monitor)
    -ServerUrl <url>            : Health monitoring server URL
    -TestConnection             : Only test connections, don't install
    -UninstallOnly             : Only uninstall, don't install
    -Force                      : Force installation even if already installed

Examples:
    .\deploy-health-monitor.ps1 -ComputerNames "LAB-PC-01,LAB-PC-02,LAB-PC-03"
    .\deploy-health-monitor.ps1 -ComputerListFile "computers.txt" -Username "admin" -Password "password"
    .\deploy-health-monitor.ps1 -ComputerNames "LAB-PC-01" -TestConnection
    .\deploy-health-monitor.ps1 -ComputerListFile "computers.txt" -UninstallOnly

"@
}

function Get-ComputerList {
    $computers = @()
    
    if ($ComputerListFile -and (Test-Path $ComputerListFile)) {
        Write-Log "Reading computer list from file: $ComputerListFile"
        $computers += Get-Content $ComputerListFile | Where-Object { $_.Trim() -ne "" }
    }
    
    if ($ComputerNames.Count -gt 0) {
        $computers += $ComputerNames
    }
    
    if ($computers.Count -eq 0) {
        Write-Log "No computers specified. Attempting to discover computers in domain..." "WARNING"
        try {
            $computers = Get-ADComputer -Filter "OperatingSystem -like '*Windows*'" | Select-Object -ExpandProperty Name
            Write-Log "Found $($computers.Count) computers in domain"
        } catch {
            Write-Log "Could not discover computers automatically. Please specify computer names." "ERROR"
            return @()
        }
    }
    
    return $computers | Sort-Object -Unique
}

function Test-RemoteConnection {
    param([string]$ComputerName)
    
    Write-Log "Testing connection to $ComputerName..."
    
    # Test ping
    if (-not (Test-Connection -ComputerName $ComputerName -Count 1 -Quiet)) {
        return @{Success=$false; Error="Computer not reachable via ping"}
    }
    
    # Test WinRM
    try {
        $result = Invoke-Command -ComputerName $ComputerName -ScriptBlock { $env:COMPUTERNAME } -ErrorAction Stop
        return @{Success=$true; Error=""}
    } catch {
        return @{Success=$false; Error="WinRM connection failed: $($_.Exception.Message)"}
    }
}

function Install-HealthMonitorRemote {
    param([string]$ComputerName)
    
    Write-Log "Installing Health Monitor on $ComputerName..."
    
    try {
        # Create remote session
        $session = New-PSSession -ComputerName $ComputerName -ErrorAction Stop
        
        # Copy files to remote computer
        Write-Log "Copying files to $ComputerName..."
        $remoteTemp = Invoke-Command -Session $session -ScriptBlock { $env:TEMP }
        $remotePath = "$remoteTemp\health-monitor-install"
        
        Copy-Item -Path $SourcePath -Destination $remotePath -ToSession $session -Recurse -Force
        
        # Execute installation script
        Write-Log "Executing installation on $ComputerName..."
        $installResult = Invoke-Command -Session $session -ScriptBlock {
            param($RemotePath, $InstallPath, $ServerUrl)
            
            # Create installation directory
            if (-not (Test-Path $InstallPath)) {
                New-Item -Path $InstallPath -ItemType Directory -Force | Out-Null
            }
            
            # Copy application files
            Copy-Item -Path "$RemotePath\*" -Destination $InstallPath -Recurse -Force
            
            # Install Node.js dependencies
            Set-Location $InstallPath
            & npm install --production --silent
            
            # Create configuration
            $configDir = "$env:PROGRAMDATA\Lab Health Monitor"
            $configFile = "$configDir\config.json"
            
            if (-not (Test-Path $configDir)) {
                New-Item -Path $configDir -ItemType Directory -Force | Out-Null
            }
            
            $config = @{
                server = @{
                    url = $ServerUrl
                    apiKey = "lab-health-monitor-key"
                }
                monitoring = @{
                    interval = 30000
                    enableTemperature = $true
                    enableProcesses = $true
                    enableNetwork = $true
                }
                thresholds = @{
                    cpu = 85
                    memory = 90
                    disk = 95
                    temperature = 80
                }
                autoStart = $true
                minimizeToTray = $true
                notifications = $true
            }
            
            $config | ConvertTo-Json -Depth 3 | Set-Content -Path $configFile
            
            # Create scheduled task
            $taskName = "Lab Health Monitor"
            $taskExists = Get-ScheduledTask -TaskName $taskName -ErrorAction SilentlyContinue
            
            if ($taskExists) {
                Unregister-ScheduledTask -TaskName $taskName -Confirm:$false
            }
            
            $action = New-ScheduledTaskAction -Execute "$InstallPath\health-monitor.exe"
            $trigger = New-ScheduledTaskTrigger -AtStartup
            $principal = New-ScheduledTaskPrincipal -UserId "SYSTEM" -LogonType ServiceAccount -RunLevel Highest
            $settings = New-ScheduledTaskSettingsSet -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries
            
            Register-ScheduledTask -TaskName $taskName -Action $action -Trigger $trigger -Principal $principal -Settings $settings | Out-Null
            
            # Start the application
            Start-Process -FilePath "$InstallPath\health-monitor.exe" -WindowStyle Hidden
            
            return "Installation completed successfully"
            
        } -ArgumentList $remotePath, $InstallPath, $ServerUrl
        
        # Clean up
        Remove-PSSession $session
        
        Write-Log "✓ Installation completed on $ComputerName"
        return @{Success=$true; Message=$installResult}
        
    } catch {
        Write-Log "✗ Installation failed on $ComputerName`: $($_.Exception.Message)" "ERROR"
        return @{Success=$false; Error=$_.Exception.Message}
    }
}

function Uninstall-HealthMonitorRemote {
    param([string]$ComputerName)
    
    Write-Log "Uninstalling Health Monitor from $ComputerName..."
    
    try {
        $result = Invoke-Command -ComputerName $ComputerName -ScriptBlock {
            param($InstallPath)
            
            # Stop application
            Get-Process -Name "health-monitor" -ErrorAction SilentlyContinue | Stop-Process -Force
            Get-Process -Name "Lab Health Monitor" -ErrorAction SilentlyContinue | Stop-Process -Force
            
            # Remove scheduled task
            $taskName = "Lab Health Monitor"
            $taskExists = Get-ScheduledTask -TaskName $taskName -ErrorAction SilentlyContinue
            if ($taskExists) {
                Unregister-ScheduledTask -TaskName $taskName -Confirm:$false
            }
            
            # Remove application files
            if (Test-Path $InstallPath) {
                Remove-Item -Path $InstallPath -Recurse -Force
            }
            
            # Remove configuration (optional)
            $configDir = "$env:PROGRAMDATA\Lab Health Monitor"
            if (Test-Path $configDir) {
                Remove-Item -Path $configDir -Recurse -Force
            }
            
            return "Uninstallation completed successfully"
            
        } -ArgumentList $InstallPath
        
        Write-Log "✓ Uninstallation completed on $ComputerName"
        return @{Success=$true; Message=$result}
        
    } catch {
        Write-Log "✗ Uninstallation failed on $ComputerName`: $($_.Exception.Message)" "ERROR"
        return @{Success=$false; Error=$_.Exception.Message}
    }
}

# Main script execution
Write-Log "Starting Lab Health Monitor bulk deployment..."
Write-Log "Log file: $LogFile"

# Validate parameters
if ($ComputerNames.Count -eq 0 -and -not $ComputerListFile) {
    Show-Usage
    exit 1
}

# Check if source files exist
if (-not $UninstallOnly -and -not (Test-Path $SourcePath)) {
    Write-Log "Source path not found: $SourcePath" "ERROR"
    Write-Log "Please ensure the health-monitor-agent folder exists in the same directory as this script." "ERROR"
    exit 1
}

# Get computer list
$computers = Get-ComputerList
if ($computers.Count -eq 0) {
    Write-Log "No computers to process" "ERROR"
    exit 1
}

Write-Log "Processing $($computers.Count) computers..."

# Results tracking
$results = @{
    Total = $computers.Count
    Success = 0
    Failed = 0
    Skipped = 0
    Details = @{}
}

# Process each computer
foreach ($computer in $computers) {
    Write-Log "Processing computer: $computer" "INFO"
    
    # Test connection
    $connectionTest = Test-RemoteConnection -ComputerName $computer
    
    if (-not $connectionTest.Success) {
        Write-Log "✗ Connection failed to $computer`: $($connectionTest.Error)" "ERROR"
        $results.Failed++
        $results.Details[$computer] = @{Status="Failed"; Error=$connectionTest.Error}
        continue
    }
    
    if ($TestConnection) {
        Write-Log "✓ Connection successful to $computer"
        $results.Success++
        $results.Details[$computer] = @{Status="Connection OK"}
        continue
    }
    
    # Perform installation or uninstallation
    if ($UninstallOnly) {
        $result = Uninstall-HealthMonitorRemote -ComputerName $computer
    } else {
        $result = Install-HealthMonitorRemote -ComputerName $computer
    }
    
    if ($result.Success) {
        $results.Success++
        $results.Details[$computer] = @{Status="Success"; Message=$result.Message}
    } else {
        $results.Failed++
        $results.Details[$computer] = @{Status="Failed"; Error=$result.Error}
    }
    
    Start-Sleep -Seconds 2  # Brief pause between operations
}

# Summary report
Write-Log "==============================================="
Write-Log "DEPLOYMENT SUMMARY"
Write-Log "==============================================="
Write-Log "Total computers: $($results.Total)"
Write-Log "Successful: $($results.Success)"
Write-Log "Failed: $($results.Failed)"
Write-Log "Skipped: $($results.Skipped)"
Write-Log "==============================================="

# Detailed results
Write-Log "DETAILED RESULTS:"
foreach ($computer in $computers) {
    $detail = $results.Details[$computer]
    if ($detail) {
        $status = $detail.Status
        $message = if ($detail.Error) { $detail.Error } elseif ($detail.Message) { $detail.Message } else { "" }
        Write-Log "$computer`: $status $(if ($message) { "- $message" })"
    }
}

Write-Log "Deployment completed. Log saved to: $LogFile"

if ($results.Failed -gt 0) {
    exit 1
} else {
    exit 0
}