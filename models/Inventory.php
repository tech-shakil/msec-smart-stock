<?php
/**
 * MSEC SMART STOCK - Inventory Model
 */

require_once dirname(__DIR__) . '/core/Database.php';

class Inventory {

    public static function getAllItems(): array {
        $pdo = Database::getInstance();
        $stmt = $pdo->query("SELECT i.*, c.name as category_name, c.type as category_type, u.code as unit_code,
                             COALESCE(SUM(s.available_quantity), 0) as total_available,
                             COALESCE(SUM(s.reserved_quantity), 0) as total_reserved
                             FROM items i
                             LEFT JOIN item_categories c ON i.category_id = c.id
                             LEFT JOIN units u ON i.unit_id = u.id
                             LEFT JOIN stock s ON i.id = s.item_id
                             GROUP BY i.id
                             ORDER BY i.item_name ASC");
        return $stmt->fetchAll();
    }

    public static function getStockByStore(int $storeId): array {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT s.*, i.item_code, i.item_name, i.unit_cost, i.min_stock, i.max_stock, i.is_consumable,
                               c.name as category_name, u.code as unit_code,
                               (s.available_quantity * i.unit_cost) as total_value
                               FROM stock s
                               JOIN items i ON s.item_id = i.id
                               JOIN item_categories c ON i.category_id = c.id
                               JOIN units u ON i.unit_id = u.id
                               WHERE s.store_id = ?
                               ORDER BY i.item_name ASC");
        $stmt->execute([$storeId]);
        return $stmt->fetchAll();
    }

    public static function getLowStockItems(): array {
        $pdo = Database::getInstance();
        $stmt = $pdo->query("SELECT s.*, i.item_code, i.item_name, i.min_stock, u.code as unit_code, st.name as store_name
                             FROM stock s
                             JOIN items i ON s.item_id = i.id
                             JOIN units u ON i.unit_id = u.id
                             JOIN stores st ON s.store_id = st.id
                             WHERE s.available_quantity <= i.min_stock
                             ORDER BY s.available_quantity ASC");
        return $stmt->fetchAll();
    }

    public static function getDeadStockItems(): array {
        $pdo = Database::getInstance();
        // Identify items with no movement for 1, 2, or 3 years (Section 21)
        $stmt = $pdo->query("SELECT s.*, i.item_code, i.item_name, i.unit_cost, (s.available_quantity * i.unit_cost) as total_value,
                             st.name as store_name, u.code as unit_code,
                             s.last_movement_date
                             FROM stock s
                             JOIN items i ON s.item_id = i.id
                             JOIN units u ON i.unit_id = u.id
                             JOIN stores st ON s.store_id = st.id
                             WHERE s.available_quantity > 0 
                             AND (s.last_movement_date IS NULL OR s.last_movement_date <= date('now', '-1 year'))
                             ORDER BY s.last_movement_date ASC");
        return $stmt->fetchAll();
    }

    public static function adjustStock(int $storeId, int $itemId, float $newQuantity, string $reason, ?string $supportingDoc, int $approvedBy): bool {
        $pdo = Database::getInstance();
        $pdo->beginTransaction();

        try {
            // Get current stock
            $stmt = $pdo->prepare("SELECT available_quantity FROM stock WHERE store_id = ? AND item_id = ?");
            $stmt->execute([$storeId, $itemId]);
            $currentStock = $stmt->fetchColumn();
            $oldQty = $currentStock !== false ? (float)$currentStock : 0.0;
            $diff = $newQuantity - $oldQty;

            // Update or insert stock
            if ($currentStock !== false) {
                $upd = $pdo->prepare("UPDATE stock SET available_quantity = ?, last_movement_date = date('now') WHERE store_id = ? AND item_id = ?");
                $upd->execute([$newQuantity, $storeId, $itemId]);
            } else {
                $ins = $pdo->prepare("INSERT INTO stock (store_id, item_id, available_quantity, store_location, last_movement_date) VALUES (?, ?, ?, 'General Shelf', date('now'))");
                $ins->execute([$storeId, $itemId, $newQuantity]);
            }

            // Create stock adjustment record
            $adjCode = 'ADJ-' . date('Ymd') . '-' . rand(1000, 9999);
            $adjStmt = $pdo->prepare("INSERT INTO stock_adjustments (adjustment_code, store_id, item_id, old_quantity, new_quantity, difference, reason, supporting_document, approved_by) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $adjStmt->execute([$adjCode, $storeId, $itemId, $oldQty, $newQuantity, $diff, $reason, $supportingDoc, $approvedBy]);

            // Create ledger entry
            $itemCostStmt = $pdo->prepare("SELECT unit_cost FROM items WHERE id = ?");
            $itemCostStmt->execute([$itemId]);
            $unitCost = (float)$itemCostStmt->fetchColumn();

            $ledgerStmt = $pdo->prepare("INSERT INTO stock_transactions (transaction_type, item_id, store_id, quantity, unit_cost, reference_type, notes, created_by) 
                                         VALUES ('ADJUSTMENT', ?, ?, ?, ?, 'Stock Adjustment', ?, ?)");
            $ledgerStmt->execute([$itemId, $storeId, $diff, $unitCost, $reason, $approvedBy]);

            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }

    /**
     * Get live departmental stock matrix across all departments or for a specific department:
     * Differentiates between 'In Use' and 'Vacant / Not In Use / Available (Extra)'.
     */
    public static function getDepartmentStockMatrix(?int $filterDeptId = null): array {
        $pdo = Database::getInstance();
        
        $sql = "SELECT d.id as dept_id, d.code as dept_code, d.name as dept_name,
                       s.id as store_id, s.name as store_name
                FROM departments d
                LEFT JOIN stores s ON s.dept_id = d.id
                WHERE 1=1";
        if ($filterDeptId) {
            $sql .= " AND d.id = " . (int)$filterDeptId;
        }
        $sql .= " ORDER BY d.id ASC";
        $depts = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($depts as $d) {
            $storeId = $d['store_id'];
            $items = [];
            $totalVacant = 0;
            $totalInUse = 0;
            $totalValuation = 0.0;

            if ($storeId) {
                $stkStmt = $pdo->prepare("
                    SELECT s.available_quantity as vacant_qty, s.reserved_quantity as in_use_qty,
                           s.store_location,
                           i.id as item_id, i.item_code, i.item_name, i.unit_cost, i.is_consumable,
                           c.name as category_name, u.code as unit_code
                    FROM stock s
                    JOIN items i ON s.item_id = i.id
                    JOIN item_categories c ON i.category_id = c.id
                    JOIN units u ON i.unit_id = u.id
                    WHERE s.store_id = ? AND (s.available_quantity > 0 OR s.reserved_quantity > 0)
                    ORDER BY s.available_quantity DESC, i.item_name ASC
                ");
                $stkStmt->execute([$storeId]);
                $items = $stkStmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($items as $it) {
                    $totalVacant += (int)$it['vacant_qty'];
                    $totalInUse += (int)$it['in_use_qty'];
                    $totalValuation += ((float)$it['vacant_qty'] + (float)$it['in_use_qty']) * (float)$it['unit_cost'];
                }
            }

            // Asset Breakdown
            $astStmt = $pdo->prepare("
                SELECT asset_status, count(*) as count 
                FROM assets 
                WHERE dept_id = ? 
                GROUP BY asset_status
            ");
            $astStmt->execute([$d['dept_id']]);
            $assetCounts = ['In Use' => 0, 'Available' => 0, 'Under Repair' => 0, 'Under Maintenance' => 0];
            foreach ($astStmt->fetchAll(PDO::FETCH_ASSOC) as $ac) {
                $assetCounts[$ac['asset_status']] = (int)$ac['count'];
            }

            $result[] = [
                'dept_id'        => $d['dept_id'],
                'dept_code'      => $d['dept_code'],
                'dept_name'      => $d['dept_name'],
                'store_id'       => $storeId,
                'store_name'     => $d['store_name'],
                'total_vacant'   => $totalVacant,
                'total_in_use'   => $totalInUse,
                'total_valuation'=> $totalValuation,
                'asset_counts'   => $assetCounts,
                'items'          => $items
            ];
        }

        return $result;
    }

    /**
     * Get item surplus and utilization across all departments:
     * Shows which departments have this item 'In Use' vs 'Vacant / Not in Use / Extra'.
     */
    public static function getItemSurplusMatrix(int $itemId): array {
        $pdo = Database::getInstance();

        // Item info
        $itemStmt = $pdo->prepare("SELECT i.*, u.code as unit_code, c.name as category_name FROM items i JOIN units u ON i.unit_id = u.id JOIN item_categories c ON i.category_id = c.id WHERE i.id = ?");
        $itemStmt->execute([$itemId]);
        $item = $itemStmt->fetch(PDO::FETCH_ASSOC);
        if (!$item) return [];

        // Central Store
        $centralStmt = $pdo->prepare("SELECT available_quantity, reserved_quantity FROM stock WHERE store_id = 1 AND item_id = ?");
        $centralStmt->execute([$itemId]);
        $centralStock = $centralStmt->fetch(PDO::FETCH_ASSOC) ?: ['available_quantity' => 0, 'reserved_quantity' => 0];

        // All departments
        $deptStmt = $pdo->prepare("
            SELECT d.id as dept_id, d.code as dept_code, d.name as dept_name,
                   COALESCE(s.available_quantity, 0) as vacant_qty,
                   COALESCE(s.reserved_quantity, 0) as in_use_qty,
                   st.id as store_id
            FROM departments d
            LEFT JOIN stores st ON st.dept_id = d.id
            LEFT JOIN stock s ON s.store_id = st.id AND s.item_id = ?
            ORDER BY vacant_qty DESC, d.name ASC
        ");
        $deptStmt->execute([$itemId]);
        $departments = $deptStmt->fetchAll(PDO::FETCH_ASSOC);

        $totalVacant = 0;
        $totalInUse = 0;
        $surplusDepts = [];

        foreach ($departments as $k => $d) {
            $vacant = (int)$d['vacant_qty'];
            $inUse = (int)$d['in_use_qty'];
            $totalVacant += $vacant;
            $totalInUse += $inUse;

            $hasSurplus = ($vacant > 0);
            $departments[$k]['has_surplus'] = $hasSurplus;
            if ($hasSurplus) {
                $surplusDepts[] = $departments[$k];
            }
        }

        return [
            'item'          => $item,
            'central_store' => [
                'vacant'  => (int)$centralStock['available_quantity'],
                'in_use'  => (int)$centralStock['reserved_quantity']
            ],
            'total_dept_vacant' => $totalVacant,
            'total_dept_in_use' => $totalInUse,
            'surplus_depts'     => $surplusDepts,
            'departments'       => $departments
        ];
    }

    /**
     * Get all vacant / available / not-in-use items for a specific department
     * Used for Inter-Department Transfer selection: From [Source Dept] -> To [Current Dept]
     */
    public static function getDepartmentVacantStock(int $deptId): array {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            SELECT s.item_id, s.available_quantity as vacant_qty,
                   i.item_code, i.item_name, i.unit_cost, i.is_consumable,
                   u.code as unit_code, c.name as category_name
            FROM stock s
            JOIN stores st ON s.store_id = st.id
            JOIN items i ON s.item_id = i.id
            JOIN units u ON i.unit_id = u.id
            JOIN item_categories c ON i.category_id = c.id
            WHERE st.dept_id = ? AND s.available_quantity > 0
            ORDER BY i.item_name ASC
        ");
        $stmt->execute([$deptId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Search vacant / unallocated stock across all departments and central store.
     * Supports filtering by search keyword (item name, code, category, department),
     * specific department ID, and category ID.
     */
    public static function searchVacantStock(string $query = '', ?int $deptId = null, ?int $categoryId = null, bool $vacantOnly = true): array {
        $pdo = Database::getInstance();
        $sql = "
            SELECT s.available_quantity as vacant_qty, s.reserved_quantity as in_use_qty,
                   s.store_location, s.store_id,
                   st.name as store_name, st.type as store_type, st.dept_id,
                   d.code as dept_code, d.name as dept_name,
                   i.id as item_id, i.item_code, i.item_name, i.unit_cost, i.is_consumable,
                   c.id as category_id, c.name as category_name, u.code as unit_code
            FROM stock s
            JOIN stores st ON s.store_id = st.id
            LEFT JOIN departments d ON st.dept_id = d.id
            JOIN items i ON s.item_id = i.id
            JOIN item_categories c ON i.category_id = c.id
            JOIN units u ON i.unit_id = u.id
            WHERE 1=1
        ";
        if ($vacantOnly) {
            $sql .= " AND s.available_quantity > 0";
        }
        $params = [];
        $trimmed = trim($query);
        if ($trimmed !== '') {
            $sql .= " AND (i.item_name LIKE ? OR i.item_code LIKE ? OR c.name LIKE ? OR d.name LIKE ? OR d.code LIKE ?)";
            $term = '%' . $trimmed . '%';
            $params = array_merge($params, [$term, $term, $term, $term, $term]);
        }
        if ($deptId !== null) {
            if ($deptId === 0) {
                $sql .= " AND st.dept_id IS NULL"; // Central Warehouse
            } else {
                $sql .= " AND st.dept_id = ?";
                $params[] = $deptId;
            }
        }
        if ($categoryId !== null && $categoryId > 0) {
            $sql .= " AND c.id = ?";
            $params[] = $categoryId;
        }
        $sql .= " ORDER BY s.available_quantity DESC, i.item_name ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

