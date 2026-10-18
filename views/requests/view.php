<?php
/**
 * MSEC SMART STOCK - Stock Requisition Lifecycle & Operational Gateway
 * Implements Section 9, 23 & 42: Complete multi-tier lifecycle including Coordinator verification,
 * HOD authorization, Store Keeper Price & Quotation Approval Sheet generation, and Executive Sanctions.
 */

$requestId = (int)($_GET['id'] ?? 1);
$req = StockRequest::getById($requestId);
if (!$req) {
    echo "<div class='page-header'><h2>Requisition Not Found</h2><p>The requested requisition record does not exist.</p></div>";
    return;
}

$items = StockRequest::getItems($requestId);
$approvals = StockRequest::getApprovals($requestId);
$currentUser = Auth::user();
$currentRole = $currentUser['role_key'] ?? '';
$pdo = Database::getInstance();

// Check if an official Store Approval Sheet already exists
$existingSheet = ApprovalSheet::getByRequestId($requestId);

// Find active pending approval level
$activeApproval = null;
foreach ($approvals as $appr) {
    if ($appr['status'] === 'Pending') {
        $activeApproval = $appr;
        break;
    }
}

// Check authorization to approve
$canApprove = $activeApproval && ($activeApproval['approver_role'] === $currentRole || in_array($currentRole, ['super_admin', 'admin'], true));
$isStoreKeeperActive = $activeApproval && ($activeApproval['approver_role'] === 'store_keeper');
$vendors = $pdo->query("SELECT id, company_name, gst_number FROM vendors WHERE status = 'Active'")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-header">
    <div>
        <a href="index.php?route=requests" style="font-size: 13px; color: var(--primary); text-decoration: none; font-weight: 600;">&larr; Back to Requisitions</a>
        <h2 class="page-title" style="margin-top: 4px;">Requisition: <?= htmlspecialchars($req['request_code']) ?></h2>
        <div class="page-desc">Department: <strong><?= htmlspecialchars($req['dept_name']) ?></strong> &bull; Requested on <?= date('d M Y', strtotime($req['created_at'])) ?></div>
    </div>
    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <button onclick="window.print()" class="btn btn-secondary hide-print">
            🖨️ Print Indent Requisition Document
        </button>

        <?php if ($existingSheet): ?>
            <a href="index.php?route=approval_sheet&id=<?= $existingSheet['id'] ?>" class="btn btn-secondary hide-print" target="_blank">
                📑 View Official Approval Sheet (<?= htmlspecialchars($existingSheet['sheet_number']) ?>)
            </a>
        <?php endif; ?>

        <span class="badge <?= match($req['status']) {
            'APPROVED' => 'badge-success',
            'REJECTED' => 'badge-danger',
            'STORE_VERIFIED' => 'badge-primary',
            'RETURNED' => 'badge-warning',
            default => 'badge-neutral'
        } ?>" style="font-size: 14px; padding: 6px 14px;">
            <?= htmlspecialchars($req['status']) ?>
        </span>

        <?php if (in_array($req['status'], ['APPROVED', 'STORE_VERIFIED']) && Auth::can('issue_stock')): ?>
            <a href="index.php?route=issue_stock&request_id=<?= $req['id'] ?>" class="btn btn-primary hide-print">Generate Store Issue Voucher &rarr;</a>
        <?php endif; ?>
    </div>
</div>

<div class="responsive-split-grid">
    <!-- Left Column: Requisition Meta & Items -->
    <div>
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-header">
                <div class="card-title">Requisition Details & Purpose</div>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; font-size: 13.5px;">
                    <div><strong>Department:</strong> <?= htmlspecialchars($req['dept_name']) ?> (<?= htmlspecialchars($req['dept_code']) ?>)</div>
                    <div><strong>Laboratory:</strong> <?= htmlspecialchars($req['lab_name'] ?? 'General Departmental') ?></div>
                    <div><strong>Requested By:</strong> <?= htmlspecialchars($req['requester_name']) ?> (<?= htmlspecialchars($req['requester_role']) ?>)</div>
                    <div><strong>Required By Date:</strong> <?= htmlspecialchars($req['required_date']) ?></div>
                </div>
                <div style="background: #f8fafc; border-left: 4px solid #102a43; padding: 12px 16px; border-radius: 6px; font-size: 13.5px;">
                    <strong>Purpose:</strong> <?= htmlspecialchars($req['purpose']) ?>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-title">Requested Line Items</div>
                <span style="font-weight: 700; color: #0f172a; font-size: 14px;">Total Value: <?= CURRENCY_SYMBOL ?><?= number_format($req['total_estimated_value'], 2) ?></span>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="data-table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Item Code</th>
                                <th>Item Name</th>
                                <th>Requested Qty</th>
                                <th>Approved Qty</th>
                                <th>Est. Unit Cost</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $it): ?>
                                <tr>
                                    <td><code><?= htmlspecialchars($it['item_code']) ?></code></td>
                                    <td>
                                        <strong><?= htmlspecialchars($it['item_name']) ?></strong>
                                        <div style="font-size: 11px; color: #64748b;"><?= htmlspecialchars($it['notes']) ?></div>
                                    </td>
                                    <td><strong><?= number_format($it['requested_quantity'], 0) ?></strong> <?= htmlspecialchars($it['unit_code']) ?></td>
                                    <td><?= number_format($it['approved_quantity'], 0) ?> <?= htmlspecialchars($it['unit_code']) ?></td>
                                    <td><?= CURRENCY_SYMBOL ?><?= number_format($it['unit_cost'], 2) ?></td>
                                    <td><strong><?= CURRENCY_SYMBOL ?><?= number_format($it['requested_quantity'] * $it['unit_cost'], 2) ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Institutional Workflow Stepper & Approval Actions -->
    <div>
        <div class="card">
            <div class="card-header">
                <div class="card-title">Institutional Approval Workflow Pipeline</div>
                <span class="badge badge-primary">Locked Matrix</span>
            </div>
            <div class="card-body">
                <div style="display: flex; flex-direction: column; gap: 20px;">
                    <?php foreach ($approvals as $idx => $appr): 
                        $statusBg = match($appr['status']) {
                            'Approved' => '#dcfce7',
                            'Pending'  => '#fef3c7',
                            'Rejected' => '#fee2e2',
                            'Returned' => '#ffedd5',
                            default    => '#f1f5f9'
                        };
                        $statusColor = match($appr['status']) {
                            'Approved' => '#15803d',
                            'Pending'  => '#b45309',
                            'Rejected' => '#b91c1c',
                            'Returned' => '#c2410c',
                            default    => '#64748b'
                        };
                    ?>
                        <div style="display: flex; gap: 14px; position: relative;">
                            <div style="width: 32px; height: 32px; border-radius: 50%; background: <?= $statusBg ?>; color: <?= $statusColor ?>; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; flex-shrink: 0; border: 2px solid <?= $statusColor ?>;">
                                <?= $appr['status'] === 'Approved' ? '✓' : ($idx + 1) ?>
                            </div>
                            <div style="flex-grow: 1;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <strong style="font-size: 13.5px;"><?= htmlspecialchars($appr['level_title']) ?></strong>
                                    <span class="badge" style="background: <?= $statusBg ?>; color: <?= $statusColor ?>; font-size: 11px;">
                                        <?= htmlspecialchars($appr['status']) ?>
                                    </span>
                                </div>
                                <div style="font-size: 12px; color: #64748b; margin-top: 2px;">
                                    Required Role: <code><?= htmlspecialchars($appr['approver_role']) ?></code>
                                    <?php if (!empty($appr['approver_name'])): ?>
                                        &bull; Processed by <strong><?= htmlspecialchars($appr['approver_name']) ?></strong>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($appr['comments'])): ?>
                                    <div style="font-size: 12px; font-style: italic; background: #f8fafc; padding: 6px 10px; border-radius: 4px; margin-top: 6px; border: 1px solid #e2e8f0;">
                                        "<?= htmlspecialchars($appr['comments']) ?>"
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Interactive Action Panel for Authorized Approver -->
                <?php if ($canApprove): ?>
                    <div style="margin-top: 28px; padding-top: 20px; border-top: 2px dashed #cbd5e1;">
                        <h4 style="font-size: 14px; font-weight: 700; color: #1e293b; margin-bottom: 12px;">
                            🎯 Your Action Required as <u><?= htmlspecialchars(ROLES[$currentRole]['name'] ?? $currentRole) ?></u>:
                        </h4>

                        <!-- Special Action for Store Keeper (Level 3 Gate): Generate Official Approval Sheet -->
                        <?php if ($isStoreKeeperActive && !$existingSheet): ?>
                            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; padding: 14px; border-radius: 8px; margin-bottom: 16px;">
                                <strong style="color: #166534; font-size: 13px;">Central Store Keeper Verification Gate:</strong>
                                <p style="font-size: 12px; color: #14532d; margin: 4px 0 10px 0;">
                                    Before forwarding to the Vice Principal/Principal, you must verify existing stock, check item specs, and attach the verified quotation & vendor to generate the official Approval Sheet.
                                </p>
                                <button type="button" class="btn btn-primary" onclick="openApprovalSheetModal()">
                                    📑 Prepare Official Digital Approval Sheet &rarr;
                                </button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="index.php?route=process_approval">
                            <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                            <div class="form-group">
                                <label class="form-label">Review / Sanction Comments *</label>
                                <textarea name="comments" class="form-control" rows="2" placeholder="e.g. Verified technical need and departmental budget quota." required></textarea>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px;">
                                <?php 
                                    $actionBtnText = match($currentRole) {
                                        'hod' => '✓ Endorse & Forward',
                                        'dept_coordinator' => '✓ Verify & Forward',
                                        'vice_principal' => '✓ Concur & Forward',
                                        'principal' => '✓ Recommend to Management',
                                        'director' => '✓ Final Sanction & Allocate',
                                        default => '✓ Approve & Forward'
                                    };
                                ?>
                                <button type="submit" name="action" value="Approve" class="btn btn-success"><?= $actionBtnText ?></button>
                                <button type="submit" name="action" value="Return" class="btn btn-warning">↩ Return for Info</button>
                                <button type="submit" name="action" value="Reject" class="btn btn-danger">✕ Reject</button>
                            </div>
                        </form>
                    </div>
                <?php elseif ($activeApproval): ?>
                    <div style="margin-top: 24px; padding: 14px; background: #f8fafc; border-radius: 8px; font-size: 12px; color: #64748b; text-align: center; border: 1px solid #e2e8f0;">
                        ⏳ Awaiting sanction decision from <strong><?= htmlspecialchars(ROLES[$activeApproval['approver_role']]['name'] ?? $activeApproval['approver_role']) ?></strong>.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Prepare Store Keeper Approval Sheet (Section 9 & 23) -->
<div id="approvalSheetModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.65); z-index:9999; align-items:center; justify-content:center; padding:20px;">
    <div style="background:#ffffff; width:640px; max-width:100%; border-radius:16px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.4); overflow:hidden;">
        <div style="background:#102a43; color:#ffffff; padding:18px 24px; display:flex; justify-content:space-between; align-items:center;">
            <h3 style="margin:0; font-size:17px; font-weight:700;">📑 Prepare Official Store Approval Sheet</h3>
            <button type="button" onclick="closeApprovalSheetModal()" style="background:none; border:none; color:#ffffff; font-size:22px; cursor:pointer;">&times;</button>
        </div>
        <form method="POST" action="index.php?route=create_approval_sheet" style="padding:24px;">
            <input type="hidden" name="request_id" value="<?= $req['id'] ?>">

            <div class="form-group">
                <label class="form-label">Physical Stock Availability in Central Store *</label>
                <textarea name="existing_stock_notes" class="form-control" rows="2" required>Verified central inventory ledger: Zero surplus stock for items 1 and 2 in store.</textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Technical Specification & Quality Compliance *</label>
                <textarea name="spec_verification_notes" class="form-control" rows="2" required>Inspected specifications: Gold-plated HDMI 2.0 and Dell KB216 conform to AI lab standards.</textarea>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
                <div class="form-group">
                    <label class="form-label">Select Verified Vendor *</label>
                    <select name="vendor_id" class="form-control" required>
                        <?php foreach ($vendors as $v): ?>
                            <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['company_name']) ?> (GST: <?= htmlspecialchars($v['gst_number']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Quotation Reference Number *</label>
                    <input type="text" name="quotation_ref" class="form-control" value="QT-MSEC-2026-<?= rand(100, 999) ?>" required>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:12px;">
                <div class="form-group">
                    <label class="form-label">Basic Price (₹)</label>
                    <input type="number" step="0.01" name="unit_price" class="form-control" value="<?= $req['total_estimated_value'] * 0.847 ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">GST Rate (%)</label>
                    <input type="number" name="gst_percent" class="form-control" value="18" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Total Verified Cost (₹)</label>
                    <input type="number" step="0.01" name="total_cost" class="form-control" value="<?= $req['total_estimated_value'] ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Store Keeper Recommendation *</label>
                <input type="text" name="recommendation" class="form-control" value="Recommended for administrative sanction." required>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; border-top:1px solid #e2e8f0; padding-top:16px;">
                <button type="button" class="btn btn-secondary" onclick="closeApprovalSheetModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Generate Approval Sheet & Advance</button>
            </div>
        </form>
    </div>
</div>

<style>
@media print {
    body { background: #ffffff !important; color: #000000 !important; }
    .app-sidebar, .app-header, .hide-print, .btn, .card-header .btn { display: none !important; }
    .app-main { margin: 0 !important; padding: 0 !important; }
    .page-content { padding: 15px !important; }
    .card { border: 1px solid #94a3b8 !important; box-shadow: none !important; margin-bottom: 16px !important; }
    .responsive-split-grid { display: block !important; }
    .print-only-header { display: block !important; }
    .print-signatures-block { display: grid !important; }
}
.print-only-header {
    display: none;
    border-bottom: 2px solid #0f172a;
    padding-bottom: 12px;
    margin-bottom: 20px;
    text-align: center;
}
.print-signatures-block {
    display: none;
    grid-template-columns: repeat(4, 1fr);
    gap: 15px;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 2px dashed #cbd5e1;
    text-align: center;
}
</style>

<div class="print-signatures-block">
    <div style="border: 1px solid #cbd5e1; border-radius: 6px; padding: 14px;">
        <div style="font-size: 11px; color: #64748b; margin-bottom: 30px;">1. INITIATED BY</div>
        <div style="font-weight: 700; font-size: 13px;"><?= htmlspecialchars($req['requester_name']) ?></div>
        <div style="font-size: 11px; color: #64748b;">Lab In-charge / Staff</div>
    </div>
    <div style="border: 1px solid #cbd5e1; border-radius: 6px; padding: 14px;">
        <div style="font-size: 11px; color: #64748b; margin-bottom: 30px;">2. VERIFIED (EXISTING CHECK)</div>
        <div style="font-weight: 700; font-size: 13px;">Dept Stock Coordinator</div>
        <div style="font-size: 11px; color: #64748b;">Specification Verified</div>
    </div>
    <div style="border: 1px solid #cbd5e1; border-radius: 6px; padding: 14px;">
        <div style="font-size: 11px; color: #64748b; margin-bottom: 30px;">3. APPROVED (DEPT SANCTION)</div>
        <div style="font-weight: 700; font-size: 13px;">Head of Department (HOD)</div>
        <div style="font-size: 11px; color: #64748b;">Official Sanction Granted</div>
    </div>
    <div style="border: 1px solid #cbd5e1; border-radius: 6px; padding: 14px;">
        <div style="font-size: 11px; color: #64748b; margin-bottom: 30px;">4. STORE KEEPER GATEWAY</div>
        <div style="font-weight: 700; font-size: 13px;">Central Store Keeper</div>
        <div style="font-size: 11px; color: #64748b;">Catalog & Warehouse Check</div>
    </div>
</div>

<script>
function openApprovalSheetModal() {
    document.getElementById('approvalSheetModal').style.display = 'flex';
}
function closeApprovalSheetModal() {
    document.getElementById('approvalSheetModal').style.display = 'none';
}
</script>
