const EventEmitter = require('events');
const si = require('systeminformation');
const schedule = require('node-schedule');
const os = require('os');

class SystemMonitor extends EventEmitter {
  constructor() {
    super();
    this.isRunning = false;
    this.interval = 30000; // 30 seconds default
    this.job = null;
    this.thresholds = {
      cpu: 80,
      memory: 85,
      disk: 90,
      temperature: 70
    };
    this.previousData = {};
    this.alertCooldowns = new Map();
  }

  async start() {
    if (this.isRunning) return;
    
    this.isRunning = true;
    console.log('System monitor started');
    
    // Get initial system info
    try {
      const systemInfo = await this.getSystemInfo();
      this.emit('system-info', systemInfo);
    } catch (error) {
      console.error('Error getting system info:', error);
    }

    // Start periodic monitoring
    this.scheduleMonitoring();
    
    // Get initial reading
    this.collectSystemData();
  }

  stop() {
    if (!this.isRunning) return;
    
    this.isRunning = false;
    if (this.job) {
      this.job.cancel();
      this.job = null;
    }
    console.log('System monitor stopped');
  }

  scheduleMonitoring() {
    if (this.job) {
      this.job.cancel();
    }

    const intervalSeconds = Math.floor(this.interval / 1000);
    const rule = `*/${intervalSeconds} * * * * *`;
    
    this.job = schedule.scheduleJob(rule, () => {
      this.collectSystemData();
    });
  }

  updateSettings({ interval, thresholds }) {
    if (interval && interval !== this.interval) {
      this.interval = interval;
      if (this.isRunning) {
        this.scheduleMonitoring();
      }
    }

    if (thresholds) {
      this.thresholds = { ...this.thresholds, ...thresholds };
    }
  }

  async collectSystemData() {
    try {
      const data = {
        timestamp: new Date().toISOString(),
        cpu: await this.getCpuData(),
        memory: await this.getMemoryData(),
        disk: await this.getDiskData(),
        network: await this.getNetworkData(),
        temperature: await this.getTemperatureData(),
        processes: await this.getProcessData(),
        system: await this.getSystemData()
      };

      // Check for alerts
      try {
        this.checkAlerts(data);
      } catch (alertError) {
        console.warn('Error checking alerts:', alertError.message);
      }

      // Store previous data for comparison
      this.previousData = data;

      // Emit data event
      this.emit('data', data);

    } catch (error) {
      console.error('Error collecting system data:', error);
      this.emit('error', error);
    }
  }

  async getCpuData() {
    const [cpuLoad, cpuTemp] = await Promise.all([
      si.currentLoad(),
      si.cpuTemperature()
    ]);

    return {
      usage: cpuLoad.currentLoad,
      cores: cpuLoad.cpus.map(cpu => ({
        load: cpu.load,
        loadUser: cpu.loadUser,
        loadSystem: cpu.loadSystem,
        loadIdle: cpu.loadIdle
      })),
      temperature: cpuTemp.main || null,
      speed: cpuLoad.avgLoad
    };
  }

  async getMemoryData() {
    const mem = await si.mem();
    
    return {
      total: mem.total,
      used: mem.used,
      free: mem.free,
      available: mem.available,
      usedPercent: (mem.used / mem.total) * 100,
      cached: mem.cached || 0,
      buffers: mem.buffers || 0
    };
  }

  async getDiskData() {
    const [fsSize, diskIO] = await Promise.all([
      si.fsSize(),
      si.disksIO()
    ]);

    const disks = fsSize.map(disk => ({
      mount: disk.mount,
      type: disk.type,
      total: disk.size,
      used: disk.used,
      available: disk.available,
      usedPercent: disk.use,
      filesystem: disk.fs
    }));

    return {
      disks,
      io: diskIO ? {
        readBytes: diskIO.rIO_sec || 0,
        writeBytes: diskIO.wIO_sec || 0,
        readOperations: diskIO.rOPS_sec || 0,
        writeOperations: diskIO.wOPS_sec || 0
      } : {
        readBytes: 0,
        writeBytes: 0,
        readOperations: 0,
        writeOperations: 0
      },
      // Primary disk (usually C: on Windows, / on Unix)
      primary: disks.find(d => d.mount === 'C:' || d.mount === '/') || disks[0],
      usedPercent: disks[0] ? disks[0].usedPercent : 0
    };
  }

  async getNetworkData() {
    const [networkStats, networkConnections] = await Promise.all([
      si.networkStats(),
      si.networkConnections()
    ]);

    const totalStats = networkStats.reduce((acc, iface) => ({
      bytesReceived: acc.bytesReceived + (iface.rx_bytes || 0),
      bytesSent: acc.bytesSent + (iface.tx_bytes || 0),
      packetsReceived: acc.packetsReceived + (iface.rx_packets || 0),
      packetsSent: acc.packetsSent + (iface.tx_packets || 0),
      errors: acc.errors + (iface.rx_errors || 0) + (iface.tx_errors || 0),
      dropped: acc.dropped + (iface.rx_dropped || 0) + (iface.tx_dropped || 0)
    }), {
      bytesReceived: 0,
      bytesSent: 0,
      packetsReceived: 0,
      packetsSent: 0,
      errors: 0,
      dropped: 0
    });

    return {
      interfaces: networkStats,
      total: totalStats,
      connections: networkConnections.length,
      activeConnections: networkConnections.filter(conn => conn.state === 'ESTABLISHED').length
    };
  }

  async getTemperatureData() {
    try {
      const temp = await si.cpuTemperature();
      return {
        cpu: temp.main,
        cores: temp.cores || [],
        max: temp.max
      };
    } catch (error) {
      return {
        cpu: null,
        cores: [],
        max: null
      };
    }
  }

  async getProcessData() {
    const processes = await si.processes();
    
    // Get top CPU and memory consuming processes
    const topCpuProcesses = processes.list
      .filter(p => p.cpu > 0)
      .sort((a, b) => b.cpu - a.cpu)
      .slice(0, 5)
      .map(p => ({
        name: p.name,
        pid: p.pid,
        cpu: p.cpu,
        memory: p.mem,
        memoryMB: p.memRss / 1024 / 1024
      }));

    const topMemoryProcesses = processes.list
      .sort((a, b) => b.mem - a.mem)
      .slice(0, 5)
      .map(p => ({
        name: p.name,
        pid: p.pid,
        cpu: p.cpu,
        memory: p.mem,
        memoryMB: p.memRss / 1024 / 1024
      }));

    return {
      total: processes.all,
      running: processes.running,
      sleeping: processes.sleeping,
      blocked: processes.blocked,
      topCpu: topCpuProcesses,
      topMemory: topMemoryProcesses
    };
  }

  async getSystemData() {
    const [osInfo, currentLoad, uptime] = await Promise.all([
      si.osInfo(),
      si.currentLoad(),
      si.time()
    ]);

    return {
      platform: osInfo.platform,
      hostname: osInfo.hostname,
      arch: osInfo.arch,
      release: osInfo.release,
      uptime: uptime.uptime,
      loadAverage: os.loadavg(),
      userSessions: await this.getUserSessions()
    };
  }

  async getUserSessions() {
    try {
      const users = await si.users();
      return users.map(user => ({
        user: user.user,
        tty: user.tty,
        date: user.date,
        time: user.time,
        ip: user.ip
      }));
    } catch (error) {
      return [];
    }
  }

  checkAlerts(data) {
    const alerts = [];

    // CPU alert
    if (data.cpu.usage > this.thresholds.cpu) {
      if (!this.isInCooldown('cpu-high')) {
        alerts.push({
          type: 'cpu-high',
          severity: 'warning',
          message: `CPU usage is high: ${data.cpu.usage.toFixed(1)}%`,
          value: data.cpu.usage,
          threshold: this.thresholds.cpu,
          timestamp: data.timestamp
        });
        this.setCooldown('cpu-high', 5 * 60 * 1000); // 5 minutes
      }
    }

    // Memory alert
    if (data.memory.usedPercent > this.thresholds.memory) {
      if (!this.isInCooldown('memory-high')) {
        alerts.push({
          type: 'memory-high',
          severity: 'warning',
          message: `Memory usage is high: ${data.memory.usedPercent.toFixed(1)}%`,
          value: data.memory.usedPercent,
          threshold: this.thresholds.memory,
          timestamp: data.timestamp
        });
        this.setCooldown('memory-high', 5 * 60 * 1000);
      }
    }

    // Disk alert
    if (data.disk.usedPercent > this.thresholds.disk) {
      if (!this.isInCooldown('disk-high')) {
        alerts.push({
          type: 'disk-high',
          severity: 'warning',
          message: `Disk usage is high: ${data.disk.usedPercent.toFixed(1)}%`,
          value: data.disk.usedPercent,
          threshold: this.thresholds.disk,
          timestamp: data.timestamp
        });
        this.setCooldown('disk-high', 10 * 60 * 1000); // 10 minutes
      }
    }

    // Temperature alert
    if (data.temperature.cpu && data.temperature.cpu > this.thresholds.temperature) {
      if (!this.isInCooldown('temperature-high')) {
        alerts.push({
          type: 'temperature-high',
          severity: 'critical',
          message: `CPU temperature is high: ${data.temperature.cpu}°C`,
          value: data.temperature.cpu,
          threshold: this.thresholds.temperature,
          timestamp: data.timestamp
        });
        this.setCooldown('temperature-high', 3 * 60 * 1000); // 3 minutes
      }
    }

    // Critical CPU alert (>95%)
    if (data.cpu.usage > 95) {
      if (!this.isInCooldown('cpu-critical')) {
        alerts.push({
          type: 'cpu-critical',
          severity: 'critical',
          message: `CPU usage is critically high: ${data.cpu.usage.toFixed(1)}%`,
          value: data.cpu.usage,
          threshold: 95,
          timestamp: data.timestamp
        });
        this.setCooldown('cpu-critical', 2 * 60 * 1000); // 2 minutes
      }
    }

    // Emit alerts
    alerts.forEach(alert => this.emit('alert', alert));
  }

  isInCooldown(alertType) {
    const cooldownEnd = this.alertCooldowns.get(alertType);
    return cooldownEnd && Date.now() < cooldownEnd;
  }

  setCooldown(alertType, duration) {
    this.alertCooldowns.set(alertType, Date.now() + duration);
  }

  async getSystemInfo() {
    try {
      const [system, cpu, mem, os, graphics] = await Promise.all([
        si.system(),
        si.cpu(),
        si.mem(),
        si.osInfo(),
        si.graphics()
      ]);

      return {
        system: {
          manufacturer: system.manufacturer,
          model: system.model,
          version: system.version,
          serial: system.serial,
          uuid: system.uuid
        },
        cpu: {
          manufacturer: cpu.manufacturer,
          brand: cpu.brand,
          family: cpu.family,
          model: cpu.model,
          speed: cpu.speed,
          speedMin: cpu.speedMin,
          speedMax: cpu.speedMax,
          cores: cpu.cores,
          physicalCores: cpu.physicalCores,
          processors: cpu.processors,
          cache: cpu.cache
        },
        memory: {
          total: mem.total,
          type: mem.type || 'Unknown'
        },
        os: {
          platform: os.platform,
          distro: os.distro,
          release: os.release,
          codename: os.codename,
          kernel: os.kernel,
          arch: os.arch,
          hostname: os.hostname,
          fqdn: os.fqdn
        },
        graphics: graphics.controllers?.map(gpu => ({
          vendor: gpu.vendor,
          model: gpu.model,
          vram: gpu.vram,
          driverVersion: gpu.driverVersion
        })) || []
      };
    } catch (error) {
      console.error('Error getting system info:', error);
      return null;
    }
  }
}

module.exports = SystemMonitor;