<?php
/**
 * MSEC SMART STOCK - Stock Movement & Inter-Department Transfers View
 * Locked Scope: Section 13 (Inter-Department Transfer) & Section 14 (Stock Movement Lifecycle)
 */

$user = Auth::user();
$userDeptId = $user['dept_id'] ?? 1;
$userDeptCode = $user['dept_code'] ?? 'CSE';
$userDeptName = $user['dept_name'] ?? 'Computer Science & Engineering';
$isAdminOrStore = in_array($user['role_key'], ['super_admin', 'admin', 'store_keeper'], true);

$pdo = Database::getInstance();
$movements = Movement::getAll();
$items = Inventory::getAllItems();
$allDepartments = $pdo->query("SELECT id, code, name FROM departments ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Map vacant stock across all departments
$deptVacantStockMap = [];
foreach ($allDepartments as $dept) {
    $deptVacantStockMap[$dept['id']] = Inventory::getDepartmentVacantStock($dept['id']);
}
// Add Central Store (dept_id = 0, store_id = 1)
$deptVacantStockMap[0] = $pdo->query("
    SELECT s.item_id, s.available_quantity as vacant_qty,
           i.item_code, i.item_name, i.unit_cost, i.is_consumable,
           u.code as unit_code, c.name as category_name
    FROM stock s
    JOIN items i ON s.item_id = i.id
    JOIN units u ON i.unit_id = u.id
    JOIN item_categories c ON i.category_id = c.id
    WHERE s.store_id = 1 AND s.available_quantity > 0
    ORDER BY i.item_name ASC
")->fetchAll(PDO::FETCH_ASSOC);

$preSelectSource = isset($_GET['source_dept_id']) ? (int)$_GET['source_dept_id'] : ($userDeptId == 2 ? 1 : 2);
$preSelectItem = isset($_GET['item_id']) ? (int)$_GET['item_id'] : null;
?>

<div class="page-header">
    <div>
        <h2 class="page-title">Stock Movement & Department Transfers</h2>
        <div class="page-desc">
            Inter-Department Reallocation &bull; 
            <?php if (!$isAdminOrStore): ?>
                Initiating Requisitions into <strong><?= htmlspecialchars($userDeptName) ?> (<?= htmlspecialchars($userDeptCode) ?>)</strong> from other departments
            <?php else: ?>
                Institutional Multi-Department Relocation & Transit Chain
            <?php endif; ?>
        </div>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="index.php?route=inventory&tab=dept_stocks" class="btn btn-secondary">🏛️ View Live Dept Stocks</a>
        <?php if (Auth::can('create_transfer')): ?>
            <button onclick="openModal('newTransferModal')" class="btn btn-primary">+ Initiate Transfer to <?= htmlspecialchars($userDeptCode) ?></button>
        <?php endif; ?>
    </div>
</div>

<?php
$totalMovements = count($movements);
$inTransit = count(array_filter($movements, fn($m) => in_array($m['status'], ['Dispatched', 'In Transit'])));
$pendingAppr = count(array_filter($movements, fn($m) => in_array($m['status'], ['Submitted', 'Pending'])));
$completed = count(array_filter($movements, fn($m) => in_array($m['status'], ['Closed', 'Acknowledged'])));
?>

<!-- Movement KPI Overview Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background: #e0e7ff; color: #4338ca;">📦</div>
        <div class="stat-content">
            <div class="stat-value"><?= $totalMovements ?> Orders</div>
            <div class="stat-label">Total Movement Cycles</div>
        </div>
    </div>
    <div class="stat-card info">
        <div class="stat-icon" style="background: #e0f2fe; color: #0284c7;">🚚</div>
        <div class="stat-content">
            <div class="stat-value"><?= $inTransit ?> Transfers</div>
            <div class="stat-label">In-Transit Consignments</div>
        </div>
    </div>
    <div class="stat-card warning">
        <div class="stat-icon" style="background: #fef3c7; color: #b45309;">⏳</div>
        <div class="stat-content">
            <div class="stat-value"><?= $pendingAppr ?> Pending</div>
            <div class="stat-label">Awaiting HoD Authorization</div>
        </div>
    </div>
    <div class="stat-card success">
        <div class="stat-icon" style="background: #dcfce7; color: #15803d;">✅</div>
        <div class="stat-content">
            <div class="stat-value"><?= $completed ?> Closed</div>
            <div class="stat-label">Delivered & Acknowledged</div>
        </div>
    </div>
</div>

<!-- Highlighted Workflow Box: Section 13 Keyboard Transfer Example -->
<div style="background: linear-gradient(135deg, #eef2ff, #e0e7ff); border: 1px solid #c7d2fe; border-radius: 12px; padding: 18px 24px; margin-bottom: 24px;">
    <div style="display: flex; align-items: center; justify-content: space-between;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <span style="font-size: 24px;">🔄</span>
            <div>
                <h4 style="font-size: 15px; font-weight: 700; color: #312e81;">Locked Workflow Example: CSE &rarr; ECE Transfer (Section 13)</h4>
                <p style="font-size: 13px; color: #4338ca; margin-top: 2px;">
                    "CSE has 10 extra keyboards and ECE needs 5." Workflow: ECE Request &rarr; CSE HOD Approval &rarr; Central Store Verification &rarr; Dispatch &rarr; ECE Ack &rarr; Stock balances updated.
                </p>
            </div>
        </div>
        <span class="badge badge-primary">Active Flow</span>
    </div>
</div>

<!-- College-Wide Vacant Stock Search & 1-Click Reallocation Explorer -->
<div class="card" style="margin-bottom: 24px; border: 1.5px solid #bae6fd; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.04);">
    <div class="card-header" style="background: linear-gradient(to right, #f0f9ff, #ffffff); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 22px;">🔍</span>
            <div>
                <div style="font-size: 15px; font-weight: 800; color: #0369a1;">Search Vacant Stock Across All Departments</div>
                <div style="font-size: 12px; color: #64748b;">Find idle or unallocated inventory held by other departments and reallocate to <?= htmlspecialchars($userDeptCode) ?> in 1 click</div>
            </div>
        </div>
        <span class="badge badge-info" style="font-size: 11px;">⚡ Live Institutional Surplus Intelligence</span>
    </div>

    <div class="card-body" style="padding: 18px 20px;">
        <div style="display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 12px;">
            <div style="flex: 1; min-width: 260px; position: relative;">
                <input type="text" id="vacantExplorerInput" class="form-control" 
                       placeholder="🔍 Search vacant items (e.g. keyboard, cable, paper, desktop, wire)..." 
                       style="padding-left: 36px; padding-right: 30px; font-size: 14px; height: 42px; border-radius: 10px;"
                       oninput="searchVacantExplorer()">
                <span style="position: absolute; left: 12px; top: 11px; font-size: 16px; color: #64748b;">🔍</span>
                <button type="button" id="clearExplorerBtn" onclick="clearVacantExplorer()" 
                        style="position: absolute; right: 10px; top: 9px; border: none; background: none; font-size: 16px; color: #94a3b8; cursor: pointer; display: none;">✕</button>
            </div>

            <!-- Quick Search Tags -->
            <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                <span style="font-size: 12px; color: #64748b; font-weight: 600;">Popular:</span>
                <button type="button" class="btn btn-secondary btn-sm" onclick="setExplorerQuery('keyboard')" style="font-size: 11px; border-radius: 16px; padding: 4px 10px;">⌨️ Keyboards</button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="setExplorerQuery('cable')" style="font-size: 11px; border-radius: 16px; padding: 4px 10px;">🔌 HDMI Cables</button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="setExplorerQuery('paper')" style="font-size: 11px; border-radius: 16px; padding: 4px 10px;">📄 A4 Sheets</button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="setExplorerQuery('wire')" style="font-size: 11px; border-radius: 16px; padding: 4px 10px;">⚡ Copper Wire</button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="setExplorerQuery('desktop')" style="font-size: 11px; border-radius: 16px; padding: 4px 10px;">🖥️ Desktops</button>
            </div>
        </div>

        <!-- Explorer Results Grid Container -->
        <div id="vacantExplorerResults" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 12px; margin-top: 14px;">
            <!-- Dynamically populated via searchVacantExplorer() -->
        </div>

        <div id="vacantExplorerEmpty" style="display: none; padding: 24px; text-align: center; color: #64748b; font-size: 13px;">
            No vacant stock found matching your query.
        </div>
    </div>
</div>


<div class="card">
    <div class="card-header">
        <div class="card-title">Movement & Inter-Department Transfer Register</div>
        <div>
            <input type="text" class="form-control table-search-input" data-table="movementTable" placeholder="Search movements..." style="width: 240px; padding: 6px 12px; font-size: 13px;">
        </div>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="data-table-container">
            <table class="data-table" id="movementTable">
                <thead>
                    <tr>
                        <th>Movement Code</th>
                        <th>Type</th>
                        <th>Source</th>
                        <th>Destination</th>
                        <th>Initiated By</th>
                        <th>Current Status</th>
                        <th>Notes / Justification</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($movements as $m): 
                        $statusClass = match($m['status']) {
                            'Closed', 'Acknowledged' => 'badge-success',
                            'In Transit', 'Dispatched' => 'badge-info',
                            'Approved', 'Ready for Dispatch' => 'badge-primary',
                            'Rejected' => 'badge-danger',
                            default => 'badge-warning'
                        };
                    ?>
                        <tr>
                            <td><code><?= htmlspecialchars($m['movement_code']) ?></code></td>
                            <td><span class="badge badge-neutral"><?= htmlspecialchars(str_replace('_', ' ', $m['movement_type'])) ?></span></td>
                            <td><strong><?= htmlspecialchars($m['source_dept_code'] ?? 'Central') ?></strong></td>
                            <td><strong><?= htmlspecialchars($m['target_dept_code'] ?? 'Store') ?></strong></td>
                            <td><?= htmlspecialchars($m['initiated_by_name']) ?></td>
                            <td><span class="badge <?= $statusClass ?>"><?= htmlspecialchars($m['status']) ?></span></td>
                            <td><?= htmlspecialchars($m['notes']) ?></td>
                            <td>
                                <div style="display: flex; gap: 6px;">
                                    <?php if ($m['status'] === 'Submitted'): ?>
                                        <?php if (Auth::can('advance_transfer')): ?>
                                            <a href="index.php?route=advance_movement&id=<?= $m['id'] ?>&status=Approved" class="btn btn-primary btn-sm">Approve</a>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Awaiting Approval</span>
                                        <?php endif; ?>
                                    <?php elseif ($m['status'] === 'Approved'): ?>
                                        <?php if (Auth::can('advance_transfer')): ?>
                                            <a href="index.php?route=advance_movement&id=<?= $m['id'] ?>&status=Dispatched" class="btn btn-info btn-sm">Dispatch</a>
                                        <?php else: ?>
                                            <span class="badge badge-info">Approved / Queue</span>
                                        <?php endif; ?>
                                    <?php elseif ($m['status'] === 'Dispatched' || $m['status'] === 'In Transit'): ?>
                                        <?php if (Auth::can('advance_transfer')): ?>
                                            <a href="index.php?route=advance_movement&id=<?= $m['id'] ?>&status=Acknowledged" class="btn btn-success btn-sm">Acknowledge & Close</a>
                                        <?php else: ?>
                                            <span class="badge badge-primary">In Transit</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span style="font-size: 12px; color: #16a34a; font-weight: 600;">✓ Completed</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (Auth::can('create_transfer')): ?>
<!-- Modal: New Inter-Department Transfer (Section 13) -->
<div id="newTransferModal" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); z-index: 999; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div style="background: white; border-radius: 14px; width: 620px; max-width: 95%; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);">
        <div style="padding: 18px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
            <div>
                <h3 style="font-size: 17px; font-weight: 800; color: #0f172a; margin: 0;">
                    <?php if (!$isAdminOrStore): ?>
                        Initiate Inward Transfer (From All &rarr; <?= htmlspecialchars($userDeptCode) ?>)
                    <?php else: ?>
                        Initiate Inter-Department Stock Transfer
                    <?php endif; ?>
                </h3>
                <div style="font-size: 12px; color: #64748b; margin-top: 2px;">
                    Request unallocated or vacant items from other departments into your departmental custody.
                </div>
            </div>
            <button onclick="closeModal('newTransferModal')" style="border: none; background: none; font-size: 22px; cursor: pointer; color: #64748b;">&times;</button>
        </div>

        <form method="POST" action="index.php?route=create_movement" style="padding: 24px;" id="transferForm">
            <input type="hidden" name="movement_type" value="DEPT_TO_DEPT">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <!-- Source Department -->
                <div class="form-group">
                    <label class="form-label" style="font-weight: 700;">Source Department (Transfer From)</label>
                    <select name="source_dept_id" id="sourceDeptSelect" class="form-control" required onchange="onSourceDeptChange(this.value)">
                        <option value="0" <?= $preSelectSource === 0 ? 'selected' : '' ?>>Central Main Warehouse</option>
                        <?php foreach ($allDepartments as $d): 
                            if (!$isAdminOrStore && $d['id'] == $userDeptId) continue; // Don't show own dept as source for dept users
                            $vacantCount = count($deptVacantStockMap[$d['id']] ?? []);
                            $isSelected = ($d['id'] == $preSelectSource);
                        ?>
                            <option value="<?= $d['id'] ?>" <?= $isSelected ? 'selected' : '' ?>>
                                <?= htmlspecialchars($d['code']) ?> - <?= htmlspecialchars($d['name']) ?> (<?= $vacantCount ?> vacant items)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div style="font-size: 11px; color: #64748b; margin-top: 4px;">Select department that has vacant/extra stock.</div>
                </div>

                <!-- Destination Department -->
                <div class="form-group">
                    <label class="form-label" style="font-weight: 700;">Destination Department (Transfer To)</label>
                    <?php if (!$isAdminOrStore): ?>
                        <input type="hidden" name="target_dept_id" value="<?= $userDeptId ?>">
                        <div class="form-control" style="background: #f1f5f9; border: 1.5px solid #cbd5e1; display: flex; align-items: center; justify-content: space-between; font-weight: 700; color: #0f172a;">
                            <span><?= htmlspecialchars($userDeptCode) ?> - <?= htmlspecialchars($userDeptName) ?></span>
                            <span class="badge badge-primary" style="font-size: 10px;">🔒 Your Dept</span>
                        </div>
                        <div style="font-size: 11px; color: #0284c7; margin-top: 4px;">Items will be credited into <?= htmlspecialchars($userDeptCode) ?> store.</div>
                    <?php else: ?>
                        <select name="target_dept_id" class="form-control" required>
                            <?php foreach ($allDepartments as $d): ?>
                                <option value="<?= $d['id'] ?>" <?= $d['id'] == $userDeptId ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($d['code']) ?> - <?= htmlspecialchars($d['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Dynamic Source Vacant Items Chips -->
            <div id="sourceVacantBox" style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 12px 14px; margin-bottom: 18px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                    <div style="font-size: 12px; font-weight: 800; color: #166534; display: flex; align-items: center; gap: 6px;">
                        <span>💡</span> Vacant & Not-In-Use Stock in Selected Source (Click to Select):
                    </div>
                    <span id="sourceDeptTag" class="badge badge-success" style="font-size: 10px;">Live Data</span>
                </div>
                <div id="sourceVacantChips" style="display: flex; flex-wrap: wrap; gap: 6px;">
                    <!-- Populated via JavaScript -->
                </div>
            </div>

            <!-- Item and Quantity Selector -->
            <div class="form-group">
                <label class="form-label" style="font-weight: 700;">Item to Transfer</label>
                <select name="items[0][item_id]" id="transferItemSelect" class="form-control" required onchange="onItemSelectChange(this)">
                    <!-- Populated via JavaScript based on source dept -->
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 14px;">
                <div class="form-group">
                    <label class="form-label" style="font-weight: 700;">Quantity</label>
                    <div class="input-wrap">
                        <input type="number" name="items[0][quantity]" id="transferItemQty" class="form-control" value="5" min="1" required placeholder="Qty">
                    </div>
                    <div id="maxQtyHint" style="font-size: 11px; color: #64748b; margin-top: 4px;">Max available: -</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Transfer Justification / Purpose</label>
                    <input type="text" name="notes" id="transferNotes" class="form-control" required placeholder="e.g. Requisitioning 5 vacant keyboards for lab maintenance" value="Inter-department reallocation: requesting unallocated surplus stock for lab use.">
                </div>
            </div>

            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; margin-top: 10px; font-size: 12px; color: #475569;">
                <strong>📋 Institutional Reallocation Rule:</strong> When this transfer request is approved by the source department HOD and Store Keeper, the stock is dispatched and credited to <?= htmlspecialchars($userDeptCode) ?> without requiring new financial purchase sanction from Management.
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" onclick="closeModal('newTransferModal')" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Submit Inward Transfer Request</button>
            </div>
        </form>
    </div>
</div>

<script>
const DEPT_VACANT_MAP = <?= json_encode($deptVacantStockMap) ?>;
const USER_DEPT_CODE = "<?= htmlspecialchars($userDeptCode) ?>";
const PRE_SELECT_ITEM = <?= $preSelectItem ? (int)$preSelectItem : 'null' ?>;
const ALL_DEPTS = <?= json_encode(array_column($allDepartments, null, 'id')) ?>;

function onSourceDeptChange(deptId) {
    deptId = parseInt(deptId) || 0;
    const items = DEPT_VACANT_MAP[deptId] || [];
    const chipsContainer = document.getElementById('sourceVacantChips');
    const itemSelect = document.getElementById('transferItemSelect');
    chipsContainer.innerHTML = '';
    itemSelect.innerHTML = '';

    if (items.length === 0) {
        chipsContainer.innerHTML = '<span style="font-size: 12px; color: #64748b; font-style: italic;">No unallocated or vacant stock currently reported in this department store.</span>';
        const opt = document.createElement('option');
        opt.value = "";
        opt.textContent = "-- No Vacant Items Available in this Department --";
        itemSelect.appendChild(opt);
        document.getElementById('maxQtyHint').textContent = 'No stock available';
        return;
    }

    items.forEach((it, idx) => {
        // Option
        const opt = document.createElement('option');
        opt.value = it.item_id;
        opt.textContent = `${it.item_name} (${it.item_code}) — Available Vacant: ${it.vacant_qty} ${it.unit_code}`;
        opt.setAttribute('data-max', it.vacant_qty);
        opt.setAttribute('data-name', it.item_name);
        if (PRE_SELECT_ITEM && it.item_id === PRE_SELECT_ITEM) {
            opt.selected = true;
        } else if (idx === 0 && !PRE_SELECT_ITEM) {
            opt.selected = true;
        }
        itemSelect.appendChild(opt);

        // Chip
        const chip = document.createElement('button');
        chip.type = 'button';
        chip.className = 'btn btn-secondary btn-sm';
        chip.style.fontSize = '11px';
        chip.style.padding = '4px 8px';
        chip.style.borderRadius = '20px';
        chip.style.background = '#ffffff';
        chip.style.border = '1px solid #86efac';
        chip.style.color = '#15803d';
        chip.style.fontWeight = '600';
        chip.innerHTML = `${it.item_name}: <strong>${it.vacant_qty} vacant</strong>`;
        chip.onclick = function() {
            itemSelect.value = it.item_id;
            onItemSelectChange(itemSelect);
        };
        chipsContainer.appendChild(chip);
    });

    onItemSelectChange(itemSelect);
}

function onItemSelectChange(selectEl) {
    if (!selectEl || !selectEl.options || selectEl.selectedIndex < 0) return;
    const opt = selectEl.options[selectEl.selectedIndex];
    const maxQty = parseInt(opt.getAttribute('data-max')) || 1;
    const itemName = opt.getAttribute('data-name') || 'items';
    const qtyInput = document.getElementById('transferItemQty');
    
    qtyInput.max = maxQty;
    if (parseInt(qtyInput.value) > maxQty || parseInt(qtyInput.value) <= 0) {
        qtyInput.value = Math.min(5, maxQty);
    }
    document.getElementById('maxQtyHint').textContent = `Maximum transferable from source: ${maxQty} units`;
    document.getElementById('transferNotes').value = `Inter-department reallocation: requesting ${qtyInput.value} vacant ${itemName} from source store into ${USER_DEPT_CODE}.`;
}

// ==========================================
// VACANT STOCK EXPLORER SEARCH (Section 13)
// ==========================================
function setExplorerQuery(q) {
    const input = document.getElementById('vacantExplorerInput');
    if (input) {
        input.value = q;
        searchVacantExplorer();
    }
}

function clearVacantExplorer() {
    const input = document.getElementById('vacantExplorerInput');
    if (input) {
        input.value = '';
        searchVacantExplorer();
    }
}

function searchVacantExplorer() {
    const input = document.getElementById('vacantExplorerInput');
    const container = document.getElementById('vacantExplorerResults');
    const emptyBox = document.getElementById('vacantExplorerEmpty');
    const clearBtn = document.getElementById('clearExplorerBtn');

    if (!input || !container) return;

    const query = input.value.trim().toLowerCase();
    if (clearBtn) clearBtn.style.display = query ? 'block' : 'none';

    container.innerHTML = '';

    // Collect all vacant items across all departments (excluding user's own dept)
    let matchingItems = [];

    for (const [deptIdStr, items] of Object.entries(DEPT_VACANT_MAP)) {
        const deptId = parseInt(deptIdStr);
        // Department users don't transfer from themselves
        if (!<?= $isAdminOrStore ? 'true' : 'false' ?> && deptId === <?= (int)$userDeptId ?>) {
            continue;
        }

        let deptName = 'Central Main Store';
        let deptCode = 'Central';
        if (deptId > 0 && ALL_DEPTS[deptId]) {
            deptName = ALL_DEPTS[deptId].name;
            deptCode = ALL_DEPTS[deptId].code;
        }

        items.forEach(it => {
            const matchesQuery = !query || 
                it.item_name.toLowerCase().includes(query) || 
                it.item_code.toLowerCase().includes(query) || 
                (it.category_name && it.category_name.toLowerCase().includes(query)) ||
                deptName.toLowerCase().includes(query) ||
                deptCode.toLowerCase().includes(query);

            if (matchesQuery) {
                matchingItems.push({
                    dept_id: deptId,
                    dept_name: deptName,
                    dept_code: deptCode,
                    item_id: it.item_id,
                    item_name: it.item_name,
                    item_code: it.item_code,
                    vacant_qty: it.vacant_qty,
                    unit_code: it.unit_code,
                    unit_cost: it.unit_cost,
                    category_name: it.category_name
                });
            }
        });
    }

    if (matchingItems.length === 0) {
        if (emptyBox) emptyBox.style.display = 'block';
        return;
    }

    if (emptyBox) emptyBox.style.display = 'none';

    // Show top matching items
    matchingItems.slice(0, 18).forEach(item => {
        const card = document.createElement('div');
        card.style.background = '#ffffff';
        card.style.borderRadius = '10px';
        card.style.border = '1px solid #bae6fd';
        card.style.padding = '12px 14px';
        card.style.display = 'flex';
        card.style.flexDirection = 'column';
        card.style.justifyContent = 'space-between';
        card.style.gap = '8px';
        card.style.boxShadow = '0 1px 3px rgba(0,0,0,0.05)';

        card.innerHTML = `
            <div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                    <span class="badge" style="background: #0f172a; color: #38bdf8; font-size: 10px; font-weight: 800;">
                        ${item.dept_code}
                    </span>
                    <span class="badge badge-info" style="font-size: 11px; font-weight: 700;">
                        💡 ${item.vacant_qty} ${item.unit_code} Vacant
                    </span>
                </div>
                <div style="font-size: 13px; font-weight: 700; color: #0f172a; margin-top: 2px;">
                    ${escapeHtml(item.item_name)}
                </div>
                <div style="font-size: 11px; color: #64748b;">
                    <code>${escapeHtml(item.item_code)}</code> &bull; ₹${parseFloat(item.unit_cost).toFixed(2)}
                </div>
            </div>
            <div style="display: flex; justify-content: flex-end; margin-top: 4px;">
                <button type="button" class="btn btn-primary btn-sm" style="font-size: 11px; width: 100%; justify-content: center;"
                        onclick="quickInitiateTransfer(${item.dept_id}, ${item.item_id}, ${item.vacant_qty}, '${escapeJs(item.item_name)}')">
                    🔄 Transfer to ${USER_DEPT_CODE}
                </button>
            </div>
        `;
        container.appendChild(card);
    });
}

function quickInitiateTransfer(sourceDeptId, itemId, maxQty, itemName) {
    const srcSelect = document.getElementById('sourceDeptSelect');
    if (srcSelect) {
        srcSelect.value = sourceDeptId;
        onSourceDeptChange(sourceDeptId);
        
        const itemSelect = document.getElementById('transferItemSelect');
        if (itemSelect) {
            itemSelect.value = itemId;
            onItemSelectChange(itemSelect);
        }
    }
    openModal('newTransferModal');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function escapeJs(str) {
    return str.replace(/'/g, "\\'").replace(/"/g, '\\"');
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const srcSelect = document.getElementById('sourceDeptSelect');
    if (srcSelect) {
        onSourceDeptChange(srcSelect.value);
    }
    searchVacantExplorer();
    <?php if (isset($_GET['source_dept_id']) || isset($_GET['item_id'])): ?>
        openModal('newTransferModal');
    <?php endif; ?>
});
</script>
<?php endif; ?>

