<?php
/**
 * MSEC SMART STOCK - Purchase & Procurement Officer Dashboard
 * Section 10: Vendor Registry, Quotation Comparisons, Purchase Orders, and Procurement Pipeline.
 */

$pdo = Database::getInstance();
$user = Auth::user();

// Procurement KPIs
$vendorCount = (int)$pdo->query("SELECT COUNT(*) FROM vendors WHERE status = 'Active'")->fetchColumn();
$openPOs = (int)$pdo->query("SELECT COUNT(*) FROM purchase_orders WHERE status = 'Issued'")->fetchColumn();
$pendingQuotes = (int)$pdo->query("SELECT COUNT(*) FROM quotations WHERE is_selected = 0")->fetchColumn();
$procurementValue = (float)$pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM purchase_orders WHERE status != 'Cancelled'")->fetchColumn();

// Vendor Directory
$vendors = $pdo->query("SELECT * FROM vendors ORDER BY performance_rating DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

// Purchase Orders List
$pos = $pdo->query("
    SELECT po.*, v.company_name as vendor_name 
    FROM purchase_orders po 
    JOIN vendors v ON po.vendor_id = v.id 
    ORDER BY po.id DESC LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Comparative Quotations
$quotes = $pdo->query("
    SELECT q.*, v.company_name as vendor_name, r.request_code 
    FROM quotations q 
    JOIN vendors v ON q.vendor_id = v.id 
    LEFT JOIN stock_requests r ON q.request_id = r.id 
    ORDER BY q.id DESC LIMIT 6
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-header">
    <div>
        <h2 class="page-title">Procurement & Purchase Directorate Dashboard</h2>
        <div class="page-desc">Purchase Officer: <strong><?= htmlspecialchars($user['full_name']) ?></strong> &bull; Commercial Procurement & Vendor Management &bull; <?= APP_INSTITUTION ?></div>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="index.php?route=procurement" class="btn btn-primary">+ Generate Purchase Order (PO)</a>
    </div>
</div>

<!-- Procurement KPIs -->
<div class="stats-grid">
    <div class="stat-card primary">
        <div class="stat-title">Active Approved Vendors</div>
        <div class="stat-value"><?= number_format($vendorCount) ?></div>
        <div class="stat-subtext">Registered with verified GSTIN/PAN</div>
    </div>
    <div class="stat-card warning">
        <div class="stat-title">Open Purchase Orders</div>
        <div class="stat-value"><?= number_format($openPOs) ?></div>
        <div class="stat-subtext">Issued POs awaiting material delivery</div>
    </div>
    <div class="stat-card info">
        <div class="stat-title">Quotations in Pipeline</div>
        <div class="stat-value"><?= number_format($pendingQuotes) ?></div>
        <div class="stat-subtext">Under commercial comparison</div>
    </div>
    <div class="stat-card success">
        <div class="stat-title">Total PO Value (FY 2026-27)</div>
        <div class="stat-value"><?= CURRENCY_SYMBOL ?><?= number_format($procurementValue) ?></div>
        <div class="stat-subtext">Institutional orders committed</div>
    </div>
</div>

<div class="responsive-split-grid">
    <!-- Quotation Comparative Statement -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">📊 Quotation Comparative Statements</div>
            <a href="index.php?route=procurement" class="badge badge-primary">Manage Quotes</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($quotes)): ?>
                <div class="empty-state-card">
                    <div class="empty-state-icon">📊</div>
                    <div class="empty-state-title">No Quotations Under Review</div>
                    <div class="empty-state-sub">Commercial quotation bids from vendors will be compared here for L1 selection.</div>
                </div>
            <?php else: ?>
                <div class="data-table-container">
                    <table class="data-table data-table-compact">
                        <thead>
                            <tr>
                                <th>Quote Ref</th>
                                <th>Vendor</th>
                                <th class="text-right">Total Quote (₹)</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($quotes as $q): ?>
                                <tr>
                                    <td><code><?= htmlspecialchars($q['quote_reference']) ?></code></td>
                                    <td><strong><?= htmlspecialchars($q['vendor_name']) ?></strong></td>
                                    <td class="text-right"><?= CURRENCY_SYMBOL ?><?= number_format($q['total_amount'], 2) ?></td>
                                    <td class="text-center">
                                        <?php if ($q['is_selected']): ?>
                                            <span class="badge badge-success">✓ Selected (L1)</span>
                                        <?php else: ?>
                                            <span class="badge badge-neutral">Competing</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Approved Vendors List -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">🏢 Institutional Approved Suppliers</div>
            <span class="badge badge-success">Verified GSTIN</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($vendors)): ?>
                <div class="empty-state-card">
                    <div class="empty-state-icon">🏢</div>
                    <div class="empty-state-title">No Suppliers Enrolled</div>
                    <div class="empty-state-sub">Registered vendors with valid GSTIN and institutional ratings will appear here.</div>
                </div>
            <?php else: ?>
                <div class="data-table-container">
                    <table class="data-table data-table-compact">
                        <thead>
                            <tr>
                                <th>Supplier</th>
                                <th>GSTIN</th>
                                <th class="text-center">Rating</th>
                                <th>Categories</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vendors as $v): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($v['company_name']) ?></strong>
                                        <div style="font-size: 11px; color: #64748b;"><?= htmlspecialchars($v['contact_person']) ?> &bull; <?= htmlspecialchars($v['mobile_number']) ?></div>
                                    </td>
                                    <td><code><?= htmlspecialchars($v['gst_number']) ?></code></td>
                                    <td class="text-center"><strong style="color: #d97706;">★ <?= $v['performance_rating'] ?></strong></td>
                                    <td style="font-size: 11.5px;"><?= htmlspecialchars(substr($v['product_categories'] ?? '', 0, 25)) ?>...</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
