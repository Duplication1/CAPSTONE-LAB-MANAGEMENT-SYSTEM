// Renderer process for Lab Health Monitor
class HealthMonitorApp {
  constructor() {
    this.systemData = null;
    this.alerts = [];
    this.isConnected = false;
    this.settings = null;
    
    this.initializeEventListeners();
    this.loadSettings();
    this.setupElectronListeners();
  }

  async initializeEventListeners() {
    // Tab switching
    document.querySelectorAll('.tab-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        this.switchTab(e.target.dataset.tab);
      });
    });

    // Settings modal
    document.getElementById('settingsBtn').addEventListener('click', () => {
      this.showSettings();
    });

    document.getElementById('closeSettingsBtn').addEventListener('click', () => {
      this.hideSettings();
    });

    document.getElementById('cancelSettingsBtn').addEventListener('click', () => {
      this.hideSettings();
    });

    // Minimize button
    document.getElementById('minimizeBtn').addEventListener('click', () => {
      window.close();
    });

    // Settings form
    document.getElementById('settingsForm').addEventListener('submit', (e) => {
      e.preventDefault();
      this.saveSettings();
    });

    document.getElementById('testConnectionBtn').addEventListener('click', () => {
      this.testConnection();
    });

    // Clear alerts
    document.getElementById('clearAlertsBtn').addEventListener('click', () => {
      this.clearAlerts();
    });

    // History controls
    document.getElementById('historyTimeframe').addEventListener('change', () => {
      this.updateHistoryChart();
    });

    document.getElementById('historyMetric').addEventListener('change', () => {
      this.updateHistoryChart();
    });
  }

  setupElectronListeners() {
    // Listen for system data updates
    window.electronAPI.onSystemData((event, data) => {
      this.systemData = data;
      this.updateUI();
    });

    // Listen for alerts
    window.electronAPI.onSystemAlert((event, alert) => {
      this.addAlert(alert);
    });

    // Listen for settings show request
    window.electronAPI.onShowSettings(() => {
      this.showSettings();
    });
  }

  async loadSettings() {
    try {
      this.settings = await window.electronAPI.getSettings();
      this.populateSettingsForm();
    } catch (error) {
      console.error('Error loading settings:', error);
    }
  }

  populateSettingsForm() {
    if (!this.settings) return;

    document.getElementById('serverUrl').value = this.settings.serverUrl || '';
    document.getElementById('apiKey').value = this.settings.apiKey || '';
    document.getElementById('computerName').value = this.settings.computerName || '';
    document.getElementById('monitoringInterval').value = this.settings.monitoringInterval || 30;
    document.getElementById('startMinimized').checked = this.settings.startMinimized || false;
    document.getElementById('startWithSystem').checked = this.settings.startWithSystem || false;
    
    const thresholds = this.settings.alertThresholds || {};
    document.getElementById('cpuThreshold').value = thresholds.cpu || 80;
    document.getElementById('memoryThreshold').value = thresholds.memory || 85;
    document.getElementById('diskThreshold').value = thresholds.disk || 90;
    document.getElementById('temperatureThreshold').value = thresholds.temperature || 70;
  }

  async saveSettings() {
    try {
      const formData = new FormData(document.getElementById('settingsForm'));
      const settings = {
        serverUrl: document.getElementById('serverUrl').value,
        apiKey: document.getElementById('apiKey').value,
        computerName: document.getElementById('computerName').value,
        monitoringInterval: parseInt(document.getElementById('monitoringInterval').value),
        startMinimized: document.getElementById('startMinimized').checked,
        startWithSystem: document.getElementById('startWithSystem').checked,
        alertThresholds: {
          cpu: parseInt(document.getElementById('cpuThreshold').value),
          memory: parseInt(document.getElementById('memoryThreshold').value),
          disk: parseInt(document.getElementById('diskThreshold').value),
          temperature: parseInt(document.getElementById('temperatureThreshold').value)
        }
      };

      const result = await window.electronAPI.saveSettings(settings);
      
      if (result.success) {
        this.settings = settings;
        this.hideSettings();
        this.showNotification('Settings saved successfully', 'success');
      } else {
        this.showNotification('Failed to save settings: ' + result.error, 'error');
      }
    } catch (error) {
      console.error('Error saving settings:', error);
      this.showNotification('Error saving settings', 'error');
    }
  }

  async testConnection() {
    const serverUrl = document.getElementById('serverUrl').value;
    const apiKey = document.getElementById('apiKey').value;
    const testBtn = document.getElementById('testConnectionBtn');
    const testResult = document.getElementById('testResult');

    if (!serverUrl) {
      testResult.textContent = 'Please enter a server URL';
      testResult.className = 'test-result error';
      return;
    }

    testBtn.disabled = true;
    testBtn.textContent = 'Testing...';
    testResult.textContent = 'Testing connection...';
    testResult.className = 'test-result';

    try {
      const result = await window.electronAPI.testConnection(serverUrl, apiKey);
      
      if (result.success) {
        testResult.textContent = '✓ Connection successful';
        testResult.className = 'test-result success';
        this.isConnected = true;
        this.updateConnectionStatus();
      } else {
        testResult.textContent = '✗ ' + result.error;
        testResult.className = 'test-result error';
      }
    } catch (error) {
      testResult.textContent = '✗ Connection failed';
      testResult.className = 'test-result error';
    }

    testBtn.disabled = false;
    testBtn.innerHTML = '<i class="fas fa-plug"></i> Test Connection';
  }

  updateUI() {
    if (!this.systemData) return;

    this.updateOverviewCards();
    this.updateProcesses();
    this.updateSystemInfo();
    this.updateConnectionStatus();
  }

  updateOverviewCards() {
    const data = this.systemData;

    // CPU
    document.getElementById('cpuUsage').textContent = data.cpu.usage.toFixed(1);
    document.getElementById('cpuProgress').style.width = `${Math.min(data.cpu.usage, 100)}%`;
    document.getElementById('cpuTemp').textContent = 
      data.cpu.temperature ? `Temperature: ${data.cpu.temperature}°C` : 'Temperature: N/A';

    // Memory
    const memoryPercent = data.memory.usedPercent;
    document.getElementById('memoryUsage').textContent = memoryPercent.toFixed(1);
    document.getElementById('memoryProgress').style.width = `${Math.min(memoryPercent, 100)}%`;
    document.getElementById('memoryDetails').textContent = 
      `${this.formatBytes(data.memory.used)} / ${this.formatBytes(data.memory.total)}`;

    // Disk
    const diskPercent = data.disk.usedPercent;
    document.getElementById('diskUsage').textContent = diskPercent.toFixed(1);
    document.getElementById('diskProgress').style.width = `${Math.min(diskPercent, 100)}%`;
    const primaryDisk = data.disk.primary;
    if (primaryDisk) {
      document.getElementById('diskDetails').textContent = 
        `${this.formatBytes(primaryDisk.used)} / ${this.formatBytes(primaryDisk.total)}`;
    }

    // Network
    document.getElementById('networkConnections').textContent = 
      `${data.network.activeConnections} active connections`;
  }

  updateProcesses() {
    const data = this.systemData;
    
    // Top CPU processes
    const topCpuContainer = document.getElementById('topCpuProcesses');
    topCpuContainer.innerHTML = '';
    
    data.processes.topCpu.forEach(process => {
      const processElement = this.createProcessElement(process, 'cpu');
      topCpuContainer.appendChild(processElement);
    });

    // Top Memory processes
    const topMemoryContainer = document.getElementById('topMemoryProcesses');
    topMemoryContainer.innerHTML = '';
    
    data.processes.topMemory.forEach(process => {
      const processElement = this.createProcessElement(process, 'memory');
      topMemoryContainer.appendChild(processElement);
    });
  }

  createProcessElement(process, type) {
    const div = document.createElement('div');
    div.className = 'process-item';
    
    const value = type === 'cpu' ? `${process.cpu.toFixed(1)}%` : `${this.formatBytes(process.memoryMB * 1024 * 1024)}`;
    
    div.innerHTML = `
      <div class="process-info">
        <div class="process-name">${this.escapeHtml(process.name)}</div>
        <div class="process-details">PID: ${process.pid}</div>
      </div>
      <div class="process-value">${value}</div>
    `;
    
    return div;
  }

  async updateSystemInfo() {
    try {
      const systemInfo = await window.electronAPI.getSystemInfo();
      if (!systemInfo) return;

      const container = document.getElementById('systemInfo');
      container.innerHTML = '';

      // System Information
      const systemSection = this.createInfoSection('System', 'fas fa-desktop', {
        'Manufacturer': systemInfo.system.manufacturer,
        'Model': systemInfo.system.model,
        'Hostname': systemInfo.os.hostname,
        'Platform': systemInfo.os.platform,
        'Architecture': systemInfo.os.arch,
        'OS Release': systemInfo.os.release
      });
      container.appendChild(systemSection);

      // CPU Information
      const cpuSection = this.createInfoSection('Processor', 'fas fa-microchip', {
        'Brand': systemInfo.cpu.brand,
        'Cores': `${systemInfo.cpu.cores} (${systemInfo.cpu.physicalCores} physical)`,
        'Speed': `${systemInfo.cpu.speed} GHz`,
        'Cache': systemInfo.cpu.cache ? `L1: ${systemInfo.cpu.cache.l1d}KB, L2: ${systemInfo.cpu.cache.l2}KB, L3: ${systemInfo.cpu.cache.l3}KB` : 'N/A'
      });
      container.appendChild(cpuSection);

      // Memory Information
      const memorySection = this.createInfoSection('Memory', 'fas fa-memory', {
        'Total': this.formatBytes(systemInfo.memory.total),
        'Type': systemInfo.memory.type || 'Unknown'
      });
      container.appendChild(memorySection);

      // Graphics Information
      if (systemInfo.graphics && systemInfo.graphics.length > 0) {
        const gpu = systemInfo.graphics[0];
        const graphicsSection = this.createInfoSection('Graphics', 'fas fa-display', {
          'Vendor': gpu.vendor || 'Unknown',
          'Model': gpu.model || 'Unknown',
          'VRAM': gpu.vram ? this.formatBytes(gpu.vram * 1024 * 1024) : 'Unknown',
          'Driver': gpu.driverVersion || 'Unknown'
        });
        container.appendChild(graphicsSection);
      }
    } catch (error) {
      console.error('Error updating system info:', error);
    }
  }

  createInfoSection(title, icon, data) {
    const section = document.createElement('div');
    section.className = 'info-section';
    
    section.innerHTML = `
      <h4><i class="${icon}"></i> ${title}</h4>
      <div class="info-list">
        ${Object.entries(data).map(([key, value]) => `
          <div class="info-item">
            <span class="info-label">${key}:</span>
            <span class="info-value">${this.escapeHtml(value || 'N/A')}</span>
          </div>
        `).join('')}
      </div>
    `;
    
    return section;
  }

  addAlert(alert) {
    this.alerts.unshift({
      ...alert,
      id: Date.now(),
      time: new Date().toLocaleTimeString()
    });

    // Limit to 50 alerts
    if (this.alerts.length > 50) {
      this.alerts = this.alerts.slice(0, 50);
    }

    this.updateAlertsUI();
    this.updateAlertCount();
  }

  updateAlertsUI() {
    const container = document.getElementById('alertsList');
    
    if (this.alerts.length === 0) {
      container.innerHTML = `
        <div class="no-alerts">
          <i class="fas fa-check-circle"></i>
          <p>No alerts at this time</p>
        </div>
      `;
      return;
    }

    container.innerHTML = this.alerts.map(alert => `
      <div class="alert-item ${alert.severity}">
        <div class="alert-icon">
          <i class="fas fa-${alert.severity === 'critical' ? 'exclamation' : 'exclamation-triangle'}"></i>
        </div>
        <div class="alert-content">
          <div class="alert-message">${this.escapeHtml(alert.message)}</div>
          <div class="alert-time">${alert.time}</div>
        </div>
      </div>
    `).join('');
  }

  updateAlertCount() {
    const countElement = document.getElementById('alertCount');
    const recentAlerts = this.alerts.filter(alert => {
      const alertTime = new Date(alert.timestamp);
      const now = new Date();
      return (now - alertTime) < 24 * 60 * 60 * 1000; // Last 24 hours
    });

    if (recentAlerts.length > 0) {
      countElement.textContent = recentAlerts.length;
      countElement.style.display = 'flex';
    } else {
      countElement.style.display = 'none';
    }
  }

  clearAlerts() {
    this.alerts = [];
    this.updateAlertsUI();
    this.updateAlertCount();
  }

  updateConnectionStatus() {
    const indicator = document.getElementById('connectionStatus');
    const hasApiKey = this.settings && this.settings.apiKey;
    
    if (hasApiKey && this.isConnected) {
      indicator.className = 'status-indicator connected';
      indicator.querySelector('span').textContent = 'Connected';
    } else if (!hasApiKey) {
      indicator.className = 'status-indicator disconnected';
      indicator.querySelector('span').textContent = 'Not Configured';
    } else {
      indicator.className = 'status-indicator connecting';
      indicator.querySelector('span').textContent = 'Connecting...';
    }
  }

  switchTab(tabName) {
    // Update tab buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
      btn.classList.toggle('active', btn.dataset.tab === tabName);
    });

    // Update tab content
    document.querySelectorAll('.tab-content').forEach(content => {
      content.classList.toggle('active', content.id === `${tabName}Tab`);
    });

    // Load tab-specific content
    if (tabName === 'system') {
      this.updateSystemInfo();
    } else if (tabName === 'history') {
      this.updateHistoryChart();
    }
  }

  showSettings() {
    document.getElementById('settingsModal').classList.add('active');
    document.body.style.overflow = 'hidden';
  }

  hideSettings() {
    document.getElementById('settingsModal').classList.remove('active');
    document.body.style.overflow = '';
  }

  updateHistoryChart() {
    // Placeholder for chart implementation
    // In a real implementation, you would use a charting library like Chart.js
    const container = document.getElementById('historyChart');
    container.innerHTML = `
      <div style="text-align: center; padding: 2rem; color: var(--text-secondary);">
        <i class="fas fa-chart-line" style="font-size: 3rem; margin-bottom: 1rem;"></i>
        <p>Historical charts will be implemented with a charting library</p>
        <p>Selected: ${document.getElementById('historyMetric').value} - ${document.getElementById('historyTimeframe').value}</p>
      </div>
    `;
  }

  formatBytes(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
  }

  escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  showNotification(message, type = 'info') {
    // Simple notification system
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    notification.style.cssText = `
      position: fixed;
      top: 20px;
      right: 20px;
      padding: 1rem;
      border-radius: 0.5rem;
      color: white;
      z-index: 10000;
      animation: slideIn 0.3s ease;
      background: ${type === 'success' ? 'var(--success-color)' : 'var(--danger-color)'};
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
      notification.remove();
    }, 3000);
  }
}

// Initialize the app when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  new HealthMonitorApp();
});