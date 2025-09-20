<?php
/**
 * Student Lab Sessions Page - Lab Management System
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is a student
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_type'] !== 'student') {
    header('Location: ../index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Sessions - Lab Management System</title>
    <link href="../../css/output.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="flex dashboard-layout">
        <!-- Sidebar -->
        <?php include '../components/sidebar.php'; ?>

        <!-- Main content area -->
        <div class="flex flex-col flex-1 main-content-area">
            <!-- Header -->
            <?php include '../components/header.php'; ?>

            <!-- Main content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
                <div class="max-w-7xl mx-auto">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold text-gray-900">Lab Sessions</h1>
                        <button id="joinSessionBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                            Join Session
                        </button>
                    </div>
                    
                    <!-- My Enrolled Sessions -->
                    <div class="bg-white shadow rounded-lg mb-6">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-medium text-gray-900">My Sessions</h2>
                        </div>
                        <div class="p-6">
                            <div id="mySessionsContainer" class="space-y-4">
                                <!-- Sessions will be loaded here -->
                                <div class="text-center text-gray-500 py-8">
                                    <p>No enrolled sessions found.</p>
                                    <p class="text-sm">Click "Join Session" to enroll in available lab sessions.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Session History -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-medium text-gray-900">Session History</h2>
                        </div>
                        <div class="p-6">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Session</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Professor</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-in Time</th>
                                        </tr>
                                    </thead>
                                    <tbody id="sessionHistoryBody" class="bg-white divide-y divide-gray-200">
                                        <!-- History will be loaded here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Join Session Modal -->
    <div id="joinSessionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Join Lab Session</h3>
                    <button id="closeJoinModalBtn" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label for="selectLabRoom" class="block text-sm font-medium text-gray-700">Select Laboratory Room</label>
                        <select id="selectLabRoom" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Choose a lab room</option>
                            <option value="1">LAB-101 - Computer Laboratory 1</option>
                            <option value="2">LAB-102 - Computer Laboratory 2</option>
                            <option value="3">LAB-201 - Advanced Computing Lab</option>
                            <option value="4">LAB-301 - Research Laboratory</option>
                        </select>
                    </div>

                    <div id="availableSessionsContainer" class="hidden">
                        <h4 class="text-md font-medium text-gray-900 mb-3">Available Sessions</h4>
                        <div id="availableSessionsList" class="space-y-3 max-h-96 overflow-y-auto">
                            <!-- Available sessions will be loaded here -->
                        </div>
                    </div>

                    <div id="noSessionsMessage" class="hidden text-center text-gray-500 py-4">
                        <p>No active sessions in this room.</p>
                        <p class="text-sm">Sessions become available when they start automatically at their scheduled time.</p>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" id="cancelJoinBtn" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 border border-gray-300 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for functionality -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebarClose = document.getElementById('sidebarClose');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const mainContent = document.querySelector('.main-content-area');
            
            // Modal elements
            const joinSessionBtn = document.getElementById('joinSessionBtn');
            const joinSessionModal = document.getElementById('joinSessionModal');
            const closeJoinModalBtn = document.getElementById('closeJoinModalBtn');
            const cancelJoinBtn = document.getElementById('cancelJoinBtn');
            const selectLabRoom = document.getElementById('selectLabRoom');
            const availableSessionsContainer = document.getElementById('availableSessionsContainer');
            const availableSessionsList = document.getElementById('availableSessionsList');
            const noSessionsMessage = document.getElementById('noSessionsMessage');
            
            let sidebarOpen = false;

            function initializeSidebar() {
                if (window.innerWidth >= 1024) {
                    sidebar.classList.remove('-translate-x-full');
                    sidebarOverlay.classList.add('hidden');
                    mainContent.classList.add('sidebar-open');
                    mainContent.classList.remove('sidebar-closed');
                    sidebarOpen = true;
                } else {
                    sidebar.classList.add('-translate-x-full');
                    sidebarOverlay.classList.add('hidden');
                    mainContent.classList.remove('sidebar-open', 'sidebar-closed');
                    sidebarOpen = false;
                }
            }

            function toggleSidebar() {
                if (window.innerWidth >= 1024) {
                    if (sidebarOpen) {
                        sidebar.classList.add('-translate-x-full');
                        mainContent.classList.remove('sidebar-open');
                        mainContent.classList.add('sidebar-closed');
                        sidebarOpen = false;
                    } else {
                        sidebar.classList.remove('-translate-x-full');
                        mainContent.classList.remove('sidebar-closed');
                        mainContent.classList.add('sidebar-open');
                        sidebarOpen = true;
                    }
                    sidebarOverlay.classList.add('hidden');
                } else {
                    sidebar.classList.toggle('-translate-x-full');
                    sidebarOverlay.classList.toggle('hidden');
                }
            }

            function closeSidebar() {
                sidebar.classList.add('-translate-x-full');
                sidebarOverlay.classList.add('hidden');
                if (window.innerWidth >= 1024) {
                    mainContent.classList.remove('sidebar-open');
                    mainContent.classList.add('sidebar-closed');
                    sidebarOpen = false;
                }
            }

            // Modal functions
            function openJoinModal() {
                joinSessionModal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }

            function closeJoinModal() {
                joinSessionModal.classList.add('hidden');
                document.body.style.overflow = 'auto';
                selectLabRoom.value = '';
                availableSessionsContainer.classList.add('hidden');
                noSessionsMessage.classList.add('hidden');
                availableSessionsList.innerHTML = '';
            }

            // Load available sessions for selected room
            function loadAvailableSessions(roomId) {
                if (!roomId) {
                    availableSessionsContainer.classList.add('hidden');
                    noSessionsMessage.classList.add('hidden');
                    return;
                }

                const formData = new FormData();
                formData.append('action', 'get_sessions_by_room');
                formData.append('roomId', roomId);

                fetch('../../controller/session_controller.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.sessions.length > 0) {
                        displayAvailableSessions(data.sessions);
                        availableSessionsContainer.classList.remove('hidden');
                        noSessionsMessage.classList.add('hidden');
                    } else {
                        availableSessionsContainer.classList.add('hidden');
                        noSessionsMessage.classList.remove('hidden');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    availableSessionsContainer.classList.add('hidden');
                    noSessionsMessage.classList.remove('hidden');
                });
            }

            // Display available sessions
            function displayAvailableSessions(sessions) {
                availableSessionsList.innerHTML = '';
                
                sessions.forEach(session => {
                    const sessionDate = new Date(session.start_time);
                    const endDate = new Date(session.end_time);
                    const now = new Date();
                    const timeRemaining = endDate - now;
                    const minutesRemaining = Math.floor(timeRemaining / (1000 * 60));
                    const isEnrolled = session.is_enrolled > 0;
                    
                    const sessionCard = document.createElement('div');
                    sessionCard.className = 'border border-gray-200 rounded-lg p-4 hover:bg-gray-50';
                    
                    const statusBadge = minutesRemaining > 30 ? 
                        '<span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded">Active</span>' :
                        `<span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded">Ending in ${minutesRemaining}m</span>`;
                    
                    const joinButton = isEnrolled ? 
                        '<span class="bg-gray-100 text-gray-600 text-sm px-3 py-1 rounded">Already Enrolled</span>' :
                        `<button onclick="joinSession(${session.id})" 
                                class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-3 py-1 rounded">
                            Join Now
                        </button>`;
                    
                    sessionCard.innerHTML = `
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h5 class="font-semibold text-gray-900">${session.session_name}</h5>
                                <p class="text-sm text-gray-600">Professor: ${session.first_name} ${session.last_name}</p>
                                <p class="text-sm text-gray-500">
                                    ${sessionDate.toLocaleDateString()} • ${sessionDate.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})} - ${endDate.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                                </p>
                                <p class="text-xs text-gray-400">
                                    Enrolled: ${session.enrolled_students}/${session.max_students} students
                                </p>
                                <p class="text-xs text-green-600 font-medium">🟢 Session is currently active</p>
                                ${session.description ? `<p class="text-sm text-gray-600 mt-1">${session.description}</p>` : ''}
                            </div>
                            <div class="ml-4 flex flex-col items-end space-y-2">
                                ${statusBadge}
                                ${joinButton}
                            </div>
                        </div>
                    `;
                    
                    availableSessionsList.appendChild(sessionCard);
                });
            }

            // Join session function
            window.joinSession = function(sessionId) {
                const formData = new FormData();
                formData.append('action', 'join');
                formData.append('sessionId', sessionId);

                fetch('../../controller/session_controller.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let message = 'Successfully joined session!';
                        if (data.status === 'late') {
                            message += ' (Marked as late - you joined after the 15-minute grace period)';
                        }
                        alert(message);
                        closeJoinModal();
                        loadMySessions(); // Reload sessions
                        loadSessionHistory(); // Reload history
                    } else {
                        alert('Error: ' + (data.message || 'Failed to join session'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while joining the session');
                });
            };

            // Load user's enrolled sessions
            function loadMySessions() {
                fetch('../../controller/session_controller.php?action=get_student_sessions')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayMySessions(data.sessions);
                    } else {
                        console.error('Error loading sessions:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }

            // Display user's enrolled sessions
            function displayMySessions(sessions) {
                const container = document.getElementById('mySessionsContainer');
                
                if (sessions.length === 0) {
                    container.innerHTML = `
                        <div class="text-center text-gray-500 py-8">
                            <p>No enrolled sessions found.</p>
                            <p class="text-sm">Click "Join Session" to enroll in available lab sessions.</p>
                        </div>
                    `;
                    return;
                }

                container.innerHTML = '';
                
                sessions.forEach(session => {
                    const sessionDate = new Date(session.start_time);
                    const endDate = new Date(session.end_time);
                    const now = new Date();
                    const isOngoing = now >= sessionDate && now <= endDate;
                    const isUpcoming = sessionDate > now;
                    
                    let statusInfo = '';
                    let borderColor = 'border-blue-400';
                    let bgColor = 'bg-blue-50';
                    
                    if (isOngoing) {
                        statusInfo = '<span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded">Ongoing</span>';
                        borderColor = 'border-green-400';
                        bgColor = 'bg-green-50';
                    } else if (isUpcoming) {
                        const timeUntilStart = sessionDate - now;
                        const hoursUntilStart = Math.floor(timeUntilStart / (1000 * 60 * 60));
                        const minutesUntilStart = Math.floor((timeUntilStart % (1000 * 60 * 60)) / (1000 * 60));
                        
                        if (hoursUntilStart < 1 && minutesUntilStart <= 15) {
                            statusInfo = '<span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded">Starting Soon</span>';
                            borderColor = 'border-yellow-400';
                            bgColor = 'bg-yellow-50';
                        } else {
                            statusInfo = `<span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">
                                ${hoursUntilStart > 0 ? `${hoursUntilStart}h ` : ''}${minutesUntilStart}m until start
                            </span>`;
                        }
                    }
                    
                    const attendanceStatus = session.attendance_status || 'enrolled';
                    const attendanceBadge = {
                        'present': '<span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded">Present</span>',
                        'late': '<span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded">Late</span>',
                        'absent': '<span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded">Absent</span>'
                    }[attendanceStatus] || '<span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded">Enrolled</span>';

                    const sessionCard = document.createElement('div');
                    sessionCard.className = `border-l-4 ${borderColor} pl-4 py-3 ${bgColor} rounded-r`;
                    
                    sessionCard.innerHTML = `
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900">${session.session_name}</h3>
                                <p class="text-gray-600 text-sm">Professor: ${session.first_name} ${session.last_name}</p>
                                <p class="text-gray-500 text-xs mt-1">
                                    Room: ${session.room_number} • 
                                    ${sessionDate.toLocaleDateString()} • 
                                    ${sessionDate.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})} - 
                                    ${endDate.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                                </p>
                                <p class="text-xs text-gray-400 mt-1">
                                    Total Enrolled: ${session.total_enrolled}/${session.max_students} students
                                </p>
                            </div>
                            <div class="flex flex-col items-end space-y-1">
                                ${statusInfo}
                                ${attendanceBadge}
                            </div>
                        </div>
                    `;
                    
                    container.appendChild(sessionCard);
                });
            }

            // Load session history
            function loadSessionHistory() {
                fetch('../../controller/session_controller.php?action=get_student_history')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displaySessionHistory(data.history);
                    } else {
                        console.error('Error loading session history:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }

            // Display session history
            function displaySessionHistory(history) {
                const tbody = document.getElementById('sessionHistoryBody');
                
                if (history.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                No session history found.
                            </td>
                        </tr>
                    `;
                    return;
                }

                tbody.innerHTML = '';
                
                history.forEach(session => {
                    const sessionDate = new Date(session.start_time);
                    const endDate = new Date(session.end_time);
                    const checkInTime = session.check_in_time ? new Date(session.check_in_time) : null;
                    
                    const attendanceClass = {
                        'present': 'text-green-600',
                        'late': 'text-yellow-600',
                        'absent': 'text-red-600'
                    }[session.attendance_status] || 'text-gray-600';

                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ${sessionDate.toLocaleDateString()}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ${session.session_name}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            ${session.room_number}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            ${session.first_name} ${session.last_name}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium ${attendanceClass}">
                            ${session.attendance_status.charAt(0).toUpperCase() + session.attendance_status.slice(1)}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            ${checkInTime ? checkInTime.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) : 'N/A'}
                        </td>
                    `;
                    
                    tbody.appendChild(row);
                });
            }

            // Event listeners
            initializeSidebar();
            sidebarToggle?.addEventListener('click', toggleSidebar);
            sidebarClose?.addEventListener('click', closeSidebar);
            sidebarOverlay?.addEventListener('click', closeSidebar);

            // Modal event listeners
            joinSessionBtn?.addEventListener('click', openJoinModal);
            closeJoinModalBtn?.addEventListener('click', closeJoinModal);
            cancelJoinBtn?.addEventListener('click', closeJoinModal);
            selectLabRoom?.addEventListener('change', (e) => loadAvailableSessions(e.target.value));

            // Close modal on outside click
            joinSessionModal?.addEventListener('click', function(e) {
                if (e.target === joinSessionModal) {
                    closeJoinModal();
                }
            });

            // Close modal on Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && !joinSessionModal.classList.contains('hidden')) {
                    closeJoinModal();
                }
            });

            document.addEventListener('click', function(event) {
                if (window.innerWidth < 1024) {
                    if (!sidebar.contains(event.target) && !sidebarToggle?.contains(event.target)) {
                        closeSidebar();
                    }
                }
            });

            window.addEventListener('resize', function() {
                initializeSidebar();
            });

            // Load initial data
            loadMySessions();
            loadSessionHistory();
        });
    </script>
</body>
</html>