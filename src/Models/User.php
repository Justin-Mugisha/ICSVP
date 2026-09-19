<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class User
{
    public static function create(string $email, string $passwordHash, string $role): int
    {
        $pdo = Database::getConnection();

        $sql = "INSERT INTO users (email, password_hash, role) VALUES (:email, :password_hash, :role)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'email' => $email,
            'password_hash' => $passwordHash,
            'role' => $role,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function findByEmail(string $email): ?array
    {
        $pdo = Database::getConnection();

        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['email' => $email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();

        $sql = "SELECT id, email, role, created_at FROM users WHERE id = :id LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }
}