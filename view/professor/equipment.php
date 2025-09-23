<?php
/**
 * Professor Equipment Page - Lab Management System
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_type'] !== 'professor') {
    header('Location: ../index.php');
    exit();
}

require_once '../../model/database.php';
$database = new Database();
$conn = $database->getConnection();

// Get next asset ID for hardware
$stmt = $conn->query("SELECT MAX(asset_id) AS max_id FROM hardware_assets");
$maxHardwareId = $stmt->fetch(PDO::FETCH_ASSOC)['max_id'];
$nextHardwareId = $maxHardwareId ? intval($maxHardwareId) + 1 : 1;

// Get next asset ID for software
$stmt = $conn->query("SELECT MAX(asset_id) AS max_id FROM software_assets");
$maxSoftwareId = $stmt->fetch(PDO::FETCH_ASSOC)['max_id'];
$nextSoftwareId = $maxSoftwareId ? intval($maxSoftwareId) + 1 : 1;

// Handle Create Asset form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_edit_asset'])) {
    require_once '../../model/database.php';
    $database = new Database();
    $conn = $database->getConnection();

    try {
        $edit_id = $_POST['edit_asset_id'];
        $asset_type = $_POST['asset_type'];
        $name = trim($_POST['name']);
        $date = trim($_POST['date']);

        // Check current asset type
        $current_type = null;
        $stmt = $conn->prepare("SELECT asset_id FROM hardware_assets WHERE asset_id = ?");
        $stmt->execute([$edit_id]);
        if ($stmt->fetch()) {
            $current_type = 'hardware';
        } else {
            $stmt = $conn->prepare("SELECT asset_id FROM software_assets WHERE asset_id = ?");
            $stmt->execute([$edit_id]);
            if ($stmt->fetch()) {
                $current_type = 'software';
            }
        }

        if ($current_type === $asset_type) {
            // Normal update
            if ($asset_type === 'hardware') {
                $condition = trim($_POST['condition']);
                $sql = "UPDATE hardware_assets SET name = ?, `condition` = ?, date = ? WHERE asset_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$name, $condition, $date, $edit_id]);
                echo json_encode([
                    'success' => true,
                    'asset_type' => 'hardware'
                ]);
            } else {
                $license_key = trim($_POST['license_key']);
                $sql = "UPDATE software_assets SET name = ?, license_key = ?, date = ? WHERE asset_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$name, $license_key, $date, $edit_id]);
                echo json_encode([
                    'success' => true,
                    'asset_type' => 'software'
                ]);
            }
        } else {
            // Move asset to other table
            if ($current_type === 'hardware' && $asset_type === 'software') {
                // Get hardware asset data
                $stmt = $conn->prepare("SELECT * FROM hardware_assets WHERE asset_id = ?");
                $stmt->execute([$edit_id]);
                $hw = $stmt->fetch(PDO::FETCH_ASSOC);

                $license_key = trim($_POST['license_key']);
                
                // Check if asset_id already exists in software_assets, if so get a new one
                $new_asset_id = $edit_id;
                $stmt = $conn->prepare("SELECT asset_id FROM software_assets WHERE asset_id = ?");
                $stmt->execute([$new_asset_id]);
                if ($stmt->fetch()) {
                    // Generate new asset_id for software_assets
                    $stmt = $conn->query("SELECT MAX(asset_id) AS max_id FROM software_assets");
                    $maxId = $stmt->fetch(PDO::FETCH_ASSOC)['max_id'];
                    $new_asset_id = $maxId ? intval($maxId) + 1 : 1;
                }
                
                // Insert into software_assets with potentially new ID
                $sql = "INSERT INTO software_assets (asset_id, name, license_key, date) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$new_asset_id, $name, $license_key, $date]);
                // Delete from hardware_assets
                $stmt = $conn->prepare("DELETE FROM hardware_assets WHERE asset_id = ?");
                $stmt->execute([$edit_id]);
                echo json_encode([
                    'success' => true,
                    'asset_type' => 'software',
                    'new_asset_id' => $new_asset_id
                ]);
            } elseif ($current_type === 'software' && $asset_type === 'hardware') {
                // Get software asset data
                $stmt = $conn->prepare("SELECT * FROM software_assets WHERE asset_id = ?");
                $stmt->execute([$edit_id]);
                $sw = $stmt->fetch(PDO::FETCH_ASSOC);

                $condition = trim($_POST['condition']);
                
                // Check if asset_id already exists in hardware_assets, if so get a new one
                $new_asset_id = $edit_id;
                $stmt = $conn->prepare("SELECT asset_id FROM hardware_assets WHERE asset_id = ?");
                $stmt->execute([$new_asset_id]);
                if ($stmt->fetch()) {
                    // Generate new asset_id for hardware_assets
                    $stmt = $conn->query("SELECT MAX(asset_id) AS max_id FROM hardware_assets");
                    $maxId = $stmt->fetch(PDO::FETCH_ASSOC)['max_id'];
                    $new_asset_id = $maxId ? intval($maxId) + 1 : 1;
                }
                
                // Insert into hardware_assets with potentially new ID
                $sql = "INSERT INTO hardware_assets (asset_id, name, `condition`, date) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$new_asset_id, $name, $condition, $date]);
                // Delete from software_assets
                $stmt = $conn->prepare("DELETE FROM software_assets WHERE asset_id = ?");
                $stmt->execute([$edit_id]);
                echo json_encode([
                    'success' => true,
                    'asset_type' => 'hardware',
                    'new_asset_id' => $new_asset_id
                ]);
            }
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_asset'])) {
    $asset_type = $_POST['asset_type'];
    $name = trim($_POST['name']);
    $date = trim($_POST['date']);

    if ($asset_type === 'hardware') {
        $condition = trim($_POST['condition']);
        $sql = "INSERT INTO hardware_assets (name, `condition`, date) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$name, $condition, $date]);
    } else {
        $license_key = trim($_POST['license_key']);
        $sql = "INSERT INTO software_assets (name, license_key, date) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$name, $license_key, $date]);
    }
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit();
}

// Handle delete hardware asset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_hardware_id'])) {
    $delete_id = $_POST['delete_hardware_id'];
    $sql = "DELETE FROM hardware_assets WHERE asset_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$delete_id]);
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit();
}

// Handle delete software asset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_software_id'])) {
    $delete_id = $_POST['delete_software_id'];
    $sql = "DELETE FROM software_assets WHERE asset_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$delete_id]);
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit();
}

// Fetch all hardware assets from the database
$hardware_assets = [];
$sql = "SELECT * FROM hardware_assets";
$stmt = $conn->prepare($sql);
$stmt->execute();
$hardware_assets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all software assets from the database
$software_assets = [];
$sql = "SELECT * FROM software_assets";
$stmt = $conn->prepare($sql);
$stmt->execute();
$software_assets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment - Lab Management System</title>
    <link href="../../css/output.css" rel="stylesheet">
    <style>
        .tab-button {
            transition: background 0.2s, color 0.2s, box-shadow 0.2s;
        }
        .tab-button.active {
            background-color: #2563eb;
            color: #fff;
            box-shadow: 0 2px 8px rgba(37,99,235,0.15);
        }
        .tab-button:not(.active) {
            background-color: #e5e7eb;
            color: #374151;
        }
        /* Modern input and form design for all equipment.php */
        .input-modern, select.input-modern, textarea.input-modern {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            background: #f3f4f6;
            font-size: 1rem;
            color: #374151;
            transition: border-color 0.2s;
        }
        .input-modern:focus, select.input-modern:focus, textarea.input-modern:focus {
            border-color: #2563eb;
            outline: none;
            background: #fff;
        }
        .label-modern {
            display: block;
            font-size: 0.95rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.25rem;
        }
        .button-modern {
            background: #2563eb;
            color: #fff;
            padding: 0.5rem 1.25rem;
            border-radius: 0.5rem;
            font-weight: 600;
            border: none;
            box-shadow: 0 2px 8px rgba(37,99,235,0.10);
            transition: background 0.2s;
        }
        .button-modern:hover {
            background: #1d4ed8;
        }
        .table-modern th, .table-modern td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #f3f4f6;
        }
        .table-modern th {
            background: #f9fafb;
            color: #6b7280;
            font-weight: 600;
            font-size: 0.85rem;
        }
        .table-modern {
            border-radius: 0.75rem;
            overflow: hidden;
            background: #fff;
        }
        /* Modal styles */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(255,255,255,0.7);
            z-index: 40;
            display: none;
        }
        .modal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            z-index: 50;
            border-radius: 1.25rem;
            box-shadow: 0 16px 40px rgba(37,99,235,0.10), 0 8px 32px rgba(0,0,0,0.12);
            width: 420px;
            max-width: 95vw;
            min-height: 0;
            padding: 2.5rem 2rem;
            display: none;
            transition: box-shadow 0.2s, width 0.2s;
        }
        .modal.active, .modal-overlay.active {
            display: block;
        }
        .modal input[readonly], .modal input[tabindex="-1"] {
            background: #f3f4f6;
            cursor: not-allowed;
        }
        .status-tab {
            background: #e0e7ff;
            border-radius: 9999px;
            padding: 0.5rem 1.25rem;
            font-weight: 600;
            display: inline-block;
            margin-right: 0.5rem;
            box-shadow: 0 2px 8px rgba(37,99,235,0.10);
            transition: background 0.2s, color 0.2s;
        }
        .status-tab.active {
            background: #2563eb;
            color: #fff;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex dashboard-layout">
        <?php include '../components/sidebar.php'; ?>
        <div class="flex flex-col flex-1 main-content-area">
            <?php include '../components/header.php'; ?>
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
                <div class="max-w-7xl mx-auto">
                    <h1 class="text-2xl font-bold text-gray-900 mb-6">Equipment Management</h1>
                    
                    <div class="bg-white shadow rounded-lg p-6">
                        <!-- Tabs -->
                        <div class="mb-4">
                            <ul class="flex flex-wrap text-sm font-medium text-center items-center space-x-2" id="assets-tab" role="tablist">
                                <li role="presentation">
                                    <button class="button-modern px-6 py-2 rounded-full tab-button active transition-colors duration-200 shadow" id="hardware-tab" type="button" role="tab" aria-controls="hardware-assets" aria-selected="true">
                                        Hardware Assets
                                    </button>
                                </li>
                                <li role="presentation">
                                    <button class="button-modern px-6 py-2 rounded-full tab-button transition-colors duration-200 bg-gray-200 text-gray-700 shadow" id="software-tab" type="button" role="tab" aria-controls="software-assets" aria-selected="false">
                                        Software Assets
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex space-x-2 mb-4">
                            <button id="status-btn" class="status-tab active">Status</button>
                            <button id="createAssetBtn" class="status-tab">Create Assets</button>
                            <a href="#" class="status-tab">Assets Logs</a>
                        </div>
                        
                        <!-- Filters -->
                        <div class="flex items-center space-x-4 mb-4">
                            <div class="relative" style="width: 320px;">
                                <input type="text" id="searchInput" class="input-modern" placeholder="  Search">
                            </div>
                            <!-- Status filter for hardware -->
                            <select id="conditionFilter" class="input-modern" style="width: 170px; color: #374151; opacity:0.95;">
                                <option value="all" selected>All</option>
                                <option value="Working">Working</option>
                                <option value="Under Maintenance">Under Maintenance</option>
                                <option value="Defective">Defective</option>
                            </select>
                            <!-- Date filter for software (hidden by default) -->
                            <select id="dateFilter" class="input-modern" style="width: 170px; color: #374151; opacity:0.95; display:none;">
                                <option value="all" selected>All Dates</option>
                                <?php
                                // Get unique dates from software assets
                                $dates = array_unique(array_map(function($a){ return $a['date']; }, $software_assets));
                                foreach ($dates as $date) {
                                    echo '<option value="' . htmlspecialchars($date) . '">' . htmlspecialchars($date) . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Hardware Assets Table -->
                        <div id="hardware-assets" role="tabpanel" aria-labelledby="hardware-tab" class="asset-table">
                            <div class="overflow-x-auto">
                                <table class="min-w-full table-modern">
                                    <thead>
                                        <tr>
                                            <th>Asset ID</th>
                                            <th>Name</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="hardware-table-body">
                                        <?php if (!empty($hardware_assets)): ?>
                                            <?php foreach ($hardware_assets as $asset): ?>
                                            <tr>
                                                <td class="text-center"><?php echo htmlspecialchars($asset['asset_id']); ?></td>
                                                <td class="text-center"><?php echo htmlspecialchars($asset['name']); ?></td>
                                                <td class="text-center">
                                                    <?php 
                                                        $condition_class = '';
                                                        switch ($asset['condition']) {
                                                            case 'Working':
                                                                $condition_class = 'bg-green-100 text-green-800';
                                                                break;
                                                            case 'Under Maintenance':
                                                                $condition_class = 'bg-yellow-100 text-yellow-800';
                                                                break;
                                                            case 'Defective':
                                                                $condition_class = 'bg-red-100 text-red-800';
                                                                break;
                                                            default:
                                                                $condition_class = 'bg-gray-100 text-gray-800';
                                                                break;
                                                        }
                                                    ?>
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $condition_class; ?>">
                                                        <?php echo htmlspecialchars($asset['condition']); ?>
                                                    </span>
                                                </td>
                                                <td class="text-center"><?php echo htmlspecialchars($asset['date']); ?></td>
                                                <td class="text-center">
                                                    <form method="post" style="display:inline;">
                                                        <input type="hidden" name="delete_hardware_id" value="<?php echo $asset['asset_id']; ?>">
                                                        <button type="submit" class="button-modern" style="background:#ef4444;color:#fff;" onclick="return confirm('Are you sure you want to delete this asset?')">Delete</button>
                                                    </form>
                                                    <button type="button"
                                                        class="button-modern bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1 rounded-full edit-hardware-btn"
                                                        data-id="<?php echo $asset['asset_id']; ?>">
                                                        Edit
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-gray-500">No hardware assets found.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Software Assets Table -->
                        <div id="software-assets" role="tabpanel" aria-labelledby="software-tab" class="hidden asset-table">
                            <div class="overflow-x-auto">
                                <table class="min-w-full table-modern">
                                    <thead>
                                        <tr>
                                            <th>Asset ID</th>
                                            <th>Name</th>
                                            <th>License Key</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="software-table-body">
                                        <?php if (!empty($software_assets)): ?>
                                            <?php foreach ($software_assets as $asset): ?>
                                            <tr>
                                                <td class="text-center"><?php echo htmlspecialchars($asset['asset_id']); ?></td>
                                                <td class="text-center"><?php echo htmlspecialchars($asset['name']); ?></td>
                                                <td class="text-center"><?php echo htmlspecialchars($asset['license_key']); ?></td>
                                                <td class="text-center"><?php echo htmlspecialchars($asset['date']); ?></td>
                                                <td class="text-center">
                                                    <form method="post" style="display:inline;">
                                                        <input type="hidden" name="delete_software_id" value="<?php echo $asset['asset_id']; ?>">
                                                        <button type="submit" class="button-modern" style="background:#ef4444;color:#fff;" onclick="return confirm('Are you sure you want to delete this asset?')">Delete</button>
                                                    </form>
                                                    <button type="button"
                                                        class="button-modern bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1 rounded-full edit-software-btn"
                                                        data-id="<?php echo $asset['asset_id']; ?>">
                                                        Edit
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-gray-500">No software assets found.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Create/Edit Asset Modal -->
                        <div id="createAssetModal" class="modal">
                            <button id="closeCreateAssetModal" class="absolute top-4 right-6 text-gray-400 hover:text-gray-600 text-2xl font-bold" style="background:none;border:none;">&times;</button>
                            <form method="post" class="space-y-6" id="assetForm">
                                <input type="hidden" name="create_asset" value="1" id="formMode">
                                <input type="hidden" name="edit_asset_id" id="edit_asset_id">
                                <h2 class="text-xl font-bold mb-2 text-gray-800" id="modalTitle">Create New Asset</h2>
                                <div>
                                    <label for="asset_type" class="label-modern">Asset Type</label>
                                    <select name="asset_type" id="asset_type" class="input-modern" required>
                                        <option value="hardware" selected>Hardware</option>
                                        <option value="software">Software</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="name" class="label-modern">Name</label>
                                    <input type="text" name="name" id="name" class="input-modern" required>
                                </div>
                                <div id="hardwareFields">
                                    <label for="condition" class="label-modern mt-4">Status</label>
                                    <select name="condition" id="condition" class="input-modern" required>
                                        <option value="" disabled selected hidden>Status</option>
                                        <option value="Working">Working</option>
                                        <option value="Under Maintenance">Under Maintenance</option>
                                        <option value="Defective">Defective</option>
                                    </select>
                                </div>
                                <div id="softwareFields" style="display:none;">
                                    <label for="license_key" class="label-modern">License Key</label>
                                    <input type="text" name="license_key" id="license_key" class="input-modern">
                                </div>
                                <div>
                                    <label for="date" class="label-modern">Date</label>
                                    <input type="date" name="date" id="date" class="input-modern" required>
                                </div>
                                <div class="flex justify-end pt-2">
                                    <button type="submit" class="button-modern" id="modalSubmitBtn">Create</button>
                                </div>
                            </form>
                        </div>
                        <div id="modalOverlay" class="modal-overlay"></div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Check if we need to switch tabs after reload
            const activeAssetTab = localStorage.getItem('activeAssetTab');
            if (activeAssetTab) {
                if (activeAssetTab === 'software') {
                    document.getElementById('software-tab').click();
                } else if (activeAssetTab === 'hardware') {
                    document.getElementById('hardware-tab').click();
                }
                localStorage.removeItem('activeAssetTab');
            }

            // Asset Tab Switching Logic
            const tabButtons = document.querySelectorAll('#assets-tab .tab-button');
            const tabContents = document.querySelectorAll('.asset-table');

            tabButtons.forEach(button => {
                button.addEventListener('click', () => {
                    tabButtons.forEach(btn => btn.classList.remove('active', 'bg-blue-600', 'text-white'));
                    tabButtons.forEach(btn => btn.classList.add('bg-gray-200', 'text-gray-700'));
                    tabContents.forEach(content => content.classList.add('hidden'));

                    button.classList.add('active', 'bg-blue-600', 'text-white');
                    button.classList.remove('bg-gray-200', 'text-gray-700');
                    const targetId = button.getAttribute('aria-controls');
                    document.getElementById(targetId).classList.remove('hidden');
                });
            });

            // Action buttons active state logic for status-tab
            const statusTabs = document.querySelectorAll('.status-tab');
            statusTabs.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    statusTabs.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                });
            });

            // Modal logic for Create Asset
            const createAssetBtn = document.getElementById('createAssetBtn');
            const createAssetModal = document.getElementById('createAssetModal');
            const modalOverlay = document.getElementById('modalOverlay');
            const closeCreateAssetModal = document.getElementById('closeCreateAssetModal');

            createAssetBtn.addEventListener('click', function() {
                createAssetModal.classList.add('active');
                modalOverlay.classList.add('active');
            });

            closeCreateAssetModal.addEventListener('click', function() {
                createAssetModal.classList.remove('active');
                modalOverlay.classList.remove('active');
            });

            modalOverlay.addEventListener('click', function() {
                createAssetModal.classList.remove('active');
                modalOverlay.classList.remove('active');
            });

            // Show/hide fields based on asset type and set asset ID
            const assetTypeSelect = document.getElementById('asset_type');
            const hardwareFields = document.getElementById('hardwareFields');
            const softwareFields = document.getElementById('softwareFields');
            const assetIdInput = document.getElementById('asset_id');
            const nextHardwareId = <?php echo json_encode($nextHardwareId); ?>;
            const nextSoftwareId = <?php echo json_encode($nextSoftwareId); ?>;
            

            assetTypeSelect.addEventListener('change', function() {
                if (assetTypeSelect.value === 'hardware') {
                    hardwareFields.style.display = '';
                    softwareFields.style.display = 'none';
                    document.getElementById('condition').required = true;
                    document.getElementById('license_key').required = false;
                } else {
                    hardwareFields.style.display = 'none';
                    softwareFields.style.display = '';
                    document.getElementById('condition').required = false;
                    document.getElementById('license_key').required = true;
                }
            });

            // Edit button logic for hardware
            document.querySelectorAll('.edit-hardware-btn').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    const assetId = btn.getAttribute('data-id');
                    const row = btn.closest('tr');
                    document.getElementById('modalTitle').textContent = "Edit Asset";
                    document.getElementById('modalSubmitBtn').textContent = "Update";
                    document.getElementById('formMode').name = "ajax_edit_asset";
                    document.getElementById('edit_asset_id').value = assetId;
                    document.getElementById('asset_type').value = "hardware";
                    document.getElementById('name').value = row.children[1].textContent.trim();
                    // Extract condition from span element  
                    const conditionSpan = row.children[2].querySelector('span');
                    document.getElementById('condition').value = conditionSpan ? conditionSpan.textContent.trim() : row.children[2].textContent.trim();
                    document.getElementById('date').value = row.children[3].textContent.trim();
                    document.getElementById('hardwareFields').style.display = '';
                    document.getElementById('softwareFields').style.display = 'none';
                    document.getElementById('license_key').required = false;
                    document.getElementById('condition').required = true;
                    createAssetModal.classList.add('active');
                    modalOverlay.classList.add('active');
                });
            });

            // Edit button logic for software
            document.querySelectorAll('.edit-software-btn').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    const assetId = btn.getAttribute('data-id');
                    const row = btn.closest('tr');
                    document.getElementById('modalTitle').textContent = "Edit Asset";
                    document.getElementById('modalSubmitBtn').textContent = "Update";
                    document.getElementById('formMode').name = "ajax_edit_asset";
                    document.getElementById('edit_asset_id').value = assetId;
                    document.getElementById('asset_type').value = "software";
                    document.getElementById('name').value = row.children[1].textContent.trim();
                    document.getElementById('license_key').value = row.children[2].textContent.trim();
                    document.getElementById('date').value = row.children[3].textContent.trim();
                    document.getElementById('hardwareFields').style.display = 'none';   
                    document.getElementById('softwareFields').style.display = '';
                    document.getElementById('license_key').required = true;
                    document.getElementById('condition').required = false;
                    createAssetModal.classList.add('active');
                    modalOverlay.classList.add('active');
                });
            });

            // AJAX update for edit
            document.getElementById('assetForm').addEventListener('submit', function(e) {
                if (document.getElementById('formMode').name === "ajax_edit_asset") {
                    e.preventDefault();
                    console.log('AJAX edit submission started');
                    const form = e.target;
                    const formData = new FormData(form);
                    formData.append('ajax_edit_asset', '1');
                    
                    // Debug: log form data
                    for (let pair of formData.entries()) {
                        console.log(pair[0] + ': ' + pair[1]);
                    }
                    
                    fetch(window.location.href, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        console.log('Response status:', response.status);
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.text();
                    })
                    .then(text => {
                        console.log('Raw response:', text);
                        try {
                            const data = JSON.parse(text);
                            console.log('Parsed data:', data);
                            if (data.success) {
                                // Check if a new asset ID was assigned
                                if (data.new_asset_id && data.new_asset_id !== formData.get('edit_asset_id')) {
                                    alert(`Asset successfully moved to ${data.asset_type} table with new ID: ${data.new_asset_id}`);
                                }
                                // Save asset_type to localStorage to switch tab after reload
                                localStorage.setItem('activeAssetTab', data.asset_type);
                                window.location.reload();
                            } else {
                                alert('Error: ' + (data.error || 'Unknown error occurred'));
                            }
                        } catch (e) {
                            console.error('JSON parse error:', e);
                            console.error('Response text:', text);
                            alert('Invalid response from server');
                        }
                    })
                    .catch(error => {
                        console.error('Fetch error:', error);
                        alert('An error occurred while updating the asset. Please try again.');
                    });
                }
                // else, normal submit for create
            });

            // Reset modal to create mode when opening normally
            createAssetBtn.addEventListener('click', function() {
                document.getElementById('modalTitle').textContent = "Create New Asset";
                document.getElementById('modalSubmitBtn').textContent = "Create";
                document.getElementById('formMode').name = "create_asset";
                document.getElementById('edit_asset_id').value = "";
                if (assetTypeSelect.value === 'hardware') {
                    document.getElementById('hardwareFields').style.display = '';
                    document.getElementById('softwareFields').style.display = 'none';
                    document.getElementById('license_key').required = false;
                    document.getElementById('condition').required = true;
                } else {
                    document.getElementById('hardwareFields').style.display = 'none';
                    document.getElementById('softwareFields').style.display = '';
                    document.getElementById('license_key').required = true;
                    document.getElementById('condition').required = false;
                }
                document.getElementById('name').value = "";
                document.getElementById('license_key').value = "";
                document.getElementById('date').value = "";
                createAssetModal.classList.add('active');
                modalOverlay.classList.add('active');
            });

            // Live search for hardware and software tables
            const searchInput = document.getElementById('searchInput');
            searchInput.addEventListener('input', function() {
                const query = searchInput.value.toLowerCase();
                // Hardware table
                document.querySelectorAll('#hardware-table-body tr').forEach(row => {
                    const nameCell = row.children[1];
                    if (!nameCell) return;
                    const name = nameCell.textContent.toLowerCase();
                    row.style.display = name.includes(query) ? '' : 'none';
                });
                // Software table
                document.querySelectorAll('#software-table-body tr').forEach(row => {
                    const nameCell = row.children[1];
                    if (!nameCell) return;
                    const name = nameCell.textContent.toLowerCase();
                    row.style.display = name.includes(query) ? '' : 'none';
                });
            });

            const hardwareTab = document.getElementById('hardware-tab');
const softwareTab = document.getElementById('software-tab');
const conditionFilter = document.getElementById('conditionFilter');
const dateFilter = document.getElementById('dateFilter');

// Show status filter only for hardware tab, date filter only for software tab
hardwareTab.addEventListener('click', function() {
    conditionFilter.style.display = '';
    dateFilter.style.display = 'none';
});
softwareTab.addEventListener('click', function() {
    conditionFilter.style.display = 'none';
    dateFilter.style.display = '';
});

// On page load, show status filter (hardware is default)
conditionFilter.style.display = '';
dateFilter.style.display = 'none';

// Filter by condition (status) logic for hardware
conditionFilter.addEventListener('change', function() {
    const selected = conditionFilter.value.toLowerCase();
    document.querySelectorAll('#hardware-table-body tr').forEach(row => {
        const statusCell = row.children[2];
        if (!statusCell) return;
        const status = statusCell.textContent.toLowerCase();
        row.style.display = (selected === "all" || status.includes(selected)) ? '' : 'none';
    });
});

// Filter by date logic for software
dateFilter.addEventListener('change', function() {
    const selectedDate = dateFilter.value;
    document.querySelectorAll('#software-table-body tr').forEach(row => {
        const dateCell = row.children[3];
        if (!dateCell) return;
        const date = dateCell.textContent.trim();
        row.style.display = (selectedDate === "all" || date === selectedDate) ? '' : 'none';
    });
});



            // Sidebar logic (unchanged)
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebarClose = document.getElementById('sidebarClose');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const mainContent = document.querySelector('.main-content-area');
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
            initializeSidebar();
            sidebarToggle?.addEventListener('click', toggleSidebar);
            sidebarClose?.addEventListener('click', closeSidebar);
            sidebarOverlay?.addEventListener('click', closeSidebar);
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
        });
        
    </script>
</body>
</html>

<?php

