<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class VolunteerProfile
{
    public static function findByUserId(int $userId): ?array
    {
        $pdo = Database::getConnection();

        $sql = "SELECT * FROM volunteer_profiles WHERE user_id = :user_id LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        $profile = $stmt->fetch(PDO::FETCH_ASSOC);

        return $profile ?: null;
    }

    public static function update(int $userId, string $fullName, ?string $bio, ?string $location): bool
    {
        $pdo = Database::getConnection();

        $sql = "UPDATE volunteer_profiles SET full_name = :full_name, bio = :bio, location = :location WHERE user_id = :user_id";
        $stmt = $pdo->prepare($sql);

        return $stmt->execute([
            'full_name' => $fullName,
            'bio' => $bio,
            'location' => $location,
            'user_id' => $userId,
        ]);
    }
}
