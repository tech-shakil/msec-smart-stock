<?php
/**
 * MSEC SMART STOCK - Dynamic Role-Filtered Sidebar Navigation
 * Enforces strict RBAC (Section 3 & 27) & Mobile Off-Canvas Drawer
 */

$currentRoute = $_GET['route'] ?? 'dashboard';
$user = Auth::user();
$currentRole = $user['role_key'] ?? 'super_admin';
$roleName = ROLES[$currentRole]['name'] ?? $currentRole;
$roleLevel = ROLES[$currentRole]['level'] ?? 'User';

// Pre-calculate section visibilities based on permissions
$canProcure     = Auth::canAccessRoute('procurement');
$canBudget      = Auth::canAccessRoute('budget');
$canVerify      = Auth::canAccessRoute('verification');
$canReport      = Auth::canAccessRoute('reports');
$showGovernance = $canProcure || $canBudget || $canVerify || $canReport;

$canApprSheet   = Auth::canAccessRoute('approval_sheet');
$canMaintenance = Auth::canAccessRoute('maintenance');

$canMasters  = Auth::canAccessRoute('admin_masters');
$canUsers    = Auth::canAccessRoute('admin_users');
$canSecurity = Auth::canAccessRoute('admin_security');
$canAudit    = Auth::canAccessRoute('admin_audit');
$canSettings = Auth::canAccessRoute('admin_settings');
$showAdminSection = $canMasters || $canUsers || $canSecurity || $canAudit || $canSettings;
?>
<aside class="app-sidebar" id="appSidebar">
    <div class="sidebar-header">
        <div style="display: flex; align-items: center; gap: 12px; flex-grow: 1; min-width: 0;">
            <img src="images/msec_logo.png" alt="MSEC Emblem" class="sidebar-logo">
            <div style="min-width: 0;">
                <div class="brand-title">MSEC STOCK</div>
                <div class="brand-subtitle">Mohamed Sathak Engg. College</div>
            </div>
        </div>
        <!-- Mobile Drawer Close Button -->
        <button type="button" class="sidebar-close-btn" onclick="closeSidebar()" aria-label="Close menu">&times;</button>
    </div>

    <!-- Active Persona Quick-Info Badge -->
    <div class="sidebar-role-card">
        <div class="role-pill-level"><?= htmlspecialchars($roleLevel) ?></div>
        <div class="role-pill-title"><?= htmlspecialchars($roleName) ?></div>
        <div class="role-pill-dept"><?= htmlspecialchars($user['dept_name'] ?? 'Kilakarai Campus') ?></div>
    </div>

    <!-- Mobile Role Simulator Switcher -->
    <div class="sidebar-mobile-role-switcher hide-desktop">
        <form method="GET" action="index.php" id="sidebarPersonaForm">
            <input type="hidden" name="route" value="<?= htmlspecialchars($_GET['route'] ?? 'dashboard') ?>">
            <label style="font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 4px;">🎭 Switch Role Persona:</label>
            <select name="switch_role" class="form-control" style="background: rgba(255,255,255,0.08); color: #f1f5f9; border-color: rgba(255,255,255,0.15); font-size: 12px; padding: 6px 10px; border-radius: 6px; width: 100%;" onchange="document.getElementById('sidebarPersonaForm').submit();">
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
    </div>

    <nav class="sidebar-nav">
        <div class="nav-label">Core Operations</div>
        
        <?php if (Auth::canAccessRoute('dashboard')): ?>
            <a href="index.php?route=dashboard" class="nav-link <?= $currentRoute === 'dashboard' ? 'active' : '' ?>">
                <span class="nav-icon">📊</span>
                <span>Dashboard</span>
            </a>
        <?php endif; ?>

        <?php if (Auth::canAccessRoute('inventory')): ?>
            <a href="index.php?route=inventory" class="nav-link <?= $currentRoute === 'inventory' ? 'active' : '' ?>">
                <span class="nav-icon">📦</span>
                <span>Inventory Master</span>
            </a>
        <?php endif; ?>

        <?php if (Auth::canAccessRoute('requests')): ?>
            <a href="index.php?route=requests" class="nav-link <?= in_array($currentRoute, ['requests', 'new_request', 'view_request']) ? 'active' : '' ?>">
                <span class="nav-icon">📝</span>
                <span>Requests & Indents</span>
            </a>
        <?php endif; ?>

        <?php if ($canApprSheet): ?>
            <a href="index.php?route=approval_sheet" class="nav-link <?= $currentRoute === 'approval_sheet' ? 'active' : '' ?>">
                <span class="nav-icon">📑</span>
                <span>Approval Sheets</span>
            </a>
        <?php endif; ?>

        <?php if (Auth::canAccessRoute('distribution')): ?>
            <a href="index.php?route=distribution" class="nav-link <?= in_array($currentRoute, ['distribution', 'issue_stock', 'view_voucher']) ? 'active' : '' ?>">
                <span class="nav-icon">🚚</span>
                <span>Issue & Vouchers</span>
            </a>
        <?php endif; ?>

        <?php if (Auth::canAccessRoute('movement')): ?>
            <a href="index.php?route=movement" class="nav-link <?= $currentRoute === 'movement' ? 'active' : '' ?>">
                <span class="nav-icon">🔄</span>
                <span>Stock Transfers</span>
            </a>
        <?php endif; ?>

        <?php if (Auth::canAccessRoute('assets')): ?>
            <a href="index.php?route=assets" class="nav-link <?= $currentRoute === 'assets' ? 'active' : '' ?>">
                <span class="nav-icon">💻</span>
                <span>Asset Register</span>
            </a>
        <?php endif; ?>

        <?php if ($canMaintenance): ?>
            <a href="index.php?route=maintenance" class="nav-link <?= $currentRoute === 'maintenance' ? 'active' : '' ?>">
                <span class="nav-icon">🔧</span>
                <span>Maintenance & Repairs</span>
            </a>
        <?php endif; ?>

        <?php if ($showGovernance): ?>
            <div class="nav-label">Procurement & Governance</div>

            <?php if ($canProcure): ?>
                <a href="index.php?route=procurement" class="nav-link <?= $currentRoute === 'procurement' ? 'active' : '' ?>">
                    <span class="nav-icon">🛒</span>
                    <span>Procurement & GRN</span>
                </a>
            <?php endif; ?>

            <?php if ($canBudget): ?>
                <a href="index.php?route=budget" class="nav-link <?= $currentRoute === 'budget' ? 'active' : '' ?>">
                    <span class="nav-icon">💰</span>
                    <span>Department Budgets</span>
                </a>
            <?php endif; ?>

            <?php if ($canVerify): ?>
                <a href="index.php?route=verification" class="nav-link <?= $currentRoute === 'verification' ? 'active' : '' ?>">
                    <span class="nav-icon">📋</span>
                    <span>Stock Verification</span>
                </a>
            <?php endif; ?>

            <?php if ($canReport): ?>
                <a href="index.php?route=reports" class="nav-link <?= $currentRoute === 'reports' ? 'active' : '' ?>">
                    <span class="nav-icon">📈</span>
                    <span>Reports & NAAC/NBA</span>
                </a>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($showAdminSection): ?>
            <div class="nav-label">System Administration</div>

            <?php if ($canUsers): ?>
                <a href="index.php?route=admin_users" class="nav-link <?= $currentRoute === 'admin_users' ? 'active' : '' ?>">
                    <span class="nav-icon">👥</span>
                    <span>User Management</span>
                </a>
            <?php endif; ?>

            <?php if ($canSecurity): ?>
                <a href="index.php?route=admin_security" class="nav-link <?= $currentRoute === 'admin_security' ? 'active' : '' ?>">
                    <span class="nav-icon">🛡️</span>
                    <span>Security Console</span>
                </a>
            <?php endif; ?>

            <?php if ($canAudit): ?>
                <a href="index.php?route=admin_audit" class="nav-link <?= $currentRoute === 'admin_audit' ? 'active' : '' ?>">
                    <span class="nav-icon">📜</span>
                    <span>Audit Trail</span>
                </a>
            <?php endif; ?>

            <?php if ($canMasters): ?>
                <a href="index.php?route=admin_masters" class="nav-link <?= $currentRoute === 'admin_masters' ? 'active' : '' ?>">
                    <span class="nav-icon">🏛️</span>
                    <span>Master Data</span>
                </a>
            <?php endif; ?>

            <?php if ($canSettings): ?>
                <a href="index.php?route=admin_settings" class="nav-link <?= $currentRoute === 'admin_settings' ? 'active' : '' ?>">
                    <span class="nav-icon">⚙️</span>
                    <span>System Settings</span>
                </a>
            <?php endif; ?>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <a href="index.php?route=logout" class="sidebar-logout-link" title="Logout">
            <span class="nav-icon">🚪</span>
            <span>Sign Out</span>
        </a>
    </div>
</aside>
