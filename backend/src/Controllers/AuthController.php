<?php

namespace App\Controllers;

use App\Config\Database;
use App\Models\User;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class AuthController
{
    private array $allowedRoles = ['volunteer', 'individual', 'organization'];

    public function register(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $role = $data['role'] ?? '';
        $name = trim($data['name'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->jsonResponse($response, ['error' => 'A valid email is required'], 400);
        }

        if (strlen($password) < 6) {
            return $this->jsonResponse($response, ['error' => 'Password must be at least 6 characters'], 400);
        }

        if (!in_array($role, $this->allowedRoles)) {
            return $this->jsonResponse($response, ['error' => 'Role must be volunteer, individual, or organization'], 400);
        }

        if (empty($name)) {
            return $this->jsonResponse($response, ['error' => 'Name is required'], 400);
        }

        if (User::findByEmail($email) !== null) {
            return $this->jsonResponse($response, ['error' => 'An account with this email already exists'], 409);
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $pdo = Database::getConnection();

        try {
            $pdo->beginTransaction();

            $userId = User::create($email, $passwordHash, $role);

            $profileTable = match ($role) {
                'volunteer' => 'volunteer_profiles',
                'individual' => 'individual_profiles',
                'organization' => 'organization_profiles',
            };

            $nameColumn = $role === 'organization' ? 'org_name' : 'full_name';

            $stmt = $pdo->prepare("INSERT INTO $profileTable (user_id, $nameColumn) VALUES (:user_id, :name)");
            $stmt->execute(['user_id' => $userId, 'name' => $name]);

            $pdo->commit();
        } catch (\Exception $e) {
            $pdo->rollBack();
            return $this->jsonResponse($response, ['error' => 'Registration failed: ' . $e->getMessage()], 500);
        }

        $_SESSION['user_id'] = $userId;
        $_SESSION['role'] = $role;

        return $this->jsonResponse($response, [
            'message' => 'Registration successful',
            'user' => ['id' => $userId, 'email' => $email, 'role' => $role],
        ], 201);
    }

    public function login(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';

        if (empty($email) || empty($password)) {
            return $this->jsonResponse($response, ['error' => 'Email and password are required'], 400);
        }

        $user = User::findByEmail($email);

        if ($user === null || !password_verify($password, $user['password_hash'])) {
            return $this->jsonResponse($response, ['error' => 'Invalid email or password'], 401);
        }

        if (!$user['is_active']) {
            return $this->jsonResponse($response, ['error' => 'This account has been deactivated'], 403);
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];

        return $this->jsonResponse($response, [
            'message' => 'Login successful',
            'user' => ['id' => $user['id'], 'email' => $user['email'], 'role' => $user['role']],
        ], 200);
    }

    public function logout(Request $request, Response $response): Response
    {
        $_SESSION = [];
        session_destroy();

        return $this->jsonResponse($response, ['message' => 'Logged out successfully'], 200);
    }

    private function jsonResponse(Response $response, array $data, int $status): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}
