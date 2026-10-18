<?php
/**
 * MSEC SMART STOCK - System Administrator Dashboard
 * Section 4: User management, account security, role & permission mapping, and immutable audit logs.
 */

$pdo = Database::getInstance();
$user = Auth::user();

// Live Admin KPIs
$totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$activeUsers = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE status = 'Active'")->fetchColumn();
$lockedUsers = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE status = 'Locked'")->fetchColumn();
$totalAuditLogs = (int)$pdo->query("SELECT COUNT(*) FROM audit_logs")->fetchColumn();
$recentSecurityLogs = AuditLogger::getSecurityEvents(6);

$usersList = $pdo->query("
    SELECT u.*, d.code as dept_code 
    FROM users u 
    LEFT JOIN departments d ON u.dept_id = d.id 
    ORDER BY u.id DESC LIMIT 8
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-header">
    <div>
        <h2 class="page-title">System Administration & Security Control Console</h2>
        <div class="page-desc">Chief Administrator: <strong><?= htmlspecialchars($user['full_name']) ?></strong> &bull; <?= APP_INSTITUTION ?></div>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="index.php?route=admin_users" class="btn btn-primary">+ Manage User Accounts</a>
        <a href="index.php?route=admin_security" class="btn btn-secondary">🛡️ Security Console</a>
    </div>
</div>

<!-- Admin KPIs -->
<div class="stats-grid">
    <div class="stat-card primary">
        <div class="stat-title">Total Enrolled Accounts</div>
        <div class="stat-value"><?= number_format($totalUsers) ?></div>
        <div class="stat-subtext">Across 14 institutional roles</div>
    </div>
    <div class="stat-card success">
        <div class="stat-title">Active Authorized Users</div>
        <div class="stat-value"><?= number_format($activeUsers) ?></div>
        <div class="stat-subtext">Verified personnel accounts</div>
    </div>
    <div class="stat-card danger">
        <div class="stat-title">Locked / Suspended</div>
        <div class="stat-value"><?= number_format($lockedUsers) ?></div>
        <div class="stat-subtext">Requires Admin identity verification</div>
    </div>
    <div class="stat-card info">
        <div class="stat-title">Permanent Audit Logs</div>
        <div class="stat-value"><?= number_format($totalAuditLogs) ?></div>
        <div class="stat-subtext">Cryptographic immutable trail</div>
    </div>
    <div class="stat-card warning">
        <div class="stat-title">Public Registration</div>
        <div class="stat-value">LOCKED</div>
        <div class="stat-subtext">Admin-only provisioning enforced</div>
    </div>
</div>

<div class="responsive-split-grid">
    <!-- User Accounts Quick Management -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">👥 User Accounts & Role Assignments</div>
            <a href="index.php?route=admin_users" class="badge badge-primary">Full Directory &rarr;</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="data-table-container">
                <table class="data-table data-table-compact">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th class="text-center">Role</th>
                            <th class="text-center">Dept</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usersList as $u): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($u['full_name']) ?></strong>
                                    <div style="font-size: 11px; color: #64748b;"><code>@<?= htmlspecialchars($u['username']) ?></code></div>
                                </td>
                                <td class="text-center"><span class="badge badge-primary"><?= htmlspecialchars(ROLES[$u['role_key']]['name'] ?? $u['role_key']) ?></span></td>
                                <td class="text-center"><strong><?= htmlspecialchars($u['dept_code'] ?? 'Central') ?></strong></td>
                                <td class="text-center">
                                    <?php if ($u['status'] === 'Locked'): ?>
                                        <span class="badge badge-danger">Locked</span>
                                    <?php elseif ($u['status'] === 'Active'): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-neutral">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="index.php?route=admin_users" class="btn btn-secondary btn-sm">Manage</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Security Activity & System Settings Links -->
    <div style="display: flex; flex-direction: column; gap: 20px;">
        <div class="card">
            <div class="card-header">
                <div class="card-title">🛡️ Security & Authentication Events</div>
                <a href="index.php?route=admin_audit" class="badge badge-secondary">Full Log</a>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="data-table-container">
                    <table class="data-table data-table-compact">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Action</th>
                                <th>Actor</th>
                                <th class="text-right">IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentSecurityLogs as $sl): ?>
                                <tr>
                                    <td style="font-size: 11px;"><?= date('H:i:s', strtotime($sl['created_at'])) ?></td>
                                    <td><code><?= htmlspecialchars($sl['action']) ?></code></td>
                                    <td><strong><?= htmlspecialchars($sl['username'] ?? 'System') ?></strong></td>
                                    <td class="text-right"><code><?= htmlspecialchars($sl['ip_address'] ?? '127.0.0.1') ?></code></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-title">⚙️ Core Configuration Modules</div>
            </div>
            <div class="card-body" style="display: flex; flex-direction: column; gap: 10px;">
                <a href="index.php?route=admin_masters" class="btn btn-secondary" style="justify-content: flex-start;">
                    🏛️ Department, Store & Laboratory Masters
                </a>
                <a href="index.php?route=admin_settings" class="btn btn-secondary" style="justify-content: flex-start;">
                    ⚙️ Institutional Parameters & Thresholds
                </a>
                <a href="index.php?route=admin_audit" class="btn btn-secondary" style="justify-content: flex-start;">
                    🛡️ Permanent Immutable Audit Trail
                </a>
            </div>
        </div>
    </div>
</div>
