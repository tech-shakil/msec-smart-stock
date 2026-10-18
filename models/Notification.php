<?php
/**
 * MSEC SMART STOCK - Role-Aware Institutional Notification System
 */

require_once dirname(__DIR__) . '/core/Database.php';

class Notification {

    public static function getForUser(int $userId, string $roleKey, int $limit = 20): array {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            SELECT * FROM notifications 
            WHERE user_id = ? OR role_key = ? OR (user_id IS NULL AND role_key IS NULL)
            ORDER BY id DESC LIMIT ?
        ");
        $stmt->execute([$userId, $roleKey, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getUnreadCount(int $userId, string $roleKey): int {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM notifications 
            WHERE (user_id = ? OR role_key = ? OR (user_id IS NULL AND role_key IS NULL))
              AND is_read = 0
        ");
        $stmt->execute([$userId, $roleKey]);
        return (int)$stmt->fetchColumn();
    }

    public static function markAsRead(int $id): bool {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function markAllAsRead(int $userId, string $roleKey): bool {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            UPDATE notifications SET is_read = 1 
            WHERE user_id = ? OR role_key = ?
        ");
        return $stmt->execute([$userId, $roleKey]);
    }

    public static function send(
        string $type,
        string $title,
        string $message,
        ?string $linkUrl = null,
        ?int $userId = null,
        ?string $roleKey = null
    ): bool {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, role_key, type, title, message, link_url, is_read, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 0, datetime('now', 'localtime'))
        ");
        return $stmt->execute([$userId, $roleKey, $type, $title, $message, $linkUrl]);
    }

    public static function checkAndNotifyLowConsumables(): int {
        $pdo = Database::getInstance();
        $lowItems = $pdo->query("
            SELECT s.available_quantity, i.item_code, i.item_name, i.min_stock, st.name as store_name
            FROM stock s
            JOIN items i ON s.item_id = i.id
            JOIN stores st ON s.store_id = st.id
            WHERE i.is_consumable = 1 AND s.available_quantity <= i.min_stock
        ")->fetchAll(PDO::FETCH_ASSOC);

        $notified = 0;
        foreach ($lowItems as $item) {
            $exists = $pdo->prepare("
                SELECT COUNT(*) FROM notifications 
                WHERE type = 'LOW_STOCK' AND message LIKE ? AND is_read = 0
            ");
            $exists->execute(["%{$item['item_code']}%"]);
            if ((int)$exists->fetchColumn() === 0) {
                self::send(
                    'LOW_STOCK',
                    "⚠️ Low Consumable Alert: {$item['item_name']}",
                    "Consumable item {$item['item_name']} ({$item['item_code']}) in {$item['store_name']} has fallen to {$item['available_quantity']} (Min: {$item['min_stock']}). Replenishment requisition required.",
                    'index.php?route=inventory',
                    null,
                    'store_keeper'
                );
                $notified++;
            }
        }
        return $notified;
    }
}
