<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class Notification
{
    public static function create(int $userId, string $message): int
    {
        $pdo = Database::getConnection();

        $sql = "INSERT INTO notifications (user_id, message) VALUES (:user_id, :message)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId, 'message' => $message]);

        return (int) $pdo->lastInsertId();
    }

    public static function getForUser(int $userId): array
    {
        $pdo = Database::getConnection();

        $sql = "SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function markAsRead(int $id, int $userId): bool
    {
        $pdo = Database::getConnection();

        $sql = "UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id, 'user_id' => $userId]);

        return $stmt->rowCount() > 0;
    }
}
