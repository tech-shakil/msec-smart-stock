<?php
/**
 * MSEC SMART STOCK - Principal Institutional Dashboard
 * Section 6: College-Wide Academic & Institutional Concurrence,
 * Store-Verified Approval Sheets Queue, and Recommendation to Management.
 */

$pdo = Database::getInstance();
$user = Auth::user();

// Live Principal KPIs
$pendingPrincipalApprovals = (int)$pdo->query("
    SELECT COUNT(*) FROM request_approvals 
    WHERE approver_role = 'principal' AND status = 'Pending'
")->fetchColumn();

$storeVerifiedQueue = (int)$pdo->query("
    SELECT COUNT(*) FROM stock_requests 
    WHERE status = 'STORE_VERIFIED'
")->fetchColumn();

$totalDepts = (int)$pdo->query("SELECT COUNT(*) FROM departments")->fetchColumn();
$openTickets = (int)$pdo->query("SELECT COUNT(*) FROM asset_maintenance WHERE status != 'Repaired'")->fetchColumn();

// Principal's Pending Approvals Queue
$approvalQueue = $pdo->query("
    SELECT r.*, d.name as dept_name, d.code as dept_code, u.full_name as requester_name,
           a.id as approval_id, a.level_title, s.sheet_number, s.total_cost, s.recommendation
    FROM stock_requests r
    JOIN request_approvals a ON r.id = a.request_id
    JOIN departments d ON r.dept_id = d.id
    JOIN users u ON r.requested_by = u.id
    LEFT JOIN approval_sheets s ON r.id = s.request_id
    WHERE a.status = 'Pending' AND a.approver_role = 'principal'
    ORDER BY r.id DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Recent Sanctions Signed by Principal
$decisionHistory = $pdo->query("
    SELECT a.*, r.request_code, d.code as dept_code, r.total_estimated_value
    FROM request_approvals a
    JOIN stock_requests r ON a.request_id = r.id
    JOIN departments d ON r.dept_id = d.id
    WHERE a.approver_role = 'principal' AND a.status IN ('Approved', 'Rejected', 'Returned')
    ORDER BY a.action_date DESC LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-header">
    <div>
        <h2 class="page-title">Principal Institutional Sanction & Governance Dashboard</h2>
        <div class="page-desc">Principal: <strong><?= htmlspecialchars($user['full_name']) ?></strong> &bull; Institutional Academic & Administrative Head &bull; <?= APP_INSTITUTION ?></div>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="index.php?route=reports" class="btn btn-secondary">📊 Accreditation & Statutory Reports</a>
    </div>
</div>

<!-- Principal KPIs -->
<div class="stats-grid">
    <div class="stat-card danger">
        <div class="stat-title">Awaiting Institutional Review</div>
        <div class="stat-value"><?= number_format($pendingPrincipalApprovals) ?></div>
        <div class="stat-subtext">For academic concurrence & submission to Management</div>
    </div>
    <div class="stat-card primary">
        <div class="stat-title">Store-Verified Approval Sheets</div>
        <div class="stat-value"><?= number_format($storeVerifiedQueue) ?></div>
        <div class="stat-subtext">Price & spec checks confirmed by Store</div>
    </div>
    <div class="stat-card info">
        <div class="stat-title">Academic Departments</div>
        <div class="stat-value"><?= number_format($totalDepts) ?></div>
        <div class="stat-subtext">CSE, ECE, EEE, MECH, CIVIL & Admin</div>
    </div>
    <div class="stat-card warning">
        <div class="stat-title">Active Equipment Service Tickets</div>
        <div class="stat-value"><?= number_format($openTickets) ?></div>
        <div class="stat-subtext">Under repair in technician bay</div>
    </div>
</div>

<div class="responsive-split-grid">
    <!-- Institutional Approval Queue -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">📝 Institutional Review & Recommendation Queue</div>
            <?php if (count($approvalQueue) > 0): ?>
                <span class="badge badge-danger"><?= count($approvalQueue) ?> Action Required</span>
            <?php else: ?>
                <span class="badge badge-success">Queue Clear</span>
            <?php endif; ?>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($approvalQueue)): ?>
                <div class="empty-state-card">
                    <div class="empty-state-icon">✅</div>
                    <div class="empty-state-title">All Sanctions Cleared</div>
                    <div class="empty-state-sub">Zero high-value department indents are awaiting institutional authorization. All queues are up to date.</div>
                </div>
            <?php else: ?>
                <div class="data-table-container">
                    <table class="data-table data-table-compact">
                        <thead>
                            <tr>
                                <th>Indent Code</th>
                                <th class="text-center">Dept</th>
                                <th class="text-right">Sanction Value</th>
                                <th class="text-center">Store Verification</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($approvalQueue as $aq): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($aq['request_code']) ?></strong>
                                        <div style="font-size: 11px; color: #64748b;"><?= htmlspecialchars(substr($aq['purpose'], 0, 32)) ?>...</div>
                                    </td>
                                    <td class="text-center"><span class="badge badge-neutral"><?= htmlspecialchars($aq['dept_code']) ?></span></td>
                                    <td class="text-right"><strong><?= CURRENCY_SYMBOL ?><?= number_format($aq['total_estimated_value']) ?></strong></td>
                                    <td class="text-center">
                                        <?php if (!empty($aq['sheet_number'])): ?>
                                            <a href="index.php?route=approval_sheet&id=<?= $aq['id'] ?>" class="badge badge-success" target="_blank">
                                                ✓ <?= htmlspecialchars($aq['sheet_number']) ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Awaiting Sheet</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="index.php?route=view_request&id=<?= $aq['id'] ?>" class="btn btn-primary btn-sm">Inspect & Sanction &rarr;</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right Side: Recent Decisions & Policy -->
    <div style="display: flex; flex-direction: column; gap: 20px;">
        <div class="card">
            <div class="card-header">
                <div class="card-title">📜 Recent Institutional Decisions</div>
            </div>
            <div class="card-body" style="padding: 0;">
                <?php if (empty($decisionHistory)): ?>
                    <div class="empty-state-card">
                        <div class="empty-state-icon">📜</div>
                        <div class="empty-state-title">No Decision Records Yet</div>
                        <div class="empty-state-sub">Institutional decisions and sanctions signed by the Principal will appear in this ledger.</div>
                    </div>
                <?php else: ?>
                    <div class="data-table-container">
                        <table class="data-table data-table-compact">
                            <thead>
                                <tr>
                                    <th>Req Code</th>
                                    <th class="text-center">Decision</th>
                                    <th>Remarks</th>
                                    <th class="text-right">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($decisionHistory as $dh): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($dh['request_code']) ?></strong></td>
                                        <td class="text-center">
                                            <span class="badge <?= $dh['status'] === 'Approved' ? 'badge-success' : 'badge-danger' ?>">
                                                <?= htmlspecialchars($dh['status']) ?>
                                            </span>
                                        </td>
                                        <td style="font-size: 11.5px;"><?= htmlspecialchars($dh['comments'] ?? 'Sanctioned') ?></td>
                                        <td class="text-right" style="font-size: 11px;"><?= date('d M', strtotime($dh['action_date'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-title">Institutional Approval Powers</div>
            </div>
            <div class="card-body" style="font-size: 12.5px; line-height: 1.5; color: #334155;">
                <div><strong>Tier 3 (Above ₹25,001):</strong> Full institutional sanction authority following Store Keeper price audit and Vice Principal concurrence.</div>
            </div>
        </div>
    </div>
</div>
