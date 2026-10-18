<?php
/**
 * MSEC SMART STOCK - Vice Principal Administrative Dashboard
 * Section 7: Administrative Review & Concurrence, Inter-Department Allocations,
 * Store-Verified Requisitions Queue, and Department Stock Health.
 */

$pdo = Database::getInstance();
$user = Auth::user();

// Live VP KPIs
$pendingVPApprovals = (int)$pdo->query("
    SELECT COUNT(*) FROM request_approvals 
    WHERE approver_role = 'vice_principal' AND status = 'Pending'
")->fetchColumn();

$activeTransfers = (int)$pdo->query("
    SELECT COUNT(*) FROM stock_movements 
    WHERE status IN ('Pending Approval', 'In Transit')
")->fetchColumn();

$lowStockDepts = (int)$pdo->query("
    SELECT COUNT(DISTINCT s.store_id) FROM stock s 
    JOIN items i ON s.item_id = i.id 
    WHERE s.available_quantity <= i.min_stock
")->fetchColumn();

$openTickets = (int)$pdo->query("SELECT COUNT(*) FROM asset_maintenance WHERE status = 'Open'")->fetchColumn();

// VP Approval Queue
$vpQueue = $pdo->query("
    SELECT r.*, d.name as dept_name, d.code as dept_code, u.full_name as requester_name,
           a.id as approval_id, a.level_title, s.sheet_number, s.total_cost, s.recommendation
    FROM stock_requests r
    JOIN request_approvals a ON r.id = a.request_id
    JOIN departments d ON r.dept_id = d.id
    JOIN users u ON r.requested_by = u.id
    LEFT JOIN approval_sheets s ON r.id = s.request_id
    WHERE a.status = 'Pending' AND a.approver_role = 'vice_principal'
    ORDER BY r.id DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Inter-Department Movements
$movements = $pdo->query("
    SELECT m.*, d1.code as src_code, d2.code as tgt_code, u.full_name as initiator_name
    FROM stock_movements m
    LEFT JOIN departments d1 ON m.source_dept_id = d1.id
    LEFT JOIN departments d2 ON m.target_dept_id = d2.id
    JOIN users u ON m.initiated_by = u.id
    ORDER BY m.id DESC LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-header">
    <div>
        <h2 class="page-title">Vice Principal Administrative & Allocation Dashboard</h2>
        <div class="page-desc">Vice Principal: <strong><?= htmlspecialchars($user['full_name']) ?></strong> &bull; Administrative Operations & Multi-Dept Sanctions &bull; <?= APP_INSTITUTION ?></div>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="index.php?route=movement" class="btn btn-secondary">🔄 Inter-Department Transfers</a>
    </div>
</div>

<!-- VP KPIs -->
<div class="stats-grid">
    <div class="stat-card warning">
        <div class="stat-title">Administrative Reviews Pending</div>
        <div class="stat-value"><?= sprintf("%02d", $pendingVPApprovals) ?></div>
        <div class="stat-subtext">Verified indents awaiting administrative review</div>
    </div>
    <div class="stat-card info">
        <div class="stat-title">Inter-Dept Movements in Transit</div>
        <div class="stat-value"><?= sprintf("%02d", $activeTransfers) ?></div>
        <div class="stat-subtext">Surplus equipment reallocations</div>
    </div>
    <div class="stat-card danger">
        <div class="stat-title">Stores with Low Stock Flags</div>
        <div class="stat-value"><?= sprintf("%02d", $lowStockDepts) ?></div>
        <div class="stat-subtext">Department buffer stock shortages</div>
    </div>
    <div class="stat-card primary">
        <div class="stat-title">Unassigned Service Tickets</div>
        <div class="stat-value"><?= sprintf("%02d", $openTickets) ?></div>
        <div class="stat-subtext">Lab hardware requiring repair</div>
    </div>
</div>

<div class="responsive-split-grid">
    <!-- VP Administrative Review Queue -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">📝 Administrative Review & Concurrence Queue</div>
            <span class="badge badge-warning"><?= count($vpQueue) ?> Pending</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Indent Code</th>
                            <th>Dept</th>
                            <th>Amount</th>
                            <th>Store Verification</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($vpQueue)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 24px; color: #16a34a;">
                                    ✓ All administrative tier requisitions are up to date.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($vpQueue as $vq): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($vq['request_code']) ?></strong>
                                        <div style="font-size: 11px; color: #64748b;"><?= htmlspecialchars(substr($vq['purpose'], 0, 35)) ?>...</div>
                                    </td>
                                    <td><span class="badge badge-neutral"><?= htmlspecialchars($vq['dept_code']) ?></span></td>
                                    <td><strong><?= CURRENCY_SYMBOL ?><?= number_format($vq['total_estimated_value']) ?></strong></td>
                                    <td>
                                        <?php if (!empty($vq['sheet_number'])): ?>
                                            <a href="index.php?route=approval_sheet&id=<?= $vq['id'] ?>" class="badge badge-success" target="_blank">
                                                ✓ <?= htmlspecialchars($vq['sheet_number']) ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Awaiting Sheet</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="index.php?route=view_request&id=<?= $vq['id'] ?>" class="btn btn-primary btn-sm">Review & Sanction &rarr;</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Inter-Department Equipment Transfers -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">🔄 Inter-Department Asset Transfers</div>
            <a href="index.php?route=movement" class="badge badge-primary">Transfer Manager</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Transfer Code</th>
                            <th>Routing</th>
                            <th>Status</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($movements as $m): ?>
                            <tr>
                                <td><code><?= htmlspecialchars($m['movement_code']) ?></code></td>
                                <td>
                                    <strong><?= htmlspecialchars($m['src_code'] ?? 'Central') ?></strong> &rarr;
                                    <strong><?= htmlspecialchars($m['tgt_code'] ?? 'Dept') ?></strong>
                                </td>
                                <td><span class="badge badge-warning"><?= htmlspecialchars($m['status']) ?></span></td>
                                <td>
                                    <span style="font-size: 11.5px;"><?= htmlspecialchars(substr($m['notes'], 0, 30)) ?>...</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
