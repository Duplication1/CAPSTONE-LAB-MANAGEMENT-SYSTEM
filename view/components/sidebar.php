<?php
/**
 * Sidebar Component - Lab Management System
 * Reusable sidebar for all dashboard pages
 */

// Get current user type from session
$user_type = $_SESSION['user_type'] ?? 'user';

// Define navigation items for each user type
$navigation_items = [
    'student' => [
        ['name' => 'Dashboard', 'href' => '../student/index.php', 'icon' => 'home', 'active' => true],
        ['name' => 'My Courses', 'href' => '../student/courses.php', 'icon' => 'academic-cap'],
        ['name' => 'Lab Sessions', 'href' => '../student/sessions.php', 'icon' => 'beaker'],
        ['name' => 'Lab Schedule', 'href' => '../student/schedule.php', 'icon' => 'calendar'],
        ['name' => 'Equipment Booking', 'href' => '../student/equipment.php', 'icon' => 'desktop-computer'],
        ['name' => 'Attendance', 'href' => '../student/attendance.php', 'icon' => 'check-circle'],
        ['name' => 'Assignments', 'href' => '../student/assignments.php', 'icon' => 'document-text'],
        ['name' => 'Profile', 'href' => '../student/profile.php', 'icon' => 'user'],
    ],
    'professor' => [
        ['name' => 'Dashboard', 'href' => '../professor/index.php', 'icon' => 'home', 'active' => true],
        ['name' => 'My Classes', 'href' => '../professor/classes.php', 'icon' => 'academic-cap'],
        ['name' => 'Lab Sessions', 'href' => '../professor/sessions.php', 'icon' => 'beaker'],
        ['name' => 'Students', 'href' => '../professor/students.php', 'icon' => 'users'],
        ['name' => 'Schedule', 'href' => '../professor/schedule.php', 'icon' => 'calendar'],
        ['name' => 'Equipment', 'href' => '../professor/equipment.php', 'icon' => 'desktop-computer'],
        ['name' => 'Attendance', 'href' => '../professor/attendance.php', 'icon' => 'clipboard-check'],
        ['name' => 'Reports', 'href' => '../professor/reports.php', 'icon' => 'chart-bar'],
        ['name' => 'Profile', 'href' => '../professor/profile.php', 'icon' => 'user'],
    ],
    'itstaff' => [
        ['name' => 'Dashboard', 'href' => '../itstaff/index.php', 'icon' => 'home', 'active' => true],
        ['name' => 'Equipment Management', 'href' => '../itstaff/equipment.php', 'icon' => 'desktop-computer'],
        ['name' => 'Maintenance Requests', 'href' => '../itstaff/maintenance.php', 'icon' => 'exclamation-triangle'],
        ['name' => 'Lab Rooms', 'href' => '../itstaff/rooms.php', 'icon' => 'office-building'],
        ['name' => 'User Management', 'href' => '../itstaff/users.php', 'icon' => 'users'],
        ['name' => 'System Health', 'href' => '../itstaff/health.php', 'icon' => 'status-online'],
        ['name' => 'Inventory', 'href' => '../itstaff/inventory.php', 'icon' => 'clipboard-list'],
        ['name' => 'Reports', 'href' => '../itstaff/reports.php', 'icon' => 'chart-bar'],
        ['name' => 'Settings', 'href' => '../itstaff/settings.php', 'icon' => 'cog'],
    ],
    'admin' => [
        ['name' => 'Dashboard', 'href' => '../admin/index.php', 'icon' => 'home', 'active' => true],
        ['name' => 'User Management', 'href' => '../admin/users.php', 'icon' => 'users'],
        ['name' => 'Lab Management', 'href' => '../admin/labs.php', 'icon' => 'beaker'],
        ['name' => 'Equipment', 'href' => '../admin/equipment.php', 'icon' => 'desktop-computer'],
        ['name' => 'System Health', 'href' => '../admin/health.php', 'icon' => 'heartbeat'],
        ['name' => 'System Settings', 'href' => '../admin/settings.php', 'icon' => 'cog'],
        ['name' => 'Database Backup', 'href' => '../admin/backup.php', 'icon' => 'database'],
        ['name' => 'System Logs', 'href' => '../admin/logs.php', 'icon' => 'document-text'],
        ['name' => 'Reports', 'href' => '../admin/reports.php', 'icon' => 'chart-bar'],
        ['name' => 'Permissions', 'href' => '../admin/permissions.php', 'icon' => 'shield-check'],
    ]
];

$current_nav = $navigation_items[$user_type] ?? $navigation_items['student'];

// SVG icons array
$icons = [
    'home' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>',
    'academic-cap' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>',
    'calendar' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>',
    'desktop-computer' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>',
    'check-circle' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
    'document-text' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>',
    'user' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>',
    'beaker' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547A1 1 0 004 17h5l-1.405 1.405A2 2 0 008.5 20h7a2 2 0 00.905-1.595L15 17h5a1 1 0 00-.072-1.572zM9 10a.75.75 0 01-.75-.75v-4.5a.75.75 0 011.5 0v4.5A.75.75 0 019 10zM15 10a.75.75 0 01-.75-.75v-4.5a.75.75 0 011.5 0v4.5A.75.75 0 0115 10z"></path>',
    'users' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>',
    'clipboard-check' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>',
    'chart-bar' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>',
    'exclamation-triangle' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>',
    'office-building' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>',
    'status-online' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>',
    'clipboard-list' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>',
    'cog' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>',
    'database' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>',
    'shield-check' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>',
    'heartbeat' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>',
];
?>

<!-- Sidebar -->
<aside id="sidebar" class="fixed inset-y-0 left-0 z-20 w-64 bg-white shadow-lg transform transition-transform duration-300 ease-in-out flex flex-col sidebar-container lg:static lg:inset-0">
    <!-- Sidebar header -->
    <div class="flex items-center justify-between h-16 px-4 border-b border-gray-200 flex-shrink-0">
        <span class="text-lg font-semibold text-gray-800 capitalize"><?php echo htmlspecialchars($user_type); ?> Menu</span>
        <!-- Close button for mobile -->
        <button id="sidebarClose" class="p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 lg:hidden">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
        <?php foreach ($current_nav as $item): ?>
            <a href="<?php echo htmlspecialchars($item['href']); ?>" 
               class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 <?php echo isset($item['active']) && $item['active'] ? 'bg-blue-100 text-blue-700 border-r-2 border-blue-700' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'; ?>">
                <svg class="mr-3 h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <?php echo $icons[$item['icon']] ?? $icons['home']; ?>
                </svg>
                <span><?php echo htmlspecialchars($item['name']); ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <!-- Sidebar footer -->
    <div class="p-4 border-t border-gray-200 flex-shrink-0">
        <div class="text-xs text-gray-500 text-center">
            &copy; 2025 Lab Management System
        </div>
    </div>
</aside>

<!-- Overlay for mobile -->
<div id="sidebarOverlay" class="fixed inset-0 z-10 bg-black bg-opacity-50 hidden lg:hidden"></div>