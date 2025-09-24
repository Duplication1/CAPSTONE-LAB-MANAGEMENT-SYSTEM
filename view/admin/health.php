<?php
/**
 * System Health Monitoring Dashboard - Lab Management System
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is an admin
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

$admin_name = $_SESSION['full_name'];
$admin_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Health Monitor - Lab Management System</title>
    <link href="../../css/output.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .status-online { color: #10b981; }
        .status-offline { color: #ef4444; }
        .status-warning { color: #f59e0b; }
        .status-critical { color: #dc2626; }
        
        .computer-card {
            transition: all 0.2s ease;
            border-left: 4px solid transparent;
        }
        
        .computer-card.online { border-left-color: #10b981; }
        .computer-card.offline { border-left-color: #6b7280; }
        .computer-card.warning { border-left-color: #f59e0b; }
        .computer-card.critical { border-left-color: #dc2626; }
        
        .computer-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        
        .metric-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .alert-badge {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            margin: 1rem 0;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex dashboard-layout min-h-screen">
        <!-- Sidebar -->
        <?php include '../components/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <!-- Header -->
            <?php include '../components/header.php'; ?>

            <!-- Dashboard Content -->
            <main class="flex-1 p-6 overflow-auto">
                <!-- Page Header -->
                <div class="mb-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">
                                <i class="fas fa-heartbeat text-blue-600 mr-3"></i>
                                System Health Monitor
                            </h1>
                            <p class="text-gray-600 mt-1">Monitor the health and performance of all lab computers</p>
                        </div>
                        <div class="flex space-x-3">
                            <button id="refreshBtn" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                <i class="fas fa-sync-alt mr-2"></i>
                                Refresh
                            </button>
                            <button id="settingsBtn" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                                <i class="fas fa-cog mr-2"></i>
                                Settings
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100">
                                <i class="fas fa-desktop text-green-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Total Computers</p>
                                <p class="text-2xl font-bold text-gray-900" id="totalComputers">--</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100">
                                <i class="fas fa-check-circle text-green-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Online</p>
                                <p class="text-2xl font-bold text-green-600" id="onlineComputers">--</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100">
                                <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Warnings</p>
                                <p class="text-2xl font-bold text-yellow-600" id="warningComputers">--</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-red-100">
                                <i class="fas fa-times-circle text-red-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Critical</p>
                                <p class="text-2xl font-bold text-red-600" id="criticalComputers">--</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="bg-white rounded-lg shadow mb-6">
                    <div class="border-b border-gray-200">
                        <nav class="flex space-x-8 px-6" aria-label="Tabs">
                            <button class="tab-btn active py-4 px-1 border-b-2 font-medium text-sm" data-tab="computers">
                                <i class="fas fa-desktop mr-2"></i>
                                Computers
                            </button>
                            <button class="tab-btn py-4 px-1 border-b-2 font-medium text-sm" data-tab="alerts">
                                <i class="fas fa-bell mr-2"></i>
                                Alerts
                                <span class="ml-2 bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full" id="alertCount">0</span>
                            </button>
                            <button class="tab-btn py-4 px-1 border-b-2 font-medium text-sm" data-tab="overview">
                                <i class="fas fa-chart-bar mr-2"></i>
                                Overview
                            </button>
                            <button class="tab-btn py-4 px-1 border-b-2 font-medium text-sm" data-tab="api-keys">
                                <i class="fas fa-key mr-2"></i>
                                API Keys
                            </button>
                        </nav>
                    </div>

                    <!-- Tab Content -->
                    <div class="p-6">
                        <!-- Computers Tab -->
                        <div id="computersTab" class="tab-content active">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Connected Computers</h3>
                                <div class="flex items-center space-x-3">
                                    <select id="statusFilter" class="border border-gray-300 rounded-md px-3 py-1 text-sm">
                                        <option value="">All Status</option>
                                        <option value="online">Online</option>
                                        <option value="warning">Warning</option>
                                        <option value="critical">Critical</option>
                                        <option value="offline">Offline</option>
                                    </select>
                                    <input type="text" id="searchComputers" placeholder="Search computers..." 
                                           class="border border-gray-300 rounded-md px-3 py-1 text-sm">
                                </div>
                            </div>
                            <div id="computersList" class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4">
                                <!-- Computer cards will be loaded here -->
                            </div>
                        </div>

                        <!-- Alerts Tab -->
                        <div id="alertsTab" class="tab-content hidden">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900">System Alerts</h3>
                                <div class="flex items-center space-x-3">
                                    <select id="severityFilter" class="border border-gray-300 rounded-md px-3 py-1 text-sm">
                                        <option value="">All Severity</option>
                                        <option value="critical">Critical</option>
                                        <option value="warning">Warning</option>
                                        <option value="info">Info</option>
                                    </select>
                                    <button id="clearResolvedBtn" class="bg-red-600 text-white px-3 py-1 rounded-md text-sm hover:bg-red-700">
                                        <i class="fas fa-trash mr-1"></i>
                                        Clear Resolved
                                    </button>
                                </div>
                            </div>
                            <div id="alertsList" class="space-y-3">
                                <!-- Alerts will be loaded here -->
                            </div>
                        </div>

                        <!-- Overview Tab -->
                        <div id="overviewTab" class="tab-content hidden">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <h4 class="text-lg font-medium mb-4">Average CPU Usage</h4>
                                    <div class="chart-container">
                                        <canvas id="cpuChart"></canvas>
                                    </div>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <h4 class="text-lg font-medium mb-4">Average Memory Usage</h4>
                                    <div class="chart-container">
                                        <canvas id="memoryChart"></canvas>
                                    </div>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <h4 class="text-lg font-medium mb-4">System Status Distribution</h4>
                                    <div class="chart-container">
                                        <canvas id="statusChart"></canvas>
                                    </div>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <h4 class="text-lg font-medium mb-4">Alert Frequency</h4>
                                    <div class="chart-container">
                                        <canvas id="alertChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- API Keys Tab -->
                        <div id="apiKeysTab" class="tab-content hidden">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900">API Keys Management</h3>
                                <button id="createApiKeyBtn" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                                    <i class="fas fa-plus mr-2"></i>
                                    Create API Key
                                </button>
                            </div>
                            <div id="apiKeysList">
                                <!-- API keys will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Computer Details Modal -->
    <div id="computerModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-screen overflow-y-auto">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold" id="modalComputerName">Computer Details</h2>
                        <button id="closeModal" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>
                <div class="p-6" id="modalContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script>
        // Health Monitor Dashboard JavaScript
        class HealthDashboard {
            constructor() {
                this.computers = [];
                this.alerts = [];
                this.charts = {};
                this.refreshInterval = null;
                this.eventSource = null;
                this.lastAlertId = 0;
                this.notifications = [];
                
                this.initializeEventListeners();
                this.loadData();
                this.startAutoRefresh();
                this.setupRealTimeNotifications();
            }

            initializeEventListeners() {
                // Tab switching
                document.querySelectorAll('.tab-btn').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        this.switchTab(e.target.dataset.tab);
                    });
                });

                // Refresh button
                document.getElementById('refreshBtn').addEventListener('click', () => {
                    this.loadData();
                });

                // Filters
                document.getElementById('statusFilter').addEventListener('change', () => {
                    this.filterComputers();
                });

                document.getElementById('searchComputers').addEventListener('input', () => {
                    this.filterComputers();
                });

                document.getElementById('severityFilter').addEventListener('change', () => {
                    this.filterAlerts();
                });

                // Modal
                document.getElementById('closeModal').addEventListener('click', () => {
                    this.closeModal();
                });

                // Click outside modal to close
                document.getElementById('computerModal').addEventListener('click', (e) => {
                    if (e.target === document.getElementById('computerModal')) {
                        this.closeModal();
                    }
                });
            }

            async loadData() {
                try {
                    await Promise.all([
                        this.loadComputers(),
                        this.loadAlerts()
                    ]);
                    this.updateStats();
                    this.updateCharts();
                } catch (error) {
                    console.error('Error loading data:', error);
                    this.showNotification('Failed to load data', 'error');
                }
            }

            async loadComputers() {
                try {
                    const response = await fetch('/CAPSTONE-LAB-MANAGEMENT-SYSTEM/api/health/computers');
                    const data = await response.json();
                    
                    if (data.success) {
                        this.computers = data.computers;
                        this.renderComputers();
                    } else {
                        throw new Error(data.error || 'Failed to load computers');
                    }
                } catch (error) {
                    console.error('Error loading computers:', error);
                    // Show offline message or cached data
                    document.getElementById('computersList').innerHTML = `
                        <div class="col-span-full text-center py-8 text-gray-500">
                            <i class="fas fa-exclamation-circle text-3xl mb-2"></i>
                            <p>Unable to load computer data</p>
                            <p class="text-sm">Check network connection and try again</p>
                        </div>
                    `;
                }
            }

            async loadAlerts() {
                try {
                    const response = await fetch('/CAPSTONE-LAB-MANAGEMENT-SYSTEM/api/health/alerts?limit=50');
                    const data = await response.json();
                    
                    if (data.success) {
                        this.alerts = data.alerts;
                        this.renderAlerts();
                    } else {
                        throw new Error(data.error || 'Failed to load alerts');
                    }
                } catch (error) {
                    console.error('Error loading alerts:', error);
                }
            }

            renderComputers() {
                const container = document.getElementById('computersList');
                
                if (this.computers.length === 0) {
                    container.innerHTML = `
                        <div class="col-span-full text-center py-8 text-gray-500">
                            <i class="fas fa-desktop text-3xl mb-2"></i>
                            <p>No computers found</p>
                            <p class="text-sm">Computers will appear here once they connect</p>
                        </div>
                    `;
                    return;
                }

                container.innerHTML = this.computers.map(computer => this.createComputerCard(computer)).join('');
            }

            createComputerCard(computer) {
                const lastSeen = new Date(computer.last_seen).toLocaleString();
                const statusIcon = this.getStatusIcon(computer.status);
                const statusColor = this.getStatusColor(computer.status);
                
                return `
                    <div class="computer-card ${computer.status} bg-white rounded-lg shadow p-4 cursor-pointer hover:shadow-lg transition-all" 
                         onclick="healthDashboard.showComputerDetails('${computer.computer_name}')">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center">
                                <i class="fas fa-desktop text-gray-600 mr-2"></i>
                                <h4 class="font-medium text-gray-900">${this.escapeHtml(computer.computer_name)}</h4>
                            </div>
                            <span class="status-${computer.status}">
                                <i class="${statusIcon}"></i>
                            </span>
                        </div>
                        
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">CPU:</span>
                                <span class="font-medium">${computer.current_cpu ? computer.current_cpu.toFixed(1) + '%' : 'N/A'}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Memory:</span>
                                <span class="font-medium">${computer.current_memory ? computer.current_memory.toFixed(1) + '%' : 'N/A'}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Disk:</span>
                                <span class="font-medium">${computer.current_disk ? computer.current_disk.toFixed(1) + '%' : 'N/A'}</span>
                            </div>
                        </div>
                        
                        <div class="mt-3 pt-3 border-t border-gray-200 flex items-center justify-between text-xs text-gray-500">
                            <span>Last seen: ${lastSeen}</span>
                            ${computer.critical_alerts > 0 || computer.warning_alerts > 0 ? 
                                `<span class="alert-badge bg-red-100 text-red-800 px-2 py-1 rounded-full">
                                    ${computer.critical_alerts + computer.warning_alerts} alerts
                                </span>` : ''
                            }
                        </div>
                    </div>
                `;
            }

            renderAlerts() {
                const container = document.getElementById('alertsList');
                
                if (this.alerts.length === 0) {
                    container.innerHTML = `
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-check-circle text-3xl mb-2 text-green-500"></i>
                            <p>No active alerts</p>
                            <p class="text-sm">All systems are operating normally</p>
                        </div>
                    `;
                    return;
                }

                container.innerHTML = this.alerts.map(alert => this.createAlertCard(alert)).join('');
                
                // Update alert count
                const unacknowledgedAlerts = this.alerts.filter(a => !a.acknowledged).length;
                document.getElementById('alertCount').textContent = unacknowledgedAlerts;
            }

            createAlertCard(alert) {
                const timestamp = new Date(alert.timestamp).toLocaleString();
                const severityIcon = alert.severity === 'critical' ? 'fas fa-exclamation-circle' : 'fas fa-exclamation-triangle';
                const severityColor = alert.severity === 'critical' ? 'red' : alert.severity === 'warning' ? 'yellow' : 'blue';
                
                return `
                    <div class="bg-${severityColor}-50 border border-${severityColor}-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <i class="${severityIcon} text-${severityColor}-600"></i>
                            </div>
                            <div class="ml-3 flex-1">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-medium text-${severityColor}-800">
                                        ${this.escapeHtml(alert.computer_name)} - ${this.escapeHtml(alert.alert_type)}
                                    </h4>
                                    <span class="text-xs text-${severityColor}-600">${timestamp}</span>
                                </div>
                                <p class="text-${severityColor}-700 mt-1">${this.escapeHtml(alert.message)}</p>
                                ${alert.value && alert.threshold_value ? 
                                    `<p class="text-xs text-${severityColor}-600 mt-1">
                                        Value: ${alert.value} | Threshold: ${alert.threshold_value}
                                    </p>` : ''
                                }
                            </div>
                        </div>
                    </div>
                `;
            }

            async showComputerDetails(computerName) {
                try {
                    document.getElementById('modalComputerName').textContent = computerName;
                    document.getElementById('modalContent').innerHTML = '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i></div>';
                    document.getElementById('computerModal').classList.remove('hidden');
                    
                    const response = await fetch(`/CAPSTONE-LAB-MANAGEMENT-SYSTEM/api/health/computer/${encodeURIComponent(computerName)}`);
                    const data = await response.json();
                    
                    if (data.success) {
                        this.renderComputerDetails(data);
                    } else {
                        throw new Error(data.error || 'Failed to load computer details');
                    }
                } catch (error) {
                    console.error('Error loading computer details:', error);
                    document.getElementById('modalContent').innerHTML = `
                        <div class="text-center py-8 text-red-500">
                            <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                            <p>Failed to load computer details</p>
                        </div>
                    `;
                }
            }

            renderComputerDetails(data) {
                const computer = data.computer;
                const healthData = data.health_data.slice(0, 10); // Last 10 data points
                const alerts = data.alerts.slice(0, 5); // Last 5 alerts
                const systemInfo = data.system_info;
                
                document.getElementById('modalContent').innerHTML = `
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Computer Info -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="font-medium text-gray-900 mb-3">Computer Information</h3>
                            <dl class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Name:</dt>
                                    <dd class="font-medium">${this.escapeHtml(computer.computer_name)}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Status:</dt>
                                    <dd class="font-medium status-${computer.status}">
                                        <i class="${this.getStatusIcon(computer.status)} mr-1"></i>
                                        ${computer.status.charAt(0).toUpperCase() + computer.status.slice(1)}
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Last Seen:</dt>
                                    <dd class="font-medium">${new Date(computer.last_seen).toLocaleString()}</dd>
                                </div>
                                ${computer.hostname ? `
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Hostname:</dt>
                                    <dd class="font-medium">${this.escapeHtml(computer.hostname)}</dd>
                                </div>` : ''}
                                ${computer.ip_address ? `
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">IP Address:</dt>
                                    <dd class="font-medium">${this.escapeHtml(computer.ip_address)}</dd>
                                </div>` : ''}
                            </dl>
                        </div>
                        
                        <!-- Latest Health Data -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="font-medium text-gray-900 mb-3">Current Status</h3>
                            ${healthData.length > 0 ? `
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600">CPU Usage</span>
                                        <div class="flex items-center">
                                            <div class="w-20 bg-gray-200 rounded-full h-2 mr-2">
                                                <div class="bg-blue-600 h-2 rounded-full" style="width: ${Math.min(healthData[0].cpu_usage || 0, 100)}%"></div>
                                            </div>
                                            <span class="text-sm font-medium">${(healthData[0].cpu_usage || 0).toFixed(1)}%</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600">Memory Usage</span>
                                        <div class="flex items-center">
                                            <div class="w-20 bg-gray-200 rounded-full h-2 mr-2">
                                                <div class="bg-green-600 h-2 rounded-full" style="width: ${Math.min(healthData[0].memory_percent || 0, 100)}%"></div>
                                            </div>
                                            <span class="text-sm font-medium">${(healthData[0].memory_percent || 0).toFixed(1)}%</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600">Disk Usage</span>
                                        <div class="flex items-center">
                                            <div class="w-20 bg-gray-200 rounded-full h-2 mr-2">
                                                <div class="bg-orange-600 h-2 rounded-full" style="width: ${Math.min(healthData[0].disk_percent || 0, 100)}%"></div>
                                            </div>
                                            <span class="text-sm font-medium">${(healthData[0].disk_percent || 0).toFixed(1)}%</span>
                                        </div>
                                    </div>
                                </div>
                            ` : '<p class="text-sm text-gray-500">No recent data available</p>'}
                        </div>
                        
                        <!-- Recent Alerts -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="font-medium text-gray-900 mb-3">Recent Alerts</h3>
                            ${alerts.length > 0 ? `
                                <div class="space-y-2">
                                    ${alerts.map(alert => `
                                        <div class="text-sm p-2 rounded ${alert.severity === 'critical' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'}">
                                            <div class="font-medium">${this.escapeHtml(alert.alert_type)}</div>
                                            <div class="text-xs">${new Date(alert.timestamp).toLocaleString()}</div>
                                        </div>
                                    `).join('')}
                                </div>
                            ` : '<p class="text-sm text-gray-500">No recent alerts</p>'}
                        </div>
                        
                        <!-- System Information -->
                        ${systemInfo ? `
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="font-medium text-gray-900 mb-3">System Information</h3>
                            <dl class="space-y-2 text-sm">
                                ${systemInfo.os ? `
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">OS:</dt>
                                    <dd class="font-medium">${this.escapeHtml(systemInfo.os.distro || systemInfo.os.platform)}</dd>
                                </div>` : ''}
                                ${systemInfo.cpu ? `
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">CPU:</dt>
                                    <dd class="font-medium">${this.escapeHtml(systemInfo.cpu.brand)}</dd>
                                </div>` : ''}
                                ${systemInfo.memory ? `
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Memory:</dt>
                                    <dd class="font-medium">${this.formatBytes(systemInfo.memory.total)}</dd>
                                </div>` : ''}
                            </dl>
                        </div>
                        ` : ''}
                    </div>
                `;
            }

            closeModal() {
                document.getElementById('computerModal').classList.add('hidden');
            }

            updateStats() {
                const total = this.computers.length;
                const online = this.computers.filter(c => c.status === 'online').length;
                const warning = this.computers.filter(c => c.status === 'warning').length;
                const critical = this.computers.filter(c => c.status === 'critical').length;
                
                document.getElementById('totalComputers').textContent = total;
                document.getElementById('onlineComputers').textContent = online;
                document.getElementById('warningComputers').textContent = warning;
                document.getElementById('criticalComputers').textContent = critical;
            }

            switchTab(tabName) {
                // Update tab buttons
                document.querySelectorAll('.tab-btn').forEach(btn => {
                    if (btn.dataset.tab === tabName) {
                        btn.classList.add('active', 'border-blue-500', 'text-blue-600');
                        btn.classList.remove('border-transparent', 'text-gray-500');
                    } else {
                        btn.classList.remove('active', 'border-blue-500', 'text-blue-600');
                        btn.classList.add('border-transparent', 'text-gray-500');
                    }
                });
                
                // Update tab content
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.add('hidden');
                    content.classList.remove('active');
                });
                
                const activeTab = document.getElementById(tabName + 'Tab');
                if (activeTab) {
                    activeTab.classList.remove('hidden');
                    activeTab.classList.add('active');
                    
                    // Load tab-specific content
                    if (tabName === 'overview') {
                        this.updateCharts();
                    }
                }
            }

            filterComputers() {
                const statusFilter = document.getElementById('statusFilter').value;
                const searchTerm = document.getElementById('searchComputers').value.toLowerCase();
                
                const filteredComputers = this.computers.filter(computer => {
                    const matchesStatus = !statusFilter || computer.status === statusFilter;
                    const matchesSearch = !searchTerm || 
                        computer.computer_name.toLowerCase().includes(searchTerm) ||
                        (computer.hostname && computer.hostname.toLowerCase().includes(searchTerm));
                    
                    return matchesStatus && matchesSearch;
                });
                
                // Temporarily update computers array for rendering
                const originalComputers = this.computers;
                this.computers = filteredComputers;
                this.renderComputers();
                this.computers = originalComputers;
            }

            filterAlerts() {
                const severityFilter = document.getElementById('severityFilter').value;
                
                const filteredAlerts = this.alerts.filter(alert => {
                    return !severityFilter || alert.severity === severityFilter;
                });
                
                // Temporarily update alerts array for rendering
                const originalAlerts = this.alerts;
                this.alerts = filteredAlerts;
                this.renderAlerts();
                this.alerts = originalAlerts;
            }

            updateCharts() {
                // Placeholder for chart implementation
                // In a real implementation, you would create charts here
            }

            startAutoRefresh() {
                this.refreshInterval = setInterval(() => {
                    this.loadData();
                }, 30000); // Refresh every 30 seconds
            }

            stopAutoRefresh() {
                if (this.refreshInterval) {
                    clearInterval(this.refreshInterval);
                    this.refreshInterval = null;
                }
            }

            getStatusIcon(status) {
                const icons = {
                    'online': 'fas fa-circle',
                    'offline': 'fas fa-circle',
                    'warning': 'fas fa-exclamation-triangle',
                    'critical': 'fas fa-exclamation-circle'
                };
                return icons[status] || 'fas fa-circle';
            }

            getStatusColor(status) {
                const colors = {
                    'online': 'text-green-500',
                    'offline': 'text-gray-400',
                    'warning': 'text-yellow-500',
                    'critical': 'text-red-500'
                };
                return colors[status] || 'text-gray-400';
            }

            formatBytes(bytes) {
                if (!bytes) return 'N/A';
                const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
                const i = Math.floor(Math.log(bytes) / Math.log(1024));
                return parseFloat((bytes / Math.pow(1024, i)).toFixed(1)) + ' ' + sizes[i];
            }

            escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            showNotification(message, type = 'info') {
                // Simple notification - could be enhanced with a proper notification system
                const color = type === 'error' ? 'red' : type === 'success' ? 'green' : 'blue';
                const notification = document.createElement('div');
                notification.className = `fixed top-4 right-4 bg-${color}-500 text-white px-4 py-2 rounded-lg shadow-lg z-50`;
                notification.textContent = message;
                document.body.appendChild(notification);
                
                setTimeout(() => {
                    notification.remove();
                }, 3000);
            }

            setupRealTimeNotifications() {
                if (typeof(EventSource) !== "undefined") {
                    this.eventSource = new EventSource('../../api/health/notifications.php?last_id=' + this.lastAlertId);
                    
                    this.eventSource.addEventListener('alert', (e) => {
                        const alert = JSON.parse(e.data);
                        this.handleNewAlert(alert);
                    });
                    
                    this.eventSource.addEventListener('activity', (e) => {
                        const activity = JSON.parse(e.data);
                        this.handleActivity(activity);
                    });
                    
                    this.eventSource.addEventListener('heartbeat', (e) => {
                        // Update connection status
                        this.updateConnectionStatus(true);
                    });
                    
                    this.eventSource.addEventListener('error', (e) => {
                        console.error('SSE Error:', e);
                        this.updateConnectionStatus(false);
                        
                        // Retry connection after 10 seconds
                        setTimeout(() => {
                            if (this.eventSource.readyState === EventSource.CLOSED) {
                                this.setupRealTimeNotifications();
                            }
                        }, 10000);
                    });
                    
                    this.eventSource.onerror = (e) => {
                        console.error('EventSource failed:', e);
                        this.updateConnectionStatus(false);
                    };
                } else {
                    console.warn('EventSource not supported, falling back to polling');
                }
            }

            handleNewAlert(alert) {
                console.log('New alert received:', alert);
                
                // Update last alert ID
                this.lastAlertId = Math.max(this.lastAlertId, alert.id);
                
                // Add to alerts array
                this.alerts.unshift(alert);
                
                // Show notification
                this.showRealtimeNotification(alert);
                
                // Update alerts display if on alerts tab
                if (document.getElementById('alertsTab').classList.contains('bg-blue-100')) {
                    this.renderAlerts();
                }
                
                // Update computer status if needed
                this.updateComputerStatus(alert.computer_name, alert.severity);
                
                // Play notification sound for critical alerts
                if (alert.severity === 'critical') {
                    this.playNotificationSound();
                }
            }

            handleActivity(activity) {
                // Update computer statuses based on recent activity
                for (const computer of activity) {
                    const existingComputer = this.computers.find(c => c.computer_name === computer.computer_name);
                    if (existingComputer) {
                        existingComputer.status = computer.status;
                        existingComputer.last_seen = computer.last_seen;
                    }
                }
                
                // Refresh computers display
                if (document.getElementById('computersTab').classList.contains('bg-blue-100')) {
                    this.renderComputers();
                }
            }

            showRealtimeNotification(alert) {
                const container = document.getElementById('notificationContainer') || this.createNotificationContainer();
                
                const notification = document.createElement('div');
                notification.className = `fixed top-4 right-4 max-w-sm w-full bg-white rounded-lg shadow-lg border-l-4 p-4 mb-4 z-50 transform translate-x-full transition-transform duration-300 ${
                    alert.severity === 'critical' ? 'border-red-500' : 'border-yellow-500'
                }`;
                
                const severityColor = alert.severity === 'critical' ? 'text-red-600' : 'text-yellow-600';
                const severityIcon = alert.severity === 'critical' ? '🚨' : '⚠️';
                
                notification.innerHTML = `
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <span class="text-lg">${severityIcon}</span>
                        </div>
                        <div class="ml-3 flex-1">
                            <h3 class="text-sm font-medium text-gray-900">
                                ${alert.computer_name} - ${alert.alert_type}
                            </h3>
                            <div class="mt-1 text-sm text-gray-500">
                                ${alert.message}
                            </div>
                            <div class="mt-2 text-xs ${severityColor} font-semibold">
                                ${alert.severity.toUpperCase()} • ${new Date(alert.timestamp).toLocaleTimeString()}
                            </div>
                        </div>
                        <div class="ml-4 flex-shrink-0">
                            <button class="text-gray-400 hover:text-gray-600" onclick="this.parentElement.parentElement.parentElement.remove()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                `;
                
                container.appendChild(notification);
                
                // Animate in
                setTimeout(() => {
                    notification.classList.remove('translate-x-full');
                }, 100);
                
                // Auto remove after 10 seconds for critical, 5 seconds for warning
                const autoRemoveDelay = alert.severity === 'critical' ? 10000 : 5000;
                setTimeout(() => {
                    notification.classList.add('translate-x-full');
                    setTimeout(() => {
                        if (notification.parentNode) {
                            notification.remove();
                        }
                    }, 300);
                }, autoRemoveDelay);
            }

            createNotificationContainer() {
                const container = document.createElement('div');
                container.id = 'notificationContainer';
                container.className = 'fixed top-4 right-4 z-50 max-w-sm w-full pointer-events-none';
                container.style.pointerEvents = 'none';
                
                // Allow interactions with child elements
                container.addEventListener('click', (e) => {
                    e.stopPropagation();
                });
                
                document.body.appendChild(container);
                return container;
            }

            updateComputerStatus(computerName, severity) {
                const computer = this.computers.find(c => c.computer_name === computerName);
                if (computer && severity === 'critical') {
                    computer.status = 'critical';
                    
                    // Update the computer card if visible
                    const computerCard = document.querySelector(`[data-computer="${computerName}"]`);
                    if (computerCard) {
                        const statusBadge = computerCard.querySelector('.status-badge');
                        if (statusBadge) {
                            statusBadge.className = 'px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 status-badge';
                            statusBadge.textContent = 'Critical';
                        }
                        
                        computerCard.className = computerCard.className.replace(/\b(online|offline|warning|critical)\b/g, '') + ' critical';
                    }
                }
            }

            playNotificationSound() {
                // Create and play notification sound for critical alerts
                try {
                    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                    const oscillator = audioContext.createOscillator();
                    const gainNode = audioContext.createGain();
                    
                    oscillator.connect(gainNode);
                    gainNode.connect(audioContext.destination);
                    
                    oscillator.frequency.value = 800;
                    oscillator.type = 'sine';
                    
                    gainNode.gain.setValueAtTime(0, audioContext.currentTime);
                    gainNode.gain.linearRampToValueAtTime(0.1, audioContext.currentTime + 0.01);
                    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
                    
                    oscillator.start(audioContext.currentTime);
                    oscillator.stop(audioContext.currentTime + 0.3);
                } catch (e) {
                    console.warn('Could not play notification sound:', e);
                }
            }

            updateConnectionStatus(connected) {
                const statusIndicator = document.getElementById('connectionStatus') || this.createConnectionStatus();
                
                if (connected) {
                    statusIndicator.className = 'flex items-center text-green-600 text-sm';
                    statusIndicator.innerHTML = '<div class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></div>Live';
                } else {
                    statusIndicator.className = 'flex items-center text-red-600 text-sm';
                    statusIndicator.innerHTML = '<div class="w-2 h-2 bg-red-500 rounded-full mr-2"></div>Disconnected';
                }
            }

            createConnectionStatus() {
                const statusDiv = document.createElement('div');
                statusDiv.id = 'connectionStatus';
                statusDiv.className = 'flex items-center text-gray-600 text-sm';
                statusDiv.innerHTML = '<div class="w-2 h-2 bg-gray-400 rounded-full mr-2"></div>Connecting...';
                
                // Add to header
                const header = document.querySelector('.bg-white.shadow-sm');
                if (header) {
                    const container = document.createElement('div');
                    container.className = 'absolute top-4 right-4';
                    container.appendChild(statusDiv);
                    header.appendChild(container);
                }
                
                return statusDiv;
            }

            stopRealTimeNotifications() {
                if (this.eventSource) {
                    this.eventSource.close();
                    this.eventSource = null;
                }
            }
        }

        // Initialize dashboard when page loads
        let healthDashboard;
        document.addEventListener('DOMContentLoaded', () => {
            healthDashboard = new HealthDashboard();
        });

        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            if (healthDashboard) {
                healthDashboard.stopAutoRefresh();
                healthDashboard.stopRealTimeNotifications();
            }
        });
    </script>
</body>
</html>