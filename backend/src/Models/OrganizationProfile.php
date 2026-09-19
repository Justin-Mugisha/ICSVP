<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class OrganizationProfile
{
    public static function findByUserId(int $userId): ?array
    {
        $pdo = Database::getConnection();

        $sql = "SELECT * FROM organization_profiles WHERE user_id = :user_id LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        $profile = $stmt->fetch(PDO::FETCH_ASSOC);

        return $profile ?: null;
    }

    public static function update(int $userId, string $orgName, ?string $description, ?string $location): bool
    {
        $pdo = Database::getConnection();

        $sql = "UPDATE organization_profiles SET org_name = :org_name, description = :description, location = :location WHERE user_id = :user_id";
        $stmt = $pdo->prepare($sql);

        return $stmt->execute([
            'org_name' => $orgName,
            'description' => $description,
            'location' => $location,
            'user_id' => $userId,
        ]);
    }
}
