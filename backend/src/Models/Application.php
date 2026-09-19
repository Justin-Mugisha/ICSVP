<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class Application
{
    public static function create(int $volunteerId, int $requestId): int
    {
        $pdo = Database::getConnection();

        $sql = "INSERT INTO applications (volunteer_id, request_id) VALUES (:volunteer_id, :request_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['volunteer_id' => $volunteerId, 'request_id' => $requestId]);

        return (int) $pdo->lastInsertId();
    }

    public static function exists(int $volunteerId, int $requestId): bool
    {
        $pdo = Database::getConnection();

        $sql = "SELECT 1 FROM applications WHERE volunteer_id = :volunteer_id AND request_id = :request_id LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['volunteer_id' => $volunteerId, 'request_id' => $requestId]);

        return $stmt->fetch() !== false;
    }

    public static function getForVolunteer(int $volunteerId): array
    {
        $pdo = Database::getConnection();

        $sql = "SELECT a.id, a.status, a.created_at, r.id AS request_id, r.title, r.status AS request_status
                 FROM applications a
                 JOIN requests r ON a.request_id = r.id
                 WHERE a.volunteer_id = :volunteer_id
                 ORDER BY a.created_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['volunteer_id' => $volunteerId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getForRequest(int $requestId): array
    {
        $pdo = Database::getConnection();

        $sql = "SELECT a.id, a.status, a.created_at,
                        u.id AS volunteer_user_id, u.email,
                        vp.full_name
                 FROM applications a
                 JOIN users u ON a.volunteer_id = u.id
                 LEFT JOIN volunteer_profiles vp ON vp.user_id = u.id
                 WHERE a.request_id = :request_id
                 ORDER BY a.created_at ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['request_id' => $requestId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();

        $sql = "SELECT a.*, r.title, r.requester_id
                 FROM applications a
                 JOIN requests r ON a.request_id = r.id
                 WHERE a.id = :id
                 LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $application = $stmt->fetch(PDO::FETCH_ASSOC);

        return $application ?: null;
    }

    public static function updateStatus(int $id, string $status): bool
    {
        $pdo = Database::getConnection();

        $sql = "UPDATE applications SET status = :status WHERE id = :id";
        $stmt = $pdo->prepare($sql);

        return $stmt->execute(['status' => $status, 'id' => $id]);
    }

    public static function getForRequestWithMatchScores(int $requestId): array
    {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("SELECT skill_id FROM request_skills WHERE request_id = :request_id");
        $stmt->execute(['request_id' => $requestId]);
        $requiredSkillIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $requiredCount = count($requiredSkillIds);

        $applicants = self::getForRequest($requestId);

        foreach ($applicants as &$applicant) {
            if ($requiredCount === 0) {
                $applicant['match_percentage'] = 0;
                continue;
            }

            $profile = VolunteerProfile::findByUserId($applicant['volunteer_user_id']);
            $volunteerProfileId = $profile['id'] ?? null;

            if ($volunteerProfileId === null) {
                $applicant['match_percentage'] = 0;
                continue;
            }

            $volunteerSkills = VolunteerSkill::getSkillsForVolunteer($volunteerProfileId);
            $volunteerSkillIds = array_column($volunteerSkills, 'id');

            $matchingCount = count(array_intersect($requiredSkillIds, $volunteerSkillIds));
            $applicant['match_percentage'] = round(($matchingCount / $requiredCount) * 100);
        }
        unset($applicant);

        usort($applicants, fn($a, $b) => $b['match_percentage'] <=> $a['match_percentage']);

        return $applicants;
    }

    public static function allForAdmin(): array
    {
        $pdo = Database::getConnection();

        $sql = "SELECT a.id, a.status, a.created_at,
                        r.title AS request_title,
                        u.email AS volunteer_email
                 FROM applications a
                 JOIN requests r ON a.request_id = r.id
                 JOIN users u ON a.volunteer_id = u.id
                 ORDER BY a.created_at DESC";

        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function countAll(): int
    {
        $pdo = Database::getConnection();
        return (int) $pdo->query("SELECT COUNT(*) FROM applications")->fetchColumn();
    }
}
