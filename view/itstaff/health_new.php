<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_type'] !== 'itstaff') { header('Location: ../index.php'); exit(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Monitoring - Lab Management System</title>
    <link href="../../css/output.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-50">
    <div class="flex dashboard-layout">
        <?php include '../components/sidebar.php'; ?>
        <div class="flex flex-col flex-1 main-content-area">
            <?php include '../components/header.php'; ?>
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
                <div class="max-w-7xl mx-auto">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold text-gray-900">System Health Monitoring</h1>
                        <div class="flex space-x-3">
                            <button onclick="refreshData()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Refresh
                            </button>
                            <span id="lastUpdate" class="text-sm text-gray-500 self-center">Last updated: --</span>
                        </div>
                    </div>

                    <!-- API Test Section -->
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">
                                    Health Monitoring System
                                </h3>
                                <div class="mt-2 text-sm text-yellow-700">
                                    <p>Make sure to set up the health monitoring database tables first. Then test the API connectivity below.</p>
                                </div>
                                <div class="mt-4">
                                    <div class="-mx-2 -my-1.5 flex">
                                        <button onclick="testAPI()" class="bg-yellow-50 px-2 py-1.5 rounded-md text-sm font-medium text-yellow-800 hover:bg-yellow-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-yellow-50 focus:ring-yellow-600">
                                            Test API Connection
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Overview Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="p-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm0 4a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1V8zm8 0a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V8zm0 4a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1v-2z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-gray-500 truncate">Total Computers</dt>
                                            <dd class="text-lg font-medium text-gray-900" id="totalComputers">--</dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="p-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-gray-500 truncate">Online</dt>
                                            <dd class="text-lg font-medium text-gray-900" id="onlineComputers">--</dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="p-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-gray-500 truncate">Active Alerts</dt>
                                            <dd class="text-lg font-medium text-gray-900" id="activeAlerts">--</dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="p-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-gray-500 truncate">Avg CPU Usage</dt>
                                            <dd class="text-lg font-medium text-gray-900" id="avgCpuUsage">--%</dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Computer Status Grid -->
                    <div class="bg-white shadow rounded-lg mb-6">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Computer Status</h3>
                            <div id="computersGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div class="text-center py-8 text-gray-500">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    <p class="mt-2">No computer data available</p>
                                    <p class="text-sm">Set up the database and start monitoring agents</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Alerts -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Recent Alerts</h3>
                            <div id="alertsList" class="space-y-3">
                                <p class="text-gray-500">No recent alerts</p>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        let healthData = null;

        async function testAPI() {
            try {
                const response = await fetch('../../api/health.php?action=status');
                const result = await response.json();
                
                if (result.success) {
                    alert('✓ API Connection Successful!\n\nResponse: ' + JSON.stringify(result.data, null, 2));
                    fetchHealthData(); // Load actual data
                } else {
                    alert('✗ API Error: ' + result.error);
                }
            } catch (error) {
                alert('✗ Connection Failed: ' + error.message + '\n\nMake sure:\n1. XAMPP is running\n2. Database tables are created\n3. API endpoint exists');
            }
        }

        async function fetchHealthData() {
            try {
                const response = await fetch('../../api/health.php?action=status');
                const result = await response.json();
                
                if (result.success) {
                    healthData = result.data;
                    updateDashboard();
                    document.getElementById('lastUpdate').textContent = 'Last updated: ' + new Date().toLocaleTimeString();
                } else {
                    console.error('API Error:', result.error);
                    showError('Failed to fetch health data: ' + result.error);
                }
            } catch (error) {
                console.error('Fetch error:', error);
                showError('Unable to connect to health monitoring API');
            }
        }

        function updateDashboard() {
            if (!healthData) return;

            // Update overview cards
            document.getElementById('totalComputers').textContent = healthData.summary.total_computers;
            document.getElementById('onlineComputers').textContent = healthData.summary.online_computers;
            document.getElementById('activeAlerts').textContent = healthData.summary.active_alerts;
            
            // Calculate average CPU usage
            const onlineComputers = healthData.computers.filter(c => c.status === 'online' && c.cpu_usage);
            const avgCpu = onlineComputers.length > 0 
                ? (onlineComputers.reduce((sum, c) => sum + parseFloat(c.cpu_usage || 0), 0) / onlineComputers.length).toFixed(1)
                : 0;
            document.getElementById('avgCpuUsage').textContent = avgCpu + '%';

            // Update computers grid
            updateComputersGrid();
            
            // Update alerts list
            updateAlertsList();
        }

        function updateComputersGrid() {
            const grid = document.getElementById('computersGrid');
            grid.innerHTML = '';

            if (healthData.computers.length === 0) {
                grid.innerHTML = `
                    <div class="col-span-full text-center py-8 text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <p class="mt-2">No computers registered</p>
                        <p class="text-sm">Start monitoring agents to see data</p>
                    </div>
                `;
                return;
            }

            healthData.computers.forEach(computer => {
                const card = createComputerCard(computer);
                grid.appendChild(card);
            });
        }

        function createComputerCard(computer) {
            const div = document.createElement('div');
            div.className = 'border rounded-lg p-4 ' + getStatusBorderClass(computer.status);
            
            const statusColor = getStatusColor(computer.status);
            const cpuUsage = computer.cpu_usage ? parseFloat(computer.cpu_usage).toFixed(1) + '%' : 'N/A';
            const memoryUsage = computer.memory_usage_percent ? parseFloat(computer.memory_usage_percent).toFixed(1) + '%' : 'N/A';
            const diskUsage = computer.disk_usage_percent ? parseFloat(computer.disk_usage_percent).toFixed(1) + '%' : 'N/A';
            
            div.innerHTML = `
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h4 class="font-medium text-gray-900">${computer.computer_name}</h4>
                        <p class="text-sm text-gray-500">${computer.lab_room || 'Unknown Room'}</p>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusColor}">
                        ${computer.status}
                    </span>
                </div>
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">CPU:</span>
                        <span class="font-medium">${cpuUsage}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Memory:</span>
                        <span class="font-medium">${memoryUsage}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Disk:</span>
                        <span class="font-medium">${diskUsage}</span>
                    </div>
                    <div class="text-xs text-gray-400">
                        Last seen: ${computer.last_seen ? new Date(computer.last_seen).toLocaleString() : 'Never'}
                    </div>
                </div>
            `;
            
            return div;
        }

        function updateAlertsList() {
            const alertsList = document.getElementById('alertsList');
            alertsList.innerHTML = '';

            if (healthData.alerts.length === 0) {
                alertsList.innerHTML = '<p class="text-gray-500">No recent alerts</p>';
                return;
            }

            healthData.alerts.slice(0, 10).forEach(alert => {
                const alertDiv = createAlertItem(alert);
                alertsList.appendChild(alertDiv);
            });
        }

        function createAlertItem(alert) {
            const div = document.createElement('div');
            div.className = 'flex items-center p-3 border rounded-lg ' + getAlertBorderClass(alert.severity);
            
            const severityColor = getSeverityColor(alert.severity);
            
            div.innerHTML = `
                <div class="flex-shrink-0">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${severityColor}">
                        ${alert.severity}
                    </span>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium text-gray-900">${alert.computer_name}</p>
                    <p class="text-sm text-gray-500">${alert.message}</p>
                </div>
                <div class="text-xs text-gray-400">
                    ${new Date(alert.created_at).toLocaleString()}
                </div>
            `;
            
            return div;
        }

        function getStatusColor(status) {
            switch (status) {
                case 'online': return 'bg-green-100 text-green-800';
                case 'offline': return 'bg-red-100 text-red-800';
                case 'maintenance': return 'bg-yellow-100 text-yellow-800';
                default: return 'bg-gray-100 text-gray-800';
            }
        }

        function getStatusBorderClass(status) {
            switch (status) {
                case 'online': return 'border-green-200';
                case 'offline': return 'border-red-200';
                case 'maintenance': return 'border-yellow-200';
                default: return 'border-gray-200';
            }
        }

        function getSeverityColor(severity) {
            switch (severity) {
                case 'critical': return 'bg-red-100 text-red-800';
                case 'warning': return 'bg-yellow-100 text-yellow-800';
                case 'info': return 'bg-blue-100 text-blue-800';
                default: return 'bg-gray-100 text-gray-800';
            }
        }

        function getAlertBorderClass(severity) {
            switch (severity) {
                case 'critical': return 'border-red-200';
                case 'warning': return 'border-yellow-200';
                case 'info': return 'border-blue-200';
                default: return 'border-gray-200';
            }
        }

        function showError(message) {
            console.error(message);
            // Could implement a proper notification system here
        }

        function refreshData() {
            fetchHealthData();
        }

        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-refresh every 30 seconds
            setInterval(fetchHealthData, 30000);
        });
    </script>
</body>
</html>