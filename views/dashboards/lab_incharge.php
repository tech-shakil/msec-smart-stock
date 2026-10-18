<?php
/**
 * MSEC SMART STOCK - Laboratory Incharge Dashboard
 * Section 14: Laboratory Inventory, Serialized Computing Assets, Consumable Indents,
 * Service Due Equipment, and Equipment Damage Reporting.
 */

$pdo = Database::getInstance();
$user = Auth::user();
$labId = $user['lab_id'] ?? 2;

// Lab Information
$lab = $pdo->query("SELECT * FROM laboratories WHERE id = {$labId}")->fetch(PDO::FETCH_ASSOC) ?: [
    'name' => 'AI & Deep Learning Laboratory', 'location' => 'Main Block - Floor 3 - Lab 304'
];

// Lab Assets
$labAssets = $pdo->query("SELECT * FROM assets WHERE lab_id = {$labId} ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
$totalLabAssets = count($labAssets);
$activeWorking = 0;
$underRepair = 0;
foreach ($labAssets as $a) {
    if ($a['asset_status'] === 'In Use') $activeWorking++;
    if ($a['asset_status'] === 'Under Repair' || $a['asset_status'] === 'Under Maintenance') $underRepair++;
}

// Lab Requisitions
$myRequests = $pdo->query("
    SELECT * FROM stock_requests 
    WHERE lab_id = {$labId} OR requested_by = {$user['id']}
    ORDER BY id DESC LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-header">
    <div>
        <h2 class="page-title">Laboratory Operations & Asset Management Hub</h2>
        <div class="page-desc">Incharge: <strong><?= htmlspecialchars($user['full_name']) ?></strong> &bull; <?= htmlspecialchars($lab['name']) ?> (<?= htmlspecialchars($lab['location']) ?>)</div>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <button type="button" class="btn btn-primary" onclick="openOnboardModal()" style="background: #102a43; border-color: #102a43;">
            📦 + Onboard Existing Equipment
        </button>
        <button type="button" class="btn btn-secondary" onclick="openDamageReportModal()">⚠️ Report Damaged Equipment</button>
        <a href="index.php?route=new_request" class="btn btn-primary">+ Raise Lab Consumables Indent</a>
    </div>
</div>

<!-- Legacy & Existing Equipment Registration Advisory Banner -->
<div class="card" style="background: linear-gradient(to right, #eff6ff, #f8fafc); border-left: 4px solid #2563eb; margin-bottom: 20px; padding: 14px 18px;">
    <div style="display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <span style="font-size: 24px;">📦</span>
            <div>
                <strong style="color: #1e3a8a; font-size: 13.5px;">Legacy & Existing Lab Equipment Onboarding</strong>
                <p style="margin: 2px 0 0 0; font-size: 12px; color: #475569;">
                    Have hardware workstations, oscilloscopes, or instruments already present in <strong><?= htmlspecialchars($lab['name']) ?></strong>? Original GST invoices/bills are <strong>not required</strong> (approximate/estimated costs accepted).
                </p>
            </div>
        </div>
        <button type="button" class="btn btn-sm btn-primary" onclick="openOnboardModal()">+ Add Existing Item</button>
    </div>
</div>

<!-- Lab KPIs -->
<div class="stats-grid">
    <div class="stat-card primary">
        <div class="stat-title">Registered Lab Systems</div>
        <div class="stat-value"><?= number_format($totalLabAssets) ?></div>
        <div class="stat-subtext">Workstations, Switches & Displays</div>
    </div>
    <div class="stat-card success">
        <div class="stat-title">Fully Operational (100% Uptime)</div>
        <div class="stat-value"><?= number_format($activeWorking) ?></div>
        <div class="stat-subtext">Verified ready for student practicals</div>
    </div>
    <div class="stat-card danger">
        <div class="stat-title">Equipment In Service Bay</div>
        <div class="stat-value"><?= number_format($underRepair) ?></div>
        <div class="stat-subtext">Fault reported; awaiting technician repair</div>
    </div>
    <div class="stat-card info">
        <div class="stat-title">Lab Indents Raised</div>
        <div class="stat-value"><?= number_format(count($myRequests)) ?></div>
        <div class="stat-subtext">Cables, toners & examination sheets</div>
    </div>
</div>

<div class="responsive-split-grid">
    <!-- Lab Assets Table -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">🖥️ Laboratory Terminals & Equipment Roster</div>
            <span class="badge badge-primary"><?= $totalLabAssets ?> Assigned</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($labAssets)): ?>
                <div class="empty-state-card">
                    <div class="empty-state-icon">🖥️</div>
                    <div class="empty-state-title">No Lab Systems Registered</div>
                    <div class="empty-state-sub">No computing systems or hardware instruments assigned to this laboratory yet.</div>
                </div>
            <?php else: ?>
                <div class="data-table-container">
                    <table class="data-table data-table-compact">
                        <thead>
                            <tr>
                                <th>Asset Tag</th>
                                <th>Description</th>
                                <th>IP Address</th>
                                <th class="text-center">Condition</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($labAssets as $la): ?>
                                <tr>
                                    <td><code><?= htmlspecialchars($la['asset_id']) ?></code></td>
                                    <td>
                                        <strong><?= htmlspecialchars($la['asset_name']) ?></strong>
                                        <div style="font-size: 11px; color: #64748b;"><?= htmlspecialchars($la['configuration'] ?? '') ?></div>
                                        <div style="font-size: 11px; color: #0284c7; font-weight: 600; margin-top: 2px;">Est. Cost: <?= CURRENCY_SYMBOL ?><?= number_format($la['purchase_cost'], 2) ?></div>
                                    </td>
                                    <td><code><?= htmlspecialchars($la['ip_address'] ?? 'DHCP') ?></code></td>
                                    <td class="text-center">
                                        <span class="badge <?= $la['asset_status'] === 'In Use' ? 'badge-success' : 'badge-danger' ?>">
                                            <?= htmlspecialchars($la['asset_status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($la['asset_status'] === 'In Use'): ?>
                                            <button type="button" class="btn btn-secondary btn-sm" style="color: #b91c1c;" onclick="reportThisAsset(<?= $la['id'] ?>, '<?= htmlspecialchars(addslashes($la['asset_id'])) ?>')">
                                                Log Fault
                                            </button>
                                        <?php else: ?>
                                            <span style="font-size: 11.5px; color: #64748b;">In Bay</span>
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

    <!-- Lab Requisitions Tracker -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">📝 Active Laboratory Indents & Vouchers</div>
            <a href="index.php?route=requests" class="badge badge-secondary">All Requisitions</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($myRequests)): ?>
                <div class="empty-state-card">
                    <div class="empty-state-icon">📝</div>
                    <div class="empty-state-title">No Lab Indents Raised</div>
                    <div class="empty-state-sub">Laboratory consumable indents and material requisitions will be tracked here.</div>
                </div>
            <?php else: ?>
                <div class="data-table-container">
                    <table class="data-table data-table-compact">
                        <thead>
                            <tr>
                                <th>Indent</th>
                                <th>Purpose</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Track</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($myRequests as $mr): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($mr['request_code']) ?></strong></td>
                                    <td style="font-size: 11.5px;"><?= htmlspecialchars(substr($mr['purpose'], 0, 30)) ?>...</td>
                                    <td class="text-center"><span class="badge badge-warning"><?= htmlspecialchars($mr['status']) ?></span></td>
                                    <td class="text-center">
                                        <a href="index.php?route=view_request&id=<?= $mr['id'] ?>" class="btn btn-secondary btn-sm">Inspect</a>
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

<!-- Modal: Report Damaged Asset -->
<div id="damageReportModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.65); z-index:9999; align-items:center; justify-content:center; padding:20px;">
    <div style="background:#ffffff; width:520px; max-width:100%; border-radius:16px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.4); overflow:hidden;">
        <div style="background:#102a43; color:#ffffff; padding:18px 24px; display:flex; justify-content:space-between; align-items:center;">
            <h3 style="margin:0; font-size:17px; font-weight:700;">⚠️ Report Equipment Fault / Damage</h3>
            <button type="button" onclick="closeDamageReportModal()" style="background:none; border:none; color:#ffffff; font-size:22px; cursor:pointer;">&times;</button>
        </div>
        <form method="POST" action="index.php?route=report_damage" style="padding:24px;">
            <div class="form-group">
                <label class="form-label">Select Laboratory Equipment *</label>
                <select name="asset_id" id="modal_asset_select" class="form-control" required>
                    <?php foreach ($labAssets as $la): ?>
                        <option value="<?= $la['id'] ?>"><?= htmlspecialchars($la['asset_id']) ?> — <?= htmlspecialchars($la['asset_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Nature of Fault / Problem Observed *</label>
                <textarea name="issue" class="form-control" rows="3" placeholder="Describe symptoms: e.g. SMPS will not power on, display artifacts, network port loose..." required></textarea>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; border-top:1px solid #e2e8f0; padding-top:16px;">
                <button type="button" class="btn btn-secondary" onclick="closeDamageReportModal()">Cancel</button>
                <button type="submit" class="btn btn-danger">Log Fault to Repair Bay</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Onboard Existing Laboratory Equipment (Legacy Registration - No GST/Invoice Required) -->
<div id="onboardExistingAssetModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.65); z-index:9999; align-items:center; justify-content:center; padding:16px; backdrop-filter:blur(4px);">
    <div style="background:#ffffff; width:640px; max-width:100%; max-height:92vh; border-radius:16px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.4); display:flex; flex-direction:column; overflow:hidden;">
        
        <!-- Modal Header -->
        <div style="background:linear-gradient(135deg, #102a43 0%, #1e429f 100%); color:#ffffff; padding:18px 24px; display:flex; justify-content:space-between; align-items:center; flex-shrink:0;">
            <div>
                <div style="font-size:10.5px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; background:rgba(255,255,255,0.2); padding:2px 8px; border-radius:999px; display:inline-block; margin-bottom:4px;">
                    Legacy Equipment Onboarding • No Bills Required
                </div>
                <h3 style="margin:0; font-size:17px; font-weight:700; color:#ffffff;">📦 Register Existing Lab Item / Equipment</h3>
            </div>
            <button type="button" onclick="closeOnboardModal()" style="background:none; border:none; color:#ffffff; font-size:24px; cursor:pointer; line-height:1;">&times;</button>
        </div>

        <!-- Form Body -->
        <form method="POST" action="index.php?route=onboard_existing_asset" style="padding:22px 24px; overflow-y:auto; flex:1;">
            <input type="hidden" name="dept_id" value="<?= (int)($user['dept_id'] ?? 1) ?>">
            <input type="hidden" name="lab_id" value="<?= (int)$labId ?>">
            <input type="hidden" name="redirect_to" value="dashboard">

            <!-- Context Info Notice -->
            <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; padding:12px 14px; margin-bottom:16px; display:flex; gap:10px; font-size:12px; color:#166534; line-height:1.45;">
                <span style="font-size:18px; flex-shrink:0;">ℹ️</span>
                <div>
                    <strong>Original Invoice or GST Bill NOT Required:</strong>
                    For existing equipment already physically installed in <strong><?= htmlspecialchars($lab['name']) ?></strong> where original receipts were lost or archived, provide equipment details and an <strong>approximate/estimated cost</strong> to bring it under official college custody.
                </div>
            </div>

            <!-- Item Name & Category -->
            <div style="display:grid; grid-template-columns:1.6fr 1fr; gap:12px; margin-bottom:12px;">
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" style="font-size:12px; font-weight:700;">Equipment / Item Name *</label>
                    <input type="text" name="asset_name" class="form-control" placeholder="e.g. Dell OptiPlex 7090 Desktop Workstation" required>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" style="font-size:12px; font-weight:700;">Category *</label>
                    <select name="category_id" class="form-control" required>
                        <option value="6">🖥️ IT Equipment & Hardware</option>
                        <option value="7">🔬 Lab Instruments & Machinery</option>
                        <option value="4">🔌 Electronics & Boards</option>
                        <option value="8">🪑 Furniture & Fixtures</option>
                    </select>
                </div>
            </div>

            <!-- Brand & Model -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" style="font-size:12px; font-weight:700;">Brand / Manufacturer</label>
                    <input type="text" name="brand" class="form-control" placeholder="Dell / HP / Cisco / Tektronix">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" style="font-size:12px; font-weight:700;">Model / Part Number</label>
                    <input type="text" name="model" class="form-control" placeholder="OptiPlex 7090 / DS1054Z">
                </div>
            </div>

            <!-- Physical Serial & Asset Tag -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" style="font-size:12px; font-weight:700;">Chassis Serial Number (Optional)</label>
                    <input type="text" name="serial_number" class="form-control" placeholder="Serial no. from sticker/chassis">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" style="font-size:12px; font-weight:700;">Asset Tag (Leave blank to auto-generate)</label>
                    <input type="text" name="asset_id" class="form-control" placeholder="e.g. MSEC-CSE-LINUX-EQ-01">
                </div>
            </div>

            <!-- Approximate Cost & Estimated Year -->
            <div style="display:grid; grid-template-columns:1.2fr 1fr; gap:12px; margin-bottom:12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:12px;">
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" style="font-size:12px; font-weight:700; color:#0f172a;">
                        Approximate / Estimated Cost (₹) *
                    </label>
                    <input type="number" step="0.01" name="purchase_cost" class="form-control" placeholder="e.g. 45000" required>
                    <small style="font-size:10.5px; color:#64748b; margin-top:3px; display:block;">Enter best estimated historical value</small>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" style="font-size:12px; font-weight:700; color:#0f172a;">Approx. Acquisition Date</label>
                    <input type="date" name="purchase_date" class="form-control" value="<?= date('Y-01-01', strtotime('-2 years')) ?>">
                    <small style="font-size:10.5px; color:#64748b; margin-top:3px; display:block;">Approximate installation date</small>
                </div>
            </div>

            <!-- Location & Operating Status -->
            <div style="display:grid; grid-template-columns:1.2fr 1fr; gap:12px; margin-bottom:12px;">
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" style="font-size:12px; font-weight:700;">Physical Location inside Lab *</label>
                    <input type="text" name="location" class="form-control" placeholder="e.g. Row 1 - Terminal 05 / Test Bench 2" value="Terminal Bench" required>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" style="font-size:12px; font-weight:700;">Current Working Condition</label>
                    <select name="asset_status" class="form-control">
                        <option value="Available">Available & 100% Operational</option>
                        <option value="In Use" selected>In Active Use by Students</option>
                        <option value="Under Maintenance">Needs Maintenance / Servicing</option>
                    </select>
                </div>
            </div>

            <!-- Hardware Specifications / Technical Notes -->
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label" style="font-size:12px; font-weight:700;">Hardware Specs / Legacy Notes (Optional)</label>
                <textarea name="configuration" class="form-control" rows="2" placeholder="e.g. Intel Core i5 8th Gen, 16GB RAM, 256GB SSD, Gigabit Ethernet. Verified physical presence by Lab In-Charge."></textarea>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; border-top:1px solid #e2e8f0; margin-top:16px; padding-top:14px;">
                <button type="button" class="btn btn-secondary" onclick="closeOnboardModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background:#102a43; border-color:#102a43;">
                    ✓ Onboard Equipment to Lab Stock
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openDamageReportModal() {
    document.getElementById('damageReportModal').style.display = 'flex';
}
function closeDamageReportModal() {
    document.getElementById('damageReportModal').style.display = 'none';
}
function reportThisAsset(assetId, assetTag) {
    document.getElementById('modal_asset_select').value = assetId;
    openDamageReportModal();
}
function openOnboardModal() {
    document.getElementById('onboardExistingAssetModal').style.display = 'flex';
}
function closeOnboardModal() {
    document.getElementById('onboardExistingAssetModal').style.display = 'none';
}
</script>
