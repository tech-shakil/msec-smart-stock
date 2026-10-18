<?php
/**
 * MSEC SMART STOCK - Department Stock Coordinator Dashboard
 * Section 13: Pre-HOD Inspection Layer, Verifies physical necessity, technical specs,
 * urgency, and gate-forwards verified requests to HOD.
 */

$pdo = Database::getInstance();
$user = Auth::user();
$deptId = $user['dept_id'] ?? 1;

// Coordinator KPIs
$pendingInspections = (int)$pdo->query("
    SELECT COUNT(*) FROM stock_requests r 
    JOIN request_approvals a ON r.id = a.request_id 
    WHERE r.dept_id = {$deptId} AND a.status = 'Pending' AND a.approver_role = 'dept_coordinator'
")->fetchColumn();

$pendingChallans = (int)$pdo->query("
    SELECT COUNT(*) FROM stock_issues 
    WHERE target_dept_id = {$deptId} AND status = 'Issued'
")->fetchColumn();

$activeTransfers = (int)$pdo->query("
    SELECT COUNT(*) FROM stock_movements 
    WHERE (source_dept_id = {$deptId} OR target_dept_id = {$deptId}) AND status IN ('Submitted', 'Pending Approval', 'In Transit')
")->fetchColumn();

// Inspection Queue (Staff & Lab requests awaiting coordinator review)
$inspectionQueue = $pdo->query("
    SELECT r.*, u.full_name as requester_name, u.role_key as requester_role, l.name as lab_name
    FROM stock_requests r
    JOIN users u ON r.requested_by = u.id
    LEFT JOIN laboratories l ON r.lab_id = l.id
    WHERE r.dept_id = {$deptId} AND r.status IN ('SUBMITTED', 'COORDINATOR_REVIEW', 'Pending Approval')
    ORDER BY r.id DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Inward Vouchers Awaiting Acknowledgment
$inwardVouchers = $pdo->query("
    SELECT i.*, s.name as store_name, u.full_name as issuer_name
    FROM stock_issues i
    JOIN stores s ON i.source_store_id = s.id
    JOIN users u ON i.issued_by = u.id
    WHERE i.target_dept_id = {$deptId} AND i.status = 'Issued'
    ORDER BY i.id DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-header">
    <div>
        <h2 class="page-title">Department Stock Coordinator Inspection Hub</h2>
        <div class="page-desc">Coordinator: <strong><?= htmlspecialchars($user['full_name']) ?></strong> &bull; Technical Need Verification & Delivery Challans &bull; <?= APP_INSTITUTION ?></div>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="index.php?route=movement" class="btn btn-secondary">🔄 Request Stock Transfer</a>
        <a href="index.php?route=new_request" class="btn btn-primary">+ New Lab Indent</a>
    </div>
</div>

<!-- Coordinator KPIs -->
<div class="stats-grid">
    <div class="stat-card warning">
        <div class="stat-title">Awaiting Spec Inspection</div>
        <div class="stat-value"><?= number_format($pendingInspections) ?></div>
        <div class="stat-subtext">Pre-HOD technical verification required</div>
    </div>
    <div class="stat-card danger">
        <div class="stat-title">Inward Vouchers To Acknowledge</div>
        <div class="stat-value"><?= number_format($pendingChallans) ?></div>
        <div class="stat-subtext">Material delivered from Central Store</div>
    </div>
    <div class="stat-card info">
        <div class="stat-title">Department Transfers in Pipeline</div>
        <div class="stat-value"><?= number_format($activeTransfers) ?></div>
        <div class="stat-subtext">Inter-department hardware movements</div>
    </div>
    <div class="stat-card success">
        <div class="stat-title">Department Authority Gate</div>
        <div class="stat-value" style="font-size: 16px; font-weight: 700;">&rarr; HOD CSE</div>
        <div class="stat-subtext">Verified requests forward to Head of Dept</div>
    </div>
</div>

<div class="responsive-split-grid">
    <!-- Requisitions Awaiting Inspection -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">🔍 Staff & Lab Indents Awaiting Technical Inspection</div>
            <?php if (count($inspectionQueue) > 0): ?>
                <span class="badge badge-warning"><?= count($inspectionQueue) ?> Pending</span>
            <?php else: ?>
                <span class="badge badge-success">Clear</span>
            <?php endif; ?>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($inspectionQueue)): ?>
                <div class="empty-state-card">
                    <div class="empty-state-icon">✅</div>
                    <div class="empty-state-title">All Indents Inspected</div>
                    <div class="empty-state-sub">Zero staff or lab requisitions are awaiting technical review. All verified requests have been forwarded to HOD.</div>
                </div>
            <?php else: ?>
                <div class="data-table-container">
                    <table class="data-table data-table-compact">
                        <thead>
                            <tr>
                                <th>Indent</th>
                                <th>Staff / Faculty</th>
                                <th>Purpose / Priority</th>
                                <th class="text-right">Amount</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inspectionQueue as $iq): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($iq['request_code']) ?></strong></td>
                                    <td>
                                        <strong><?= htmlspecialchars($iq['requester_name']) ?></strong>
                                        <div style="font-size: 11px; color: #64748b;"><?= htmlspecialchars($iq['lab_name'] ?? 'General') ?></div>
                                    </td>
                                    <td style="font-size: 12px;">
                                        <?= htmlspecialchars(substr($iq['purpose'], 0, 30)) ?>...
                                        <span class="badge badge-neutral" style="font-size: 10px;"><?= htmlspecialchars($iq['priority'] ?? 'Normal') ?></span>
                                    </td>
                                    <td class="text-right"><strong><?= CURRENCY_SYMBOL ?><?= number_format($iq['total_estimated_value']) ?></strong></td>
                                    <td class="text-center">
                                        <a href="index.php?route=view_request&id=<?= $iq['id'] ?>" class="btn btn-primary btn-sm">Inspect &rarr;</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Store Delivery Challans to Acknowledge -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">📦 Store Delivery Challans Awaiting Receipt Acknowledgment</div>
            <?php if (count($inwardVouchers) > 0): ?>
                <span class="badge badge-danger"><?= count($inwardVouchers) ?> To Confirm</span>
            <?php else: ?>
                <span class="badge badge-success">All Received</span>
            <?php endif; ?>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($inwardVouchers)): ?>
                <div class="empty-state-card">
                    <div class="empty-state-icon">📦</div>
                    <div class="empty-state-title">All Deliveries Acknowledged</div>
                    <div class="empty-state-sub">Zero inbound stock deliveries awaiting physical receipt confirmation. All challans cleared.</div>
                </div>
            <?php else: ?>
                <div class="data-table-container">
                    <table class="data-table data-table-compact">
                        <thead>
                            <tr>
                                <th>Voucher #</th>
                                <th>Store</th>
                                <th class="text-right">Date Dispatched</th>
                                <th class="text-center">Acknowledge</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inwardVouchers as $iv): ?>
                                <tr>
                                    <td><code><?= htmlspecialchars($iv['issue_code']) ?></code></td>
                                    <td><strong><?= htmlspecialchars($iv['store_name']) ?></strong></td>
                                    <td class="text-right" style="font-size: 11.5px;"><?= date('d M Y', strtotime($iv['issue_date'])) ?></td>
                                    <td class="text-center">
                                        <form method="POST" action="index.php?route=ack_issue">
                                            <input type="hidden" name="issue_id" value="<?= $iv['id'] ?>">
                                            <input type="hidden" name="dept_id" value="<?= $deptId ?>">
                                            <button type="submit" class="btn btn-success btn-sm">✓ Confirm</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
