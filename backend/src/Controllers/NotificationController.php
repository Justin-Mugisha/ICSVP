<?php

namespace App\Controllers;

use App\Models\Notification;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class NotificationController
{
    public function getMyNotifications(Request $request, Response $response): Response
    {
        $userId = $_SESSION['user_id'];
        $notifications = Notification::getForUser($userId);

        return $this->jsonResponse($response, ['notifications' => $notifications], 200);
    }

    public function markAsRead(Request $request, Response $response, array $args): Response
    {
        $userId = $_SESSION['user_id'];
        $notificationId = (int) $args['id'];

        $success = Notification::markAsRead($notificationId, $userId);

        if (!$success) {
            return $this->jsonResponse($response, ['error' => 'Notification not found or does not belong to you'], 404);
        }

        return $this->jsonResponse($response, ['message' => 'Notification marked as read'], 200);
    }

    private function jsonResponse(Response $response, array $data, int $status): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}
