<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class RequestModel
{
    public static function allOpen(): array
    {
        $pdo = Database::getConnection();

        $sql = "SELECT r.*, 
                        COALESCE(o.org_name, i.full_name) AS requester_name
                 FROM requests r
                 JOIN users u ON r.requester_id = u.id
                 LEFT JOIN organization_profiles o ON o.user_id = u.id
                 LEFT JOIN individual_profiles i ON i.user_id = u.id
                 WHERE r.status = 'open'
                 ORDER BY r.created_at DESC";

        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();

        $sql = "SELECT r.*, 
                        COALESCE(o.org_name, i.full_name) AS requester_name
                 FROM requests r
                 JOIN users u ON r.requester_id = u.id
                 LEFT JOIN organization_profiles o ON o.user_id = u.id
                 LEFT JOIN individual_profiles i ON i.user_id = u.id
                 WHERE r.id = :id
                 LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $request = $stmt->fetch(PDO::FETCH_ASSOC);

        return $request ?: null;
    }

    public static function getRequiredSkills(int $requestId): array
    {
        $pdo = Database::getConnection();

        $sql = "SELECT s.id, s.name
                 FROM request_skills rs
                 JOIN skills s ON rs.skill_id = s.id
                 WHERE rs.request_id = :request_id
                 ORDER BY s.name ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['request_id' => $requestId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create(int $requesterId, string $title, ?string $description): int
    {
        $pdo = Database::getConnection();

        $sql = "INSERT INTO requests (requester_id, title, description, status) VALUES (:requester_id, :title, :description, 'open')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'requester_id' => $requesterId,
            'title' => $title,
            'description' => $description,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function addRequiredSkill(int $requestId, int $skillId): void
    {
        $pdo = Database::getConnection();
        $sql = "INSERT INTO request_skills (request_id, skill_id) VALUES (:request_id, :skill_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['request_id' => $requestId, 'skill_id' => $skillId]);
    }

    public static function update(int $requestId, string $title, ?string $description): bool
    {
        $pdo = Database::getConnection();

        $sql = "UPDATE requests SET title = :title, description = :description WHERE id = :id";
        $stmt = $pdo->prepare($sql);

        return $stmt->execute([
            'title' => $title,
            'description' => $description,
            'id' => $requestId,
        ]);
    }

    public static function delete(int $requestId): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM requests WHERE id = :id");
        return $stmt->execute(['id' => $requestId]);
    }

    public static function getForRequester(int $requesterId): array
    {
        $pdo = Database::getConnection();

        $sql = "SELECT * FROM requests WHERE requester_id = :requester_id ORDER BY created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['requester_id' => $requesterId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function isOwnedBy(int $requestId, int $userId): bool
    {
        $pdo = Database::getConnection();

        $sql = "SELECT 1 FROM requests WHERE id = :id AND requester_id = :requester_id LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $requestId, 'requester_id' => $userId]);

        return $stmt->fetch() !== false;
    }

    public static function clearRequiredSkills(int $requestId): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM request_skills WHERE request_id = :request_id");
        $stmt->execute(['request_id' => $requestId]);
    }

    public static function allForAdmin(): array
    {
        $pdo = Database::getConnection();

        $sql = "SELECT r.*, 
                        COALESCE(o.org_name, i.full_name) AS requester_name
                 FROM requests r
                 JOIN users u ON r.requester_id = u.id
                 LEFT JOIN organization_profiles o ON o.user_id = u.id
                 LEFT JOIN individual_profiles i ON i.user_id = u.id
                 ORDER BY r.created_at DESC";

        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function countAll(): int
    {
        $pdo = Database::getConnection();
        return (int) $pdo->query("SELECT COUNT(*) FROM requests")->fetchColumn();
    }
}
