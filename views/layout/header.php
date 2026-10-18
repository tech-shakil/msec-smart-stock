<?php
/**
 * MSEC SMART STOCK - Main Header Layout
 * Responsive Mobile & PC Navigation with Role-Aware Notification Bell and Session Indicator
 */

$user = Auth::user();
$currentRole = $user['role_key'] ?? 'super_admin';
$roleName = ROLES[$currentRole]['name'] ?? $currentRole;
$roleLevel = ROLES[$currentRole]['level'] ?? 'User';

// Role-aware notifications & automatic low-stock consumable checks
Notification::checkAndNotifyLowConsumables();
$unreadCount = Notification::getUnreadCount($user['id'] ?? 0, $currentRole);
$recentNotifications = Notification::getForUser($user['id'] ?? 0, $currentRole, 5);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="MSEC Stock">
    <meta name="format-detection" content="telephone=no">
    <meta name="theme-color" content="#102a43">
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="apple-touch-icon" href="images/msec_logo.png">
    <title><?= APP_NAME ?> — <?= APP_INSTITUTION_FULL ?></title>
    <link rel="stylesheet" href="css/style.css?v=<?= filemtime(dirname(__DIR__, 2) . '/public/css/style.css') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&family=Outfit:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .notif-dropdown-wrap {
            position: relative;
        }
        .notif-btn {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: 10px;
            padding: 8px 12px;
            cursor: pointer;
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            color: #334155;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.04);
        }
        .notif-btn:hover {
            background: #ffffff;
            border-color: #94a3b8;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px -2px rgba(15, 23, 42, 0.08);
            color: #0f172a;
        }
        .notif-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: #ffffff;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 800;
            padding: 2px 6px;
            line-height: 1.1;
            box-shadow: 0 0 10px rgba(239, 68, 68, 0.6);
            border: 1.5px solid #ffffff;
            animation: notifPulse 2s infinite ease-in-out;
        }
        @keyframes notifPulse {
            0%, 100% { transform: scale(1); box-shadow: 0 0 8px rgba(239, 68, 68, 0.6); }
            50% { transform: scale(1.08); box-shadow: 0 0 14px rgba(239, 68, 68, 0.85); }
        }
        .notif-panel {
            display: none;
            position: absolute;
            right: 0;
            top: calc(100% + 10px);
            width: 360px;
            max-width: 90vw;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border-radius: 16px;
            box-shadow: 0 20px 40px -8px rgba(15, 23, 42, 0.16), 0 8px 16px -4px rgba(15, 23, 42, 0.08);
            border: 1px solid rgba(226, 232, 240, 0.9);
            z-index: 1000;
            overflow: hidden;
            animation: notifSlideDown 0.22s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes notifSlideDown {
            from { opacity: 0; transform: translateY(-8px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        .notif-panel-header {
            padding: 14px 18px;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #ffffff;
            font-size: 13px;
            font-weight: 700;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .notif-list {
            max-height: 320px;
            overflow-y: auto;
        }
        .notif-item {
            padding: 13px 16px;
            border-bottom: 1px solid #f1f5f9;
            display: block;
            text-decoration: none;
            color: inherit;
            transition: all 0.15s ease;
            position: relative;
        }
        .notif-item:last-child {
            border-bottom: none;
        }
        .notif-item:hover {
            background: #f8fafc;
            padding-left: 19px;
        }
        .notif-item-title {
            font-size: 12.5px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 3px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .notif-item-desc {
            font-size: 12px;
            color: #64748b;
            line-height: 1.4;
        }
        .notif-item-time {
            font-size: 10.5px;
            color: #94a3b8;
            margin-top: 6px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
    </style>
</head>
<body>
<!-- Backdrop Overlay for Mobile Sidebar Drawer -->
<div class="sidebar-backdrop" id="sidebarBackdrop" onclick="closeSidebar()"></div>

<div class="app-container">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="app-main">
        <header class="app-header">
            <div class="header-left">
                <!-- Mobile Hamburger Button -->
                <button type="button" class="mobile-menu-btn" id="mobileMenuBtn" onclick="toggleSidebar()" aria-label="Toggle menu">
                    <span class="hamburger-bar"></span>
                    <span class="hamburger-bar"></span>
                    <span class="hamburger-bar"></span>
                </button>

                <div class="brand-header-info">
                    <img src="images/msec_logo.png" alt="MSEC Logo" class="header-brand-logo">
                    <div class="header-title-wrap">
                        <div class="header-top-row">
                            <h1 class="portal-title"><?= APP_NAME ?></h1>
                            <span class="header-tagline-badge hide-tablet-screen"><?= APP_INSTITUTION_TAGLINE ?></span>
                        </div>
                        <div class="header-institution-sub">
                            <?= APP_INSTITUTION_FULL ?> &bull; <span class="hide-tablet-screen"><?= APP_INSTITUTION_LOCATION ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="header-right">
                <!-- Global Vacant Stock Finder Trigger Button -->
                <button type="button" class="notif-btn" onclick="openGlobalVacantFinder()" id="btnGlobalVacantFinder" title="Search Vacant Stock Across College (Ctrl+K)" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 700; color: #0284c7; border: 1.5px solid #bae6fd; background: rgba(240, 249, 255, 0.95); padding: 7px 12px; border-radius: 10px;">
                    <span>🔍</span>
                    <span class="hide-tablet-screen" style="font-size: 12px; font-weight: 700;">Find Vacant Stock</span>
                    <span class="hide-tablet-screen" style="font-size: 10px; background: rgba(2, 132, 199, 0.15); color: #0284c7; padding: 1px 5px; border-radius: 4px; font-family: monospace;">Ctrl+K</span>
                </button>

                <!-- Role Notification Center (Section 25 & 40) -->
                <div class="notif-dropdown-wrap">
                    <button type="button" class="notif-btn" onclick="toggleNotifPanel()" id="notifBellBtn" title="Role Notifications">
                        <span>🔔</span>
                        <?php if ($unreadCount > 0): ?>
                            <span class="notif-badge"><?= $unreadCount ?></span>
                        <?php endif; ?>
                    </button>

                    <div class="notif-panel" id="notifPanel">
                        <div class="notif-panel-header">
                            <span>Notifications (<?= $unreadCount ?> unread)</span>
                            <span style="font-size: 11px; opacity: 0.8;"><?= htmlspecialchars($roleName) ?></span>
                        </div>
                        <div class="notif-list">
                            <?php if (empty($recentNotifications)): ?>
                                <div style="padding: 20px; text-align: center; color: #64748b; font-size: 12px;">
                                    ✓ No unread notifications
                                </div>
                            <?php else: ?>
                                <?php foreach ($recentNotifications as $n): ?>
                                    <a href="<?= htmlspecialchars($n['link_url'] ?? '#') ?>" class="notif-item">
                                        <div class="notif-item-title"><?= htmlspecialchars($n['title']) ?></div>
                                        <div class="notif-item-desc"><?= htmlspecialchars($n['message']) ?></div>
                                        <div class="notif-item-time"><?= date('d M H:i', strtotime($n['created_at'])) ?></div>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Demonstration Role Simulator Dropdown -->
                <form method="GET" action="index.php" class="persona-switcher" id="personaForm" title="Switch Demo Persona">
                    <input type="hidden" name="route" value="<?= htmlspecialchars($_GET['route'] ?? 'dashboard') ?>">
                    <span class="persona-icon">🎭</span>
                    <select name="switch_role" id="switch_role" class="persona-select" onchange="document.getElementById('personaForm').submit();" title="Simulate different role views">
                        <optgroup label="🏛️ Executive">
                            <option value="super_admin" <?= $currentRole === 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
                            <option value="director" <?= $currentRole === 'director' ? 'selected' : '' ?>>Director</option>
                            <option value="principal" <?= $currentRole === 'principal' ? 'selected' : '' ?>>Principal</option>
                            <option value="vice_principal" <?= $currentRole === 'vice_principal' ? 'selected' : '' ?>>Vice Principal</option>
                            <option value="management_viewer" <?= $currentRole === 'management_viewer' ? 'selected' : '' ?>>Management</option>
                        </optgroup>
                        <optgroup label="📦 Central Stores">
                            <option value="store_keeper" <?= $currentRole === 'store_keeper' ? 'selected' : '' ?>>Store Keeper</option>
                            <option value="purchase_officer" <?= $currentRole === 'purchase_officer' ? 'selected' : '' ?>>Purchase Officer</option>
                            <option value="accounts_officer" <?= $currentRole === 'accounts_officer' ? 'selected' : '' ?>>Accounts Officer</option>
                        </optgroup>
                        <optgroup label="🏢 Department">
                            <option value="hod" <?= $currentRole === 'hod' ? 'selected' : '' ?>>HOD CSE</option>
                            <option value="dept_coordinator" <?= $currentRole === 'dept_coordinator' ? 'selected' : '' ?>>Coordinator</option>
                            <option value="lab_incharge" <?= $currentRole === 'lab_incharge' ? 'selected' : '' ?>>Lab Incharge</option>
                        </optgroup>
                        <optgroup label="👤 Technical Staff">
                            <option value="technician" <?= $currentRole === 'technician' ? 'selected' : '' ?>>Technician</option>
                        </optgroup>
                        <optgroup label="🛡️ Audit">
                            <option value="auditor" <?= $currentRole === 'auditor' ? 'selected' : '' ?>>Auditor</option>
                        </optgroup>
                    </select>
                </form>

                <!-- Current User Profile Badge -->
                <div class="user-profile-badge">
                    <div class="user-avatar" title="<?= htmlspecialchars($user['full_name'] ?? 'User') ?>">
                        <?= strtoupper(substr($user['full_name'] ?? 'U', 0, 1)) ?>
                        <span class="user-status-dot" title="Active Session"></span>
                    </div>
                    <div class="user-details hide-laptop-compact">
                        <div class="user-name"><?= htmlspecialchars($user['full_name'] ?? 'Guest') ?></div>
                        <div class="user-role-badge">
                            <span class="role-name"><?= htmlspecialchars($roleName) ?></span>
                        </div>
                    </div>
                </div>

                <a href="index.php?route=logout" class="btn btn-secondary btn-sm header-signout-btn" title="Sign Out">Sign Out</a>
            </div>
        </header>

        <!-- Global Vacant Stock Finder Modal -->
        <div id="globalVacantFinderModal" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.65); z-index: 1100; align-items: center; justify-content: center; backdrop-filter: blur(6px);">
            <div style="background: white; border-radius: 16px; width: 840px; max-width: 95%; max-height: 88vh; display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.3); overflow: hidden;">
                <div style="padding: 18px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: white;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 22px;">🔍</span>
                        <div>
                            <h3 style="font-size: 17px; font-weight: 800; margin: 0; color: white;">Institutional Vacant Stock Finder</h3>
                            <div style="font-size: 12px; color: #94a3b8; margin-top: 2px;">
                                Search unallocated & vacant items across all college departments & Central Store
                            </div>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 11px; background: rgba(255,255,255,0.1); color: #cbd5e1; padding: 3px 8px; border-radius: 4px; font-family: monospace;">ESC to close</span>
                        <button onclick="closeGlobalVacantFinder()" style="border: none; background: none; font-size: 24px; cursor: pointer; color: #94a3b8; line-height: 1;">&times;</button>
                    </div>
                </div>

                <div style="padding: 18px 24px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; gap: 12px; flex-wrap: wrap;">
                    <div style="flex: 2; min-width: 240px; position: relative;">
                        <input type="text" id="globalVacantSearchInput" class="form-control" 
                               placeholder="🔍 Search item name, code, category, or department (e.g. keyboard, cable, paper)..." 
                               style="height: 42px; font-size: 14px; border-radius: 10px; padding-left: 36px;"
                               oninput="triggerGlobalVacantSearch()">
                        <span style="position: absolute; left: 12px; top: 11px; font-size: 16px; color: #64748b;">🔍</span>
                    </div>

                    <select id="globalVacantDeptFilter" class="form-control" style="flex: 1; min-width: 140px; height: 42px; font-size: 13px; border-radius: 10px;" onchange="triggerGlobalVacantSearch()">
                        <option value="all">All Stores & Depts</option>
                        <option value="0">🏢 Central Main Store</option>
                        <option value="1">CSE Department</option>
                        <option value="2">ECE Department</option>
                        <option value="3">EEE Department</option>
                        <option value="4">Mechanical Department</option>
                        <option value="5">Civil Department</option>
                        <option value="6">IT Department</option>
                        <option value="7">AI & DS Department</option>
                        <option value="8">Central Library</option>
                        <option value="9">Incubation & EDC</option>
                    </select>

                    <select id="globalVacantCatFilter" class="form-control" style="flex: 1; min-width: 140px; height: 42px; font-size: 13px; border-radius: 10px;" onchange="triggerGlobalVacantSearch()">
                        <option value="all">All Categories</option>
                        <option value="1">Stationery & Paper</option>
                        <option value="2">IT Consumables</option>
                        <option value="3">Electrical & Wiring</option>
                        <option value="4">Electronic Components</option>
                        <option value="5">Lab Chemicals & Reagents</option>
                        <option value="6">IT Equipment & Hardware</option>
                        <option value="7">Lab Instruments & Machinery</option>
                        <option value="8">Furniture & Fixtures</option>
                    </select>
                </div>

                <div id="globalVacantResultsContainer" style="padding: 16px 24px; flex: 1; overflow-y: auto; max-height: calc(88vh - 180px);">
                    <!-- Dynamic Search Results -->
                </div>

                <div style="padding: 12px 24px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: #64748b;">
                    <div id="globalVacantCountText">Type a keyword to discover available vacant stock</div>
                    <div>Logged in Department: <strong style="color: #0f172a;"><?= htmlspecialchars($user['dept_code'] ?? 'CSE') ?></strong></div>
                </div>
            </div>
        </div>

        <script>
        // Clean any leftover zoom styles or storage
        try {
            document.body.style.zoom = '1';
            localStorage.removeItem('msec_desktop_scale');
        } catch(e) {}

        function toggleNotifPanel() {
            const panel = document.getElementById('notifPanel');
            panel.style.display = (panel.style.display === 'block') ? 'none' : 'block';
        }
        document.addEventListener('click', function(e) {
            const wrap = document.querySelector('.notif-dropdown-wrap');
            if (wrap && !wrap.contains(e.target)) {
                const panel = document.getElementById('notifPanel');
                if (panel) panel.style.display = 'none';
            }
        });

        // Global Vacant Stock Finder logic
        const GLOBAL_USER_DEPT_ID = <?= (int)($user['dept_id'] ?? 1) ?>;
        const GLOBAL_USER_DEPT_CODE = "<?= htmlspecialchars($user['dept_code'] ?? 'CSE') ?>";
        let globalSearchDebounceTimer = null;

        function openGlobalVacantFinder() {
            const modal = document.getElementById('globalVacantFinderModal');
            if (modal) {
                modal.style.display = 'flex';
                const input = document.getElementById('globalVacantSearchInput');
                if (input) {
                    input.focus();
                    if (!input.value) {
                        triggerGlobalVacantSearch();
                    }
                }
            }
        }

        function closeGlobalVacantFinder() {
            const modal = document.getElementById('globalVacantFinderModal');
            if (modal) modal.style.display = 'none';
        }

        function triggerGlobalVacantSearch() {
            clearTimeout(globalSearchDebounceTimer);
            globalSearchDebounceTimer = setTimeout(fetchGlobalVacantStock, 150);
        }

        function fetchGlobalVacantStock() {
            const input = document.getElementById('globalVacantSearchInput');
            const deptFilter = document.getElementById('globalVacantDeptFilter');
            const catFilter = document.getElementById('globalVacantCatFilter');
            const container = document.getElementById('globalVacantResultsContainer');
            const countText = document.getElementById('globalVacantCountText');

            if (!container) return;

            const q = input ? encodeURIComponent(input.value.trim()) : '';
            const deptId = deptFilter ? deptFilter.value : 'all';
            const catId = catFilter ? catFilter.value : 'all';

            container.innerHTML = '<div style="padding: 24px; text-align: center; color: #64748b; font-size: 13px;">⚡ Searching live institutional stock...</div>';

            fetch(`index.php?route=api_search_vacant&q=${q}&dept_id=${deptId}&category_id=${catId}`)
                .then(res => res.json())
                .then(items => {
                    renderGlobalVacantResults(items, container, countText);
                })
                .catch(err => {
                    container.innerHTML = '<div style="padding: 24px; text-align: center; color: #dc2626; font-size: 13px;">Error fetching vacant stock.</div>';
                });
        }

        function renderGlobalVacantResults(items, container, countText) {
            container.innerHTML = '';
            if (!items || items.length === 0) {
                container.innerHTML = `
                    <div style="padding: 36px 20px; text-align: center; color: #64748b;">
                        <div style="font-size: 32px; margin-bottom: 8px;">🔍</div>
                        <div style="font-weight: 700; color: #1e293b; margin-bottom: 4px;">No Vacant Stock Found</div>
                        <div style="font-size: 13px;">No department currently reports vacant or unallocated units matching these criteria.</div>
                    </div>
                `;
                if (countText) countText.textContent = '0 items found';
                return;
            }

            let totalVacantUnits = 0;
            items.forEach(it => { totalVacantUnits += parseInt(it.vacant_qty); });
            if (countText) {
                countText.innerHTML = `Found <strong>${items.length} records</strong> with <strong>${totalVacantUnits} vacant units</strong> available across college`;
            }

            const table = document.createElement('table');
            table.className = 'data-table';
            table.style.fontSize = '13px';
            table.innerHTML = `
                <thead>
                    <tr>
                        <th>Item Details</th>
                        <th>Category</th>
                        <th>Store / Department</th>
                        <th style="text-align: center;">Vacant / Available</th>
                        <th style="text-align: center;">In Active Use</th>
                        <th>Unit Cost</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            `;
            const tbody = table.querySelector('tbody');

            items.forEach(it => {
                const isCentral = (!it.dept_id || it.store_id == 1);
                const isMyDept = (it.dept_id && parseInt(it.dept_id) === GLOBAL_USER_DEPT_ID);
                const deptLabel = isCentral ? 'Central Warehouse' : (it.dept_code || 'Dept');
                const storeLabel = it.store_name || 'Sub-Store';

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>
                        <div style="font-weight: 700; color: #0f172a;">${escapeHtml(it.item_name)}</div>
                        <div style="font-size: 11px; color: #64748b;"><code>${escapeHtml(it.item_code)}</code></div>
                    </td>
                    <td><span style="font-size: 12px; color: #475569;">${escapeHtml(it.category_name)}</span></td>
                    <td>
                        <span class="badge" style="background: ${isCentral ? '#0f172a; color: #38bdf8;' : '#e0e7ff; color: #4338ca;'} font-weight: 800; font-size: 11px;">
                            ${escapeHtml(deptLabel)}
                        </span>
                        <div style="font-size: 11px; color: #64748b; margin-top: 2px;">${escapeHtml(storeLabel)}</div>
                    </td>
                    <td style="text-align: center;">
                        <span class="badge badge-info" style="font-weight: 800; font-size: 12px; background: #e0f2fe; color: #0284c7; border: 1px solid #38bdf8;">
                            💡 ${it.vacant_qty} ${it.unit_code}
                        </span>
                    </td>
                    <td style="text-align: center;">
                        <span style="font-size: 12px; color: #16a34a; font-weight: 600;">
                            🟢 ${it.in_use_qty || 0}
                        </span>
                    </td>
                    <td>₹${parseFloat(it.unit_cost).toFixed(2)}</td>
                    <td style="text-align: right;">
                        ${!isMyDept ? `
                            <a href="index.php?route=movement&source_dept_id=${isCentral ? 0 : it.dept_id}&item_id=${it.item_id}" 
                               class="btn btn-primary btn-sm" style="font-size: 11px; white-space: nowrap; padding: 4px 8px;">
                                🔄 Transfer &rarr; ${GLOBAL_USER_DEPT_CODE}
                            </a>
                        ` : `
                            <span style="font-size: 11px; color: #64748b; font-weight: 600;">In Your Store</span>
                        `}
                    </td>
                `;
                tbody.appendChild(tr);
            });

            container.appendChild(table);
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text || '';
            return div.innerHTML;
        }

        // Keyboard Shortcut: Ctrl+K or Cmd+K
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                openGlobalVacantFinder();
            }
            if (e.key === 'Escape') {
                closeGlobalVacantFinder();
            }
        });
        </script>


        <main class="page-content">
