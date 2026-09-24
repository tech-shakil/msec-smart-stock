<?php
/**
 * MSEC SMART STOCK - Main Header Layout
 * Responsive Mobile & PC Navigation with Role-Aware Notification Bell and Session Indicator
 */

$user = Auth::user();
$currentRole = $user['role_key'] ?? 'super_admin';
$roleName = ROLES[$currentRole]['name'] ?? $currentRole;
$roleLevel = ROLES[$currentRole]['level'] ?? 'User';

// Role-aware notifications
$unreadCount = Notification::getUnreadCount($user['id'] ?? 0, $currentRole);
$recentNotifications = Notification::getForUser($user['id'] ?? 0, $currentRole, 5);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> — <?= APP_INSTITUTION_FULL ?></title>
    <link rel="stylesheet" href="css/style.css?v=<?= filemtime(dirname(__DIR__, 2) . '/public/css/style.css') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    <style>
        .notif-dropdown-wrap {
            position: relative;
        }
        .notif-btn {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 7px 11px;
            cursor: pointer;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            color: #334155;
            transition: all 0.2s ease;
        }
        .notif-btn:hover {
            background: #f1f5f9;
            border-color: #94a3b8;
        }
        .notif-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            background: #ef4444;
            color: #ffffff;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 800;
            padding: 1px 5px;
            line-height: 1.2;
            box-shadow: 0 2px 5px rgba(239, 68, 68, 0.4);
        }
        .notif-panel {
            display: none;
            position: absolute;
            right: 0;
            top: calc(100% + 8px);
            width: 340px;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
            z-index: 1000;
            overflow: hidden;
        }
        .notif-panel-header {
            padding: 12px 16px;
            background: #102a43;
            color: #ffffff;
            font-size: 13px;
            font-weight: 700;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .notif-list {
            max-height: 280px;
            overflow-y: auto;
        }
        .notif-item {
            padding: 12px 14px;
            border-bottom: 1px solid #f1f5f9;
            display: block;
            text-decoration: none;
            color: inherit;
            transition: background 0.15s ease;
        }
        .notif-item:hover {
            background: #f8fafc;
        }
        .notif-item-title {
            font-size: 12px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 2px;
        }
        .notif-item-desc {
            font-size: 11.5px;
            color: #64748b;
            line-height: 1.35;
        }
        .notif-item-time {
            font-size: 10px;
            color: #94a3b8;
            margin-top: 4px;
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
                        <optgroup label="👤 Academic">
                            <option value="faculty" <?= $currentRole === 'faculty' ? 'selected' : '' ?>>Faculty</option>
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
        </script>

        <main class="page-content">
