const { contextBridge, ipcRenderer } = require('electron');

// Expose protected methods that allow the renderer process to use
// the ipcRenderer without exposing the entire object
contextBridge.exposeInMainWorld('electronAPI', {
  // Settings
  getSettings: () => ipcRenderer.invoke('get-settings'),
  saveSettings: (settings) => ipcRenderer.invoke('save-settings', settings),
  testConnection: (serverUrl, apiKey) => ipcRenderer.invoke('test-connection', serverUrl, apiKey),
  
  // System info
  getSystemInfo: () => ipcRenderer.invoke('get-system-info'),
  
  // Events
  onSystemData: (callback) => ipcRenderer.on('system-data', callback),
  onSystemAlert: (callback) => ipcRenderer.on('system-alert', callback),
  onShowSettings: (callback) => ipcRenderer.on('show-settings', callback),
  
  // Remove listeners
  removeAllListeners: (channel) => ipcRenderer.removeAllListeners(channel)
});