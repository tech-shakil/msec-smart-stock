<?php
/**
 * MSEC SMART STOCK - Stock Requests & Indents View
 * Locked Scope: Section 10 (Stock Request / Indent) & Section 11 (Approval Workflow)
 */

$user = Auth::user();
$userDeptId = $user['dept_id'] ?? 1;
$userDeptCode = $user['dept_code'] ?? 'CSE';
$userDeptName = $user['dept_name'] ?? 'Computer Science & Engineering';

$requests = StockRequest::getAll();
$items = Inventory::getAllItems();
$pdo = Database::getInstance();
$departments = $pdo->query("SELECT id, code, name FROM departments ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$laboratories = $pdo->query("SELECT id, dept_id, code, name FROM laboratories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Map surplus across all departments for each item
$itemSurplusMap = [];
foreach ($items as $it) {
    $itemSurplusMap[$it['id']] = Inventory::getItemSurplusMatrix($it['id']);
}
?>

<div class="page-header">
    <div>
        <h2 class="page-title">Stock Requests & Indents</h2>
        <div class="page-desc">Requisitions, Multilevel Approval Hierarchy & Indent Tracking</div>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <button onclick="openModal('vacantLookupModal')" class="btn btn-secondary">🔍 Search Vacant Stock Across College</button>
        <?php if (Auth::can('raise_indent')): ?>
            <button onclick="openModal('newRequestModal')" class="btn btn-primary">+ Raise New Stock Request</button>
        <?php endif; ?>
    </div>
</div>


<!-- Requests Table -->
<div class="card">
    <div class="card-header">
        <div class="card-title">Stock Requisitions Log</div>
        <div>
            <input type="text" class="form-control table-search-input" data-table="requestsTable" placeholder="Search requests..." style="width: 240px; padding: 6px 12px; font-size: 13px;">
        </div>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="data-table-container">
            <table class="data-table" id="requestsTable">
                <thead>
                    <tr>
                        <th>Request ID</th>
                        <th>Department</th>
                        <th>Requested By</th>
                        <th>Purpose</th>
                        <th>Required Date</th>
                        <th>Estimated Value</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $req): 
                        $statusClass = match($req['status']) {
                            'Approved' => 'badge-success',
                            'Pending Approval' => 'badge-warning',
                            'Fulfilled' => 'badge-primary',
                            'Rejected' => 'badge-danger',
                            default => 'badge-neutral'
                        };
                    ?>
                        <tr>
                            <td><code><?= htmlspecialchars($req['request_code']) ?></code></td>
                            <td>
                                <strong><?= htmlspecialchars($req['dept_code']) ?></strong>
                                <?php if (!empty($req['lab_name'])): ?>
                                    <div style="font-size: 11px; color: #64748b;"><?= htmlspecialchars($req['lab_name']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div><?= htmlspecialchars($req['requester_name']) ?></div>
                                <span class="badge badge-neutral" style="font-size: 10px;"><?= htmlspecialchars($req['requester_role']) ?></span>
                            </td>
                            <td><?= htmlspecialchars($req['purpose']) ?></td>
                            <td><?= htmlspecialchars($req['required_date']) ?></td>
                            <td><strong><?= CURRENCY_SYMBOL ?><?= number_format($req['total_estimated_value'], 2) ?></strong></td>
                            <td><span class="badge <?= $statusClass ?>"><?= htmlspecialchars($req['status']) ?></span></td>
                            <td>
                                <a href="index.php?route=view_request&id=<?= $req['id'] ?>" class="btn btn-secondary btn-sm">Inspect & Track</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- New Request Modal (Section 10) -->
<div id="newRequestModal" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); z-index: 999; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div style="background: white; border-radius: 12px; width: 650px; max-width: 95%; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div style="padding: 18px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
            <h3 style="font-size: 16px; font-weight: 700;">Raise Stock Indent / Requisition (Section 10)</h3>
            <button onclick="closeModal('newRequestModal')" style="border: none; background: none; font-size: 20px; cursor: pointer;">&times;</button>
        </div>
        <form method="POST" action="index.php?route=create_request" style="padding: 24px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label class="form-label">Request Type</label>
                    <select name="request_type" class="form-control" required>
                        <option value="New Product">📦 New Product Requisition</option>
                        <option value="Service Request">🔧 Service / Repair Request</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Department</label>
                    <select name="dept_id" id="requestDeptSelect" class="form-control" required onchange="filterLabsByDept()">
                        <?php foreach ($departments as $d): ?>
                            <option value="<?= $d['id'] ?>" <?= $d['code'] === 'CSE' ? 'selected' : '' ?>>
                                <?= htmlspecialchars($d['code']) ?> - <?= htmlspecialchars($d['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Laboratory / Unit</label>
                    <select name="lab_id" id="requestLabSelect" class="form-control">
                        <option value="">General Department Requirement</option>
                        <?php foreach ($laboratories as $l): ?>
                            <option value="<?= $l['id'] ?>" data-dept="<?= $l['dept_id'] ?>">
                                <?= htmlspecialchars($l['name']) ?> (<?= htmlspecialchars($l['code']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label class="form-label">Purpose / Justification</label>
                    <input type="text" name="purpose" class="form-control" required placeholder="e.g. AI Laboratory Maintenance & Lab Exam Preparation" value="AI Laboratory Maintenance & Semester Lab Exam Preparation">
                </div>
                <div class="form-group">
                    <label class="form-label">Required Date</label>
                    <input type="date" name="required_date" class="form-control" required value="<?= date('Y-m-d', strtotime('+7 days')) ?>">
                </div>
            </div>

            <div style="border-top: 1px solid #e2e8f0; padding-top: 16px; margin-top: 8px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                    <h4 style="font-size: 14px; font-weight: 700; margin: 0;">Select Requisition Items:</h4>
                    <span style="font-size: 11px; color: #0284c7;">⚡ Live stock check enabled</span>
                </div>

                <div style="margin-bottom: 10px; position: relative;">
                    <input type="text" id="reqVacantSearchInput" class="form-control" 
                           placeholder="🔍 Search items or check vacant stock in other departments before purchasing..." 
                           style="font-size: 13px; height: 38px; border-radius: 8px; padding-left: 34px;"
                           oninput="filterRequisitionVacantSearch(this.value)">
                    <span style="position: absolute; left: 10px; top: 10px; font-size: 14px; color: #64748b;">🔍</span>
                </div>

                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <div style="display: grid; grid-template-columns: 3fr 1fr 2fr; gap: 10px; align-items: center;">
                        <select name="items[0][item_id]" id="primaryItemSelect" class="form-control" required onchange="checkPrimaryItemSurplus(this.value)">
                            <?php foreach ($items as $it): ?>
                                <option value="<?= $it['id'] ?>" <?= $it['item_code'] === 'IT-KEY-002' ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($it['item_name']) ?> (₹<?= $it['unit_cost'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="number" name="items[0][quantity]" class="form-control" placeholder="Qty" value="10" required>
                        <input type="text" name="items[0][notes]" class="form-control" placeholder="Notes" value="10 Nos for lab monitor displays">
                    </div>

                    <div style="display: grid; grid-template-columns: 3fr 1fr 2fr; gap: 10px; align-items: center;">
                        <select name="items[1][item_id]" class="form-control" onchange="checkPrimaryItemSurplus(this.value)">
                            <option value="">-- Optional Second Item --</option>
                            <?php foreach ($items as $it): ?>
                                <option value="<?= $it['id'] ?>" <?= $it['item_code'] === 'IT-CBL-001' ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($it['item_name']) ?> (₹<?= $it['unit_cost'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="number" name="items[1][quantity]" class="form-control" placeholder="Qty" value="5">
                        <input type="text" name="items[1][notes]" class="form-control" placeholder="Notes" value="5 Nos replacement for damaged terminals">
                    </div>
                </div>
            </div>

            <!-- Live College-Wide Surplus & Idle Stock Detector -->
            <div id="surplusCheckCard" style="background: linear-gradient(135deg, #f0fdf4, #ecfdf5); border: 1.5px solid #86efac; border-radius: 12px; padding: 14px 16px; margin-top: 16px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 18px;">⚡</span>
                        <div>
                            <strong style="font-size: 13px; color: #166534;">College-Wide Department Surplus & Idle Stock Detector:</strong>
                            <div style="font-size: 11px; color: #15803d;">Real-time In-Use vs. Vacant stock status across all departments for the selected item:</div>
                        </div>
                    </div>
                    <span id="surplusBadge" class="badge badge-success" style="font-size: 10px; padding: 4px 8px;">Live Intelligence</span>
                </div>

                <div id="surplusPillContainer" style="display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px;">
                    <!-- Filled dynamically via checkPrimaryItemSurplus() -->
                </div>

                <div id="surplusRecommendationBox" style="display: none; margin-top: 12px; padding: 10px 14px; background: #ffffff; border: 1.5px dashed #22c55e; border-radius: 8px; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
                    <div style="font-size: 12px; color: #166534; flex: 1;">
                        <strong style="color: #15803d;">💡 Institutional Surplus Alert:</strong> <span id="surplusAlertText">Other departments have vacant stock.</span>
                    </div>
                    <a id="btnTransferShortcut" href="#" class="btn btn-primary btn-sm" style="font-size: 11px; white-space: nowrap; background: #16a34a; border-color: #15803d;">
                        🔄 Request Inter-Dept Transfer Instead
                    </a>
                </div>
            </div>

            <div style="background: #f1f5f9; padding: 12px; border-radius: 8px; margin-top: 16px; font-size: 12px; color: #475569;">
                ℹ️ <strong>Workflow Verification Ladder:</strong> Lab Originator &rarr; Coordinator (Stock Check & Scrutiny) &rarr; HOD (Dept Endorsement) &rarr; Store Keeper (Approval Sheet) &rarr; Vice Principal (Admin Review) &rarr; Principal (Institutional Review) &rarr; Management / Directorate (Final Financial Sanction) &rarr; Store Fulfillment.
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 18px;">
                <button type="button" onclick="closeModal('newRequestModal')" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Submit Purchase Requisition</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Search Vacant Stock Across College (Before Indenting) -->
<div id="vacantLookupModal" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); z-index: 999; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div style="background: white; border-radius: 14px; width: 720px; max-width: 95%; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);">
        <div style="padding: 18px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
            <div>
                <h3 style="font-size: 17px; font-weight: 800; color: #0f172a; margin: 0;">🔍 Search Vacant & Surplus Stock Across College</h3>
                <div style="font-size: 12px; color: #64748b; margin-top: 2px;">
                    Check unallocated inventory in other departments before raising new purchase requests
                </div>
            </div>
            <button onclick="closeModal('vacantLookupModal')" style="border: none; background: none; font-size: 22px; cursor: pointer; color: #64748b;">&times;</button>
        </div>

        <div style="padding: 20px 24px;">
            <div style="position: relative; margin-bottom: 14px;">
                <input type="text" id="vacantLookupInput" class="form-control" 
                       placeholder="🔍 Type item name, category, or code (e.g. keyboard, cable, paper, oscilloscope)..." 
                       style="font-size: 14px; height: 42px; border-radius: 10px; padding-left: 36px;"
                       oninput="onVacantLookupSearch(this.value)">
                <span style="position: absolute; left: 12px; top: 11px; font-size: 16px; color: #64748b;">🔍</span>
            </div>

            <div id="vacantLookupResults" style="display: flex; flex-direction: column; gap: 10px; max-height: 420px; overflow-y: auto;">
                <!-- Dynamically loaded via onVacantLookupSearch() -->
            </div>
        </div>
    </div>
</div>


<script>
const ITEM_SURPLUS_MAP = <?= json_encode($itemSurplusMap) ?>;
const USER_DEPT_ID = <?= (int)$userDeptId ?>;
const USER_DEPT_CODE = "<?= htmlspecialchars($userDeptCode) ?>";

function checkPrimaryItemSurplus(itemId) {
    itemId = parseInt(itemId);
    if (!itemId) return;
    const data = ITEM_SURPLUS_MAP[itemId];
    if (!data) return;

    const container = document.getElementById('surplusPillContainer');
    const recBox = document.getElementById('surplusRecommendationBox');
    const recText = document.getElementById('surplusAlertText');
    const btnShortcut = document.getElementById('btnTransferShortcut');
    container.innerHTML = '';

    // Central store pill
    const cPill = document.createElement('div');
    cPill.className = 'badge';
    cPill.style.background = '#e0f2fe';
    cPill.style.color = '#0369a1';
    cPill.style.padding = '6px 10px';
    cPill.style.fontSize = '11px';
    cPill.innerHTML = `🏢 Central Main Warehouse: <strong>${data.central_store.vacant} Available</strong>`;
    container.appendChild(cPill);

    let otherDeptSurplusFound = false;
    let topSurplusDept = null;
    let topSurplusQty = 0;

    data.departments.forEach(d => {
        if (parseInt(d.vacant_qty) === 0 && parseInt(d.in_use_qty) === 0) return;
        const isMyDept = (parseInt(d.dept_id) === USER_DEPT_ID);
        const pill = document.createElement('div');
        pill.className = 'badge';
        pill.style.padding = '6px 10px';
        pill.style.fontSize = '11px';

        if (parseInt(d.vacant_qty) > 0) {
            pill.style.background = isMyDept ? '#fef3c7' : '#dcfce7';
            pill.style.color = isMyDept ? '#b45309' : '#15803d';
            pill.style.border = isMyDept ? '1px solid #fde68a' : '1px solid #86efac';
            pill.innerHTML = `<strong>${d.dept_code}</strong>: ${d.in_use_qty} In Use &bull; <strong style="color: #16a34a;">${d.vacant_qty} Vacant (Extra)</strong>`;
            if (!isMyDept && parseInt(d.vacant_qty) > topSurplusQty) {
                topSurplusQty = parseInt(d.vacant_qty);
                topSurplusDept = d;
                otherDeptSurplusFound = true;
            }
        } else {
            pill.style.background = '#f1f5f9';
            pill.style.color = '#64748b';
            pill.innerHTML = `<strong>${d.dept_code}</strong>: ${d.in_use_qty} In Use &bull; 0 Vacant`;
        }
        container.appendChild(pill);
    });

    if (otherDeptSurplusFound && topSurplusDept) {
        recBox.style.display = 'flex';
        recText.innerHTML = `<strong>${topSurplusDept.dept_code} Department</strong> currently has <strong>${topSurplusDept.vacant_qty} units VACANT / NOT IN USE</strong> in their sub-store. Under institutional resource-sharing policy, you can request an Inter-Department Inward Transfer directly to save Management budget!`;
        btnShortcut.href = `index.php?route=movement&source_dept_id=${topSurplusDept.dept_id}&item_id=${itemId}`;
        btnShortcut.textContent = `🔄 Transfer from ${topSurplusDept.dept_code} &rarr; ${USER_DEPT_CODE} Instead`;
    } else {
        recBox.style.display = 'none';
    }
}

function filterLabsByDept() {
    const deptSelect = document.getElementById('requestDeptSelect');
    const labSelect = document.getElementById('requestLabSelect');
    if (!deptSelect || !labSelect) return;
    const selectedDeptId = deptSelect.value;
    
    Array.from(labSelect.options).forEach(opt => {
        if (!opt.value) {
            opt.style.display = 'block';
            return;
        }
        const optDept = opt.getAttribute('data-dept');
        if (optDept === selectedDeptId) {
            opt.style.display = 'block';
        } else {
            opt.style.display = 'none';
        }
    });
}

// Quick filter for requisition primary item
function filterRequisitionVacantSearch(term) {
    term = term.trim().toLowerCase();
    const select = document.getElementById('primaryItemSelect');
    if (!select) return;

    let firstMatch = null;
    Array.from(select.options).forEach(opt => {
        const text = opt.textContent.toLowerCase();
        if (!term || text.includes(term)) {
            opt.style.display = '';
            if (!firstMatch) firstMatch = opt;
        } else {
            opt.style.display = 'none';
        }
    });

    if (firstMatch && term) {
        select.value = firstMatch.value;
        checkPrimaryItemSurplus(firstMatch.value);
    }
}

// Search across all items with surplus/vacant stock for vacantLookupModal
function onVacantLookupSearch(term) {
    term = (term || '').trim().toLowerCase();
    const container = document.getElementById('vacantLookupResults');
    if (!container) return;
    container.innerHTML = '';

    let matchCount = 0;

    for (const [itemIdStr, data] of Object.entries(ITEM_SURPLUS_MAP)) {
        if (!data || !data.item) continue;
        const it = data.item;
        const itemName = (it.item_name || '').toLowerCase();
        const itemCode = (it.item_code || '').toLowerCase();
        const category = (it.category_name || '').toLowerCase();

        const matches = !term || itemName.includes(term) || itemCode.includes(term) || category.includes(term);
        if (!matches) continue;

        // Check if there is vacant stock in central or any dept
        const hasCentralVacant = data.central_store && data.central_store.vacant > 0;
        const surplusDepts = data.surplus_depts || [];
        const totalVacant = (data.central_store ? data.central_store.vacant : 0) + (data.total_dept_vacant || 0);

        if (totalVacant <= 0) continue; // Only show items with vacant stock

        matchCount++;

        const card = document.createElement('div');
        card.style.background = '#ffffff';
        card.style.border = '1.5px solid #bae6fd';
        card.style.borderRadius = '10px';
        card.style.padding = '14px 16px';
        card.style.display = 'flex';
        card.style.flexDirection = 'column';
        card.style.gap = '8px';

        let deptsHtml = '';
        if (hasCentralVacant) {
            deptsHtml += `<span class="badge" style="background: #e0f2fe; color: #0284c7; font-size: 11px; padding: 4px 8px;">🏢 Central Warehouse: <strong>${data.central_store.vacant} Available</strong></span> `;
        }
        surplusDepts.forEach(sd => {
            const isMine = (parseInt(sd.dept_id) === USER_DEPT_ID);
            deptsHtml += `
                <span class="badge" style="background: ${isMine ? '#fef3c7' : '#dcfce7'}; color: ${isMine ? '#b45309' : '#15803d'}; font-size: 11px; padding: 4px 8px; border: 1px solid ${isMine ? '#fde68a' : '#86efac'};">
                    <strong>${sd.dept_code}</strong>: ${sd.vacant_qty} Vacant ${isMine ? '(Your Dept)' : ''}
                </span>
            `;
        });

        // First other department for 1-click transfer
        const targetOtherDept = surplusDepts.find(sd => parseInt(sd.dept_id) !== USER_DEPT_ID);
        let actionBtnHtml = '';
        if (targetOtherDept) {
            actionBtnHtml = `
                <a href="index.php?route=movement&source_dept_id=${targetOtherDept.dept_id}&item_id=${it.id}" class="btn btn-primary btn-sm" style="font-size: 11px; white-space: nowrap;">
                    🔄 Request Transfer from ${targetOtherDept.dept_code} &rarr; ${USER_DEPT_CODE}
                </a>
            `;
        } else if (hasCentralVacant) {
            actionBtnHtml = `
                <a href="index.php?route=movement&source_dept_id=0&item_id=${it.id}" class="btn btn-primary btn-sm" style="font-size: 11px; white-space: nowrap;">
                    🔄 Request from Central Store &rarr; ${USER_DEPT_CODE}
                </a>
            `;
        }

        card.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; flex-wrap: wrap;">
                <div>
                    <div style="font-size: 14px; font-weight: 800; color: #0f172a;">
                        ${escapeHtml(it.item_name)}
                    </div>
                    <div style="font-size: 12px; color: #64748b; margin-top: 2px;">
                        <code>${escapeHtml(it.item_code)}</code> &bull; ${escapeHtml(it.category_name)} &bull; ₹${parseFloat(it.unit_cost).toFixed(2)}
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="badge badge-info" style="font-size: 12px; padding: 4px 10px; font-weight: 800;">
                        💡 ${totalVacant} Total Vacant Units
                    </span>
                    ${actionBtnHtml}
                </div>
            </div>
            <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-top: 4px; padding-top: 6px; border-top: 1px dashed #e2e8f0;">
                <span style="font-size: 11px; color: #64748b; font-weight: 600; align-self: center;">Available at:</span>
                ${deptsHtml}
            </div>
        `;
        container.appendChild(card);
    }

    if (matchCount === 0) {
        container.innerHTML = `
            <div style="padding: 24px; text-align: center; color: #64748b; font-size: 13px;">
                No vacant or surplus stock found matching "<strong>${escapeHtml(term)}</strong>".
            </div>
        `;
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

document.addEventListener('DOMContentLoaded', function() {
    filterLabsByDept();
    const pSel = document.getElementById('primaryItemSelect');
    if (pSel) {
        checkPrimaryItemSurplus(pSel.value);
    }
    onVacantLookupSearch('');
});
</script>

