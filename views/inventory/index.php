<?php
/**
 * MSEC SMART STOCK - Inventory Management View
 * Locked Scope: Section 6.2 (Stock Details), 7 (Classification), 21 (Dead Stock), 22 (Low Stock), 24 (Adjustments)
 */

$allItems = Inventory::getAllItems();
$lowStock = Inventory::getLowStockItems();
$deadStock = Inventory::getDeadStockItems();
$deptMatrix = Inventory::getDepartmentStockMatrix();
$tab = $_GET['tab'] ?? 'all';
$selectedDept = $_GET['dept'] ?? 'all';
?>

<div class="page-header">
    <div>
        <h2 class="page-title">Inventory Control & Master Stock</h2>
        <div class="page-desc">Central & Departmental Multi-Store Balances &bull; Live Utilization (In Use vs. Vacant) & Adjustments</div>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <?php if (Auth::can('create_transfer')): ?>
            <a href="index.php?route=movement" class="btn btn-secondary">🔄 Inter-Dept Transfers</a>
        <?php endif; ?>
        <?php if (Auth::can('adjust_stock')): ?>
            <button onclick="openModal('adjustModal')" class="btn btn-secondary">⚡ Stock Adjustment</button>
            <button onclick="openModal('addItemModal')" class="btn btn-primary">+ Add New Item</button>
        <?php endif; ?>
    </div>
</div>

<!-- Tab Navigation (Mobile Touch-Scrollable) -->
<div class="tabs-nav-scroll">
    <a href="index.php?route=inventory&tab=all" class="btn <?= $tab === 'all' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">All Master Stock (<?= count($allItems) ?>)</a>
    <a href="index.php?route=inventory&tab=dept_stocks" class="btn <?= $tab === 'dept_stocks' ? 'btn-primary' : 'btn-secondary' ?> btn-sm" style="<?= $tab === 'dept_stocks' ? 'background: linear-gradient(135deg, #102a43, #0284c7); border-color: #38bdf8;' : '' ?>">🏛️ Live Department Stocks (In Use vs. Vacant)</a>
    <a href="index.php?route=inventory&tab=consumables" class="btn <?= $tab === 'consumables' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">Consumables</a>
    <a href="index.php?route=inventory&tab=non_consumables" class="btn <?= $tab === 'non_consumables' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">Non-Consumables / Assets</a>
    <a href="index.php?route=inventory&tab=low_stock" class="btn <?= $tab === 'low_stock' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">⚠️ Low Stock (<?= count($lowStock) ?>)</a>
    <a href="index.php?route=inventory&tab=dead_stock" class="btn <?= $tab === 'dead_stock' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">⏳ Dead Stock</a>
</div>

<?php if ($tab === 'dept_stocks'): 
    $userDeptCode = $user['dept_code'] ?? 'CSE';
    $userDeptId = $user['dept_id'] ?? 1;
    $totalDeptStores = count($deptMatrix);
    $totalDeptVacant = 0;
    $totalDeptInUse = 0;
    $totalDeptVal = 0.0;
    foreach ($deptMatrix as $dm) {
        $totalDeptVacant += $dm['total_vacant'];
        $totalDeptInUse += $dm['total_in_use'];
        $totalDeptVal += $dm['total_valuation'];
    }
?>
    <!-- Department Stocks KPI Grid -->
    <div class="stats-grid" style="margin-bottom: 24px;">
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(14, 165, 233, 0.15); color: #0284c7;">🏛️</div>
            <div class="stat-content">
                <div class="stat-value"><?= $totalDeptStores ?> Sub-Stores</div>
                <div class="stat-label">Institutional Department Stores</div>
            </div>
        </div>
        <div class="stat-card success">
            <div class="stat-icon" style="background: rgba(34, 197, 94, 0.15); color: #16a34a;">🟢</div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($totalDeptInUse) ?> Units</div>
                <div class="stat-label">Currently in Active Use (Labs/Faculty)</div>
            </div>
        </div>
        <div class="stat-card info">
            <div class="stat-icon" style="background: rgba(245, 158, 11, 0.15); color: #d97706;">💡</div>
            <div class="stat-content">
                <div class="stat-value" style="color: #0284c7;"><?= number_format($totalDeptVacant) ?> Units</div>
                <div class="stat-label">Vacant / Not in Use (Extra Stock Available)</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(99, 102, 241, 0.15); color: #4f46e5;">💰</div>
            <div class="stat-content">
                <div class="stat-value"><?= CURRENCY_SYMBOL ?><?= number_format($totalDeptVal, 0) ?></div>
                <div class="stat-label">Department Stores Valuation</div>
            </div>
        </div>
    </div>

    <!-- Vacant Stock Search & Real-Time Filter Console -->
    <div style="background: white; border-radius: 14px; padding: 18px 22px; border: 1.5px solid #cbd5e1; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom: 20px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; margin-bottom: 12px;">
            <div style="display: flex; align-items: center; gap: 10px; flex: 1; min-width: 280px;">
                <div style="position: relative; width: 100%;">
                    <input type="text" id="deptVacantSearchInput" class="form-control" 
                           placeholder="🔍 Search vacant items, equipment or departments (e.g. keyboard, cable, paper, ECE, CSE)..." 
                           style="padding-left: 38px; padding-right: 32px; font-size: 14px; height: 42px; border-radius: 10px; border: 1.5px solid #94a3b8;"
                           oninput="filterDeptVacantStocks()">
                    <span style="position: absolute; left: 12px; top: 11px; font-size: 16px; color: #64748b;">🔍</span>
                    <button type="button" id="clearVacantSearchBtn" onclick="clearDeptVacantSearch()" 
                            style="position: absolute; right: 10px; top: 9px; border: none; background: none; font-size: 16px; color: #94a3b8; cursor: pointer; display: none;">✕</button>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <!-- Filter Toggle: Vacant Only -->
                <button type="button" id="btnToggleVacantOnly" class="btn btn-secondary btn-sm" onclick="toggleVacantOnlyFilter()" 
                        style="background: #e0f2fe; color: #0284c7; border: 1.5px solid #38bdf8; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                    <span id="vacantToggleIcon">✅</span> <span>Only Show Vacant Stock (&gt;0)</span>
                </button>

                <!-- Category Filter -->
                <select id="vacantCategoryFilter" class="form-control" style="width: auto; height: 38px; font-size: 13px; border-radius: 8px;" onchange="filterDeptVacantStocks()">
                    <option value="all">All Categories</option>
                    <option value="Stationery & Paper">Stationery & Paper</option>
                    <option value="IT Consumables">IT Consumables</option>
                    <option value="IT Equipment & Hardware">IT Equipment & Hardware</option>
                    <option value="Electrical & Wiring">Electrical & Wiring</option>
                    <option value="Electronic Components">Electronic Components</option>
                    <option value="Lab Chemicals & Reagents">Lab Chemicals & Reagents</option>
                    <option value="Lab Instruments & Machinery">Lab Instruments & Machinery</option>
                    <option value="Furniture & Fixtures">Furniture & Fixtures</option>
                </select>

                <?php if (Auth::can('create_transfer')): ?>
                    <a href="index.php?route=movement" class="btn btn-primary btn-sm" style="height: 38px; display: inline-flex; align-items: center;">
                        🔄 Initiate Transfer &rarr; <?= htmlspecialchars($userDeptCode) ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; border-top: 1px solid #f1f5f9; padding-top: 10px;">
            <!-- Department Quick Filter Pills -->
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <span style="font-weight: 700; font-size: 12px; color: #475569;">Quick Dept Filter:</span>
                <button type="button" class="btn btn-sm dept-filter-pill active" onclick="setQuickDeptFilter('all', this)" style="border-radius: 20px; font-size: 11px;">All</button>
                <?php foreach ($deptMatrix as $dm): 
                    if (empty($dm['items'])) continue;
                ?>
                    <button type="button" class="btn btn-secondary btn-sm dept-filter-pill" onclick="setQuickDeptFilter('<?= $dm['dept_code'] ?>', this)" style="border-radius: 20px; font-size: 11px;">
                        <?= htmlspecialchars($dm['dept_code']) ?>
                        <span class="badge" style="background: rgba(0,0,0,0.08); font-size: 10px; margin-left: 3px;"><?= $dm['total_vacant'] ?> vacant</span>
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- Dynamic Search Summary Indicator -->
            <div id="vacantLiveSummary" style="font-size: 12px; font-weight: 600; color: #0284c7; background: #f0f9ff; padding: 4px 10px; border-radius: 6px; border: 1px solid #bae6fd;">
                ⚡ Showing all departmental stock &bull; Filter enabled for vacant units
            </div>
        </div>
    </div>


    <!-- No Results Placeholder -->
    <div id="noVacantMatchBox" style="display: none; padding: 48px 24px; text-align: center; background: white; border-radius: 14px; border: 2px dashed #cbd5e1; margin-bottom: 24px;">
        <div style="font-size: 36px; margin-bottom: 10px;">🔍</div>
        <h4 style="font-size: 16px; font-weight: 700; color: #1e293b; margin-bottom: 6px;">No Matching Vacant Stock Found</h4>
        <p style="font-size: 13px; color: #64748b; max-width: 480px; margin: 0 auto 16px;">
            No departmental sub-stores currently report vacant stock matching your search query and filters.
        </p>
        <button type="button" onclick="clearDeptVacantSearch()" class="btn btn-secondary btn-sm">Reset All Filters</button>
    </div>

    <!-- Department Details & Item Matrix -->
    <?php foreach ($deptMatrix as $dm): 
        if ($selectedDept !== 'all' && $selectedDept !== $dm['dept_code']) continue;
        if (empty($dm['items'])) continue;
        $isMyDept = ($dm['dept_id'] == $userDeptId);
    ?>
        <div class="card dept-stock-card" data-dept-code="<?= $dm['dept_code'] ?>" data-dept-name="<?= strtolower($dm['dept_name']) ?>" style="margin-bottom: 24px; border: 1px solid <?= $dm['total_vacant'] > 0 ? '#bae6fd' : '#e2e8f0' ?>;">
            <div class="card-header" style="background: <?= $dm['total_vacant'] > 0 ? 'linear-gradient(to right, #f0f9ff, #ffffff)' : '#fafafa' ?>; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 40px; height: 40px; border-radius: 8px; background: #0f172a; color: #38bdf8; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13px;">
                        <?= htmlspecialchars($dm['dept_code']) ?>
                    </div>
                    <div>
                        <div style="font-size: 16px; font-weight: 800; color: #0f172a;">
                            <?= htmlspecialchars($dm['dept_name']) ?>
                            <?php if ($isMyDept): ?>
                                <span class="badge badge-primary" style="font-size: 11px; margin-left: 6px;">Your Department</span>
                            <?php endif; ?>
                        </div>
                        <div style="font-size: 12px; color: #64748b;"><?= htmlspecialchars($dm['store_name'] ?? 'Department Sub-Store') ?></div>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span class="badge badge-success" style="font-size: 12px; padding: 6px 12px;">
                        🟢 In Use: <strong><?= number_format($dm['total_in_use']) ?></strong>
                    </span>
                    <span class="badge" style="font-size: 12px; padding: 6px 12px; background: <?= $dm['total_vacant'] > 0 ? '#e0f2fe; color: #0369a1; border: 1px solid #7dd3fc;' : '#f1f5f9; color: #64748b;' ?>">
                        💡 Vacant / Not In Use: <strong class="dept-card-vacant-count"><?= number_format($dm['total_vacant']) ?></strong>
                    </span>
                    <?php if (!$isMyDept && $dm['total_vacant'] > 0): ?>
                        <a href="index.php?route=movement&source_dept_id=<?= $dm['dept_id'] ?>" class="btn btn-primary btn-sm">
                            🔄 Transfer from <?= htmlspecialchars($dm['dept_code']) ?> &rarr; <?= htmlspecialchars($userDeptCode) ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card-body" style="padding: 0;">
                <div class="data-table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Item Code</th>
                                <th>Item Name</th>
                                <th>Category</th>
                                <th style="text-align: center;">In Active Use (Labs)</th>
                                <th style="text-align: center;">Vacant / Not in Use (Extra)</th>
                                <th>Sub-Store Location</th>
                                <th>Unit Cost</th>
                                <th>Total Dept Value</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dm['items'] as $it): 
                                $hasExtra = ($it['vacant_qty'] > 0);
                            ?>
                                <tr class="dept-stock-row" 
                                    data-item-name="<?= strtolower(htmlspecialchars($it['item_name'])) ?>" 
                                    data-item-code="<?= strtolower(htmlspecialchars($it['item_code'])) ?>" 
                                    data-category="<?= htmlspecialchars($it['category_name']) ?>" 
                                    data-vacant="<?= (int)$it['vacant_qty'] ?>" 
                                    data-in-use="<?= (int)$it['in_use_qty'] ?>" 
                                    data-dept="<?= $dm['dept_code'] ?>">
                                    <td><code><?= htmlspecialchars($it['item_code']) ?></code></td>
                                    <td>
                                        <strong class="item-name-col"><?= htmlspecialchars($it['item_name']) ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars($it['category_name']) ?></td>
                                    <td style="text-align: center;">
                                        <span class="badge badge-success" style="font-weight: 700;">
                                             <?= number_format($it['in_use_qty']) ?> <?= htmlspecialchars($it['unit_code']) ?>
                                        </span>
                                    </td>
                                    <td style="text-align: center;">
                                        <?php if ($hasExtra): ?>
                                            <span class="badge badge-info" style="font-weight: 800; font-size: 12px; background: #e0f2fe; color: #0284c7; border: 1px solid #38bdf8;">
                                                💡 <?= number_format($it['vacant_qty']) ?> <?= htmlspecialchars($it['unit_code']) ?> (Vacant)
                                            </span>
                                        <?php else: ?>
                                            <span style="color: #94a3b8; font-size: 12px;">0 (Fully Deployed)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span style="font-size: 12px; color: #475569;"><?= htmlspecialchars($it['store_location']) ?></span></td>
                                    <td><?= CURRENCY_SYMBOL ?><?= number_format($it['unit_cost'], 2) ?></td>
                                    <td><strong><?= CURRENCY_SYMBOL ?><?= number_format(($it['vacant_qty'] + $it['in_use_qty']) * $it['unit_cost'], 2) ?></strong></td>
                                    <td style="text-align: right;">
                                        <?php if (!$isMyDept && $hasExtra): ?>
                                            <a href="index.php?route=movement&source_dept_id=<?= $dm['dept_id'] ?>&item_id=<?= $it['item_id'] ?>" class="btn btn-secondary btn-sm" style="font-size: 11px;">
                                                🔄 Request <?= htmlspecialchars($dm['dept_code']) ?> &rarr; <?= htmlspecialchars($userDeptCode) ?>
                                            </a>
                                        <?php elseif ($isMyDept): ?>
                                            <span style="font-size: 11px; color: #64748b;">In Your Custody</span>
                                        <?php else: ?>
                                            <span style="font-size: 11px; color: #94a3b8;">No Vacant Stock</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endforeach; ?>


<?php else: ?>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <?php
            if ($tab === 'consumables') echo "Consumables Inventory (Purchased &rarr; Central Store &rarr; Dept Request &rarr; Issued &rarr; Consumed)";
            elseif ($tab === 'non_consumables') echo "Non-Consumable Inventory / Capital Equipment (Asset Tagged & Lifecycle Managed)";
            elseif ($tab === 'low_stock') echo "Reorder Alert Watchlist (Available &le; Minimum Threshold)";
            elseif ($tab === 'dead_stock') echo "Dead Stock Analysis (No Movement for 1, 2 or 3 Years)";
            else echo "Institutional Stock Overview";
            ?>
        </div>
        <div>
            <input type="text" class="form-control table-search-input" data-table="inventoryTable" placeholder="Search by item code or name..." style="width: 250px; padding: 6px 12px; font-size: 13px;">
        </div>
    </div>

    <div class="card-body" style="padding: 0;">
        <div class="data-table-container">
            <table class="data-table" id="inventoryTable">
                <thead>
                    <tr>
                        <th>Item Code</th>
                        <th>Item Name</th>
                        <th>Category</th>
                        <th>Classification</th>
                        <th>Available Qty</th>
                        <th>Reserved</th>
                        <th>Min Stock</th>
                        <th>Unit Cost</th>
                        <th>Valuation</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $displayItems = $allItems;
                    if ($tab === 'consumables') {
                        $displayItems = array_filter($allItems, fn($i) => $i['is_consumable'] == 1);
                    } elseif ($tab === 'non_consumables') {
                        $displayItems = array_filter($allItems, fn($i) => $i['is_consumable'] == 0);
                    } elseif ($tab === 'low_stock') {
                        $displayItems = array_filter($allItems, fn($i) => $i['total_available'] <= $i['min_stock']);
                    } elseif ($tab === 'dead_stock') {
                        $displayItems = array_filter($allItems, fn($i) => str_contains($i['item_code'], 'OLD') || str_contains($i['item_code'], 'SWT'));
                    }

                    foreach ($displayItems as $item):
                        $isLow = $item['total_available'] <= $item['min_stock'];
                    ?>
                        <tr>
                            <td><code><?= htmlspecialchars($item['item_code']) ?></code></td>
                            <td>
                                <strong><?= htmlspecialchars($item['item_name']) ?></strong>
                                <div style="font-size: 11px; color: #64748b;"><?= htmlspecialchars($item['description'] ?? '') ?></div>
                            </td>
                            <td><?= htmlspecialchars($item['category_name']) ?></td>
                            <td>
                                <?php if ($item['is_consumable']): ?>
                                    <span class="badge badge-info">Consumable</span>
                                <?php else: ?>
                                    <span class="badge badge-primary">Non-Consumable</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong style="font-size: 15px; <?= $isLow ? 'color: #dc2626;' : 'color: #16a34a;' ?>">
                                    <?= number_format($item['total_available'], 0) ?>
                                </strong>
                                <span style="font-size: 11px; color: #64748b;"><?= htmlspecialchars($item['unit_code']) ?></span>
                            </td>
                            <td><?= number_format($item['total_reserved'] ?? 0, 0) ?></td>
                            <td><?= number_format($item['min_stock'], 0) ?></td>
                            <td><?= CURRENCY_SYMBOL ?><?= number_format($item['unit_cost'], 2) ?></td>
                            <td><strong><?= CURRENCY_SYMBOL ?><?= number_format($item['total_available'] * $item['unit_cost'], 2) ?></strong></td>
                            <td>
                                <button onclick="selectForAdjust(<?= $item['id'] ?>, '<?= htmlspecialchars(addslashes($item['item_name'])) ?>', <?= $item['total_available'] ?>)" class="btn btn-secondary btn-sm">Adjust</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Stock Adjustment Modal (Section 24) -->
<div id="adjustModal" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); z-index: 999; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div style="background: white; border-radius: 12px; width: 500px; max-width: 90%; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); overflow: hidden;">
        <div style="padding: 18px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
            <h3 style="font-size: 16px; font-weight: 700;">Stock Adjustment (Section 24)</h3>
            <button onclick="closeModal('adjustModal')" style="border: none; background: none; font-size: 20px; cursor: pointer;">&times;</button>
        </div>
        <form method="POST" action="index.php?route=do_adjustment" style="padding: 24px;">
            <div class="form-group">
                <label class="form-label">Item to Adjust</label>
                <select name="item_id" id="adj_item_id" class="form-control" required onchange="updateOldQty(this)">
                    <?php foreach ($allItems as $it): ?>
                        <option value="<?= $it['id'] ?>" data-qty="<?= $it['total_available'] ?>">
                            <?= htmlspecialchars($it['item_name']) ?> (Current: <?= $it['total_available'] ?> <?= $it['unit_code'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label class="form-label">Old Quantity</label>
                    <input type="number" id="adj_old_qty" class="form-control" readonly value="<?= $allItems[0]['total_available'] ?? 0 ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">New Verified Quantity</label>
                    <input type="number" name="new_quantity" step="0.01" class="form-control" required placeholder="e.g. 25">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Store Location</label>
                <select name="store_id" class="form-control" required>
                    <option value="1">Central Store</option>
                    <option value="2">CSE Department Store</option>
                    <option value="3">ECE Department Store</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Mandatory Reason (Audit Rule)</label>
                <textarea name="reason" rows="2" class="form-control" required placeholder="e.g. Physical stock verification variance / damaged during handling"></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Supporting Document Reference</label>
                <input type="text" name="supporting_doc" class="form-control" placeholder="e.g. AUDIT-REP-2026-Q3 / Survey Report #12">
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
                <button type="button" onclick="closeModal('adjustModal')" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Authorize & Apply Adjustment</button>
            </div>
        </form>
    </div>
</div>

<script>
function selectForAdjust(id, name, qty) {
    const sel = document.getElementById('adj_item_id');
    if (sel) {
        sel.value = id;
        document.getElementById('adj_old_qty').value = qty;
    }
    openModal('adjustModal');
}
function updateOldQty(el) {
    const opt = el.options[el.selectedIndex];
    document.getElementById('adj_old_qty').value = opt.getAttribute('data-qty') || 0;
}

// ==========================================
// VACANT STOCK SEARCH & FILTER LOGIC
// ==========================================
let vacantOnlyActive = true;
let currentQuickDept = 'all';

function toggleVacantOnlyFilter() {
    vacantOnlyActive = !vacantOnlyActive;
    const btn = document.getElementById('btnToggleVacantOnly');
    const icon = document.getElementById('vacantToggleIcon');
    if (vacantOnlyActive) {
        btn.style.background = '#e0f2fe';
        btn.style.color = '#0284c7';
        btn.style.borderColor = '#38bdf8';
        icon.textContent = '✅';
    } else {
        btn.style.background = '#f8fafc';
        btn.style.color = '#64748b';
        btn.style.borderColor = '#cbd5e1';
        icon.textContent = '⚪';
    }
    filterDeptVacantStocks();
}

function setQuickDeptFilter(deptCode, btnEl) {
    currentQuickDept = deptCode;
    document.querySelectorAll('.dept-filter-pill').forEach(b => {
        b.classList.remove('active', 'btn-primary');
        b.classList.add('btn-secondary');
    });
    if (btnEl) {
        btnEl.classList.remove('btn-secondary');
        btnEl.classList.add('btn-primary', 'active');
    }
    filterDeptVacantStocks();
}

function clearDeptVacantSearch() {
    const input = document.getElementById('deptVacantSearchInput');
    if (input) input.value = '';
    const cat = document.getElementById('vacantCategoryFilter');
    if (cat) cat.value = 'all';
    currentQuickDept = 'all';
    vacantOnlyActive = true;
    document.querySelectorAll('.dept-filter-pill').forEach((b, idx) => {
        if (idx === 0) {
            b.classList.add('btn-primary', 'active');
            b.classList.remove('btn-secondary');
        } else {
            b.classList.remove('btn-primary', 'active');
            b.classList.add('btn-secondary');
        }
    });
    const clearBtn = document.getElementById('clearVacantSearchBtn');
    if (clearBtn) clearBtn.style.display = 'none';
    filterDeptVacantStocks();
}

function filterDeptVacantStocks() {
    const searchInput = document.getElementById('deptVacantSearchInput');
    const catFilter = document.getElementById('vacantCategoryFilter');
    const clearBtn = document.getElementById('clearVacantSearchBtn');
    const summary = document.getElementById('vacantLiveSummary');
    const noResultsBox = document.getElementById('noVacantMatchBox');
    
    if (!searchInput) return;

    const query = searchInput.value.trim().toLowerCase();
    const selectedCategory = catFilter ? catFilter.value : 'all';

    if (clearBtn) {
        clearBtn.style.display = query ? 'block' : 'none';
    }

    const cards = document.querySelectorAll('.dept-stock-card');
    let totalVisibleRows = 0;
    let totalMatchingVacantUnits = 0;
    let visibleCardsCount = 0;

    cards.forEach(card => {
        const cardDeptCode = card.getAttribute('data-dept-code');
        const cardDeptName = card.getAttribute('data-dept-name') || '';

        // Check if card matches department filter
        const deptMatch = (currentQuickDept === 'all' || currentQuickDept === cardDeptCode);

        const rows = card.querySelectorAll('.dept-stock-row');
        let cardVisibleRows = 0;
        let cardVacantSum = 0;

        rows.forEach(row => {
            if (!deptMatch) {
                row.style.display = 'none';
                return;
            }

            const itemName = row.getAttribute('data-item-name') || '';
            const itemCode = row.getAttribute('data-item-code') || '';
            const category = row.getAttribute('data-category') || '';
            const vacantQty = parseInt(row.getAttribute('data-vacant')) || 0;

            const matchesQuery = !query || itemName.includes(query) || itemCode.includes(query) || category.toLowerCase().includes(query) || cardDeptCode.toLowerCase().includes(query) || cardDeptName.includes(query);
            const matchesCategory = (selectedCategory === 'all' || category === selectedCategory);
            const matchesVacant = (!vacantOnlyActive || vacantQty > 0);

            if (matchesQuery && matchesCategory && matchesVacant) {
                row.style.display = '';
                cardVisibleRows++;
                totalVisibleRows++;
                cardVacantSum += vacantQty;
                totalMatchingVacantUnits += vacantQty;
            } else {
                row.style.display = 'none';
            }
        });

        // Hide card if no matching rows or dept didn't match
        if (deptMatch && cardVisibleRows > 0) {
            card.style.display = '';
            visibleCardsCount++;
        } else {
            card.style.display = 'none';
        }
    });

    if (summary) {
        if (query) {
            summary.innerHTML = `🔍 <strong>${totalMatchingVacantUnits} vacant units</strong> found across <strong>${visibleCardsCount} departments</strong> matching "<em>${escapeHtml(query)}</em>"`;
        } else if (vacantOnlyActive) {
            summary.innerHTML = `💡 Showing <strong>${totalVisibleRows} items with available vacant stock</strong> (${totalMatchingVacantUnits} units total)`;
        } else {
            summary.innerHTML = `⚡ Showing all departmental stock &bull; ${visibleCardsCount} departments active`;
        }
    }

    if (noResultsBox) {
        noResultsBox.style.display = (visibleCardsCount === 0) ? 'block' : 'none';
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Initial trigger on load
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('deptVacantSearchInput')) {
        filterDeptVacantStocks();
    }
});
</script>

