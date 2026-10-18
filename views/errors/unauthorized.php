<?php
/**
 * MSEC SMART STOCK - Access Restricted View (403 RBAC Guard)
 */
$user = Auth::user();
$currentRole = $user['role_key'] ?? 'super_admin';
$roleName = ROLES[$currentRole]['name'] ?? $currentRole;
$roleLevel = ROLES[$currentRole]['level'] ?? 'User';
?>

<div style="max-width: 680px; margin: 40px auto; padding: 20px;">
    <div class="card" style="text-align: center; padding: 40px 32px; border: 1px solid #fed7aa; box-shadow: 0 10px 25px -5px rgba(245, 158, 11, 0.1);">
        <div style="width: 72px; height: 72px; margin: 0 auto 20px; background: linear-gradient(135deg, #fef3c7, #fee2e2); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px; border: 2px solid #f59e0b;">
            🛡️
        </div>

        <span class="badge badge-warning" style="font-size: 13px; padding: 6px 16px; margin-bottom: 12px; font-weight: 700;">
            Institutional Role Policy Enforcement
        </span>

        <h2 style="font-family: var(--font-heading); font-size: 24px; font-weight: 800; color: #0f172a; margin-top: 8px;">
            Access Restricted to Authorized Roles
        </h2>

        <p style="font-size: 14.5px; color: #64748b; margin-top: 10px; line-height: 1.6;">
            You are currently signed in as <strong><?= htmlspecialchars($roleName) ?></strong> (<em><?= htmlspecialchars($roleLevel) ?></em>). This section contains privileged operational data restricted by the college governance policy.
        </p>

        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; margin: 24px 0; text-align: left; font-size: 13.5px;">
            <div style="font-weight: 700; color: #1e293b; margin-bottom: 8px;">Your Permitted Privileges:</div>
            <ul style="padding-left: 20px; color: #475569; line-height: 1.7;">
                <?php if (in_array($currentRole, ['technician'])): ?>
                    <li>Raise stock requisitions and indents for class & laboratory needs</li>
                    <li>Track status of your submitted indents and approval lifecycle</li>
                    <li>Inspect equipment and assets assigned to your department/lab</li>
                    <li>Report damaged equipment or request maintenance repairs</li>
                <?php elseif (in_array($currentRole, ['hod', 'dept_coordinator', 'lab_incharge'])): ?>
                    <li>Manage and audit departmental / laboratory inventory</li>
                    <li>Approve departmental stock indents (Tier 1 approvals)</li>
                    <li>Acknowledge material receipts and initiate inter-department transfers</li>
                <?php elseif ($currentRole === 'auditor'): ?>
                    <li>Independent read-only audit inspection across all store registers and logs</li>
                    <li>Review stock ledger reconciliation and physical audit verifications</li>
                <?php else: ?>
                    <li>Standard operational workflow based on designated role profile</li>
                <?php endif; ?>
            </ul>
        </div>

        <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
            <a href="index.php?route=dashboard" class="btn btn-primary" style="padding: 10px 24px;">
                &larr; Return to My Dashboard
            </a>
            <button onclick="document.getElementById('switch_role').focus();" class="btn btn-secondary" style="padding: 10px 20px;">
                🎭 Switch Demo Role (Top Right)
            </button>
        </div>
    </div>
</div>
