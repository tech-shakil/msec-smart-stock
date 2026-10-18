<?php
/**
 * MSEC SMART STOCK - Director Executive Dashboard
 * Section 5: Complete Institutional Visibility, Major Capital Approvals (> ₹1,00,000),
 * Department Expenditure, and Trust Governance.
 */

$pdo = Database::getInstance();
$user = Auth::user();

// Live Institutional Metrics
$totalStockValue = (float)$pdo->query("
    SELECT COALESCE(SUM(s.available_quantity * i.unit_cost), 0) 
    FROM stock s 
    JOIN items i ON s.item_id = i.id
")->fetchColumn();

$totalAssets = (int)$pdo->query("SELECT COUNT(*) FROM assets")->fetchColumn();
$totalAssetValue = (float)$pdo->query("SELECT COALESCE(SUM(purchase_cost), 0) FROM assets")->fetchColumn();
$pendingCapitalSanctions = (int)$pdo->query("
    SELECT COUNT(*) FROM stock_requests 
    WHERE total_estimated_value > 100000 AND status NOT IN ('APPROVED', 'REJECTED', 'COMPLETED')
")->fetchColumn();

$deptBudgets = Budget::getAll();
$budgetSummary = Budget::getSummary();

// High-Value Requisitions Queue
$capitalRequests = $pdo->query("
    SELECT r.*, d.name as dept_name, d.code as dept_code, u.full_name as requester_name
    FROM stock_requests r
    JOIN departments d ON r.dept_id = d.id
    JOIN users u ON r.requested_by = u.id
    WHERE r.total_estimated_value >= 100000
    ORDER BY r.id DESC LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Recent Institutional Sanctions
$recentSanctions = $pdo->query("
    SELECT r.*, d.code as dept_code, u.full_name as approver_name
    FROM stock_requests r
    JOIN departments d ON r.dept_id = d.id
    JOIN request_approvals a ON r.id = a.request_id
    LEFT JOIN users u ON a.approver_user_id = u.id
    WHERE a.approver_role IN ('director', 'principal') AND a.status = 'Approved'
    ORDER BY a.action_date DESC LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-header">
    <div>
        <h2 class="page-title">Executive Directorate & Management Sanction Console</h2>
        <div class="page-desc">Director: <strong><?= htmlspecialchars($user['full_name']) ?></strong> &bull; Ultimate Institutional Financial Sanction & Trust Authority &bull; <?= APP_INSTITUTION ?></div>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="index.php?route=reports" class="btn btn-secondary">📈 Statutory Audit & NAAC/NBA Reports</a>
    </div>
</div>

<!-- Directorate KPIs -->
<div class="stats-grid">
    <div class="stat-card primary">
        <div class="stat-title">Total Institutional Stock Value</div>
        <div class="stat-value"><?= CURRENCY_SYMBOL ?><?= number_format($totalStockValue) ?></div>
        <div class="stat-subtext">Across Central & Departmental Stores</div>
    </div>
    <div class="stat-card info">
        <div class="stat-title">Capital Assets Valuation</div>
        <div class="stat-value"><?= CURRENCY_SYMBOL ?><?= number_format($totalAssetValue) ?></div>
        <div class="stat-subtext"><?= number_format($totalAssets) ?> Tagged Physical & IT Assets</div>
    </div>
    <div class="stat-card warning">
        <div class="stat-title">Institutional Sanctions Queue</div>
        <div class="stat-value"><?= number_format($pendingCapitalSanctions) ?></div>
        <div class="stat-subtext">Awaiting Management final approval & fund release</div>
    </div>
    <div class="stat-card success">
        <div class="stat-title">Annual Budget Allocation</div>
        <div class="stat-value"><?= CURRENCY_SYMBOL ?><?= number_format($budgetSummary['total_allocated']) ?></div>
        <div class="stat-subtext">Utilized: <?= CURRENCY_SYMBOL ?><?= number_format($budgetSummary['total_utilized']) ?></div>
    </div>
</div>

<div class="responsive-split-grid">
    <!-- Management Sanction Queue -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">🏛️ Institutional Procurements Awaiting Management Sanction</div>
            <?php if (count($capitalRequests) > 0): ?>
                <span class="badge badge-warning"><?= count($capitalRequests) ?> Under Review</span>
            <?php else: ?>
                <span class="badge badge-success">Clear</span>
            <?php endif; ?>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($capitalRequests)): ?>
                <div class="empty-state-card">
                    <div class="empty-state-icon">🏛️</div>
                    <div class="empty-state-title">No Major Sanctions Pending</div>
                    <div class="empty-state-sub">Zero high-value capital procurements (> ₹1,00,000) currently awaiting directorate review.</div>
                </div>
            <?php else: ?>
                <div class="data-table-container">
                    <table class="data-table data-table-compact">
                        <thead>
                            <tr>
                                <th>Requisition #</th>
                                <th class="text-center">Dept</th>
                                <th class="text-right">Estimated Value</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($capitalRequests as $cr): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($cr['request_code']) ?></strong>
                                        <div style="font-size: 11px; color: #64748b;"><?= htmlspecialchars(substr($cr['purpose'], 0, 30)) ?>...</div>
                                    </td>
                                    <td class="text-center"><span class="badge badge-neutral"><?= htmlspecialchars($cr['dept_code']) ?></span></td>
                                    <td class="text-right"><strong><?= CURRENCY_SYMBOL ?><?= number_format($cr['total_estimated_value']) ?></strong></td>
                                    <td class="text-center"><span class="badge badge-warning"><?= htmlspecialchars($cr['status']) ?></span></td>
                                    <td class="text-center">
                                        <a href="index.php?route=view_request&id=<?= $cr['id'] ?>" class="btn btn-primary btn-sm">Inspect &rarr;</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Department-Wise Budget Utilization -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">💰 Department Budget Allocations & Utilization</div>
            <a href="index.php?route=budget" class="badge badge-primary">Financial Heads</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="data-table-container">
                <table class="data-table data-table-compact">
                    <thead>
                        <tr>
                            <th>Dept Code</th>
                            <th>Head</th>
                            <th class="text-right">Allocated</th>
                            <th class="text-right">Utilized</th>
                            <th class="text-right">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($deptBudgets as $b): 
                            $pct = ($b['allocated_amount'] > 0) ? round(($b['utilized_amount'] / $b['allocated_amount']) * 100) : 0;
                        ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($b['dept_code']) ?></strong></td>
                                <td><span style="font-size: 11px;"><code><?= htmlspecialchars($b['budget_head_code']) ?></code></span></td>
                                <td class="text-right"><?= CURRENCY_SYMBOL ?><?= number_format($b['allocated_amount']) ?></td>
                                <td class="text-right">
                                    <?= CURRENCY_SYMBOL ?><?= number_format($b['utilized_amount']) ?>
                                    <div style="font-size: 10px; color: #64748b;"><?= $pct ?>% used</div>
                                </td>
                                <td class="text-right"><strong style="color: #166534;"><?= CURRENCY_SYMBOL ?><?= number_format($b['available_balance']) ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
