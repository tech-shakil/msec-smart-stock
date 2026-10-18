<?php
/**
 * MSEC SMART STOCK - IT Asset Tracking & Asset Register
 * Locked Scope: Section 8 (IT Asset Tracking) & Section 18 (Asset Management)
 */

$user = Auth::user();
$statusFilter = $_GET['status'] ?? null;
$assets = Asset::getAll(null, $statusFilter);
$db = Database::getInstance();
$allDepts = $db->query("SELECT * FROM departments ORDER BY name ASC")->fetchAll();
$allLabs = $db->query("SELECT * FROM laboratories ORDER BY name ASC")->fetchAll();
?>

<div class="page-header">
    <div>
        <h2 class="page-title">IT Asset Tracking & Capital Equipment</h2>
        <div class="page-desc">Institutional Asset Register &bull; Hardware Config, MAC/IP, AMC & Lifecycle (Section 8 & 18)</div>
    </div>
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <?php if (Auth::can('onboard_existing_item') || Auth::can('create_asset')): ?>
            <button onclick="openModal('onboardExistingAssetModal')" class="btn btn-warning" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 600;">
                📦 Onboard Existing Equipment
            </button>
        <?php endif; ?>
        <?php if (Auth::can('create_asset')): ?>
            <button onclick="openModal('newAssetModal')" class="btn btn-primary">+ Register New Asset</button>
        <?php endif; ?>
    </div>
</div>

<!-- Quick Status Filter Pills (Section 18.2) -->
<div class="tabs-nav-scroll">
    <a href="index.php?route=assets" class="btn <?= empty($statusFilter) ? 'btn-primary' : 'btn-secondary' ?> btn-sm">All Assets (<?= count($assets) ?>)</a>
    <a href="index.php?route=assets&status=In Use" class="btn <?= $statusFilter === 'In Use' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">In Use</a>
    <a href="index.php?route=assets&status=Available" class="btn <?= $statusFilter === 'Available' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">Available</a>
    <a href="index.php?route=assets&status=Under Repair" class="btn <?= $statusFilter === 'Under Repair' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">⚠️ Under Repair</a>
    <a href="index.php?route=assets&status=Under Maintenance" class="btn <?= $statusFilter === 'Under Maintenance' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">Maintenance</a>
    <a href="index.php?route=assets&status=Condemned" class="btn <?= $statusFilter === 'Condemned' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">Condemned / Disposal</a>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">Institutional IT Hardware & Equipment Register</div>
        <div>
            <input type="text" class="form-control table-search-input" data-table="assetTable" placeholder="Search by tag, serial, MAC, IP..." style="width: 260px; padding: 6px 12px; font-size: 13px;">
        </div>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="data-table-container">
            <table class="data-table" id="assetTable">
                <thead>
                    <tr>
                        <th>Asset Tag</th>
                        <th>Asset Name & Model</th>
                        <th>Serial / MAC / IP</th>
                        <th>Department / Lab</th>
                        <th>Custodian</th>
                        <th>Purchase Cost</th>
                        <th>Warranty / AMC</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($assets as $ast): 
                        $badgeClass = match($ast['asset_status']) {
                            'In Use' => 'badge-success',
                            'Available' => 'badge-info',
                            'Under Repair', 'Damaged' => 'badge-danger',
                            'Under Maintenance' => 'badge-warning',
                            'Condemned', 'Disposed' => 'badge-neutral',
                            default => 'badge-primary'
                        };
                    ?>
                        <tr>
                            <td><code><?= htmlspecialchars($ast['asset_id']) ?></code></td>
                            <td>
                                <strong><?= htmlspecialchars($ast['asset_name']) ?></strong>
                                <div style="font-size: 11px; color: #64748b;"><?= htmlspecialchars($ast['brand']) ?> <?= htmlspecialchars($ast['model']) ?></div>
                                <?php if (!empty($ast['configuration'])): ?>
                                    <div style="font-size: 10.5px; color: #4338ca; margin-top: 2px;">⚡ <?= htmlspecialchars($ast['configuration']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="font-size: 12px;"><strong>SN:</strong> <?= htmlspecialchars($ast['serial_number'] ?: 'N/A') ?></div>
                                <?php if (!empty($ast['mac_address'])): ?>
                                    <div style="font-size: 11px; color: #64748b;"><strong>MAC:</strong> <?= htmlspecialchars($ast['mac_address']) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($ast['ip_address'])): ?>
                                    <div style="font-size: 11px; color: #0284c7;"><strong>IP:</strong> <?= htmlspecialchars($ast['ip_address']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($ast['dept_code']) ?></strong>
                                <div style="font-size: 11px; color: #64748b;"><?= htmlspecialchars($ast['lab_name'] ?? $ast['location']) ?></div>
                            </td>
                            <td><?= htmlspecialchars($ast['custodian_name'] ?? 'Unassigned') ?></td>
                            <td><strong><?= CURRENCY_SYMBOL ?><?= number_format($ast['purchase_cost'], 2) ?></strong></td>
                            <td>
                                <div style="font-size: 12px;">Till: <?= htmlspecialchars($ast['warranty_expiry'] ?: 'N/A') ?></div>
                                <?php if (!empty($ast['amc_details'])): ?>
                                    <div style="font-size: 10.5px; color: #059669;"><?= htmlspecialchars($ast['amc_details']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($ast['asset_status']) ?></span></td>
                            <td>
                                <?php if (Auth::can('report_damage')): ?>
                                    <button onclick="openServiceModal(<?= $ast['id'] ?>, '<?= htmlspecialchars(addslashes($ast['asset_name'])) ?>', '<?= htmlspecialchars($ast['asset_id']) ?>')" class="btn btn-secondary btn-sm">Log Issue</button>
                                <?php else: ?>
                                    <span class="badge badge-neutral" style="font-size: 11px;">View Only</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (Auth::can('create_asset')): ?>
<!-- Modal: Register Asset (Section 18.1) -->
<div id="newAssetModal" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); z-index: 999; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div style="background: white; border-radius: 12px; width: 680px; max-width: 95%; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div style="padding: 18px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
            <h3 style="font-size: 16px; font-weight: 700;">Asset Registration & IT Spec Tagging (Section 8 & 18.1)</h3>
            <button onclick="closeModal('newAssetModal')" style="border: none; background: none; font-size: 20px; cursor: pointer;">&times;</button>
        </div>
        <form method="POST" action="index.php?route=create_asset" style="padding: 24px;">
            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 14px;">
                <div class="form-group">
                    <label class="form-label">Asset Identifier / Tag</label>
                    <input type="text" name="asset_id" class="form-control" required placeholder="e.g. MSEC-CSE-PC-004" value="MSEC-CSE-PC-00<?= rand(10, 99) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Asset Name</label>
                    <input type="text" name="asset_name" class="form-control" required placeholder="e.g. Dell OptiPlex 7090 Desktop Workstation">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-control" required>
                        <option value="6">IT Equipment & Hardware</option>
                        <option value="7">Lab Instruments</option>
                        <option value="8">Furniture</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Brand</label>
                    <input type="text" name="brand" class="form-control" placeholder="Dell / Cisco / HP" value="Dell">
                </div>
                <div class="form-group">
                    <label class="form-label">Model / Part No.</label>
                    <input type="text" name="model" class="form-control" placeholder="OptiPlex 7090">
                </div>
            </div>

            <!-- IT Spec Tagging (Section 8) -->
            <div style="background: #f1f5f9; padding: 14px; border-radius: 8px; margin-bottom: 16px;">
                <div style="font-size: 11px; font-weight: 800; color: #475569; text-transform: uppercase; margin-bottom: 8px;">Hardware & Network Identifiers (Section 8 Spec)</div>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                    <div>
                        <label class="form-label" style="font-size: 11px;">Serial Number</label>
                        <input type="text" name="serial_number" class="form-control" placeholder="e.g. SN-88392-DL">
                    </div>
                    <div>
                        <label class="form-label" style="font-size: 11px;">MAC Address</label>
                        <input type="text" name="mac_address" class="form-control" placeholder="e.g. 00:1A:2B:3C:4D:5E">
                    </div>
                    <div>
                        <label class="form-label" style="font-size: 11px;">Static IP Address</label>
                        <input type="text" name="ip_address" class="form-control" placeholder="e.g. 192.168.10.45">
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-top: 8px;">
                    <div>
                        <label class="form-label" style="font-size: 11px;">Processor (CPU)</label>
                        <input type="text" name="cpu_specs" class="form-control" placeholder="Intel Core i7-11700">
                    </div>
                    <div>
                        <label class="form-label" style="font-size: 11px;">RAM & Storage</label>
                        <input type="text" name="ram_specs" class="form-control" placeholder="16GB DDR4 / 512GB NVMe SSD">
                    </div>
                    <div>
                        <label class="form-label" style="font-size: 11px;">OS Installed</label>
                        <input type="text" name="os_installed" class="form-control" placeholder="Ubuntu 22.04 LTS / Win 11 Pro">
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label class="form-label">Department</label>
                    <select name="dept_id" class="form-control" required>
                        <option value="1">CSE</option>
                        <option value="2">ECE</option>
                        <option value="3">EEE</option>
                        <option value="4">MECH</option>
                        <option value="5">CIVIL</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Laboratory</label>
                    <select name="lab_id" class="form-control">
                        <option value="2">AI Lab</option>
                        <option value="1">Programming Lab</option>
                        <option value="3">Network Lab</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Purchase Cost (₹)</label>
                    <input type="number" name="purchase_cost" class="form-control" value="68000" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label class="form-label">Warranty Expiry</label>
                    <input type="date" name="warranty_expiry" class="form-control" value="<?= date('Y-m-d', strtotime('+3 years')) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">AMC Details</label>
                    <input type="text" name="amc_details" class="form-control" value="Dell ProSupport Plus with 4-hr Response">
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 18px;">
                <button type="button" onclick="closeModal('newAssetModal')" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Asset to Master Register</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php if (Auth::can('report_damage')): ?>
<!-- Modal: Log Maintenance / Damage (Section 20) -->
<div id="serviceModal" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); z-index: 999; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div style="background: white; border-radius: 12px; width: 450px; max-width: 90%; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); overflow: hidden;">
        <div style="padding: 18px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
            <h3 style="font-size: 16px; font-weight: 700;">Report Damaged Asset (Section 20)</h3>
            <button onclick="closeModal('serviceModal')" style="border: none; background: none; font-size: 20px; cursor: pointer;">&times;</button>
        </div>
        <form method="POST" action="index.php?route=report_damage" style="padding: 24px;">
            <input type="hidden" name="asset_id" id="svc_asset_id">
            <div style="font-size: 13.5px; margin-bottom: 12px;">
                Asset: <strong id="svc_asset_title"></strong> (<code id="svc_asset_tag"></code>)
            </div>
            <div class="form-group">
                <label class="form-label">Damage / Issue Report</label>
                <textarea name="issue" class="form-control" rows="3" required placeholder="Describe fault (e.g. SMPS power failure, display artifacting, network NIC dropped packets)"></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Initial Assessment Status</label>
                <select name="status" class="form-control">
                    <option value="Repairable">Repairable (Warranty / Vendor Service)</option>
                    <option value="Under Repair">Under Repair</option>
                    <option value="Beyond Repair">Beyond Repair (Recommend Condemnation)</option>
                </select>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeModal('serviceModal')" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-danger">Log Fault & Transfer to Service Bay</button>
            </div>
        </form>
    </div>
</div>

<script>
function openServiceModal(id, name, tag) {
    document.getElementById('svc_asset_id').value = id;
    document.getElementById('svc_asset_title').innerText = name;
    document.getElementById('svc_asset_tag').innerText = tag;
    openModal('serviceModal');
}
</script>
<?php endif; ?>

<?php if (Auth::can('onboard_existing_item') || Auth::can('create_asset')): ?>
<!-- Modal: Onboard Existing Laboratory Equipment (Legacy Registration - No GST/Invoice Required) -->
<div id="onboardExistingAssetModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.65); z-index:9999; align-items:center; justify-content:center; padding:16px; backdrop-filter:blur(4px);">
    <div style="background:#ffffff; width:660px; max-width:100%; max-height:92vh; border-radius:16px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.4); display:flex; flex-direction:column; overflow:hidden;">
        
        <!-- Modal Header -->
        <div style="background:linear-gradient(135deg, #102a43 0%, #1e429f 100%); color:#ffffff; padding:18px 24px; display:flex; justify-content:space-between; align-items:center; flex-shrink:0;">
            <div>
                <div style="font-size:10.5px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; background:rgba(255,255,255,0.2); padding:2px 8px; border-radius:999px; display:inline-block; margin-bottom:4px;">
                    Legacy Equipment Onboarding • No Bills Required
                </div>
                <h3 style="margin:0; font-size:17px; font-weight:700; color:#ffffff;">📦 Register Existing Lab Item / Equipment</h3>
            </div>
            <button type="button" onclick="closeModal('onboardExistingAssetModal')" style="background:none; border:none; color:#ffffff; font-size:24px; cursor:pointer; line-height:1;">&times;</button>
        </div>

        <!-- Form Body -->
        <form method="POST" action="index.php?route=onboard_existing_asset" style="padding:22px 24px; overflow-y:auto; flex:1;">
            <input type="hidden" name="redirect_to" value="assets">

            <!-- Context Info Notice -->
            <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; padding:12px 14px; margin-bottom:16px; display:flex; gap:10px; font-size:12px; color:#166534; line-height:1.45;">
                <span style="font-size:18px; flex-shrink:0;">ℹ️</span>
                <div>
                    <strong>Original Invoices or GST Bills are NOT Required:</strong>
                    For legacy equipment physically located in laboratories where original bills or purchase orders are unavailable, specify the equipment specifications along with an <strong>approximate / estimated cost</strong> to bring it under institutional tracking.
                </div>
            </div>

            <!-- Department & Laboratory Placement -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" style="font-size:12px; font-weight:700;">Department *</label>
                    <select name="dept_id" id="onboard_dept_select" class="form-control" required onchange="filterOnboardLabs()">
                        <?php foreach ($allDepts as $dept): ?>
                            <option value="<?= $dept['id'] ?>" <?= ((int)($user['dept_id'] ?? 1) === (int)$dept['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($dept['name']) ?> (<?= htmlspecialchars($dept['code']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" style="font-size:12px; font-weight:700;">Laboratory / Section *</label>
                    <select name="lab_id" id="onboard_lab_select" class="form-control" required>
                        <?php foreach ($allLabs as $l): ?>
                            <option value="<?= $l['id'] ?>" data-dept="<?= $l['dept_id'] ?>">
                                <?= htmlspecialchars($l['name']) ?> (<?= htmlspecialchars($l['code']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
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
                    <small style="font-size:10.5px; color:#64748b; margin-top:3px; display:block;">Enter best estimated historical or valuation figure</small>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" style="font-size:12px; font-weight:700; color:#0f172a;">Approx. Acquisition Date</label>
                    <input type="date" name="purchase_date" class="form-control" value="<?= date('Y-01-01', strtotime('-2 years')) ?>">
                    <small style="font-size:10.5px; color:#64748b; margin-top:3px; display:block;">Estimated installation / purchase year</small>
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
                <textarea name="configuration" class="form-control" rows="2" placeholder="e.g. Intel Core i5 8th Gen, 16GB RAM, 256GB SSD, Gigabit Ethernet. Legacy machine onboarded by Lab In-Charge."></textarea>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; border-top:1px solid #e2e8f0; margin-top:16px; padding-top:14px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('onboardExistingAssetModal')">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background:#102a43; border-color:#102a43;">
                    ✓ Onboard Equipment to Master Register
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function filterOnboardLabs() {
    var deptSelect = document.getElementById('onboard_dept_select');
    var labSelect = document.getElementById('onboard_lab_select');
    if (!deptSelect || !labSelect) return;
    var selectedDept = deptSelect.value;
    var options = labSelect.options;
    var firstVisible = null;
    for (var i = 0; i < options.length; i++) {
        var opt = options[i];
        var deptId = opt.getAttribute('data-dept');
        if (!deptId || deptId === selectedDept) {
            opt.style.display = '';
            if (!firstVisible) firstVisible = opt;
        } else {
            opt.style.display = 'none';
        }
    }
    if (firstVisible && options[labSelect.selectedIndex] && options[labSelect.selectedIndex].style.display === 'none') {
        labSelect.value = firstVisible.value;
    }
}
document.addEventListener('DOMContentLoaded', filterOnboardLabs);
</script>
<?php endif; ?>
