const axios = require('axios');

class ApiClient {
  constructor(serverUrl, apiKey, computerName) {
    this.serverUrl = serverUrl;
    this.apiKey = apiKey;
    this.computerName = computerName;
    this.axiosInstance = this.createAxiosInstance();
  }

  createAxiosInstance() {
    return axios.create({
      baseURL: this.serverUrl,
      timeout: 30000,
      headers: {
        'Content-Type': 'application/json',
        'X-API-Key': this.apiKey,
        'X-Computer-Name': this.computerName
      }
    });
  }

  updateConfig(serverUrl, apiKey, computerName) {
    this.serverUrl = serverUrl;
    this.apiKey = apiKey;
    this.computerName = computerName;
    this.axiosInstance = this.createAxiosInstance();
  }

  async testConnection() {
    try {
      const response = await this.axiosInstance.get('/CAPSTONE-LAB-MANAGEMENT-SYSTEM/api/health.php?action=status');
      return {
        success: true,
        message: 'Connection successful',
        serverTime: new Date().toISOString(),
        data: response.data
      };
    } catch (error) {
      return {
        success: false,
        error: this.formatError(error)
      };
    }
  }

  async sendHealthData(data) {
    try {
      const payload = {
        computer_name: this.computerName,
        timestamp: data.timestamp,
        cpu: {
          usage: data.cpu?.usage || 0,
          temperature: data.cpu?.temperature || 0
        },
        memory: {
          total: data.memory?.total || 0,
          used: data.memory?.used || 0,
          usedPercent: data.memory?.usedPercent || 0
        },
        disk: {
          total: data.disk?.total || 0,
          used: data.disk?.used || 0,
          usedPercent: data.disk?.usedPercent || 0
        },
        network: {
          sent: data.network?.sent || 0,
          received: data.network?.received || 0
        },
        system: {
          uptime: data.system?.uptime || 0,
          loadAverage: data.system?.loadAverage || 0,
          processes: data.processes?.count || 0
        }
      };

      const response = await this.axiosInstance.post('/CAPSTONE-LAB-MANAGEMENT-SYSTEM/api/health.php?action=health', payload);
      return response.data;
    } catch (error) {
      console.error('Error sending health data:', this.formatError(error));
      throw error;
    }
  }

  async sendAlert(alert) {
    try {
      const payload = {
        computer_name: this.computerName,
        alert_type: alert.type,
        severity: alert.severity,
        message: alert.message,
        value: alert.value,
        threshold: alert.threshold,
        timestamp: alert.timestamp
      };

      const response = await this.axiosInstance.post('/api/health/alert', payload);
      return response.data;
    } catch (error) {
      console.error('Error sending alert:', this.formatError(error));
      throw error;
    }
  }

  async sendSystemInfo(systemInfo) {
    try {
      const payload = {
        computer_name: this.computerName,
        system_info: JSON.stringify(systemInfo),
        timestamp: new Date().toISOString()
      };

      const response = await this.axiosInstance.post('/api/health/system-info', payload);
      return response.data;
    } catch (error) {
      console.error('Error sending system info:', this.formatError(error));
      throw error;
    }
  }

  async getServerSettings() {
    try {
      const response = await this.axiosInstance.get(`/api/health/settings/${this.computerName}`);
      return response.data;
    } catch (error) {
      console.error('Error getting server settings:', this.formatError(error));
      throw error;
    }
  }

  async registerComputer(systemInfo) {
    try {
      const payload = {
        computer_name: this.computerName,
        system_info: JSON.stringify(systemInfo),
        first_seen: new Date().toISOString(),
        last_seen: new Date().toISOString(),
        status: 'online'
      };

      const response = await this.axiosInstance.post('/api/health/register', payload);
      return response.data;
    } catch (error) {
      console.error('Error registering computer:', this.formatError(error));
      throw error;
    }
  }

  async sendHeartbeat() {
    try {
      const payload = {
        computer_name: this.computerName,
        timestamp: new Date().toISOString(),
        status: 'online'
      };

      const response = await this.axiosInstance.post('/api/health/heartbeat', payload);
      return response.data;
    } catch (error) {
      console.error('Error sending heartbeat:', this.formatError(error));
      throw error;
    }
  }

  formatError(error) {
    if (error.response) {
      return `Server Error ${error.response.status}: ${error.response.data?.message || error.response.statusText}`;
    } else if (error.request) {
      return 'Network Error: Unable to connect to server';
    } else {
      return `Error: ${error.message}`;
    }
  }
}

module.exports = ApiClient;