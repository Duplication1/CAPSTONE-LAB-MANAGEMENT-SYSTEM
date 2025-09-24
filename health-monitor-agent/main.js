const { app, BrowserWindow, ipcMain, Tray, Menu, nativeImage, dialog, Notification } = require('electron');
const path = require('path');
const { autoUpdater } = require('electron-updater');
const Store = require('electron-store');
const AutoLaunch = require('auto-launch');
const SystemMonitor = require('./src/system-monitor');
const ApiClient = require('./src/api-client');

// Initialize store for settings
const store = new Store();
let mainWindow;
let tray;
let systemMonitor;
let apiClient;
let isQuitting = false;

// Auto-launch setup
const autoLauncher = new AutoLaunch({
  name: 'Lab Health Monitor',
  path: app.getPath('exe'),
});

function createWindow() {
  const windowOptions = {
    width: 1200,
    height: 800,
    minWidth: 800,
    minHeight: 600,
    show: false, // Start hidden, show when ready
    webPreferences: {
      nodeIntegration: false,
      contextIsolation: true,
      preload: path.join(__dirname, 'preload.js')
    },
    autoHideMenuBar: true
  };

  // Add icon if it exists
  const iconPath = path.join(__dirname, 'assets', 'icon.png');
  try {
    if (require('fs').existsSync(iconPath)) {
      windowOptions.icon = iconPath;
    }
  } catch (error) {
    console.log('Could not load window icon');
  }

  mainWindow = new BrowserWindow(windowOptions);

  mainWindow.loadFile('index.html');

  // Show window when ready
  mainWindow.once('ready-to-show', () => {
    if (!store.get('startMinimized', true)) {
      mainWindow.show();
    }
  });

  // Handle window close
  mainWindow.on('close', (event) => {
    if (!isQuitting) {
      event.preventDefault();
      mainWindow.hide();
      
      // Show notification on first minimize
      if (!store.get('hasSeenTrayNotification', false)) {
        new Notification('Lab Health Monitor', {
          body: 'Application was minimized to tray and will continue monitoring in the background.'
        });
        store.set('hasSeenTrayNotification', true);
      }
    }
  });

  mainWindow.on('closed', () => {
    mainWindow = null;
  });

  // Development tools
  if (process.env.NODE_ENV === 'development') {
    mainWindow.webContents.openDevTools();
  }
}

function createTray() {
  const iconPath = path.join(__dirname, 'assets', 'icon-tray.png');
  let trayIcon;
  
  try {
    trayIcon = nativeImage.createFromPath(iconPath);
    if (trayIcon.isEmpty()) {
      // Create a simple tray icon if file doesn't exist
      trayIcon = nativeImage.createEmpty();
    }
  } catch (error) {
    console.log('Could not load tray icon, using default');
    trayIcon = nativeImage.createEmpty();
  }
  
  tray = new Tray(trayIcon);

  const contextMenu = Menu.buildFromTemplate([
    {
      label: 'Show Health Monitor',
      click: () => {
        mainWindow.show();
        mainWindow.focus();
      }
    },
    { type: 'separator' },
    {
      label: 'System Status',
      submenu: [
        {
          label: 'CPU: Loading...',
          enabled: false,
          id: 'cpu-status'
        },
        {
          label: 'Memory: Loading...',
          enabled: false,
          id: 'memory-status'
        },
        {
          label: 'Disk: Loading...',
          enabled: false,
          id: 'disk-status'
        }
      ]
    },
    { type: 'separator' },
    {
      label: 'Settings',
      click: () => {
        mainWindow.show();
        mainWindow.focus();
        mainWindow.webContents.send('show-settings');
      }
    },
    {
      label: 'Check for Updates',
      click: () => {
        autoUpdater.checkForUpdatesAndNotify();
      }
    },
    { type: 'separator' },
    {
      label: 'Quit',
      click: () => {
        isQuitting = true;
        app.quit();
      }
    }
  ]);

  tray.setToolTip('Lab Health Monitor');
  tray.setContextMenu(contextMenu);

  tray.on('double-click', () => {
    mainWindow.show();
    mainWindow.focus();
  });
}

function updateTrayStatus(data) {
  if (!tray) return;

  // Recreate the tray menu with updated data - getContextMenu() doesn't exist in current Electron
  const contextMenu = Menu.buildFromTemplate([
    {
      label: 'Show Health Monitor',
      click: () => {
        mainWindow.show();
        mainWindow.focus();
      }
    },
    { type: 'separator' },
    {
      label: 'System Status',
      submenu: [
        {
          label: `CPU: ${data.cpu?.usage?.toFixed(1) || 'N/A'}%`,
          enabled: false,
          id: 'cpu-status'
        },
        {
          label: `Memory: ${data.memory?.usedPercent?.toFixed(1) || 'N/A'}%`,
          enabled: false,
          id: 'memory-status'
        },
        {
          label: `Disk: ${data.disk?.usedPercent?.toFixed(1) || 'N/A'}%`,
          enabled: false,
          id: 'disk-status'
        }
      ]
    },
    { type: 'separator' },
    {
      label: 'Settings',
      click: () => {
        mainWindow.show();
        mainWindow.focus();
        mainWindow.webContents.send('show-settings');
      }
    },
    {
      label: 'Check for Updates',
      click: () => {
        autoUpdater.checkForUpdatesAndNotify();
      }
    },
    { type: 'separator' },
    {
      label: 'Quit',
      click: () => {
        app.quit();
      }
    }
  ]);

  tray.setContextMenu(contextMenu);
}

// Disable GPU acceleration to prevent warnings on systems without proper GPU support
app.disableHardwareAcceleration();

// Additional command line arguments to suppress warnings
app.commandLine.appendSwitch('disable-gpu-sandbox');
app.commandLine.appendSwitch('disable-software-rasterizer');

// App event handlers
app.whenReady().then(() => {
  createWindow();
  createTray();

  // Initialize system monitor and API client
  const serverUrl = store.get('serverUrl', 'http://localhost');
  const apiKey = store.get('apiKey', '');
  const computerName = store.get('computerName', require('os').hostname());

  systemMonitor = new SystemMonitor();
  apiClient = new ApiClient(serverUrl, apiKey, computerName);

  // Start monitoring
  systemMonitor.start();

  // Handle system data updates
  systemMonitor.on('data', (data) => {
    // Send to renderer
    if (mainWindow && !mainWindow.isDestroyed()) {
      mainWindow.webContents.send('system-data', data);
    }

    // Update tray
    try {
      updateTrayStatus(data);
    } catch (error) {
      console.error('Error updating tray status:', error);
    }

    // Send to server
    if (apiKey) {
      apiClient.sendHealthData(data).catch(console.error);
    }
  });

  // Handle alerts
  systemMonitor.on('alert', (alert) => {
    try {
      // Show desktop notification
      if (Notification.isSupported()) {
        const notification = new Notification(`Health Alert: ${alert.type}`, {
          body: alert.message,
          silent: false
        });
        
        notification.show();
      }
    } catch (error) {
      console.warn('Failed to show notification:', error.message);
    }

    // Send to renderer
    if (mainWindow && !mainWindow.isDestroyed()) {
      mainWindow.webContents.send('system-alert', alert);
    }

    // Send to server
    if (apiKey) {
      apiClient.sendAlert(alert).catch(console.error);
    }
  });

  // Auto-updater events
  autoUpdater.checkForUpdatesAndNotify();
});

app.on('window-all-closed', () => {
  // Keep app running in background on all platforms
});

app.on('before-quit', () => {
  isQuitting = true;
});

app.on('activate', () => {
  if (BrowserWindow.getAllWindows().length === 0) {
    createWindow();
  }
});

// IPC handlers
ipcMain.handle('get-settings', () => {
  return {
    serverUrl: store.get('serverUrl', 'http://localhost'),
    apiKey: store.get('apiKey', ''),
    computerName: store.get('computerName', require('os').hostname()),
    monitoringInterval: store.get('monitoringInterval', 30),
    startMinimized: store.get('startMinimized', true),
    startWithSystem: store.get('startWithSystem', true),
    alertThresholds: store.get('alertThresholds', {
      cpu: 80,
      memory: 85,
      disk: 90,
      temperature: 70
    })
  };
});

ipcMain.handle('save-settings', async (event, settings) => {
  try {
    // Save settings
    store.set('serverUrl', settings.serverUrl);
    store.set('apiKey', settings.apiKey);
    store.set('computerName', settings.computerName);
    store.set('monitoringInterval', settings.monitoringInterval);
    store.set('startMinimized', settings.startMinimized);
    store.set('startWithSystem', settings.startWithSystem);
    store.set('alertThresholds', settings.alertThresholds);

    // Update auto-launch
    if (settings.startWithSystem) {
      await autoLauncher.enable();
    } else {
      await autoLauncher.disable();
    }

    // Update system monitor settings
    systemMonitor.updateSettings({
      interval: settings.monitoringInterval * 1000,
      thresholds: settings.alertThresholds
    });

    // Update API client
    apiClient.updateConfig(settings.serverUrl, settings.apiKey, settings.computerName);

    return { success: true };
  } catch (error) {
    console.error('Error saving settings:', error);
    return { success: false, error: error.message };
  }
});

ipcMain.handle('test-connection', async (event, serverUrl, apiKey) => {
  try {
    const testClient = new ApiClient(serverUrl, apiKey, 'test');
    const result = await testClient.testConnection();
    return result;
  } catch (error) {
    return { success: false, error: error.message };
  }
});

ipcMain.handle('get-system-info', () => {
  return systemMonitor.getSystemInfo();
});

// Auto-updater events
autoUpdater.on('checking-for-update', () => {
  console.log('Checking for update...');
});

autoUpdater.on('update-available', (info) => {
  console.log('Update available.');
});

autoUpdater.on('update-not-available', (info) => {
  console.log('Update not available.');
});

autoUpdater.on('error', (err) => {
  console.log('Error in auto-updater. ' + err);
});

autoUpdater.on('download-progress', (progressObj) => {
  let log_message = "Download speed: " + progressObj.bytesPerSecond;
  log_message = log_message + ' - Downloaded ' + progressObj.percent + '%';
  log_message = log_message + ' (' + progressObj.transferred + "/" + progressObj.total + ')';
  console.log(log_message);
});

autoUpdater.on('update-downloaded', (info) => {
  console.log('Update downloaded');
  autoUpdater.quitAndInstall();
});