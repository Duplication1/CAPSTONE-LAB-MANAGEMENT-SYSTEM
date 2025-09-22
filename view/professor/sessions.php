<?php
/**
 * Professor Lab Sessions Page - Lab Management System
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is a professor
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_type'] !== 'professor') {
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
                        <button id="scheduleSessionBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                            Schedule New Session
                        </button>
                    </div>
                    
                    <!-- Active Sessions -->
                    <div class="bg-white shadow rounded-lg mb-6">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-medium text-gray-900">Today's Sessions</h2>
                        </div>
                        <div class="p-6">
                            <div id="todaysSessionsContainer" class="space-y-4">
                                <!-- Today's sessions will be loaded here -->
                                <div class="text-center text-gray-500 py-8">
                                    <p>Loading today's sessions...</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Upcoming Sessions -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-medium text-gray-900">Upcoming Sessions</h2>
                        </div>
                        <div class="p-6">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Students</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="upcomingSessionsBody" class="bg-white divide-y divide-gray-200">
                                        <!-- Upcoming sessions will be loaded here -->
                                        <tr>
                                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                                Loading upcoming sessions...
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Schedule New Session Modal -->
    <div id="scheduleSessionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Schedule New Lab Session</h3>
                    <button id="closeModalBtn" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <form id="scheduleSessionForm" class="space-y-4">
                    <div>
                        <label for="sessionTitle" class="block text-sm font-medium text-gray-700">Session Title</label>
                        <input type="text" id="sessionTitle" name="sessionTitle" required 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="e.g., CS101 Programming Lab">
                    </div>

                    <div>
                        <label for="labRoom" class="block text-sm font-medium text-gray-700">Laboratory Room</label>
                        <select id="labRoom" name="labRoom" required 
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select a lab room</option>
                            <option value="1">LAB-101 - Computer Laboratory 1 (30 seats)</option>
                            <option value="2">LAB-102 - Computer Laboratory 2 (25 seats)</option>
                            <option value="3">LAB-201 - Advanced Computing Lab (20 seats)</option>
                            <option value="4">LAB-301 - Research Laboratory (15 seats)</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="sessionDate" class="block text-sm font-medium text-gray-700">Date</label>
                            <input type="date" id="sessionDate" name="sessionDate" required 
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="sessionTime" class="block text-sm font-medium text-gray-700">Start Time</label>
                            <input type="time" id="sessionTime" name="sessionTime" required 
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div>
                        <label for="duration" class="block text-sm font-medium text-gray-700">Duration (hours)</label>
                        <select id="duration" name="duration" required 
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select duration</option>
                            <option value="1">1 hour</option>
                            <option value="1.5">1.5 hours</option>
                            <option value="2">2 hours</option>
                            <option value="2.5">2.5 hours</option>
                            <option value="3">3 hours</option>
                            <option value="4">4 hours</option>
                        </select>
                    </div>

                    <div>
                        <label for="maxStudents" class="block text-sm font-medium text-gray-700">Maximum Students</label>
                        <input type="number" id="maxStudents" name="maxStudents" min="1" max="30" value="30"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description (Optional)</label>
                        <textarea id="description" name="description" rows="3"
                                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Session description or objectives..."></textarea>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" id="cancelBtn" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 border border-gray-300 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Schedule Session
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript for sidebar toggle and modal functionality -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebarClose = document.getElementById('sidebarClose');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const mainContent = document.querySelector('.main-content-area');
            
            // Modal elements
            const scheduleSessionBtn = document.getElementById('scheduleSessionBtn');
            const scheduleSessionModal = document.getElementById('scheduleSessionModal');
            const closeModalBtn = document.getElementById('closeModalBtn');
            const cancelBtn = document.getElementById('cancelBtn');
            const scheduleSessionForm = document.getElementById('scheduleSessionForm');
            
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
            function openModal() {
                scheduleSessionModal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
                
                // Set minimum date to today
                const today = new Date().toISOString().split('T')[0];
                document.getElementById('sessionDate').setAttribute('min', today);
            }

            function closeModal() {
                scheduleSessionModal.classList.add('hidden');
                document.body.style.overflow = 'auto';
                scheduleSessionForm.reset();
            }

            // Update max students based on selected room
            function updateMaxStudents() {
                const labRoom = document.getElementById('labRoom');
                const maxStudents = document.getElementById('maxStudents');
                const capacities = {
                    '1': 30, // LAB-101
                    '2': 25, // LAB-102
                    '3': 20, // LAB-201
                    '4': 15  // LAB-301
                };
                
                if (labRoom.value && capacities[labRoom.value]) {
                    maxStudents.value = capacities[labRoom.value];
                    maxStudents.setAttribute('max', capacities[labRoom.value]);
                }
            }

            // Form submission
            function handleFormSubmission(e) {
                e.preventDefault();
                
                const formData = new FormData(scheduleSessionForm);
                
                // Show loading state
                const submitBtn = scheduleSessionForm.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                submitBtn.textContent = 'Scheduling...';
                submitBtn.disabled = true;
                
                // Send data to server
                fetch('../../controller/session_controller.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Session scheduled successfully!');
                        closeModal();
                        loadTodaysSessions(); // Reload today's sessions
                        loadUpcomingSessions(); // Reload upcoming sessions
                    } else {
                        alert('Error: ' + (data.message || 'Failed to schedule session'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while scheduling the session');
                })
                .finally(() => {
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                });
            }

            // Load today's sessions
            function loadTodaysSessions() {
                const today = new Date().toISOString().split('T')[0];
                
                fetch(`../../controller/session_controller.php?action=get_professor_sessions&date=${today}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayTodaysSessions(data.sessions);
                    } else {
                        console.error('Error loading today\'s sessions:', data.message);
                        document.getElementById('todaysSessionsContainer').innerHTML = 
                            '<div class="text-center text-gray-500 py-8"><p>Error loading today\'s sessions.</p></div>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('todaysSessionsContainer').innerHTML = 
                        '<div class="text-center text-gray-500 py-8"><p>Error loading today\'s sessions.</p></div>';
                });
            }

            // Display today's sessions
            function displayTodaysSessions(sessions) {
                const container = document.getElementById('todaysSessionsContainer');
                
                if (sessions.length === 0) {
                    container.innerHTML = `
                        <div class="text-center text-gray-500 py-8">
                            <p>No sessions scheduled for today.</p>
                            <p class="text-sm">Click "Schedule New Session" to add a session.</p>
                        </div>
                    `;
                    return;
                }

                container.innerHTML = '';
                
                sessions.forEach(session => {
                    const sessionDate = new Date(session.start_time);
                    const endDate = new Date(session.end_time);
                    const now = new Date();
                    
                    let borderColor = 'border-blue-400';
                    let bgColor = 'bg-blue-50';
                    let statusElement = '';
                    let actionButtons = '';
                    
                    const isOngoing = session.status === 'ongoing';
                    const isPast = session.status === 'completed';
                    const isScheduled = session.status === 'scheduled';
                    const isCancelled = session.status === 'cancelled';
                    
                    if (isOngoing) {
                        borderColor = 'border-green-400';
                        bgColor = 'bg-green-50';
                        statusElement = '<span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Active (Auto-started)</span>';
                        actionButtons = `
                            <button onclick="endSession(${session.id})" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm">
                                End Session
                            </button>
                        `;
                    } else if (isPast) {
                        borderColor = 'border-gray-400';
                        bgColor = 'bg-gray-50';
                        statusElement = '<span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs">Completed</span>';
                        actionButtons = `
                            <button onclick="viewSessionDetails(${session.id})" class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-1 rounded text-sm">
                                View Details
                            </button>
                        `;
                    } else if (isScheduled) {
                        const timeUntilStart = sessionDate - now;
                        if (timeUntilStart > 0) {
                            const minutesUntilStart = Math.floor(timeUntilStart / (1000 * 60));
                            statusElement = `<span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">Starts in ${minutesUntilStart}m (Auto-start)</span>`;
                        } else {
                            statusElement = '<span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">Starting soon...</span>';
                        }
                        actionButtons = `
                            <button onclick="viewSessionDetails(${session.id})" class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-1 rounded text-sm">
                                View Details
                            </button>
                        `;
                    } else if (isCancelled) {
                        borderColor = 'border-red-400';
                        bgColor = 'bg-red-50';
                        statusElement = '<span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">Cancelled</span>';
                        actionButtons = `
                            <button onclick="viewSessionDetails(${session.id})" class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-1 rounded text-sm">
                                View Details
                            </button>
                        `;
                    }

                    const sessionCard = document.createElement('div');
                    sessionCard.className = `border-l-4 ${borderColor} pl-4 py-3 ${bgColor} rounded-r`;
                    
                    sessionCard.innerHTML = `
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900">${session.session_name}</h3>
                                <p class="text-gray-600 text-sm">${session.description || 'No description'}</p>
                                <p class="text-gray-500 text-xs mt-1">
                                    Room: ${session.room_number} • ${sessionDate.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})} - ${endDate.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                                </p>
                                <p class="text-gray-400 text-xs mt-1">
                                    Enrolled: ${session.enrolled_students || 0}/${session.max_students} students
                                </p>
                            </div>
                            <div class="flex space-x-2 items-center">
                                ${statusElement}
                                <div class="flex space-x-2">
                                    ${actionButtons}
                                </div>
                            </div>
                        </div>
                    `;
                    
                    container.appendChild(sessionCard);
                });
            }

            // Load upcoming sessions (future sessions)
            function loadUpcomingSessions() {
                fetch('../../controller/session_controller.php?action=get_professor_sessions')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Filter to show sessions that haven't started yet (including today's future sessions)
                        const now = new Date();
                        const upcomingSessions = data.sessions.filter(session => {
                            const sessionStart = new Date(session.start_time);
                            return sessionStart > now && session.status === 'scheduled';
                        });
                        displayUpcomingSessions(upcomingSessions);
                    } else {
                        console.error('Error loading upcoming sessions:', data.message);
                        document.getElementById('upcomingSessionsBody').innerHTML = 
                            '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">Error loading upcoming sessions.</td></tr>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('upcomingSessionsBody').innerHTML = 
                        '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">Error loading upcoming sessions.</td></tr>';
                });
            }

            // Display upcoming sessions
            function displayUpcomingSessions(sessions) {
                const tbody = document.getElementById('upcomingSessionsBody');
                
                if (sessions.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                No upcoming sessions scheduled.
                            </td>
                        </tr>
                    `;
                    return;
                }

                tbody.innerHTML = '';
                
                sessions.forEach(session => {
                    const sessionDate = new Date(session.start_time);
                    const endDate = new Date(session.end_time);
                    
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
                            ${sessionDate.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})} - ${endDate.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            ${session.enrolled_students || 0}/${session.max_students}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button onclick="editSession(${session.id})" class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                            <button onclick="cancelSession(${session.id})" class="text-red-600 hover:text-red-900">Cancel</button>
                        </td>
                    `;
                    
                    tbody.appendChild(row);
                });
            }

            // Session management functions
            window.endSession = function(sessionId) {
                if (confirm('Are you sure you want to end this session?')) {
                    fetch('../../controller/session_controller.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=update_status&sessionId=${sessionId}&status=completed`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Session ended successfully!');
                            loadTodaysSessions();
                        } else {
                            alert('Error: ' + (data.message || 'Failed to end session'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while ending the session');
                    });
                }
            };

            window.cancelSession = function(sessionId) {
                if (confirm('Are you sure you want to cancel this session? This action cannot be undone.')) {
                    fetch('../../controller/session_controller.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=update_status&sessionId=${sessionId}&status=cancelled`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Session cancelled successfully!');
                            loadTodaysSessions();
                            loadUpcomingSessions();
                        } else {
                            alert('Error: ' + (data.message || 'Failed to cancel session'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while cancelling the session');
                    });
                }
            };

            window.viewSessionDetails = function(sessionId) {
                // Fetch session details
                fetch(`../../controller/session_controller.php?action=get_session_details&sessionId=${sessionId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const session = data.data;
                        displaySessionDetails(session);
                    } else {
                        alert('Error: ' + (data.message || 'Failed to load session details'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while loading session details');
                });
            };

            window.editSession = function(sessionId) {
                // Fetch session details for editing
                fetch(`../../controller/session_controller.php?action=get_session_details&sessionId=${sessionId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const session = data.data;
                        populateEditForm(session);
                        openEditModal();
                    } else {
                        alert('Error: ' + (data.message || 'Failed to load session details'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while loading session details');
                });
            };

            // Modal management functions
            function displaySessionDetails(session) {
                const content = document.getElementById('viewSessionContent');
                const title = document.getElementById('viewModalTitle');
                
                title.textContent = session.session_name;
                
                content.innerHTML = `
                    <div class="space-y-6">
                        <!-- Session Information -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-lg font-semibold text-gray-900 mb-3">Session Information</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Session Name</p>
                                    <p class="text-sm text-gray-900">${session.session_name}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Status</p>
                                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full ${getStatusColor(session.status)}">
                                        ${session.status.charAt(0).toUpperCase() + session.status.slice(1)}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Start Time</p>
                                    <p class="text-sm text-gray-900">${formatDateTime(session.start_time)}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-600">End Time</p>
                                    <p class="text-sm text-gray-900">${formatDateTime(session.end_time)}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Room</p>
                                    <p class="text-sm text-gray-900">${session.room_name}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Capacity</p>
                                    <p class="text-sm text-gray-900">${session.enrolled_students || 0}/${session.max_students} students</p>
                                </div>
                            </div>
                            ${session.description ? `
                                <div class="mt-4">
                                    <p class="text-sm font-medium text-gray-600">Description</p>
                                    <p class="text-sm text-gray-900">${session.description}</p>
                                </div>
                            ` : ''}
                        </div>

                        <!-- Enrolled Students -->
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h4 class="text-lg font-semibold text-gray-900 mb-3">Enrolled Students (${session.enrolled_students || 0})</h4>
                            <div id="enrolledStudentsList" class="space-y-2">
                                <p class="text-sm text-gray-600">Loading student list...</p>
                            </div>
                        </div>
                    </div>
                `;
                
                // Load enrolled students
                loadEnrolledStudents(session.id);
                
                // Show modal
                document.getElementById('viewSessionModal').classList.remove('hidden');
            }

            function loadEnrolledStudents(sessionId) {
                fetch(`../../controller/session_controller.php?action=get_enrolled_students&sessionId=${sessionId}`)
                .then(response => response.json())
                .then(data => {
                    const studentsList = document.getElementById('enrolledStudentsList');
                    if (data.success && data.data.length > 0) {
                        studentsList.innerHTML = data.data.map(student => `
                            <div class="flex justify-between items-center py-2 px-3 bg-white rounded border">
                                <span class="text-sm font-medium">${student.first_name} ${student.last_name}</span>
                                <span class="text-sm text-gray-600">${student.student_id}</span>
                            </div>
                        `).join('');
                    } else {
                        studentsList.innerHTML = '<p class="text-sm text-gray-600">No students enrolled yet.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error loading students:', error);
                    document.getElementById('enrolledStudentsList').innerHTML = 
                        '<p class="text-sm text-red-600">Error loading student list.</p>';
                });
            }

            function populateEditForm(session) {
                document.getElementById('editSessionId').value = session.id;
                document.getElementById('editSessionName').value = session.session_name;
                document.getElementById('editStartTime').value = formatForInput(session.start_time);
                document.getElementById('editEndTime').value = formatForInput(session.end_time);
                document.getElementById('editMaxStudents').value = session.max_students;
                document.getElementById('editDescription').value = session.description || '';
                
                // Load and set room options
                loadRoomsForEdit(session.laboratory_room_id);
            }

            function loadRoomsForEdit(selectedRoomId) {
                fetch('../../controller/session_controller.php?action=get_rooms')
                .then(response => response.json())
                .then(data => {
                    const roomSelect = document.getElementById('editRoom');
                    roomSelect.innerHTML = '<option value="">Select a room</option>';
                    
                    if (data.success) {
                        data.rooms.forEach(room => {
                            const option = document.createElement('option');
                            option.value = room.id;
                            option.textContent = `${room.room_name} (Capacity: ${room.capacity})`;
                            if (room.id == selectedRoomId) {
                                option.selected = true;
                            }
                            roomSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error loading rooms:', error);
                });
            }

            function openEditModal() {
                document.getElementById('editSessionModal').classList.remove('hidden');
            }

            function closeViewModal() {
                document.getElementById('viewSessionModal').classList.add('hidden');
            }

            function closeEditModal() {
                document.getElementById('editSessionModal').classList.add('hidden');
            }

            // Utility functions
            function getStatusColor(status) {
                switch(status) {
                    case 'scheduled': return 'bg-blue-100 text-blue-800';
                    case 'ongoing': return 'bg-green-100 text-green-800';
                    case 'completed': return 'bg-gray-100 text-gray-800';
                    case 'cancelled': return 'bg-red-100 text-red-800';
                    default: return 'bg-gray-100 text-gray-800';
                }
            }

            function formatDateTime(dateTimeString) {
                const date = new Date(dateTimeString);
                return date.toLocaleString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }

            function formatForInput(dateTimeString) {
                const date = new Date(dateTimeString);
                return date.toISOString().slice(0, 16);
            }

            // Handle edit form submission
            function handleEditSubmission(e) {
                e.preventDefault();
                
                const formData = new FormData(e.target);
                const data = Object.fromEntries(formData);
                
                // Add action to the data
                data.action = 'update_session';
                
                // Convert to URL encoded format
                const urlParams = new URLSearchParams(data);
                
                fetch('../../controller/session_controller.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: urlParams.toString()
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Session updated successfully!');
                        closeEditModal();
                        loadTodaysSessions();
                        loadUpcomingSessions();
                    } else {
                        alert('Error: ' + (data.message || 'Failed to update session'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating the session');
                });
            }

            // Event listeners
            initializeSidebar();
            sidebarToggle?.addEventListener('click', toggleSidebar);
            sidebarClose?.addEventListener('click', closeSidebar);
            sidebarOverlay?.addEventListener('click', closeSidebar);

            // Modal event listeners
            scheduleSessionBtn?.addEventListener('click', openModal);
            closeModalBtn?.addEventListener('click', closeModal);
            cancelBtn?.addEventListener('click', closeModal);
            scheduleSessionForm?.addEventListener('submit', handleFormSubmission);
            document.getElementById('labRoom')?.addEventListener('change', updateMaxStudents);

            // View and Edit Modal event listeners
            document.getElementById('closeViewModalBtn')?.addEventListener('click', closeViewModal);
            document.getElementById('closeEditModalBtn')?.addEventListener('click', closeEditModal);
            document.getElementById('cancelEditBtn')?.addEventListener('click', closeEditModal);
            document.getElementById('editSessionForm')?.addEventListener('submit', handleEditSubmission);

            // Close modal on outside click
            scheduleSessionModal?.addEventListener('click', function(e) {
                if (e.target === scheduleSessionModal) {
                    closeModal();
                }
            });

            document.getElementById('viewSessionModal')?.addEventListener('click', function(e) {
                if (e.target === document.getElementById('viewSessionModal')) {
                    closeViewModal();
                }
            });

            document.getElementById('editSessionModal')?.addEventListener('click', function(e) {
                if (e.target === document.getElementById('editSessionModal')) {
                    closeEditModal();
                }
            });

            // Close modal on Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    if (!scheduleSessionModal.classList.contains('hidden')) {
                        closeModal();
                    } else if (!document.getElementById('viewSessionModal').classList.contains('hidden')) {
                        closeViewModal();
                    } else if (!document.getElementById('editSessionModal').classList.contains('hidden')) {
                        closeEditModal();
                    }
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
            loadTodaysSessions();
            loadUpcomingSessions();

            // Auto-refresh sessions every 30 seconds to show real-time status updates
            setInterval(function() {
                loadTodaysSessions();
            }, 30000);
        });
    </script>

    <!-- View Session Details Modal -->
    <div id="viewSessionModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-screen overflow-y-auto">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900" id="viewModalTitle">Session Details</h3>
                        <button id="closeViewModalBtn" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="px-6 py-4" id="viewSessionContent">
                    <!-- Session details will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Session Modal -->
    <div id="editSessionModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-lg shadow-xl max-w-lg w-full">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Edit Session</h3>
                        <button id="closeEditModalBtn" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <form id="editSessionForm" class="px-6 py-4">
                    <input type="hidden" id="editSessionId" name="sessionId">
                    
                    <div class="space-y-4">
                        <div>
                            <label for="editSessionName" class="block text-sm font-medium text-gray-700">Session Name</label>
                            <input type="text" id="editSessionName" name="sessionName" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="editStartTime" class="block text-sm font-medium text-gray-700">Start Time</label>
                                <input type="datetime-local" id="editStartTime" name="startTime" required
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label for="editEndTime" class="block text-sm font-medium text-gray-700">End Time</label>
                                <input type="datetime-local" id="editEndTime" name="endTime" required
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <div>
                            <label for="editRoom" class="block text-sm font-medium text-gray-700">Room</label>
                            <select id="editRoom" name="room" required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select a room</option>
                            </select>
                        </div>

                        <div>
                            <label for="editMaxStudents" class="block text-sm font-medium text-gray-700">Maximum Students</label>
                            <input type="number" id="editMaxStudents" name="maxStudents" min="1" max="30" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="editDescription" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea id="editDescription" name="description" rows="3"
                                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-6">
                        <button type="button" id="cancelEditBtn" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 border border-gray-300 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Update Session
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>