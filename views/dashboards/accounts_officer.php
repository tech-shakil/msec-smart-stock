<?php
/**
 * MSEC SMART STOCK - Accounts & Finance Officer Dashboard
 * Section 11: Department Budget Verification, Ledger Heads, Utilization, and Financial Clearance.
 */

$pdo = Database::getInstance();
$user = Auth::user();

// Live Budget & Financial KPIs
$summary = Budget::getSummary();
$budgets = Budget::getAll();

$pendingFinanceClearing = (int)$pdo->query("
    SELECT COUNT(*) FROM stock_requests 
    WHERE status IN ('STORE_VERIFIED', 'MANAGEMENT_APPROVAL')
")->fetchColumn();

$pendingClearanceRequests = $pdo->query("
    SELECT r.*, d.name as dept_name, d.code as dept_code, u.full_name as requester_name
    FROM stock_requests r
    JOIN departments d ON r.dept_id = d.id
    JOIN users u ON r.requested_by = u.id
    WHERE r.status IN ('STORE_VERIFIED', 'MANAGEMENT_APPROVAL')
    ORDER BY r.id DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-header">
    <div>
        <h2 class="page-title">Accounts & Financial Clearance Dashboard</h2>
        <div class="page-desc">Accounts Officer: <strong><?= htmlspecialchars($user['full_name']) ?></strong> &bull; Institutional Ledger & Department Budget Tracking &bull; <?= APP_INSTITUTION ?></div>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="index.php?route=reports" class="btn btn-secondary">📊 Department Expenditure Reports</a>
    </div>
</div>

<!-- Financial KPIs -->
<div class="stats-grid">
    <div class="stat-card primary">
        <div class="stat-title">Total Sanctioned Budget</div>
        <div class="stat-value"><?= CURRENCY_SYMBOL ?><?= number_format($summary['total_allocated']) ?></div>
        <div class="stat-subtext">Capital & Operational Heads (2026-27)</div>
    </div>
    <div class="stat-card info">
        <div class="stat-title">Utilized Expenditure</div>
        <div class="stat-value"><?= CURRENCY_SYMBOL ?><?= number_format($summary['total_utilized']) ?></div>
        <div class="stat-subtext">Settled vendor invoices & bills</div>
    </div>
    <div class="stat-card warning">
        <div class="stat-title">Committed Purchase Orders</div>
        <div class="stat-value"><?= CURRENCY_SYMBOL ?><?= number_format($summary['total_committed']) ?></div>
        <div class="stat-subtext">Committed pending GRN payment</div>
    </div>
    <div class="stat-card success">
        <div class="stat-title">Available Net Balance</div>
        <div class="stat-value"><?= CURRENCY_SYMBOL ?><?= number_format($summary['total_available']) ?></div>
        <div class="stat-subtext">Liquid institutional funds remaining</div>
    </div>
</div>

<div class="responsive-split-grid">
    <!-- Department Budget Ledger -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">💰 Department Budget Heads & Utilization Ledger</div>
            <span class="badge badge-primary">FY 2026-27</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="data-table-container">
                <table class="data-table data-table-compact">
                    <thead>
                        <tr>
                            <th>Head Code</th>
                            <th class="text-center">Dept</th>
                            <th class="text-center">Category</th>
                            <th class="text-right">Allocated</th>
                            <th class="text-right">Utilized</th>
                            <th class="text-right">Available Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($budgets as $b): ?>
                            <tr>
                                <td><code><?= htmlspecialchars($b['budget_head_code']) ?></code></td>
                                <td class="text-center"><strong><?= htmlspecialchars($b['dept_code']) ?></strong></td>
                                <td class="text-center">
                                    <span class="badge <?= $b['category'] === 'CAPITAL' ? 'badge-primary' : 'badge-neutral' ?>">
                                        <?= htmlspecialchars($b['category']) ?>
                                    </span>
                                </td>
                                <td class="text-right"><?= CURRENCY_SYMBOL ?><?= number_format($b['allocated_amount']) ?></td>
                                <td class="text-right"><?= CURRENCY_SYMBOL ?><?= number_format($b['utilized_amount']) ?></td>
                                <td class="text-right"><strong style="color: #166534;"><?= CURRENCY_SYMBOL ?><?= number_format($b['available_balance']) ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Requisitions Requiring Financial Budget Check -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">🔍 Requisitions Under Financial Audit</div>
            <?php if (count($pendingClearanceRequests) > 0): ?>
                <span class="badge badge-warning"><?= count($pendingClearanceRequests) ?> Pending</span>
            <?php else: ?>
                <span class="badge badge-success">Audited</span>
            <?php endif; ?>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($pendingClearanceRequests)): ?>
                <div class="empty-state-card">
                    <div class="empty-state-icon">✅</div>
                    <div class="empty-state-title">All Requisitions Cleared</div>
                    <div class="empty-state-sub">Zero indents currently pending financial clearance. All requisitions have verified budget allocations.</div>
                </div>
            <?php else: ?>
                <div class="data-table-container">
                    <table class="data-table data-table-compact">
                        <thead>
                            <tr>
                                <th>Indent</th>
                                <th class="text-center">Dept</th>
                                <th class="text-right">Amount</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingClearanceRequests as $pr): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($pr['request_code']) ?></strong></td>
                                    <td class="text-center"><span class="badge badge-neutral"><?= htmlspecialchars($pr['dept_code']) ?></span></td>
                                    <td class="text-right"><strong><?= CURRENCY_SYMBOL ?><?= number_format($pr['total_estimated_value']) ?></strong></td>
                                    <td class="text-center"><span class="badge badge-warning"><?= htmlspecialchars($pr['status']) ?></span></td>
                                    <td class="text-center">
                                        <a href="index.php?route=view_request&id=<?= $pr['id'] ?>" class="btn btn-secondary btn-sm">Audit &rarr;</a>
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
