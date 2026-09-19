<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class Skill
{
    public static function all(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM skills ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM skills WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $skill = $stmt->fetch(PDO::FETCH_ASSOC);
        return $skill ?: null;
    }

    public static function create(string $name): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("INSERT INTO skills (name) VALUES (:name)");
        $stmt->execute(['name' => $name]);
        return (int) $pdo->lastInsertId();
    }

    public static function existsByName(string $name): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT 1 FROM skills WHERE name = :name LIMIT 1");
        $stmt->execute(['name' => $name]);
        return $stmt->fetch() !== false;
    }
}
