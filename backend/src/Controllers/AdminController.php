<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\RequestModel;
use App\Models\Application;
use App\Models\Skill;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class AdminController
{
    public function getDashboard(Request $request, Response $response): Response
    {
        $stats = [
            'total_users' => User::countAll(),
            'total_volunteers' => User::countByRole('volunteer'),
            'total_individuals' => User::countByRole('individual'),
            'total_organizations' => User::countByRole('organization'),
            'total_requests' => RequestModel::countAll(),
            'total_applications' => Application::countAll(),
        ];

        return $this->jsonResponse($response, ['stats' => $stats], 200);
    }

    public function getUsers(Request $request, Response $response): Response
    {
        $users = User::all();
        return $this->jsonResponse($response, ['users' => $users], 200);
    }

    public function getRequests(Request $request, Response $response): Response
    {
        $requests = RequestModel::allForAdmin();
        return $this->jsonResponse($response, ['requests' => $requests], 200);
    }

    public function getApplications(Request $request, Response $response): Response
    {
        $applications = Application::allForAdmin();
        return $this->jsonResponse($response, ['applications' => $applications], 200);
    }

    public function getSkills(Request $request, Response $response): Response
    {
        $skills = Skill::all();
        return $this->jsonResponse($response, ['skills' => $skills], 200);
    }

    public function createSkill(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $name = trim($data['name'] ?? '');

        if (empty($name)) {
            return $this->jsonResponse($response, ['error' => 'Skill name is required'], 400);
        }

        if (Skill::existsByName($name)) {
            return $this->jsonResponse($response, ['error' => 'This skill already exists'], 409);
        }

        $skillId = Skill::create($name);

        return $this->jsonResponse($response, [
            'message' => 'Skill created successfully',
            'skill' => ['id' => $skillId, 'name' => $name],
        ], 201);
    }

    private function jsonResponse(Response $response, array $data, int $status): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}
