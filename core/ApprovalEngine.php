<?php
/**
 * MSEC SMART STOCK - Enterprise Approval Workflow Engine
 * Implements the mandatory operational workflow:
 * Requester -> Coordinator -> HOD -> Store Keeper Verification (Approval Sheet) -> VP/Principal/Director -> Store Keeper (PO/GRN/Issue)
 */

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Auth.php';
require_once __DIR__ . '/AuditLogger.php';

class ApprovalEngine {

    /**
     * Initialize workflow stages when an indent is submitted
     */
    public static function initializeWorkflow(int $requestId, float $totalValue, string $requestType = 'NEW_PURCHASE'): void {
        $pdo = Database::getInstance();

        // Institutional Governance Workflow Hierarchy:
        // HOD endorses departmental indents without autonomous sanction power.
        // Requisitions flow through VP and Principal, with final financial sanction authorized by Management (Director).
        $levels = [
            [
                'level' => 1,
                'title' => 'Department Coordinator Need & Technical Verification',
                'role'  => 'dept_coordinator'
            ],
            [
                'level' => 2,
                'title' => 'Head of Department (HOD) Indent Endorsement',
                'role'  => 'hod'
            ],
            [
                'level' => 3,
                'title' => 'Central Store Keeper Operational Gate & Approval Sheet Verification',
                'role'  => 'store_keeper'
            ],
            [
                'level' => 4,
                'title' => 'Vice Principal Administrative Review & Concurrence',
                'role'  => 'vice_principal'
            ],
            [
                'level' => 5,
                'title' => 'Principal Institutional Academic Review & Recommendation',
                'role'  => 'principal'
            ],
            [
                'level' => 6,
                'title' => 'Director / Management Final Financial Sanction & Allocation',
                'role'  => 'director'
            ],
            [
                'level' => 7,
                'title' => 'Central Store Keeper Material Fulfillment & Distribution',
                'role'  => 'store_keeper'
            ]
        ];

        $stmt = $pdo->prepare("
            INSERT INTO request_approvals (request_id, level_number, level_title, approver_role, status)
            VALUES (?, ?, ?, ?, 'Pending')
        ");
        foreach ($levels as $l) {
            $stmt->execute([$requestId, $l['level'], $l['title'], $l['role']]);
        }
    }

    /**
     * Process workflow transition: Approve, Reject, Return, or Store-Verify
     */
    public static function processAction(int $requestId, string $action, ?string $comments = null, ?array $meta = []): array {
        $pdo = Database::getInstance();
        $user = Auth::user();
        if (!$user) return ['success' => false, 'message' => 'Unauthorized'];

        // Fetch current active approval record
        $stmt = $pdo->prepare("
            SELECT * FROM request_approvals 
            WHERE request_id = ? AND status = 'Pending' 
            ORDER BY level_number ASC LIMIT 1
        ");
        $stmt->execute([$requestId]);
        $currentApproval = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$currentApproval) {
            return ['success' => false, 'message' => 'No pending approval stage found for this requisition.'];
        }

        // Strict role validation (Admin can override for testing/emergency)
        if ($user['role_key'] !== $currentApproval['approver_role'] && !in_array($user['role_key'], ['super_admin', 'admin'], true)) {
            return ['success' => false, 'message' => "You do not have the required role ({$currentApproval['approver_role']}) to take action on this step."];
        }

        $now = date('Y-m-d H:i:s');
        $approverId = $user['id'];

        if ($action === 'Approve') {
            // Mark current stage Approved
            $update = $pdo->prepare("
                UPDATE request_approvals 
                SET status = 'Approved', approver_user_id = ?, comments = ?, action_date = ? 
                WHERE id = ?
            ");
            $update->execute([$approverId, $comments, $now, $currentApproval['id']]);

            // Check if subsequent pending levels remain
            $checkNext = $pdo->prepare("
                SELECT * FROM request_approvals 
                WHERE request_id = ? AND status = 'Pending' 
                ORDER BY level_number ASC LIMIT 1
            ");
            $checkNext->execute([$requestId]);
            $nextApproval = $checkNext->fetch(PDO::FETCH_ASSOC);

            if ($nextApproval) {
                // Determine intermediate request status based on next stage
                $nextStatus = match ($nextApproval['approver_role']) {
                    'hod'              => 'HOD_REVIEW',
                    'store_keeper'     => ($nextApproval['level_number'] == 3) ? 'STORE_REVIEW' : 'STORE_FULFILLMENT',
                    'vice_principal'   => 'VP_REVIEW',
                    'principal'        => 'PRINCIPAL_REVIEW',
                    'director'         => 'MANAGEMENT_APPROVAL',
                    default            => 'Pending Approval'
                };

                $pdo->prepare("UPDATE stock_requests SET current_level = ?, status = ? WHERE id = ?")
                    ->execute([$nextApproval['level_number'], $nextStatus, $requestId]);
            } else {
                // All approval stages fully cleared!
                $pdo->prepare("UPDATE stock_requests SET status = 'APPROVED' WHERE id = ?")
                    ->execute([$requestId]);
            }

            AuditLogger::log(
                "STAGE_APPROVED_L{$currentApproval['level_number']}",
                'Approval Workflow',
                "Requisition #{$requestId} approved at {$currentApproval['level_title']}. Remarks: {$comments}",
                'StockRequest',
                $requestId,
                ['status' => 'Pending'],
                ['status' => 'Approved', 'level' => $currentApproval['level_number']],
                $approverId,
                $user['role_key']
            );

            return ['success' => true, 'message' => "Stage '{$currentApproval['level_title']}' approved successfully."];

        } elseif ($action === 'Reject') {
            $update = $pdo->prepare("
                UPDATE request_approvals 
                SET status = 'Rejected', approver_user_id = ?, comments = ?, action_date = ? 
                WHERE id = ?
            ");
            $update->execute([$approverId, $comments, $now, $currentApproval['id']]);

            $pdo->prepare("UPDATE stock_requests SET status = 'REJECTED' WHERE id = ?")
                ->execute([$requestId]);

            AuditLogger::log(
                "STAGE_REJECTED_L{$currentApproval['level_number']}",
                'Approval Workflow',
                "Requisition #{$requestId} formally rejected by {$user['role_key']}. Reason: {$comments}",
                'StockRequest',
                $requestId,
                ['status' => 'Pending'],
                ['status' => 'REJECTED'],
                $approverId,
                $user['role_key']
            );

            return ['success' => true, 'message' => "Requisition #{$requestId} has been rejected."];

        } elseif ($action === 'Return') {
            // Return for clarification to previous level or requester
            $update = $pdo->prepare("
                UPDATE request_approvals 
                SET status = 'Returned', approver_user_id = ?, comments = ?, action_date = ? 
                WHERE id = ?
            ");
            $update->execute([$approverId, $comments, $now, $currentApproval['id']]);

            $pdo->prepare("UPDATE stock_requests SET status = 'RETURNED' WHERE id = ?")
                ->execute([$requestId]);

            AuditLogger::log(
                "STAGE_RETURNED_L{$currentApproval['level_number']}",
                'Approval Workflow',
                "Requisition #{$requestId} returned for clarification by {$user['role_key']}. Remarks: {$comments}",
                'StockRequest',
                $requestId,
                null,
                ['status' => 'RETURNED', 'remarks' => $comments],
                $approverId,
                $user['role_key']
            );

            return ['success' => true, 'message' => "Requisition #{$requestId} returned for clarification."];
        }

        return ['success' => false, 'message' => 'Invalid workflow action.'];
    }

    /**
     * Store Keeper creates and signs the official verification approval sheet
     */
    public static function createApprovalSheet(int $requestId, array $data, int $storeKeeperId): array {
        $pdo = Database::getInstance();

        $sheetNumber = 'AS-' . date('Y') . '-' . str_pad($requestId, 4, '0', STR_PAD_LEFT);
        $vendorId = !empty($data['vendor_id']) ? (int)$data['vendor_id'] : null;
        $unitPrice = (float)($data['unit_price'] ?? 0);
        $gstPercent = (float)($data['gst_percent'] ?? 18);
        $totalCost = (float)($data['total_cost'] ?? ($unitPrice * (1 + $gstPercent / 100)));
        $quoteRef = $data['quotation_ref'] ?? '';
        $existingStockNotes = $data['existing_stock_notes'] ?? 'No identical buffer stock available in central warehouse.';
        $specNotes = $data['spec_verification_notes'] ?? 'Verified technical specifications against college laboratory standards.';
        $recommendation = $data['recommendation'] ?? 'Recommended for administrative sanction.';

        $stmt = $pdo->prepare("
            INSERT INTO approval_sheets 
            (sheet_number, request_id, store_keeper_id, existing_stock_notes, spec_verification_notes, vendor_id, quotation_ref, item_price, gst_percent, total_cost, recommendation, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, datetime('now', 'localtime'))
        ");
        $stmt->execute([
            $sheetNumber,
            $requestId,
            $storeKeeperId,
            $existingStockNotes,
            $specNotes,
            $vendorId,
            $quoteRef,
            $unitPrice,
            $gstPercent,
            $totalCost,
            $recommendation
        ]);

        $sheetId = (int)$pdo->lastInsertId();

        // Advance request status to STORE_VERIFIED
        $pdo->prepare("UPDATE stock_requests SET status = 'STORE_VERIFIED', total_estimated_value = ? WHERE id = ?")
            ->execute([$totalCost, $requestId]);

        AuditLogger::log(
            'APPROVAL_SHEET_GENERATED',
            'Central Store Operations',
            "Store Keeper generated Official Approval Sheet {$sheetNumber} for Requisition #{$requestId} (Value: ₹" . number_format($totalCost, 2) . ")",
            'ApprovalSheet',
            $sheetId,
            null,
            ['sheet_number' => $sheetNumber, 'total_cost' => $totalCost],
            $storeKeeperId,
            'store_keeper'
        );

        return ['success' => true, 'sheet_id' => $sheetId, 'sheet_number' => $sheetNumber];
    }
}
