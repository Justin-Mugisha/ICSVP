<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class IndividualProfile
{
    public static function findByUserId(int $userId): ?array
    {
        $pdo = Database::getConnection();

        $sql = "SELECT * FROM individual_profiles WHERE user_id = :user_id LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        $profile = $stmt->fetch(PDO::FETCH_ASSOC);

        return $profile ?: null;
    }

    public static function update(int $userId, string $fullName, ?string $location): bool
    {
        $pdo = Database::getConnection();

        $sql = "UPDATE individual_profiles SET full_name = :full_name, location = :location WHERE user_id = :user_id";
        $stmt = $pdo->prepare($sql);

        return $stmt->execute([
            'full_name' => $fullName,
            'location' => $location,
            'user_id' => $userId,
        ]);
    }
}
