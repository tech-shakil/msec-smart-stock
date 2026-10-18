<?php
/**
 * MSEC SMART STOCK - Central Store Keeper Dashboard
 * Section 9: Central Operational Gateway - Verifies stock, prepares approval sheets,
 * executes GRN receipts, registers serialized assets, and issues stock vouchers.
 */

$pdo = Database::getInstance();
$user = Auth::user();

// KPIs
$pendingStoreReviews = (int)$pdo->query("
    SELECT COUNT(*) FROM stock_requests r 
    JOIN request_approvals a ON r.id = a.request_id 
    WHERE a.status = 'Pending' AND a.approver_role = 'store_keeper'
")->fetchColumn();

$pendingApprovalSheets = (int)$pdo->query("
    SELECT COUNT(*) FROM stock_requests 
    WHERE status IN ('HOD_APPROVED', 'STORE_REVIEW') 
      AND id NOT IN (SELECT request_id FROM approval_sheets)
")->fetchColumn();

$lowStockCount = (int)$pdo->query("
    SELECT COUNT(*) FROM stock s 
    JOIN items i ON s.item_id = i.id 
    WHERE s.available_quantity <= i.min_stock AND s.store_id = 1
")->fetchColumn();

$pendingGRNs = (int)$pdo->query("
    SELECT COUNT(*) FROM purchase_orders 
    WHERE status = 'Issued'
")->fetchColumn();

$pendingTransfers = (int)$pdo->query("
    SELECT COUNT(*) FROM stock_movements 
    WHERE status IN ('Pending Approval', 'In Transit', 'Dispatched')
")->fetchColumn();

$totalStockValuation = (float)$pdo->query("
    SELECT COALESCE(SUM(s.available_quantity * i.unit_cost), 0) 
    FROM stock s 
    JOIN items i ON s.item_id = i.id 
    WHERE s.store_id = 1
")->fetchColumn();

// Queue A: Requisitions Awaiting Store Keeper Verification
$incomingRequests = $pdo->query("
    SELECT r.*, d.name as dept_name, d.code as dept_code, u.full_name as requester_name
    FROM stock_requests r
    JOIN departments d ON r.dept_id = d.id
    JOIN users u ON r.requested_by = u.id
    WHERE r.status IN ('HOD_APPROVED', 'STORE_REVIEW', 'Pending Approval')
    ORDER BY r.id DESC LIMIT 8
")->fetchAll(PDO::FETCH_ASSOC);

// Queue B: Generated Approval Sheets
$approvalSheets = $pdo->query("
    SELECT s.*, r.request_code, d.code as dept_code, v.company_name as vendor_name
    FROM approval_sheets s
    JOIN stock_requests r ON s.request_id = r.id
    JOIN departments d ON r.dept_id = d.id
    LEFT JOIN vendors v ON s.vendor_id = v.id
    ORDER BY s.id DESC LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Queue C: Low Stock Warning Items
$lowStockItems = $pdo->query("
    SELECT i.item_code, i.item_name, s.available_quantity, i.min_stock, u.code as unit_code, i.unit_cost
    FROM stock s
    JOIN items i ON s.item_id = i.id
    JOIN units u ON i.unit_id = u.id
    WHERE s.available_quantity <= i.min_stock AND s.store_id = 1
    LIMIT 6
")->fetchAll(PDO::FETCH_ASSOC);

// Queue D: Active Transfers Under Store Control
$activeTransfers = $pdo->query("
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
        <h2 class="page-title">Central Store Operations & Gateway Hub</h2>
        <div class="page-desc">Central Receiving Warehouse &bull; Custodian: <strong><?= htmlspecialchars($user['full_name']) ?></strong> &bull; <?= APP_INSTITUTION ?></div>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="index.php?route=issue_stock" class="btn btn-primary">+ Generate Issue Voucher</a>
        <a href="index.php?route=procurement" class="btn btn-secondary">📦 Process GRN Inward</a>
    </div>
</div>

<!-- Section 9: Operational KPIs -->
<div class="stats-grid">
    <div class="stat-card warning">
        <div class="stat-title">Awaiting Store Verification</div>
        <div class="stat-value"><?= number_format($pendingStoreReviews) ?></div>
        <div class="stat-subtext">HOD-approved indents requiring review</div>
    </div>
    <div class="stat-card primary">
        <div class="stat-title">Approval Sheets Needed</div>
        <div class="stat-value"><?= number_format($pendingApprovalSheets) ?></div>
        <div class="stat-subtext">Spec & price check before Principal</div>
    </div>
    <div class="stat-card danger">
        <div class="stat-title">Low Stock Buffer Alerts</div>
        <div class="stat-value"><?= number_format($lowStockCount) ?></div>
        <div class="stat-subtext">Items below reserve reorder point</div>
    </div>
    <div class="stat-card info">
        <div class="stat-title">Pending Shipments / GRN</div>
        <div class="stat-value"><?= number_format($pendingGRNs) ?></div>
        <div class="stat-subtext">Open POs awaiting material arrival</div>
    </div>
    <div class="stat-card success">
        <div class="stat-title">Central Store Valuation</div>
        <div class="stat-value"><?= CURRENCY_SYMBOL ?><?= number_format($totalStockValuation) ?></div>
        <div class="stat-subtext">Verified physical stock on hand</div>
    </div>
</div>

<!-- Workflow Section A & B: Store Review Queue & Approval Sheets -->
<div class="responsive-split-grid">
    <!-- Section A: Incoming Requisitions for Verification -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">📥 Indents Under Store Review (Gateway)</div>
            <?php if (count($incomingRequests) > 0): ?>
                <span class="badge badge-warning"><?= count($incomingRequests) ?> Active</span>
            <?php else: ?>
                <span class="badge badge-success">Up to Date</span>
            <?php endif; ?>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($incomingRequests)): ?>
                <div class="empty-state-card">
                    <div class="empty-state-icon">✅</div>
                    <div class="empty-state-title">No Requisitions Awaiting Review</div>
                    <div class="empty-state-sub">All department requisitions have been verified or issued. Gateway queue is clear.</div>
                </div>
            <?php else: ?>
                <div class="data-table-container">
                    <table class="data-table data-table-compact">
                        <thead>
                            <tr>
                                <th>Indent Code</th>
                                <th class="text-center">Dept</th>
                                <th class="text-right">Estimated Value</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($incomingRequests as $req): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($req['request_code']) ?></strong>
                                        <div style="font-size: 11px; color: #64748b;"><?= htmlspecialchars(substr($req['purpose'], 0, 30)) ?>...</div>
                                    </td>
                                    <td class="text-center"><span class="badge badge-neutral"><?= htmlspecialchars($req['dept_code']) ?></span></td>
                                    <td class="text-right"><strong><?= CURRENCY_SYMBOL ?><?= number_format($req['total_estimated_value']) ?></strong></td>
                                    <td class="text-center"><span class="badge badge-warning"><?= htmlspecialchars($req['status']) ?></span></td>
                                    <td class="text-center">
                                        <a href="index.php?route=view_request&id=<?= $req['id'] ?>" class="btn btn-primary btn-sm">Verify &rarr;</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Section B: Official Approval Sheets Generated -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">📑 Official Approval Sheets (Signed)</div>
            <span class="badge badge-success">Printable</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($approvalSheets)): ?>
                <div class="empty-state-card">
                    <div class="empty-state-icon">📑</div>
                    <div class="empty-state-title">No Approval Sheets Generated</div>
                    <div class="empty-state-sub">Approval sheets prepared from indents will be archived here for printing and inspection.</div>
                </div>
            <?php else: ?>
                <div class="data-table-container">
                    <table class="data-table data-table-compact">
                        <thead>
                            <tr>
                                <th>Sheet #</th>
                                <th>Req Code</th>
                                <th>Vendor</th>
                                <th class="text-right">Total Cost</th>
                                <th class="text-center">View</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($approvalSheets as $as): ?>
                                <tr>
                                    <td><code><?= htmlspecialchars($as['sheet_number']) ?></code></td>
                                    <td><strong><?= htmlspecialchars($as['request_code']) ?></strong></td>
                                    <td><span style="font-size: 11.5px;"><?= htmlspecialchars($as['vendor_name'] ?? 'Vendor Quotation') ?></span></td>
                                    <td class="text-right"><strong><?= CURRENCY_SYMBOL ?><?= number_format($as['total_cost']) ?></strong></td>
                                    <td class="text-center">
                                        <a href="index.php?route=approval_sheet&id=<?= $as['id'] ?>" class="btn btn-secondary btn-sm" target="_blank">🖨️ View</a>
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

<!-- Section C & D: Low Stock Monitoring & Inter-Department Movements -->
<div class="responsive-split-grid" style="margin-top: 20px;">
    <!-- Low Stock Buffer Reorder Panel -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">⚠️ Low-Stock Buffer Monitor</div>
            <a href="index.php?route=inventory" class="badge badge-danger">View Full Catalog</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($lowStockItems)): ?>
                <div class="empty-state-card">
                    <div class="empty-state-icon">📦</div>
                    <div class="empty-state-title">Stock Levels Normal</div>
                    <div class="empty-state-sub">All central inventory items are above their reorder safety thresholds.</div>
                </div>
            <?php else: ?>
                <div class="data-table-container">
                    <table class="data-table data-table-compact">
                        <thead>
                            <tr>
                                <th>Item Code</th>
                                <th>Item Name</th>
                                <th class="text-right">Current Stock</th>
                                <th class="text-right">Min Buffer</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lowStockItems as $ls): ?>
                                <tr>
                                    <td><code><?= htmlspecialchars($ls['item_code']) ?></code></td>
                                    <td><strong><?= htmlspecialchars($ls['item_name']) ?></strong></td>
                                    <td class="text-right"><span style="color: #dc2626; font-weight: 700;"><?= number_format($ls['available_quantity']) ?> <?= htmlspecialchars($ls['unit_code']) ?></span></td>
                                    <td class="text-right"><?= number_format($ls['min_stock']) ?> <?= htmlspecialchars($ls['unit_code']) ?></td>
                                    <td class="text-center">
                                        <a href="index.php?route=new_request" class="btn btn-secondary btn-sm">+ Reorder</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Inter-Department Stock Transfers Under Store Control -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">🔄 Inter-Department Transfers (Gateway Controlled)</div>
            <a href="index.php?route=movement" class="badge badge-primary">Transfer Ledger</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($activeTransfers)): ?>
                <div class="empty-state-card">
                    <div class="empty-state-icon">🔄</div>
                    <div class="empty-state-title">No Active Transfers</div>
                    <div class="empty-state-sub">No inter-department stock movements currently in transit or awaiting approval.</div>
                </div>
            <?php else: ?>
                <div class="data-table-container">
                    <table class="data-table data-table-compact">
                        <thead>
                            <tr>
                                <th>Transfer #</th>
                                <th>Routing</th>
                                <th class="text-center">Status</th>
                                <th>Initiator</th>
                                <th class="text-center">Control</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activeTransfers as $tr): ?>
                                <tr>
                                    <td><code><?= htmlspecialchars($tr['movement_code']) ?></code></td>
                                    <td>
                                        <strong><?= htmlspecialchars($tr['src_code'] ?? 'Central') ?></strong> &rarr; 
                                        <strong><?= htmlspecialchars($tr['tgt_code'] ?? 'Dept') ?></strong>
                                    </td>
                                    <td class="text-center"><span class="badge badge-warning"><?= htmlspecialchars($tr['status']) ?></span></td>
                                    <td><span style="font-size: 11.5px;"><?= htmlspecialchars($tr['initiator_name']) ?></span></td>
                                    <td class="text-center">
                                        <a href="index.php?route=movement" class="btn btn-secondary btn-sm">Inspect</a>
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
