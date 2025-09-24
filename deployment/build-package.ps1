# Lab Health Monitor - Package Builder
# This script builds distribution packages for the Health Monitor Agent

param(
    [Parameter(Mandatory=$false)]
    [string]$Version = "1.0.0",
    
    [Parameter(Mandatory=$false)]
    [string]$OutputDir = "dist",
    
    [Parameter(Mandatory=$false)]
    [switch]$BuildPortable = $true,
    
    [Parameter(Mandatory=$false)]
    [switch]$BuildInstaller = $true,
    
    [Parameter(Mandatory=$false)]
    [switch]$BuildZip = $true,
    
    [Parameter(Mandatory=$false)]
    [switch]$Clean = $false
)

$ScriptPath = Split-Path -Parent $MyInvocation.MyCommand.Definition
$ProjectRoot = Split-Path -Parent $ScriptPath
$SourcePath = Join-Path $ProjectRoot "health-monitor-agent"
$DeploymentPath = Join-Path $ProjectRoot "deployment"
$DistPath = Join-Path $ProjectRoot $OutputDir

Write-Host "Lab Health Monitor Package Builder v$Version" -ForegroundColor Cyan
Write-Host "=================================================" -ForegroundColor Cyan

function Write-Status {
    param([string]$Message, [string]$Status = "INFO")
    $color = switch ($Status) {
        "SUCCESS" { "Green" }
        "ERROR" { "Red" }
        "WARNING" { "Yellow" }
        default { "White" }
    }
    Write-Host "[$Status] $Message" -ForegroundColor $color
}

function Test-Prerequisites {
    Write-Status "Checking prerequisites..."
    
    # Check if source exists
    if (-not (Test-Path $SourcePath)) {
        Write-Status "Source directory not found: $SourcePath" "ERROR"
        return $false
    }
    
    # Check if Node.js is available
    try {
        $nodeVersion = & node --version
        Write-Status "Node.js version: $nodeVersion" "SUCCESS"
    } catch {
        Write-Status "Node.js not found. Please install Node.js." "ERROR"
        return $false
    }
    
    # Check if electron-builder is available (for installer)
    if ($BuildInstaller) {
        try {
            & npx electron-builder --version | Out-Null
            Write-Status "electron-builder available" "SUCCESS"
        } catch {
            Write-Status "electron-builder not available. Installing..." "WARNING"
            try {
                Set-Location $SourcePath
                & npm install --save-dev electron-builder
                Write-Status "electron-builder installed" "SUCCESS"
            } catch {
                Write-Status "Failed to install electron-builder" "ERROR"
                return $false
            }
        }
    }
    
    return $true
}

function Initialize-BuildEnvironment {
    Write-Status "Initializing build environment..."
    
    # Clean output directory if requested
    if ($Clean -and (Test-Path $DistPath)) {
        Remove-Item -Path $DistPath -Recurse -Force
        Write-Status "Cleaned output directory" "SUCCESS"
    }
    
    # Create output directory
    if (-not (Test-Path $DistPath)) {
        New-Item -Path $DistPath -ItemType Directory -Force | Out-Null
        Write-Status "Created output directory: $DistPath" "SUCCESS"
    }
    
    # Update package.json with version
    $packageJsonPath = Join-Path $SourcePath "package.json"
    if (Test-Path $packageJsonPath) {
        $packageJson = Get-Content $packageJsonPath | ConvertFrom-Json
        $packageJson.version = $Version
        $packageJson | ConvertTo-Json -Depth 10 | Set-Content $packageJsonPath
        Write-Status "Updated package.json version to $Version" "SUCCESS"
    }
}

function Build-ElectronApp {
    Write-Status "Building Electron application..."
    
    try {
        Set-Location $SourcePath
        
        # Install production dependencies
        Write-Status "Installing dependencies..."
        & npm install --production
        
        if ($LASTEXITCODE -ne 0) {
            throw "npm install failed"
        }
        
        Write-Status "Electron app prepared" "SUCCESS"
        return $true
        
    } catch {
        Write-Status "Failed to build Electron app: $_" "ERROR"
        return $false
    }
}

function Build-PortablePackage {
    Write-Status "Creating portable package..."
    
    try {
        $portableDir = Join-Path $DistPath "lab-health-monitor-portable-v$Version"
        
        # Copy source files
        Copy-Item -Path $SourcePath -Destination $portableDir -Recurse -Force
        
        # Remove development files
        $devFiles = @("*.log", "node_modules/.cache", ".electron-gyp", "*.tmp")
        foreach ($pattern in $devFiles) {
            Get-ChildItem -Path $portableDir -Filter $pattern -Recurse | Remove-Item -Recurse -Force -ErrorAction SilentlyContinue
        }
        
        # Create launcher script
        $launcherScript = Join-Path $portableDir "start-health-monitor.bat"
        @"
@echo off
echo Starting Lab Health Monitor (Portable)...
cd /d "%~dp0"
node main.js
pause
"@ | Set-Content $launcherScript
        
        # Create README
        $readmePath = Join-Path $portableDir "README.txt"
        @"
Lab Health Monitor Agent - Portable Version v$Version

INSTALLATION:
1. Extract this folder to your desired location
2. Run 'npm install' to install dependencies
3. Configure settings in config/config.json
4. Run start-health-monitor.bat to start the application

REQUIREMENTS:
- Node.js 16 or later
- Windows 10/11

CONFIGURATION:
Edit config/config.json to set:
- Server URL
- API key
- Monitoring thresholds
- Update intervals

For technical support, please contact your system administrator.
"@ | Set-Content $readmePath
        
        Write-Status "Portable package created: $portableDir" "SUCCESS"
        return $true
        
    } catch {
        Write-Status "Failed to create portable package: $_" "ERROR"
        return $false
    }
}

function Build-InstallerPackage {
    Write-Status "Creating installer package..."
    
    try {
        Set-Location $SourcePath
        
        # Create electron-builder configuration if it doesn't exist
        $builderConfigPath = Join-Path $SourcePath "electron-builder.json"
        if (-not (Test-Path $builderConfigPath)) {
            $builderConfig = @{
                appId = "com.labsystem.healthmonitor"
                productName = "Lab Health Monitor"
                directories = @{
                    output = $DistPath
                }
                files = @(
                    "**/*",
                    "!node_modules/.cache",
                    "!*.log"
                )
                win = @{
                    target = "nsis"
                    icon = "assets/icon.ico"
                }
                nsis = @{
                    oneClick = $false
                    allowElevation = $true
                    allowToChangeInstallationDirectory = $true
                    createDesktopShortcut = $true
                    createStartMenuShortcut = $true
                    shortcutName = "Lab Health Monitor"
                }
            }
            $builderConfig | ConvertTo-Json -Depth 10 | Set-Content $builderConfigPath
        }
        
        # Build installer
        & npx electron-builder --win --publish never
        
        if ($LASTEXITCODE -ne 0) {
            throw "electron-builder failed"
        }
        
        Write-Status "Installer package created" "SUCCESS"
        return $true
        
    } catch {
        Write-Status "Failed to create installer package: $_" "ERROR"
        return $false
    }
}

function Build-ZipPackage {
    Write-Status "Creating ZIP package..."
    
    try {
        $zipName = "lab-health-monitor-v$Version.zip"
        $zipPath = Join-Path $DistPath $zipName
        
        # Create temporary directory with all deployment files
        $tempDir = Join-Path $env:TEMP "health-monitor-package"
        if (Test-Path $tempDir) {
            Remove-Item -Path $tempDir -Recurse -Force
        }
        New-Item -Path $tempDir -ItemType Directory -Force | Out-Null
        
        # Copy application files
        Copy-Item -Path $SourcePath -Destination (Join-Path $tempDir "health-monitor-agent") -Recurse -Force
        
        # Copy deployment scripts
        Copy-Item -Path (Join-Path $DeploymentPath "install-health-monitor.bat") -Destination $tempDir -Force
        Copy-Item -Path (Join-Path $DeploymentPath "uninstall-health-monitor.bat") -Destination $tempDir -Force
        Copy-Item -Path (Join-Path $DeploymentPath "deploy-health-monitor.ps1") -Destination $tempDir -Force
        
        # Create deployment README
        $deployReadmePath = Join-Path $tempDir "DEPLOYMENT-README.txt"
        @"
Lab Health Monitor Agent - Deployment Package v$Version

CONTENTS:
- health-monitor-agent/          : Main application files
- install-health-monitor.bat     : Windows installation script
- uninstall-health-monitor.bat   : Windows uninstallation script  
- deploy-health-monitor.ps1      : PowerShell bulk deployment script

SINGLE COMPUTER INSTALLATION:
1. Run install-health-monitor.bat as Administrator
2. The installer will:
   - Install Node.js dependencies
   - Create application directories
   - Set up auto-start task
   - Create desktop shortcuts
   - Start the application

BULK DEPLOYMENT:
1. Prepare a list of computer names in computers.txt
2. Run PowerShell as Administrator
3. Execute: .\deploy-health-monitor.ps1 -ComputerListFile computers.txt

CONFIGURATION:
After installation, edit configuration file:
%PROGRAMDATA%\Lab Health Monitor\config.json

Set the correct server URL and API key for your environment.

REQUIREMENTS:
- Windows 10/11
- Node.js 16 or later
- Administrator privileges for installation
- Network access to health monitoring server

UNINSTALLATION:
Run uninstall-health-monitor.bat as Administrator

For technical support, please contact your system administrator.
"@ | Set-Content $deployReadmePath
        
        # Create ZIP file
        Compress-Archive -Path "$tempDir\*" -DestinationPath $zipPath -Force
        
        # Clean up temporary directory
        Remove-Item -Path $tempDir -Recurse -Force
        
        Write-Status "ZIP package created: $zipPath" "SUCCESS"
        return $true
        
    } catch {
        Write-Status "Failed to create ZIP package: $_" "ERROR"
        return $false
    }
}

function Show-BuildSummary {
    Write-Host "`nBuild Summary:" -ForegroundColor Cyan
    Write-Host "=============" -ForegroundColor Cyan
    
    $files = Get-ChildItem -Path $DistPath -File
    foreach ($file in $files) {
        $sizeKB = [math]::Round($file.Length / 1KB, 2)
        Write-Host "  $($file.Name) ($sizeKB KB)" -ForegroundColor Green
    }
    
    $dirs = Get-ChildItem -Path $DistPath -Directory
    foreach ($dir in $dirs) {
        Write-Host "  $($dir.Name)/ (folder)" -ForegroundColor Yellow
    }
    
    Write-Host "`nOutput directory: $DistPath" -ForegroundColor Cyan
}

# Main execution
try {
    if (-not (Test-Prerequisites)) {
        exit 1
    }
    
    Initialize-BuildEnvironment
    
    if (-not (Build-ElectronApp)) {
        exit 1
    }
    
    $success = $true
    
    if ($BuildPortable) {
        $success = $success -and (Build-PortablePackage)
    }
    
    if ($BuildInstaller) {
        $success = $success -and (Build-InstallerPackage)
    }
    
    if ($BuildZip) {
        $success = $success -and (Build-ZipPackage)
    }
    
    if ($success) {
        Show-BuildSummary
        Write-Status "Build completed successfully!" "SUCCESS"
    } else {
        Write-Status "Build completed with errors" "WARNING"
    }
    
} catch {
    Write-Status "Build failed: $_" "ERROR"
    exit 1
}