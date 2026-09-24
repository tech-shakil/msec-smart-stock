<?php
/**
 * MSEC SMART STOCK - Front Controller & Enterprise Router
 * Mohamed Sathak Engineering College (Autonomous), Kilakarai
 */

require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/core/Database.php';
require_once dirname(__DIR__) . '/core/AuditLogger.php';
require_once dirname(__DIR__) . '/core/Auth.php';
require_once dirname(__DIR__) . '/core/ApprovalEngine.php';

// Models
require_once dirname(__DIR__) . '/models/Inventory.php';
require_once dirname(__DIR__) . '/models/Request.php';
require_once dirname(__DIR__) . '/models/ApprovalSheet.php';
require_once dirname(__DIR__) . '/models/Distribution.php';
require_once dirname(__DIR__) . '/models/Movement.php';
require_once dirname(__DIR__) . '/models/Asset.php';
require_once dirname(__DIR__) . '/models/Maintenance.php';
require_once dirname(__DIR__) . '/models/Procurement.php';
require_once dirname(__DIR__) . '/models/Budget.php';
require_once dirname(__DIR__) . '/models/Notification.php';
require_once dirname(__DIR__) . '/models/Report.php';

Auth::init();

// Demo Role Switcher Handler (For Project Evaluation & Committee Reviews)
if (isset($_GET['switch_role']) && !empty($_GET['switch_role'])) {
    $targetRole = $_GET['switch_role'];
    $pdo = Database::getInstance();
    $stmt = $pdo->prepare("
        SELECT u.*, d.name as dept_name, d.code as dept_code 
        FROM users u 
        LEFT JOIN departments d ON u.dept_id = d.id 
        WHERE u.role_key = ? AND u.status = 'Active' 
        LIMIT 1
    ");
    $stmt->execute([$targetRole]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($u) {
        unset($u['password_hash']);
        $_SESSION['msec_user'] = $u;
        AuditLogger::log('DEMO_ROLE_SWITCHED', 'Authentication', "Reviewer switched persona to {$u['username']} ({$targetRole})", 'User', $u['id'], null, null, $u['id'], $targetRole);
    }
}

$route = $_GET['route'] ?? 'dashboard';

// Public / Auth Handlers
if ($route === 'login') {
    include dirname(__DIR__) . '/views/auth/login.php';
    exit;
}

if ($route === 'do_login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $res = Auth::login($username, $password);

    if ($res['success']) {
        header("Location: index.php?route=dashboard");
    } else {
        $msg = urlencode($res['message'] ?? 'Invalid credentials.');
        header("Location: index.php?route=login&error={$msg}");
    }
    exit;
}

if ($route === 'logout') {
    Auth::logout();
    header("Location: index.php?route=login&msg=" . urlencode("You have signed out successfully."));
    exit;
}

// Strict Authentication Gate (Section 2 & 30: No auto-login on unauthenticated visits)
if (!Auth::check()) {
    header("Location: index.php?route=login");
    exit;
}

$user = Auth::user();

// ==========================================
// ACTION HANDLERS (POST)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($route) {
        // --- SYSTEM ADMIN USER MANAGEMENT ---
        case 'admin_create_user':
            $res = Auth::createUser($_POST, $user['id']);
            $flag = $res['success'] ? 'msg' : 'error';
            header("Location: index.php?route=admin_users&{$flag}=" . urlencode($res['message']));
            exit;

        case 'admin_reset_password':
            $targetUserId = (int)$_POST['user_id'];
            $newPassword = $_POST['new_password'];
            $res = Auth::resetPasswordByAdmin($targetUserId, $newPassword, $user['id']);
            $flag = $res['success'] ? 'msg' : 'error';
            header("Location: index.php?route=admin_users&{$flag}=" . urlencode($res['message']));
            exit;

        case 'admin_toggle_lock':
            $targetUserId = (int)$_POST['user_id'];
            $lock = (bool)$_POST['lock'];
            $reason = $_POST['reason'] ?? 'System Admin intervention';
            $res = Auth::toggleUserLock($targetUserId, $lock, $reason, $user['id']);
            header("Location: index.php?route=admin_users&msg=" . urlencode($res['message']));
            exit;

        case 'admin_toggle_status':
            $targetUserId = (int)$_POST['user_id'];
            $status = $_POST['status'];
            $res = Auth::toggleUserStatus($targetUserId, $status, $user['id']);
            header("Location: index.php?route=admin_users&msg=" . urlencode($res['message']));
            exit;

        case 'admin_update_role':
            $targetUserId = (int)$_POST['user_id'];
            $roleKey = $_POST['role_key'];
            $deptId = !empty($_POST['dept_id']) ? (int)$_POST['dept_id'] : null;
            $designation = $_POST['designation'] ?? null;
            $res = Auth::updateUserRole($targetUserId, $roleKey, $deptId, $designation, $user['id']);
            header("Location: index.php?route=admin_users&msg=" . urlencode($res['message']));
            exit;

        // --- REQUISITION & APPROVAL ACTIONS ---
        case 'create_request':
            if (!Auth::can('create_request')) {
                header("Location: index.php?route=unauthorized");
                exit;
            }
            $deptId = (int)$_POST['dept_id'];
            $labId = !empty($_POST['lab_id']) ? (int)$_POST['lab_id'] : null;
            $purpose = $_POST['purpose'];
            $reqDate = $_POST['required_date'];
            $items = $_POST['items'] ?? [];
            $reqId = StockRequest::create($deptId, $labId, $user['id'], $purpose, $reqDate, $items);
            if ($reqId) {
                AuditLogger::log('REQUEST_CREATED', 'Stock Requisition', "Raised requisition #{$reqId}: {$purpose}", 'StockRequest', $reqId);
                Notification::send('NEW_INDENT', 'New Indent Raised', "Requisition #{$reqId} submitted for review", "index.php?route=view_request&id={$reqId}", null, 'dept_coordinator');
                header("Location: index.php?route=view_request&id={$reqId}");
            } else {
                header("Location: index.php?route=requests&error=Failed+to+create+requisition");
            }
            exit;

        case 'process_approval':
            $reqId = (int)$_POST['request_id'];
            $action = $_POST['action'] ?? 'Approve';
            $comments = $_POST['comments'] ?? null;
            $res = ApprovalEngine::processAction($reqId, $action, $comments);
            header("Location: index.php?route=view_request&id={$reqId}");
            exit;

        case 'create_approval_sheet':
            if (!Auth::can('store_verify')) {
                header("Location: index.php?route=unauthorized");
                exit;
            }
            $reqId = (int)$_POST['request_id'];
            $sheetRes = ApprovalEngine::createApprovalSheet($reqId, $_POST, $user['id']);
            header("Location: index.php?route=approval_sheet&id={$sheetRes['sheet_id']}");
            exit;

        // --- INVENTORY & STOCK DISTRIBUTION ---
        case 'do_adjustment':
            if (!Auth::can('adjust_stock')) {
                header("Location: index.php?route=unauthorized");
                exit;
            }
            $storeId = (int)$_POST['store_id'];
            $itemId = (int)$_POST['item_id'];
            $newQty = (float)$_POST['new_quantity'];
            $reason = $_POST['reason'];
            $doc = $_POST['supporting_doc'] ?? null;
            Inventory::adjustStock($storeId, $itemId, $newQty, $reason, $doc, $user['id']);
            AuditLogger::log('STOCK_ADJUSTMENT', 'Inventory', "Adjusted item #{$itemId} in store #{$storeId} to {$newQty}. Reason: {$reason}", 'Stock', $itemId);
            header("Location: index.php?route=inventory&msg=Stock+adjusted+successfully");
            exit;

        case 'create_issue':
            if (!Auth::can('issue_stock')) {
                header("Location: index.php?route=unauthorized");
                exit;
            }
            $sourceStoreId = (int)$_POST['source_store_id'];
            $targetDeptId = (int)$_POST['target_dept_id'];
            $targetLabId = !empty($_POST['target_lab_id']) ? (int)$_POST['target_lab_id'] : null;
            $voucherType = $_POST['voucher_type'];
            $items = $_POST['items'] ?? [];
            $filteredItems = array_filter($items, fn($it) => !empty($it['item_id']) && !empty($it['quantity']));
            $issueId = Distribution::createIssue($sourceStoreId, $targetDeptId, $targetLabId, $user['id'], $voucherType, $filteredItems);
            if ($issueId) {
                AuditLogger::log('STOCK_ISSUED', 'Distribution', "Generated Store Voucher #{$issueId} to Dept #{$targetDeptId}", 'StockIssue', $issueId);
                header("Location: index.php?route=view_voucher&id={$issueId}");
            } else {
                header("Location: index.php?route=distribution&error=Failed+to+issue");
            }
            exit;

        case 'ack_issue':
            if (!Auth::can('ack_receipt')) {
                header("Location: index.php?route=unauthorized");
                exit;
            }
            $issueId = (int)$_POST['issue_id'];
            $deptId = (int)$_POST['dept_id'];
            $remarks = $_POST['remarks'] ?? null;
            Distribution::acknowledge($issueId, $deptId, $user['id'], $remarks);
            AuditLogger::log('VOUCHER_ACKNOWLEDGED', 'Distribution', "Acknowledged delivery for Voucher #{$issueId}", 'StockIssue', $issueId);
            header("Location: index.php?route=distribution&msg=Receipt+acknowledged");
            exit;

        // --- TRANSFERS & ASSETS ---
        case 'create_movement':
            if (!Auth::can('create_transfer')) {
                header("Location: index.php?route=unauthorized");
                exit;
            }
            $movType = $_POST['movement_type'];
            $sourceDeptId = !empty($_POST['source_dept_id']) ? (int)$_POST['source_dept_id'] : null;
            $targetDeptId = !empty($_POST['target_dept_id']) ? (int)$_POST['target_dept_id'] : null;
            $notes = $_POST['notes'] ?? '';
            $items = $_POST['items'] ?? [];
            $movId = Movement::createTransfer($movType, $sourceDeptId, $targetDeptId, null, null, $user['id'], $notes, $items);
            AuditLogger::log('TRANSFER_INITIATED', 'Movement', "Initiated stock transfer #{$movId}", 'StockMovement', $movId);
            header("Location: index.php?route=movement&msg=Transfer+initiated");
            exit;

        case 'create_asset':
            if (!Auth::can('create_asset')) {
                header("Location: index.php?route=unauthorized");
                exit;
            }
            $assetData = $_POST;
            $newAssetId = Asset::create($assetData);
            AuditLogger::log('ASSET_REGISTERED', 'Asset Management', "Registered new asset {$assetData['asset_id']}", 'Asset', $newAssetId);
            header("Location: index.php?route=assets&msg=Asset+registered+successfully");
            exit;

        case 'report_damage':
            $assetId = (int)$_POST['asset_id'];
            $issue = $_POST['issue'];
            $tckId = Maintenance::createTicket(['asset_id' => $assetId, 'issue_reported' => $issue], $user['id']);
            header("Location: index.php?route=maintenance&msg=Service+ticket+opened+in+repair+bay");
            exit;

        case 'create_maintenance_ticket':
            $tckId = Maintenance::createTicket($_POST, $user['id']);
            header("Location: index.php?route=maintenance&msg=Service+ticket+created");
            exit;

        case 'update_ticket':
            if (!Auth::can('perform_maintenance')) {
                header("Location: index.php?route=unauthorized");
                exit;
            }
            $ticketId = (int)$_POST['ticket_id'];
            Maintenance::updateTicket($ticketId, $_POST, $user['id']);
            header("Location: index.php?route=maintenance&msg=Ticket+record+updated");
            exit;

        // --- PROCUREMENT & GRN ---
        case 'create_po':
            if (!Auth::can('create_po')) {
                header("Location: index.php?route=unauthorized");
                exit;
            }
            $poId = Procurement::createPO($_POST, $user['id']);
            if ($poId) {
                AuditLogger::log('PO_ISSUED', 'Procurement', "Issued Purchase Order #{$poId}", 'PurchaseOrder', $poId);
                header("Location: index.php?route=procurement&msg=Purchase+Order+issued+successfully");
            } else {
                header("Location: index.php?route=procurement&error=Failed+to+issue+Purchase+Order");
            }
            exit;

        case 'add_quotation':
            if (!Auth::can('create_po')) {
                header("Location: index.php?route=unauthorized");
                exit;
            }
            $quoteId = Procurement::addQuotation($_POST);
            AuditLogger::log('QUOTATION_ADDED', 'Procurement', "Added vendor quotation {$quoteId}", 'Quotation', $quoteId);
            header("Location: index.php?route=procurement&tab=quotes&msg=Quotation+added+to+comparison+matrix");
            exit;

        case 'create_vendor':
            if (!Auth::can('create_po') && !Auth::can('manage_settings')) {
                header("Location: index.php?route=unauthorized");
                exit;
            }
            $vId = Procurement::createVendor($_POST);
            AuditLogger::log('VENDOR_REGISTERED', 'Procurement', "Registered vendor #{$vId}", 'Vendor', $vId);
            header("Location: index.php?route=procurement&tab=vendors&msg=Vendor+registered+successfully");
            exit;

        case 'record_audit':
            if (!Auth::can('conduct_audit')) {
                header("Location: index.php?route=unauthorized");
                exit;
            }
            $cycle = $_POST['cycle'] ?? 'Quarterly';
            $storeId = (int)($_POST['store_id'] ?? 1);
            $remarks = $_POST['remarks'] ?? 'Physical stock reconciled with zero unaccounted discrepancies.';
            $pdo = Database::getInstance();
            $code = "AUD-" . date('Y') . "-" . rand(1000, 9999);
            $stmt = $pdo->prepare("INSERT INTO stock_verifications (verification_code, store_id, verification_type, conducted_by, verified_date, status, remarks) VALUES (?, ?, ?, ?, date('now'), 'Completed', ?)");
            $stmt->execute([$code, $storeId, $cycle, $user['id'], $remarks]);
            AuditLogger::log('PHYSICAL_AUDIT_COMPLETED', 'Audit', "Physical stock audit conducted: {$code}", 'StockVerification', (int)$pdo->lastInsertId());
            header("Location: index.php?route=verification&msg=Physical+audit+completed+and+certified");
            exit;

        case 'admin_settings':
            if (!Auth::can('manage_settings')) {
                header("Location: index.php?route=unauthorized");
                exit;
            }
            $pdo = Database::getInstance();
            $settingsToSave = $_POST['settings'] ?? [];
            $updateStmt = $pdo->prepare("INSERT OR REPLACE INTO system_settings (setting_key, setting_value, category, description) VALUES (?, ?, ?, ?)");
            foreach ($settingsToSave as $k => $v) {
                $category = $_POST['categories'][$k] ?? 'General';
                $desc = $_POST['descriptions'][$k] ?? '';
                $updateStmt->execute([$k, $v, $category, $desc]);
            }
            AuditLogger::log('SETTINGS_UPDATED', 'System Administration', 'Institutional settings updated by System Administrator', 'SystemSettings', null);
            header("Location: index.php?route=admin_settings&msg=Settings+saved+successfully");
            exit;

        case 'create_grn':
            if (!Auth::can('create_grn')) {
                header("Location: index.php?route=unauthorized");
                exit;
            }
            $grnData = [
                'po_id'         => !empty($_POST['po_id']) ? (int)$_POST['po_id'] : null,
                'vendor_id'     => (int)$_POST['vendor_id'],
                'store_id'      => (int)$_POST['store_id'],
                'received_date' => $_POST['received_date'],
                'verified_by'   => $user['id'],
                'invoice_no'    => $_POST['invoice_no'],
                'remarks'       => $_POST['remarks'] ?? ''
            ];
            $items = $_POST['items'] ?? [];
            Procurement::createGRN($grnData, $items);
            AuditLogger::log('GRN_CREATED', 'Procurement', "GRN receipt generated and inventory balances credited", 'GRN', null);
            header("Location: index.php?route=procurement&tab=grn&msg=GRN+receipt+processed");
            exit;
    }
}

// GET status transition handlers for movement
if ($route === 'advance_movement' && isset($_GET['id'], $_GET['status'])) {
    if (!Auth::can('advance_transfer')) {
        header("Location: index.php?route=unauthorized");
        exit;
    }
    Movement::advanceStatus((int)$_GET['id'], $_GET['status'], $user['id']);
    AuditLogger::log('TRANSFER_STATUS_ADVANCED', 'Movement', "Transfer #{$_GET['id']} moved to status {$_GET['status']}", 'StockMovement', (int)$_GET['id']);
    header("Location: index.php?route=movement");
    exit;
}

// Standalone Printable Routes
if ($route === 'view_voucher') {
    include dirname(__DIR__) . '/views/distribution/voucher.php';
    exit;
}

if ($route === 'view_po') {
    include dirname(__DIR__) . '/views/procurement/po.php';
    exit;
}

// RBAC Route Guard: Verify role permissions before rendering
if (!Auth::canAccessRoute($route)) {
    $route = 'unauthorized';
}

// ==========================================
// RENDER STANDARD VIEW WITH LAYOUT
// ==========================================
include dirname(__DIR__) . '/views/layout/header.php';

switch ($route) {
    case 'unauthorized':
        include dirname(__DIR__) . '/views/errors/unauthorized.php';
        break;
    case 'dashboard':
        include dirname(__DIR__) . '/views/dashboard/index.php';
        break;
    case 'inventory':
        include dirname(__DIR__) . '/views/inventory/index.php';
        break;
    case 'requests':
    case 'new_request':
        include dirname(__DIR__) . '/views/requests/index.php';
        break;
    case 'view_request':
        include dirname(__DIR__) . '/views/requests/view.php';
        break;
    case 'approval_sheet':
        include dirname(__DIR__) . '/views/approval_sheets/view.php';
        break;
    case 'distribution':
    case 'issue_stock':
        include dirname(__DIR__) . '/views/distribution/index.php';
        break;
    case 'movement':
        include dirname(__DIR__) . '/views/movement/index.php';
        break;
    case 'assets':
        include dirname(__DIR__) . '/views/assets/index.php';
        break;
    case 'maintenance':
        include dirname(__DIR__) . '/views/maintenance/index.php';
        break;
    case 'procurement':
        include dirname(__DIR__) . '/views/procurement/index.php';
        break;
    case 'budget':
        include dirname(__DIR__) . '/views/budget/index.php';
        break;
    case 'verification':
        include dirname(__DIR__) . '/views/verification/index.php';
        break;
    case 'reports':
        include dirname(__DIR__) . '/views/reports/index.php';
        break;
    case 'admin_users':
        include dirname(__DIR__) . '/views/admin/users.php';
        break;
    case 'admin_security':
        include dirname(__DIR__) . '/views/admin/security.php';
        break;
    case 'admin_audit':
        include dirname(__DIR__) . '/views/admin/audit.php';
        break;
    case 'admin_masters':
        include dirname(__DIR__) . '/views/admin/masters.php';
        break;
    case 'admin_settings':
        include dirname(__DIR__) . '/views/admin/settings.php';
        break;
    default:
        include dirname(__DIR__) . '/views/dashboard/index.php';
        break;
}

include dirname(__DIR__) . '/views/layout/footer.php';
