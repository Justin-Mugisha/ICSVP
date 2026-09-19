<?php

namespace App\Controllers;

use App\Models\IndividualProfile;
use App\Models\OrganizationProfile;
use App\Models\RequestModel;
use App\Models\Application;
use App\Models\User;
use App\Models\Notification;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class RequesterController
{
    public function getProfile(Request $request, Response $response): Response
    {
        $userId = $_SESSION['user_id'];
        $role = $_SESSION['role'];

        $profile = $role === 'organization'
            ? OrganizationProfile::findByUserId($userId)
            : IndividualProfile::findByUserId($userId);

        if ($profile === null) {
            return $this->jsonResponse($response, ['error' => 'Profile not found'], 404);
        }

        return $this->jsonResponse($response, ['profile' => $profile], 200);
    }

    public function updateProfile(Request $request, Response $response): Response
    {
        $userId = $_SESSION['user_id'];
        $role = $_SESSION['role'];
        $data = $request->getParsedBody();
        $location = $data['location'] ?? null;

        if ($role === 'organization') {
            $orgName = trim($data['org_name'] ?? '');
            $description = $data['description'] ?? null;

            if (empty($orgName)) {
                return $this->jsonResponse($response, ['error' => 'Organization name is required'], 400);
            }

            OrganizationProfile::update($userId, $orgName, $description, $location);
            $profile = OrganizationProfile::findByUserId($userId);
        } else {
            $fullName = trim($data['full_name'] ?? '');

            if (empty($fullName)) {
                return $this->jsonResponse($response, ['error' => 'Full name is required'], 400);
            }

            IndividualProfile::update($userId, $fullName, $location);
            $profile = IndividualProfile::findByUserId($userId);
        }

        return $this->jsonResponse($response, [
            'message' => 'Profile updated successfully',
            'profile' => $profile,
        ], 200);
    }

    public function createRequest(Request $request, Response $response): Response
    {
        $userId = $_SESSION['user_id'];
        $data = $request->getParsedBody();

        $title = trim($data['title'] ?? '');
        $description = $data['description'] ?? null;
        $skillIds = $data['skill_ids'] ?? [];

        if (empty($title)) {
            return $this->jsonResponse($response, ['error' => 'Title is required'], 400);
        }

        if (!is_array($skillIds) || count($skillIds) === 0) {
            return $this->jsonResponse($response, ['error' => 'At least one required skill must be specified'], 400);
        }

        $requestId = RequestModel::create($userId, $title, $description);

        foreach ($skillIds as $skillId) {
            RequestModel::addRequiredSkill($requestId, (int) $skillId);
        }

        $requestData = RequestModel::findById($requestId);
        $requestData['required_skills'] = RequestModel::getRequiredSkills($requestId);

        return $this->jsonResponse($response, [
            'message' => 'Request created successfully',
            'request' => $requestData,
        ], 201);
    }

    public function getMyRequests(Request $request, Response $response): Response
    {
        $userId = $_SESSION['user_id'];
        $requests = RequestModel::getForRequester($userId);

        return $this->jsonResponse($response, ['requests' => $requests], 200);
    }

    public function updateRequest(Request $request, Response $response, array $args): Response
    {
        $userId = $_SESSION['user_id'];
        $requestId = (int) $args['id'];

        if (!RequestModel::isOwnedBy($requestId, $userId)) {
            return $this->jsonResponse($response, ['error' => 'You do not have permission to edit this request'], 403);
        }

        $data = $request->getParsedBody();
        $title = trim($data['title'] ?? '');
        $description = $data['description'] ?? null;
        $skillIds = $data['skill_ids'] ?? null;

        if (empty($title)) {
            return $this->jsonResponse($response, ['error' => 'Title is required'], 400);
        }

        RequestModel::update($requestId, $title, $description);

        if ($skillIds !== null && is_array($skillIds)) {
            RequestModel::clearRequiredSkills($requestId);
            foreach ($skillIds as $skillId) {
                RequestModel::addRequiredSkill($requestId, (int) $skillId);
            }
        }

        $requestData = RequestModel::findById($requestId);
        $requestData['required_skills'] = RequestModel::getRequiredSkills($requestId);

        return $this->jsonResponse($response, [
            'message' => 'Request updated successfully',
            'request' => $requestData,
        ], 200);
    }

    public function deleteRequest(Request $request, Response $response, array $args): Response
    {
        $userId = $_SESSION['user_id'];
        $requestId = (int) $args['id'];

        if (!RequestModel::isOwnedBy($requestId, $userId)) {
            return $this->jsonResponse($response, ['error' => 'You do not have permission to delete this request'], 403);
        }

        RequestModel::delete($requestId);

        return $this->jsonResponse($response, ['message' => 'Request deleted successfully'], 200);
    }

    public function getApplicants(Request $request, Response $response, array $args): Response
    {
        $userId = $_SESSION['user_id'];
        $requestId = (int) $args['id'];

        if (!RequestModel::isOwnedBy($requestId, $userId)) {
            return $this->jsonResponse($response, ['error' => 'You do not have permission to view applicants for this request'], 403);
        }

        $applicants = Application::getForRequest($requestId);

        return $this->jsonResponse($response, ['applicants' => $applicants], 200);
    }

    public function getMatches(Request $request, Response $response, array $args): Response
    {
        $userId = $_SESSION['user_id'];
        $requestId = (int) $args['id'];

        if (!RequestModel::isOwnedBy($requestId, $userId)) {
            return $this->jsonResponse($response, ['error' => 'You do not have permission to view matches for this request'], 403);
        }

        $matches = Application::getForRequestWithMatchScores($requestId);

        return $this->jsonResponse($response, ['matches' => $matches], 200);
    }

    public function updateApplicationStatus(Request $request, Response $response, array $args): Response
    {
        $userId = $_SESSION['user_id'];
        $applicationId = (int) $args['id'];
        $data = $request->getParsedBody();
        $status = $data['status'] ?? '';

        if (!in_array($status, ['accepted', 'rejected'])) {
            return $this->jsonResponse($response, ['error' => 'Status must be accepted or rejected'], 400);
        }

        $application = Application::findById($applicationId);
        if ($application === null) {
            return $this->jsonResponse($response, ['error' => 'Application not found'], 404);
        }

        if (!RequestModel::isOwnedBy($application['request_id'], $userId)) {
            return $this->jsonResponse($response, ['error' => 'You do not have permission to manage this application'], 403);
        }

        Application::updateStatus($applicationId, $status);

        $message = $status === 'accepted'
            ? 'Your application for \"' . $application['title'] . '\" has been accepted!'
            : 'Your application for \"' . $application['title'] . '\" has been rejected.';

        Notification::create($application['volunteer_id'], $message);

        return $this->jsonResponse($response, ['message' => "Application $status successfully"], 200);
    }

    private function jsonResponse(Response $response, array $data, int $status): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}
