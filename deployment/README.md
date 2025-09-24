# Lab Health Monitor - Deployment Guide

## Overview

The Lab Health Monitor system consists of two main components:
1. **Desktop Agent**: Electron application that runs on each lab computer to collect health metrics
2. **Web Dashboard**: Admin interface integrated with the lab management system to monitor all computers

## System Requirements

### Desktop Agent
- Windows 10/11 (64-bit)
- Node.js 16 or later
- 100MB available disk space
- Network access to the monitoring server
- Administrator privileges for installation

### Server/Dashboard
- Web server with PHP 7.4+
- MySQL 5.7+ database
- Existing Lab Management System installation

## Deployment Scripts Overview

### 1. install-health-monitor.bat
**Purpose**: Single computer installation script  
**Requirements**: Administrator privileges  
**Usage**: Right-click and "Run as administrator"

**Features**:
- Automatically detects system requirements
- Installs application to `C:\Program Files\Lab Health Monitor`
- Creates configuration directory at `%PROGRAMDATA%\Lab Health Monitor`
- Sets up auto-start with Windows
- Creates desktop and Start Menu shortcuts
- Configures default settings

### 2. uninstall-health-monitor.bat
**Purpose**: Remove Health Monitor from a single computer  
**Requirements**: Administrator privileges  
**Usage**: Right-click and "Run as administrator"

**Features**:
- Stops running application
- Removes scheduled tasks
- Deletes application files
- Option to keep or remove configuration/logs
- Removes shortcuts

### 3. deploy-health-monitor.ps1
**Purpose**: Bulk deployment across multiple lab computers  
**Requirements**: PowerShell with administrative privileges, WinRM enabled on target computers  

**Usage Examples**:
```powershell
# Deploy to specific computers
.\deploy-health-monitor.ps1 -ComputerNames "LAB-PC-01,LAB-PC-02,LAB-PC-03"

# Deploy using computer list file
.\deploy-health-monitor.ps1 -ComputerListFile "computers.txt"

# Test connections only
.\deploy-health-monitor.ps1 -ComputerNames "LAB-PC-01" -TestConnection

# Uninstall from all computers in list
.\deploy-health-monitor.ps1 -ComputerListFile "computers.txt" -UninstallOnly
```

### 4. build-package.ps1
**Purpose**: Create distribution packages for deployment  
**Requirements**: Node.js, PowerShell  

**Usage Examples**:
```powershell
# Build all package types
.\build-package.ps1 -Version "1.0.1"

# Build only ZIP package
.\build-package.ps1 -BuildZip -BuildPortable:$false -BuildInstaller:$false

# Clean build
.\build-package.ps1 -Clean
```

### 5. manage-config.ps1
**Purpose**: Configuration management across multiple installations  
**Requirements**: PowerShell, network access to target computers  

**Usage Examples**:
```powershell
# Create configuration template
.\manage-config.ps1 -Action create -OutputFile "lab-config.json"

# Validate configuration
.\manage-config.ps1 -Action validate -ConfigFile "lab-config.json"

# Deploy configuration to all computers
.\manage-config.ps1 -Action deploy -ConfigFile "lab-config.json" -ComputerListFile "computers.txt"

# Update server URL on specific computers
.\manage-config.ps1 -Action update -ServerUrl "http://new-server.local/lab" -ComputerNames "LAB-PC-01,LAB-PC-02"

# Backup all configurations
.\manage-config.ps1 -Action backup -ComputerListFile "computers.txt" -OutputFile "backups"
```

## Deployment Scenarios

### Scenario 1: Single Computer Setup (Manual)

1. Copy the `health-monitor-agent` folder to the target computer
2. Run `install-health-monitor.bat` as administrator
3. Edit configuration file if needed: `%PROGRAMDATA%\Lab Health Monitor\config.json`
4. Restart application or reboot computer

### Scenario 2: Small Lab (5-20 computers)

1. Build deployment package: `.\build-package.ps1 -BuildZip`
2. Extract package on administrative computer
3. Create `computers.txt` with list of computer names
4. Run bulk deployment: `.\deploy-health-monitor.ps1 -ComputerListFile computers.txt`
5. Update configuration if needed: `.\manage-config.ps1 -Action deploy -ConfigFile config.json -ComputerListFile computers.txt`

### Scenario 3: Large Lab (20+ computers)

1. **Preparation**:
   - Build deployment packages
   - Create standardized configuration file
   - Prepare computer inventory list
   - Ensure WinRM is enabled on all target computers

2. **Configuration Management**:
   ```powershell
   # Create master configuration
   .\manage-config.ps1 -Action create -OutputFile "master-config.json"
   
   # Edit master-config.json with your settings
   
   # Validate configuration
   .\manage-config.ps1 -Action validate -ConfigFile "master-config.json"
   ```

3. **Bulk Deployment**:
   ```powershell
   # Test connections first
   .\deploy-health-monitor.ps1 -ComputerListFile "all-computers.txt" -TestConnection
   
   # Deploy to all computers
   .\deploy-health-monitor.ps1 -ComputerListFile "all-computers.txt"
   
   # Deploy configuration
   .\manage-config.ps1 -Action deploy -ConfigFile "master-config.json" -ComputerListFile "all-computers.txt"
   ```

4. **Verification**:
   - Check deployment logs
   - Verify computers appear in web dashboard
   - Test alert functionality

## Configuration Management

### Default Configuration Location
- **Windows**: `%PROGRAMDATA%\Lab Health Monitor\config.json`
- **Application**: `C:\Program Files\Lab Health Monitor\`

### Key Configuration Settings

```json
{
  "server": {
    "url": "http://your-server/CAPSTONE-LAB-MANAGEMENT-SYSTEM",
    "apiKey": "your-api-key"
  },
  "monitoring": {
    "interval": 30000,
    "enableTemperature": true
  },
  "thresholds": {
    "cpu": { "warning": 75, "critical": 85 },
    "memory": { "warning": 80, "critical": 90 },
    "disk": { "warning": 85, "critical": 95 }
  }
}
```

### Updating Configuration

1. **Single Computer**: Edit config file directly and restart application
2. **Multiple Computers**: Use `manage-config.ps1` for bulk updates
3. **All Computers**: Deploy new configuration file using deployment script

## Troubleshooting

### Common Installation Issues

**Error**: "Node.js not found"  
**Solution**: Install Node.js from https://nodejs.org/ before running installation

**Error**: "Access denied"  
**Solution**: Run installation script as administrator

**Error**: "Network connection failed"  
**Solution**: Check server URL and network connectivity

### Common Deployment Issues

**Error**: "WinRM connection failed"  
**Solution**: Enable WinRM on target computers:
```cmd
winrm quickconfig
winrm set winrm/config/service/auth @{Basic="true"}
```

**Error**: "Computer not reachable"  
**Solution**: Verify computer names, network connectivity, and firewall settings

### Verification Steps

1. **Check if application is running**:
   - Look for system tray icon
   - Check Task Manager for "health-monitor" process
   - Verify scheduled task exists

2. **Check configuration**:
   - Verify config.json exists and is valid
   - Test server connectivity from application

3. **Check dashboard**:
   - Computer should appear in admin dashboard
   - Recent health data should be visible
   - No connection errors in logs

## Server Configuration

### Database Setup
The health monitoring tables are created automatically by the API when first accessed.

### API Endpoints
- `POST /api/health/data` - Receive health data
- `POST /api/health/alert` - Receive alerts
- `GET /api/health/computers` - List computers
- `GET /api/health/computer/{name}` - Computer details

### Email Notifications
Configure email settings in the notification service:
- Update admin email addresses in users table
- Configure SMTP settings for email delivery
- Test notification system

## Maintenance

### Regular Tasks

1. **Weekly**:
   - Review alert logs
   - Check computer connectivity
   - Verify backup configurations

2. **Monthly**:
   - Update agent software if needed
   - Review monitoring thresholds
   - Clean up old log files

3. **Quarterly**:
   - Full system health check
   - Review and update configurations
   - Test disaster recovery procedures

### Log Files

- **Agent Logs**: `%PROGRAMDATA%\Lab Health Monitor\logs\`
- **Server Logs**: Check PHP error logs and database logs
- **Deployment Logs**: Generated during bulk deployment operations

### Updates

1. **Agent Updates**:
   - Build new package with updated version
   - Use deployment scripts to update all computers
   - Test thoroughly before wide deployment

2. **Configuration Updates**:
   - Use configuration management scripts
   - Always backup existing configurations
   - Test changes on subset of computers first

## Security Considerations

1. **API Security**:
   - Use HTTPS in production
   - Implement proper API key management
   - Monitor for unauthorized access

2. **Network Security**:
   - Restrict network access to monitoring server
   - Use VPN for remote management
   - Monitor network traffic

3. **Computer Security**:
   - Run agent with minimal required privileges
   - Regular security updates
   - Monitor for unauthorized modifications

## Support and Maintenance

### Log Collection
Use PowerShell to collect logs from multiple computers:
```powershell
# Collect logs from all computers
foreach ($computer in $computers) {
    Copy-Item "\\$computer\c$\ProgramData\Lab Health Monitor\logs\*" -Destination "logs\$computer\" -Recurse
}
```

### Emergency Procedures

**To quickly disable monitoring**:
```powershell
# Stop on all computers
.\deploy-health-monitor.ps1 -ComputerListFile computers.txt -UninstallOnly
```

**To restore from backup configuration**:
```powershell
# Restore configuration
.\manage-config.ps1 -Action deploy -ConfigFile "backup-config.json" -ComputerListFile computers.txt
```

For additional support, refer to the application logs and contact your system administrator.