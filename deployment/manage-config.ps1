# Lab Health Monitor - Configuration Management Script
# This script helps manage configuration across multiple installations

param(
    [Parameter(Mandatory=$false)]
    [string]$Action = "help",
    
    [Parameter(Mandatory=$false)]
    [string]$ConfigFile = "",
    
    [Parameter(Mandatory=$false)]
    [string]$ServerUrl = "",
    
    [Parameter(Mandatory=$false)]
    [string]$ApiKey = "",
    
    [Parameter(Mandatory=$false)]
    [string[]]$ComputerNames = @(),
    
    [Parameter(Mandatory=$false)]
    [string]$ComputerListFile = "",
    
    [Parameter(Mandatory=$false)]
    [string]$OutputFile = ""
)

function Show-Usage {
    Write-Host @"
Lab Health Monitor - Configuration Management

Usage:
    .\manage-config.ps1 -Action <action> [options]

Actions:
    create      - Create a new configuration file template
    validate    - Validate an existing configuration file
    deploy      - Deploy configuration to remote computers
    backup      - Backup existing configurations from remote computers
    update      - Update specific settings across multiple computers

Options:
    -ConfigFile <file>      : Configuration file path
    -ServerUrl <url>        : Health monitoring server URL
    -ApiKey <key>           : API key for authentication
    -ComputerNames <names>  : Comma-separated computer names
    -ComputerListFile <file>: Text file with computer names
    -OutputFile <file>      : Output file for backup/export

Examples:
    .\manage-config.ps1 -Action create -OutputFile "lab-config.json"
    .\manage-config.ps1 -Action validate -ConfigFile "lab-config.json"
    .\manage-config.ps1 -Action deploy -ConfigFile "lab-config.json" -ComputerListFile "computers.txt"
    .\manage-config.ps1 -Action update -ServerUrl "http://new-server/lab" -ComputerNames "LAB-PC-01,LAB-PC-02"

"@
}

function New-ConfigurationTemplate {
    param([string]$OutputPath)
    
    $template = @{
        server = @{
            url = "http://localhost/CAPSTONE-LAB-MANAGEMENT-SYSTEM"
            apiKey = "lab-health-monitor-key"
            timeout = 30000
            retryAttempts = 3
            retryDelay = 5000
        }
        monitoring = @{
            interval = 30000
            enableTemperature = $true
            enableProcesses = $true
            enableNetwork = $true
            enableDiskIO = $true
            processLimit = 10
        }
        thresholds = @{
            cpu = @{
                warning = 75
                critical = 85
            }
            memory = @{
                warning = 80
                critical = 90
            }
            disk = @{
                warning = 85
                critical = 95
            }
            temperature = @{
                warning = 70
                critical = 80
            }
            network = @{
                maxBandwidthMbps = 1000
                warningPercent = 75
                criticalPercent = 90
            }
        }
        alerts = @{
            enableEmail = $true
            enableDesktop = $true
            enableSound = $true
            emailCooldown = 300
            desktopCooldown = 60
        }
        ui = @{
            autoStart = $true
            minimizeToTray = $true
            startMinimized = $true
            showNotifications = $true
            theme = "light"
        }
        logging = @{
            level = "info"
            maxFileSize = "10MB"
            maxFiles = 5
            enableConsole = $false
        }
        advanced = @{
            enableAutoUpdate = $true
            updateCheckInterval = 86400000
            enableTelemetry = $false
            maxMemoryUsage = "100MB"
        }
    }
    
    try {
        $template | ConvertTo-Json -Depth 10 | Set-Content -Path $OutputPath -Encoding UTF8
        Write-Host "✓ Configuration template created: $OutputPath" -ForegroundColor Green
        return $true
    } catch {
        Write-Host "✗ Failed to create configuration template: $_" -ForegroundColor Red
        return $false
    }
}

function Test-Configuration {
    param([string]$ConfigPath)
    
    Write-Host "Validating configuration file: $ConfigPath"
    
    if (-not (Test-Path $ConfigPath)) {
        Write-Host "✗ Configuration file not found" -ForegroundColor Red
        return $false
    }
    
    try {
        $config = Get-Content -Path $ConfigPath -Raw | ConvertFrom-Json
        
        # Validate required sections
        $requiredSections = @('server', 'monitoring', 'thresholds')
        foreach ($section in $requiredSections) {
            if (-not $config.$section) {
                Write-Host "✗ Missing required section: $section" -ForegroundColor Red
                return $false
            }
        }
        
        # Validate server settings
        if (-not $config.server.url) {
            Write-Host "✗ Server URL is required" -ForegroundColor Red
            return $false
        }
        
        if ($config.server.url -notmatch '^https?://') {
            Write-Host "⚠ Server URL should start with http:// or https://" -ForegroundColor Yellow
        }
        
        # Validate monitoring interval
        if ($config.monitoring.interval -lt 1000) {
            Write-Host "⚠ Monitoring interval less than 1 second may cause performance issues" -ForegroundColor Yellow
        }
        
        # Validate thresholds
        $thresholdChecks = @('cpu', 'memory', 'disk')
        foreach ($check in $thresholdChecks) {
            if ($config.thresholds.$check) {
                $warning = $config.thresholds.$check.warning
                $critical = $config.thresholds.$check.critical
                
                if ($warning -ge $critical) {
                    Write-Host "✗ Warning threshold must be less than critical threshold for $check" -ForegroundColor Red
                    return $false
                }
                
                if ($critical -gt 100) {
                    Write-Host "⚠ Critical threshold for $check is above 100%" -ForegroundColor Yellow
                }
            }
        }
        
        Write-Host "✓ Configuration file is valid" -ForegroundColor Green
        return $true
        
    } catch {
        Write-Host "✗ Invalid JSON format: $_" -ForegroundColor Red
        return $false
    }
}

function Deploy-Configuration {
    param(
        [string]$ConfigPath,
        [string[]]$Computers
    )
    
    Write-Host "Deploying configuration to $($Computers.Count) computers..."
    
    if (-not (Test-Path $ConfigPath)) {
        Write-Host "✗ Configuration file not found: $ConfigPath" -ForegroundColor Red
        return $false
    }
    
    # Validate configuration first
    if (-not (Test-Configuration -ConfigPath $ConfigPath)) {
        Write-Host "✗ Configuration validation failed" -ForegroundColor Red
        return $false
    }
    
    $successCount = 0
    $failCount = 0
    
    foreach ($computer in $Computers) {
        Write-Host "Deploying to $computer..."
        
        try {
            # Test connection
            if (-not (Test-Connection -ComputerName $computer -Count 1 -Quiet)) {
                Write-Host "  ✗ Cannot reach $computer" -ForegroundColor Red
                $failCount++
                continue
            }
            
            # Copy configuration file
            $remotePath = "\\$computer\c$\ProgramData\Lab Health Monitor\config.json"
            Copy-Item -Path $ConfigPath -Destination $remotePath -Force
            
            # Restart the service to apply new configuration
            Invoke-Command -ComputerName $computer -ScriptBlock {
                # Stop the application
                Get-Process -Name "health-monitor" -ErrorAction SilentlyContinue | Stop-Process -Force
                
                # Wait a moment
                Start-Sleep -Seconds 2
                
                # Restart via scheduled task
                Start-ScheduledTask -TaskName "Lab Health Monitor" -ErrorAction SilentlyContinue
            } -ErrorAction SilentlyContinue
            
            Write-Host "  ✓ Configuration deployed to $computer" -ForegroundColor Green
            $successCount++
            
        } catch {
            Write-Host "  ✗ Failed to deploy to $computer`: $_" -ForegroundColor Red
            $failCount++
        }
    }
    
    Write-Host "`nDeployment Summary:"
    Write-Host "  Success: $successCount" -ForegroundColor Green
    Write-Host "  Failed: $failCount" -ForegroundColor Red
    
    return ($failCount -eq 0)
}

function Backup-Configurations {
    param(
        [string[]]$Computers,
        [string]$OutputDir
    )
    
    Write-Host "Backing up configurations from $($Computers.Count) computers..."
    
    if (-not (Test-Path $OutputDir)) {
        New-Item -Path $OutputDir -ItemType Directory -Force | Out-Null
    }
    
    $backupDate = Get-Date -Format "yyyy-MM-dd-HH-mm"
    $successCount = 0
    
    foreach ($computer in $Computers) {
        Write-Host "Backing up from $computer..."
        
        try {
            # Test connection
            if (-not (Test-Connection -ComputerName $computer -Count 1 -Quiet)) {
                Write-Host "  ✗ Cannot reach $computer" -ForegroundColor Red
                continue
            }
            
            # Copy configuration file
            $remotePath = "\\$computer\c$\ProgramData\Lab Health Monitor\config.json"
            $localPath = Join-Path $OutputDir "$computer-$backupDate.json"
            
            if (Test-Path $remotePath) {
                Copy-Item -Path $remotePath -Destination $localPath -Force
                Write-Host "  ✓ Configuration backed up from $computer" -ForegroundColor Green
                $successCount++
            } else {
                Write-Host "  ⚠ No configuration found on $computer" -ForegroundColor Yellow
            }
            
        } catch {
            Write-Host "  ✗ Failed to backup from $computer`: $_" -ForegroundColor Red
        }
    }
    
    Write-Host "`nBackup completed. $successCount configurations saved to: $OutputDir"
}

function Update-SpecificSettings {
    param(
        [string[]]$Computers,
        [hashtable]$Updates
    )
    
    Write-Host "Updating specific settings on $($Computers.Count) computers..."
    
    $successCount = 0
    $failCount = 0
    
    foreach ($computer in $Computers) {
        Write-Host "Updating $computer..."
        
        try {
            # Test connection
            if (-not (Test-Connection -ComputerName $computer -Count 1 -Quiet)) {
                Write-Host "  ✗ Cannot reach $computer" -ForegroundColor Red
                $failCount++
                continue
            }
            
            # Get current configuration
            $remotePath = "\\$computer\c$\ProgramData\Lab Health Monitor\config.json"
            
            if (-not (Test-Path $remotePath)) {
                Write-Host "  ✗ No configuration found on $computer" -ForegroundColor Red
                $failCount++
                continue
            }
            
            $config = Get-Content -Path $remotePath -Raw | ConvertFrom-Json
            
            # Apply updates
            foreach ($key in $Updates.Keys) {
                $value = $Updates[$key]
                
                # Handle nested keys (e.g., "server.url")
                if ($key -contains ".") {
                    $parts = $key -split '\.'
                    $current = $config
                    
                    for ($i = 0; $i -lt ($parts.Length - 1); $i++) {
                        if (-not $current.($parts[$i])) {
                            $current | Add-Member -NotePropertyName $parts[$i] -NotePropertyValue @{}
                        }
                        $current = $current.($parts[$i])
                    }
                    
                    $current.($parts[-1]) = $value
                } else {
                    $config.$key = $value
                }
            }
            
            # Save updated configuration
            $config | ConvertTo-Json -Depth 10 | Set-Content -Path $remotePath -Encoding UTF8
            
            # Restart the service
            Invoke-Command -ComputerName $computer -ScriptBlock {
                Get-Process -Name "health-monitor" -ErrorAction SilentlyContinue | Stop-Process -Force
                Start-Sleep -Seconds 2
                Start-ScheduledTask -TaskName "Lab Health Monitor" -ErrorAction SilentlyContinue
            } -ErrorAction SilentlyContinue
            
            Write-Host "  ✓ Settings updated on $computer" -ForegroundColor Green
            $successCount++
            
        } catch {
            Write-Host "  ✗ Failed to update $computer`: $_" -ForegroundColor Red
            $failCount++
        }
    }
    
    Write-Host "`nUpdate Summary:"
    Write-Host "  Success: $successCount" -ForegroundColor Green
    Write-Host "  Failed: $failCount" -ForegroundColor Red
}

function Get-ComputerList {
    $computers = @()
    
    if ($ComputerListFile -and (Test-Path $ComputerListFile)) {
        $computers += Get-Content $ComputerListFile | Where-Object { $_.Trim() -ne "" }
    }
    
    if ($ComputerNames.Count -gt 0) {
        $computers += $ComputerNames
    }
    
    return $computers | Sort-Object -Unique
}

# Main execution
switch ($Action.ToLower()) {
    "create" {
        $outputPath = if ($OutputFile) { $OutputFile } else { "health-monitor-config.json" }
        New-ConfigurationTemplate -OutputPath $outputPath
    }
    
    "validate" {
        if (-not $ConfigFile) {
            Write-Host "Configuration file path is required for validation" -ForegroundColor Red
            Show-Usage
            exit 1
        }
        Test-Configuration -ConfigPath $ConfigFile
    }
    
    "deploy" {
        if (-not $ConfigFile) {
            Write-Host "Configuration file path is required for deployment" -ForegroundColor Red
            exit 1
        }
        
        $computers = Get-ComputerList
        if ($computers.Count -eq 0) {
            Write-Host "No computers specified for deployment" -ForegroundColor Red
            exit 1
        }
        
        Deploy-Configuration -ConfigPath $ConfigFile -Computers $computers
    }
    
    "backup" {
        $computers = Get-ComputerList
        if ($computers.Count -eq 0) {
            Write-Host "No computers specified for backup" -ForegroundColor Red
            exit 1
        }
        
        $outputDir = if ($OutputFile) { $OutputFile } else { "config-backups" }
        Backup-Configurations -Computers $computers -OutputDir $outputDir
    }
    
    "update" {
        $computers = Get-ComputerList
        if ($computers.Count -eq 0) {
            Write-Host "No computers specified for update" -ForegroundColor Red
            exit 1
        }
        
        $updates = @{}
        if ($ServerUrl) { $updates["server.url"] = $ServerUrl }
        if ($ApiKey) { $updates["server.apiKey"] = $ApiKey }
        
        if ($updates.Count -eq 0) {
            Write-Host "No updates specified" -ForegroundColor Red
            exit 1
        }
        
        Update-SpecificSettings -Computers $computers -Updates $updates
    }
    
    "help" {
        Show-Usage
    }
    
    default {
        Write-Host "Unknown action: $Action" -ForegroundColor Red
        Show-Usage
        exit 1
    }
}