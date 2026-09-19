<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class VolunteerSkill
{
    public static function getSkillsForVolunteer(int $volunteerProfileId): array
    {
        $pdo = Database::getConnection();

        $sql = "SELECT s.id, s.name
                 FROM volunteer_skills vs
                 JOIN skills s ON vs.skill_id = s.id
                 WHERE vs.volunteer_id = :volunteer_id
                 ORDER BY s.name ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['volunteer_id' => $volunteerProfileId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function addSkill(int $volunteerProfileId, int $skillId): bool
    {
        $pdo = Database::getConnection();

        $sql = "INSERT INTO volunteer_skills (volunteer_id, skill_id) VALUES (:volunteer_id, :skill_id)";
        $stmt = $pdo->prepare($sql);

        return $stmt->execute([
            'volunteer_id' => $volunteerProfileId,
            'skill_id' => $skillId,
        ]);
    }

    public static function removeSkill(int $volunteerProfileId, int $skillId): bool
    {
        $pdo = Database::getConnection();

        $sql = "DELETE FROM volunteer_skills WHERE volunteer_id = :volunteer_id AND skill_id = :skill_id";
        $stmt = $pdo->prepare($sql);

        return $stmt->execute([
            'volunteer_id' => $volunteerProfileId,
            'skill_id' => $skillId,
        ]);
    }

    public static function hasSkill(int $volunteerProfileId, int $skillId): bool
    {
        $pdo = Database::getConnection();

        $sql = "SELECT 1 FROM volunteer_skills WHERE volunteer_id = :volunteer_id AND skill_id = :skill_id LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['volunteer_id' => $volunteerProfileId, 'skill_id' => $skillId]);

        return $stmt->fetch() !== false;
    }
}
