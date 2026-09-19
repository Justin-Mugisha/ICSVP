<?php

namespace App\Controllers;

use App\Models\VolunteerProfile;
use App\Models\VolunteerSkill;
use App\Models\Skill;
use App\Models\RequestModel;
use App\Models\Application;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class VolunteerController
{
    public function getProfile(Request $request, Response $response): Response
    {
        $userId = $_SESSION['user_id'];
        $profile = VolunteerProfile::findByUserId($userId);

        if ($profile === null) {
            return $this->jsonResponse($response, ['error' => 'Profile not found'], 404);
        }

        return $this->jsonResponse($response, ['profile' => $profile], 200);
    }

    public function updateProfile(Request $request, Response $response): Response
    {
        $userId = $_SESSION['user_id'];
        $data = $request->getParsedBody();

        $fullName = trim($data['full_name'] ?? '');
        $bio = $data['bio'] ?? null;
        $location = $data['location'] ?? null;

        if (empty($fullName)) {
            return $this->jsonResponse($response, ['error' => 'Full name is required'], 400);
        }

        $success = VolunteerProfile::update($userId, $fullName, $bio, $location);

        if (!$success) {
            return $this->jsonResponse($response, ['error' => 'Failed to update profile'], 500);
        }

        $profile = VolunteerProfile::findByUserId($userId);

        return $this->jsonResponse($response, [
            'message' => 'Profile updated successfully',
            'profile' => $profile,
        ], 200);
    }

    public function getSkills(Request $request, Response $response): Response
    {
        $userId = $_SESSION['user_id'];
        $profile = VolunteerProfile::findByUserId($userId);

        if ($profile === null) {
            return $this->jsonResponse($response, ['error' => 'Profile not found'], 404);
        }

        $skills = VolunteerSkill::getSkillsForVolunteer($profile['id']);

        return $this->jsonResponse($response, ['skills' => $skills], 200);
    }

    public function addSkill(Request $request, Response $response): Response
    {
        $userId = $_SESSION['user_id'];
        $data = $request->getParsedBody();
        $skillId = (int) ($data['skill_id'] ?? 0);

        if ($skillId <= 0) {
            return $this->jsonResponse($response, ['error' => 'A valid skill_id is required'], 400);
        }

        $skill = Skill::findById($skillId);
        if ($skill === null) {
            return $this->jsonResponse($response, ['error' => 'Skill not found'], 404);
        }

        $profile = VolunteerProfile::findByUserId($userId);
        if ($profile === null) {
            return $this->jsonResponse($response, ['error' => 'Profile not found'], 404);
        }

        if (VolunteerSkill::hasSkill($profile['id'], $skillId)) {
            return $this->jsonResponse($response, ['error' => 'You already have this skill listed'], 409);
        }

        VolunteerSkill::addSkill($profile['id'], $skillId);
        $skills = VolunteerSkill::getSkillsForVolunteer($profile['id']);

        return $this->jsonResponse($response, [
            'message' => 'Skill added successfully',
            'skills' => $skills,
        ], 201);
    }

    public function removeSkill(Request $request, Response $response, array $args): Response
    {
        $userId = $_SESSION['user_id'];
        $skillId = (int) $args['skillId'];

        $profile = VolunteerProfile::findByUserId($userId);
        if ($profile === null) {
            return $this->jsonResponse($response, ['error' => 'Profile not found'], 404);
        }

        if (!VolunteerSkill::hasSkill($profile['id'], $skillId)) {
            return $this->jsonResponse($response, ['error' => 'You do not have this skill listed'], 404);
        }

        VolunteerSkill::removeSkill($profile['id'], $skillId);
        $skills = VolunteerSkill::getSkillsForVolunteer($profile['id']);

        return $this->jsonResponse($response, [
            'message' => 'Skill removed successfully',
            'skills' => $skills,
        ], 200);
    }

    public function listOpenRequests(Request $request, Response $response): Response
    {
        $requests = RequestModel::allOpen();
        return $this->jsonResponse($response, ['requests' => $requests], 200);
    }

    public function getRequestDetail(Request $request, Response $response, array $args): Response
    {
        $requestId = (int) $args['id'];
        $requestData = RequestModel::findById($requestId);

        if ($requestData === null) {
            return $this->jsonResponse($response, ['error' => 'Request not found'], 404);
        }

        $requestData['required_skills'] = RequestModel::getRequiredSkills($requestId);

        return $this->jsonResponse($response, ['request' => $requestData], 200);
    }

    public function applyToRequest(Request $request, Response $response, array $args): Response
    {
        $userId = $_SESSION['user_id'];
        $requestId = (int) $args['id'];

        $requestData = RequestModel::findById($requestId);
        if ($requestData === null) {
            return $this->jsonResponse($response, ['error' => 'Request not found'], 404);
        }

        if ($requestData['status'] !== 'open') {
            return $this->jsonResponse($response, ['error' => 'This request is no longer open'], 400);
        }

        if (Application::exists($userId, $requestId)) {
            return $this->jsonResponse($response, ['error' => 'You have already applied to this request'], 409);
        }

        $applicationId = Application::create($userId, $requestId);

        return $this->jsonResponse($response, [
            'message' => 'Application submitted successfully',
            'application_id' => $applicationId,
        ], 201);
    }

    public function getMyApplications(Request $request, Response $response): Response
    {
        $userId = $_SESSION['user_id'];
        $applications = Application::getForVolunteer($userId);

        return $this->jsonResponse($response, ['applications' => $applications], 200);
    }

    private function jsonResponse(Response $response, array $data, int $status): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}
