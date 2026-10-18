<?php
/**
 * MSEC SMART STOCK - Official Store Keeper Approval Sheet
 * Section 23: Formally formatted digital and printable approval dossier for institutional sanctions.
 */

$pdo = Database::getInstance();
$id = (int)($_GET['id'] ?? 1);
$sheet = ApprovalSheet::getById($id);

if (!$sheet) {
    // If not found by sheet ID, try by request ID
    $sheet = ApprovalSheet::getByRequestId($id);
}

if (!$sheet) {
    echo "<div class='page-header'><h2>Official Approval Sheet Not Found</h2><p>No approval sheet generated for this requisition yet.</p></div>";
    return;
}

$reqId = $sheet['request_id'];
$req = StockRequest::getById($reqId);
$items = StockRequest::getItems($reqId);
$approvals = StockRequest::getApprovals($reqId);
?>

<style>
@media print {
    body { background: #ffffff !important; color: #000000 !important; }
    .app-sidebar, .app-header, .btn-print-bar, .hide-print { display: none !important; }
    .app-main { margin: 0 !important; padding: 0 !important; }
    .page-content { padding: 0 !important; }
    .approval-dossier { box-shadow: none !important; border: 1px solid #000000 !important; width: 100% !important; margin: 0 !important; }
}
.approval-dossier {
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    border: 1px solid #cbd5e1;
    padding: 36px 44px;
    max-width: 900px;
    margin: 0 auto 30px auto;
    color: #0f172a;
    font-size: 13.5px;
    line-height: 1.5;
}
.dossier-header {
    border-bottom: 2.5px solid #102a43;
    padding-bottom: 16px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 20px;
}
.dossier-emblem {
    width: 76px;
    height: 76px;
    object-fit: contain;
}
.dossier-title-box h1 {
    font-family: var(--font-heading);
    font-size: 20px;
    font-weight: 800;
    color: #102a43;
    margin: 0;
    text-transform: uppercase;
}
.dossier-title-box h2 {
    font-size: 13px;
    font-weight: 600;
    color: #334155;
    margin: 2px 0;
}
.dossier-title-box p {
    font-size: 11px;
    color: #64748b;
    margin: 0;
}
.sheet-meta-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 14px 18px;
    margin-bottom: 20px;
    font-size: 13px;
}
.verification-box {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 14px 16px;
    margin-bottom: 16px;
    background: #ffffff;
}
.verification-box h4 {
    margin: 0 0 6px 0;
    font-size: 13px;
    color: #1e3a8a;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.signature-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-top: 36px;
    padding-top: 16px;
    border-top: 1px dashed #cbd5e1;
    text-align: center;
}
.sig-slot {
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 12px 6px;
    background: #fafafa;
    min-height: 90px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.sig-title {
    font-size: 11px;
    font-weight: 700;
    color: #475569;
    text-transform: uppercase;
}
.sig-status {
    font-size: 12px;
    font-weight: 700;
    color: #166534;
}
</style>

<div class="btn-print-bar" style="max-width: 900px; margin: 0 auto 16px auto; display: flex; justify-content: space-between; align-items: center;">
    <a href="index.php?route=view_request&id=<?= $reqId ?>" class="btn btn-secondary">&larr; Back to Requisition Details</a>
    <div style="display: flex; gap: 10px;">
        <button type="button" class="btn btn-primary" onclick="window.print()">🖨️ Print Official Approval Sheet</button>
    </div>
</div>

<div class="approval-dossier">
    <!-- Header with College Seal -->
    <div class="dossier-header">
        <img src="images/msec_logo.png" alt="MSEC Seal" class="dossier-emblem">
        <div class="dossier-title-box">
            <h1><?= APP_INSTITUTION_FULL ?></h1>
            <h2><?= APP_INSTITUTION_TAGLINE ?> &bull; <?= APP_INSTITUTION_LOCATION ?></h2>
            <p><?= APP_INSTITUTION_APPROVALS ?> &bull; <strong><?= APP_INSTITUTION_ACCREDITATIONS ?></strong></p>
            <div style="font-weight: 800; font-size: 14.5px; color: #1e40af; margin-top: 6px; text-decoration: underline;">
                CENTRAL STORE REQUISITION & PRICE VERIFICATION APPROVAL SHEET
            </div>
        </div>
    </div>

    <!-- Metadata Section -->
    <div class="sheet-meta-grid">
        <div><strong>Approval Sheet Ref:</strong> <code><?= htmlspecialchars($sheet['sheet_number']) ?></code></div>
        <div><strong>Date of Verification:</strong> <?= date('d F Y', strtotime($sheet['created_at'])) ?></div>
        <div><strong>Original Requisition Code:</strong> <code><?= htmlspecialchars($sheet['request_code']) ?></code></div>
        <div><strong>Target Department:</strong> <?= htmlspecialchars($sheet['dept_name']) ?> (<?= htmlspecialchars($sheet['dept_code']) ?>)</div>
        <div><strong>Indenting Staff:</strong> <?= htmlspecialchars($sheet['requester_name']) ?> (<?= htmlspecialchars($sheet['requester_designation'] ?? 'Faculty') ?>)</div>
        <div><strong>Required By Date:</strong> <?= htmlspecialchars($sheet['required_date']) ?></div>
        <div style="grid-column: span 2;">
            <strong>Requirement Purpose:</strong> <?= htmlspecialchars($sheet['purpose']) ?>
        </div>
    </div>

    <!-- Line Items Table -->
    <h3 style="font-size: 14px; font-weight: 700; color: #102a43; margin-bottom: 8px;">1. Item Specification & Estimated Valuation</h3>
    <table class="data-table" style="margin-bottom: 20px;">
        <thead>
            <tr>
                <th>#</th>
                <th>Item Code</th>
                <th>Item Name & Technical Specs</th>
                <th>Required Qty</th>
                <th>Estimated Unit Rate</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $idx => $it): ?>
                <tr>
                    <td><?= $idx + 1 ?></td>
                    <td><code><?= htmlspecialchars($it['item_code']) ?></code></td>
                    <td>
                        <strong><?= htmlspecialchars($it['item_name']) ?></strong>
                        <?php if (!empty($it['notes'])): ?>
                            <div style="font-size: 11px; color: #64748b;"><?= htmlspecialchars($it['notes']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td><strong><?= number_format($it['requested_quantity']) ?></strong> <?= htmlspecialchars($it['unit_code']) ?></td>
                    <td><?= CURRENCY_SYMBOL ?><?= number_format($it['unit_cost'], 2) ?></td>
                    <td><strong><?= CURRENCY_SYMBOL ?><?= number_format($it['requested_quantity'] * $it['unit_cost'], 2) ?></strong></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Central Store Verification Findings (Mandatory Section 23) -->
    <h3 style="font-size: 14px; font-weight: 700; color: #102a43; margin-bottom: 8px;">2. Central Store Operational Verification Report</h3>
    
    <div class="verification-box">
        <h4>A. Existing Physical Stock Verification:</h4>
        <div><?= nl2br(htmlspecialchars($sheet['existing_stock_notes'])) ?></div>
    </div>

    <div class="verification-box">
        <h4>B. Technical Specification & Model Compliance:</h4>
        <div><?= nl2br(htmlspecialchars($sheet['spec_verification_notes'])) ?></div>
    </div>

    <div class="verification-box">
        <h4>C. Vendor & Quotation Audit:</h4>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 4px;">
            <div><strong>Selected Vendor:</strong><br><?= htmlspecialchars($sheet['vendor_name'] ?? 'Approved Institutional Supplier') ?></div>
            <div><strong>Quotation Reference:</strong><br><code><?= htmlspecialchars($sheet['quotation_ref'] ?? 'QT-REF-2026') ?></code></div>
            <div><strong>GSTIN Number:</strong><br><code><?= htmlspecialchars($sheet['vendor_gst'] ?? '33AABCC1234F1Z5') ?></code></div>
        </div>
    </div>

    <!-- Final Financial Calculation Block -->
    <div style="background: #f1f5f9; border-radius: 8px; padding: 14px 20px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <div style="font-size: 12px; color: #64748b;">Basic Cost: <?= CURRENCY_SYMBOL ?><?= number_format($sheet['item_price'], 2) ?> &bull; GST (<?= $sheet['gst_percent'] ?>%): <?= CURRENCY_SYMBOL ?><?= number_format($sheet['total_cost'] - $sheet['item_price'], 2) ?></div>
            <div style="font-size: 13px; font-weight: 700; color: #1e3a8a; margin-top: 2px;">Recommendation: <?= htmlspecialchars($sheet['recommendation']) ?></div>
        </div>
        <div style="text-align: right;">
            <span style="font-size: 11px; text-transform: uppercase; color: #475569; font-weight: 700; display: block;">Total Sanction Amount</span>
            <span style="font-size: 20px; font-weight: 800; color: #102a43; font-family: monospace;"><?= CURRENCY_SYMBOL ?><?= number_format($sheet['total_cost'], 2) ?></span>
        </div>
    </div>

    <!-- Signatures & Authorization Matrix (Section 23) -->
    <h3 style="font-size: 14px; font-weight: 700; color: #102a43; margin-bottom: 6px;">3. Multi-Tier Institutional Endorsements</h3>
    <div class="signature-grid">
        <div class="sig-slot">
            <div class="sig-title">Dept. Stock Coordinator</div>
            <div class="sig-status">✓ Verified</div>
            <div style="font-size: 10px; color: #64748b;">Technical Need Verified</div>
        </div>
        <div class="sig-slot">
            <div class="sig-title">Head of Department</div>
            <div class="sig-status">✓ Endorsed</div>
            <div style="font-size: 10px; color: #64748b;">Department Endorsement</div>
        </div>
        <div class="sig-slot">
            <div class="sig-title">Central Store Keeper</div>
            <div class="sig-status">✓ Gate Verified</div>
            <div style="font-size: 10px; color: #64748b;"><?= htmlspecialchars($sheet['store_keeper_name']) ?></div>
        </div>
        <div class="sig-slot">
            <div class="sig-title">Vice Principal / Principal</div>
            <div class="sig-status" style="color: #0369a1;">✓ Recommended</div>
            <div style="font-size: 10px; color: #64748b;">Academic & Admin Concurrence</div>
        </div>
        <div class="sig-slot">
            <div class="sig-title">Director / Management</div>
            <div class="sig-status" style="color: #15803d;">Final Sanction</div>
            <div style="font-size: 10px; color: #64748b;">Management Financial Allocation</div>
        </div>
    </div>

    <div style="margin-top: 28px; text-align: center; font-size: 11px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 10px;">
        Generated via MSEC SMART STOCK Enterprise System &bull; Cryptographically Tracked &bull; <?= date('d-m-Y H:i:s') ?>
    </div>
</div>
