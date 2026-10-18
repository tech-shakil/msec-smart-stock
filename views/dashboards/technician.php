<?php
/**
 * MSEC SMART STOCK - Laboratory Technician Dashboard
 * Section 16: Service Tickets, Hardware Repair Bay, Diagnostic Logging,
 * Calibration Alerts, and Maintenance Completion.
 */

$pdo = Database::getInstance();
$user = Auth::user();
$kpis = Maintenance::getKPIs();

// Open & In-Progress Tickets
$tickets = Maintenance::getAll();

// Repaired recent tickets
$completed = $pdo->query("
    SELECT m.*, a.asset_id as asset_tag, a.asset_name, u.full_name as reporter_name 
    FROM asset_maintenance m 
    JOIN assets a ON m.asset_id = a.id 
    JOIN users u ON m.reported_by = u.id 
    WHERE m.status = 'Repaired' 
    ORDER BY m.id DESC LIMIT 4
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-header">
    <div>
        <h2 class="page-title">Hardware Maintenance & Diagnostic Service Bay</h2>
        <div class="page-desc">Technician: <strong><?= htmlspecialchars($user['full_name']) ?></strong> &bull; Laboratory Repair Operations & Calibration &bull; <?= APP_INSTITUTION ?></div>
    </div>
</div>

<!-- Technician KPIs -->
<div class="stats-grid">
    <div class="stat-card danger">
        <div class="stat-title">Active Service Tickets</div>
        <div class="stat-value"><?= number_format($kpis['open_tickets']) ?></div>
        <div class="stat-subtext">Hardware faults in repair queue</div>
    </div>
    <div class="stat-card warning">
        <div class="stat-title">Units Under Repair</div>
        <div class="stat-value"><?= number_format($kpis['assets_under_repair']) ?></div>
        <div class="stat-subtext">Components disassembled on workbench</div>
    </div>
    <div class="stat-card success">
        <div class="stat-title">Repairs Completed (This Term)</div>
        <div class="stat-value"><?= number_format($kpis['completed_tickets']) ?></div>
        <div class="stat-subtext">Restored to active laboratory service</div>
    </div>
    <div class="stat-card primary">
        <div class="stat-title">Total Repair Spares Cost</div>
        <div class="stat-value"><?= CURRENCY_SYMBOL ?><?= number_format($kpis['total_repair_cost']) ?></div>
        <div class="stat-subtext">Components and replacement parts</div>
    </div>
</div>

<div class="responsive-split-grid">
    <!-- Active Service Ticket Queue -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">🔧 Active Hardware Service Tickets</div>
            <span class="badge badge-danger"><?= $kpis['open_tickets'] ?> Requiring Action</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($tickets)): ?>
                <div class="empty-state-card">
                    <div class="empty-state-icon">✅</div>
                    <div class="empty-state-title">No Active Service Tickets</div>
                    <div class="empty-state-sub">Zero outstanding service tickets. All laboratory computing and electronic equipment functioning normally.</div>
                </div>
            <?php else: ?>
                <div class="data-table-container">
                    <table class="data-table data-table-compact">
                        <thead>
                            <tr>
                                <th>Ticket #</th>
                                <th>Equipment / Tag</th>
                                <th>Reported Problem</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Workbench Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tickets as $t): ?>
                                <tr>
                                    <td><code><?= htmlspecialchars($t['ticket_number']) ?></code></td>
                                    <td>
                                        <strong><?= htmlspecialchars($t['asset_name']) ?></strong>
                                        <div style="font-size: 11px; color: #64748b;"><code><?= htmlspecialchars($t['asset_tag']) ?></code> &bull; <?= htmlspecialchars($t['dept_code']) ?></div>
                                    </td>
                                    <td style="font-size: 12px; max-width: 220px;">
                                        <?= htmlspecialchars(substr($t['issue_reported'], 0, 45)) ?>...
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?= $t['status'] === 'Repaired' ? 'badge-success' : 'badge-warning' ?>">
                                            <?= htmlspecialchars($t['status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-primary btn-sm" onclick="openUpdateTicketModal(<?= $t['id'] ?>, '<?= htmlspecialchars(addslashes($t['ticket_number'])) ?>', '<?= htmlspecialchars(addslashes($t['asset_name'])) ?>', '<?= htmlspecialchars(addslashes($t['issue_reported'])) ?>', '<?= htmlspecialchars(addslashes($t['diagnosis'] ?? '')) ?>', '<?= $t['repair_cost'] ?>', '<?= $t['status'] ?>')">
                                            Update Ticket &rarr;
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recently Restored Equipment -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">✅ Restored & Repaired Equipment</div>
            <span class="badge badge-success">Quality Inspected</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($completed)): ?>
                <div class="empty-state-card">
                    <div class="empty-state-icon">🔧</div>
                    <div class="empty-state-title">No Recent Repaired Units</div>
                    <div class="empty-state-sub">Completed repair logs with diagnosis notes and parts cost will be archived here.</div>
                </div>
            <?php else: ?>
                <div class="data-table-container">
                    <table class="data-table data-table-compact">
                        <thead>
                            <tr>
                                <th>Ticket</th>
                                <th>Equipment</th>
                                <th class="text-right">Parts Cost</th>
                                <th>Resolution</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($completed as $c): ?>
                                <tr>
                                    <td><code><?= htmlspecialchars($c['ticket_number']) ?></code></td>
                                    <td>
                                        <strong><?= htmlspecialchars($c['asset_name']) ?></strong>
                                        <div style="font-size: 10.5px; color: #64748b;"><?= htmlspecialchars($c['asset_tag']) ?></div>
                                    </td>
                                    <td class="text-right"><?= CURRENCY_SYMBOL ?><?= number_format($c['repair_cost']) ?></td>
                                    <td style="font-size: 11.5px;"><?= htmlspecialchars(substr($c['resolution_notes'] ?? 'Restored', 0, 25)) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal: Update Service Ticket (Technician Diagnosis & Completion) -->
<div id="updateTicketModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.65); z-index:9999; align-items:center; justify-content:center; padding:20px;">
    <div style="background:#ffffff; width:580px; max-width:100%; border-radius:16px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.4); overflow:hidden;">
        <div style="background:#102a43; color:#ffffff; padding:18px 24px; display:flex; justify-content:space-between; align-items:center;">
            <h3 style="margin:0; font-size:17px; font-weight:700;">🔧 Technician Workbench Diagnosis</h3>
            <button type="button" onclick="closeUpdateTicketModal()" style="background:none; border:none; color:#ffffff; font-size:22px; cursor:pointer;">&times;</button>
        </div>
        <form method="POST" action="index.php?route=update_ticket" style="padding:24px;">
            <input type="hidden" name="ticket_id" id="tck_id">
            
            <div style="background:#f8fafc; border-left:4px solid #2563eb; padding:12px; border-radius:6px; margin-bottom:16px; font-size:13px;">
                Ticket: <strong id="tck_num"></strong> &bull; Equipment: <strong id="tck_asset"></strong><br>
                Reported Issue: <span id="tck_issue" style="color:#b91c1c;"></span>
            </div>

            <div class="form-group">
                <label class="form-label">Technical Diagnosis *</label>
                <textarea name="diagnosis" id="tck_diag" class="form-control" rows="2" placeholder="e.g. Blown capacitor on power supply rail. Replacing with 450V 220uF unit." required></textarea>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
                <div class="form-group">
                    <label class="form-label">Service Status *</label>
                    <select name="status" id="tck_status" class="form-control" required>
                        <option value="In Progress">In Progress (Awaiting Spares)</option>
                        <option value="Under Repair">Under Repair (On Workbench)</option>
                        <option value="Repaired">Repaired & Tested (Restore to Service)</option>
                        <option value="Condemned">Beyond Economical Repair (Condemn)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Spare Parts Cost (₹)</label>
                    <input type="number" step="0.01" name="repair_cost" id="tck_cost" class="form-control" value="0.00">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Resolution / Service Notes</label>
                <textarea name="resolution_notes" class="form-control" rows="2" placeholder="Details of component replacement and test results..."></textarea>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; border-top:1px solid #e2e8f0; padding-top:16px;">
                <button type="button" class="btn btn-secondary" onclick="closeUpdateTicketModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Workbench Record</button>
            </div>
        </form>
    </div>
</div>

<script>
function openUpdateTicketModal(id, num, asset, issue, diag, cost, status) {
    document.getElementById('tck_id').value = id;
    document.getElementById('tck_num').textContent = num;
    document.getElementById('tck_asset').textContent = asset;
    document.getElementById('tck_issue').textContent = issue;
    document.getElementById('tck_diag').value = diag;
    document.getElementById('tck_cost').value = cost;
    document.getElementById('tck_status').value = status;
    document.getElementById('updateTicketModal').style.display = 'flex';
}
function closeUpdateTicketModal() {
    document.getElementById('updateTicketModal').style.display = 'none';
}
</script>
