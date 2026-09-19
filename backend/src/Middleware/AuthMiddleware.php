<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Psr7\Response;

class AuthMiddleware
{
    private ?array $allowedRoles;

    public function __construct(?array $allowedRoles = null)
    {
        $this->allowedRoles = $allowedRoles;
    }

    public function __invoke(Request $request, Handler $handler)
    {
        if (!isset($_SESSION['user_id'])) {
            return $this->unauthorized('You must be logged in to access this resource');
        }

        if ($this->allowedRoles !== null && !in_array($_SESSION['role'], $this->allowedRoles)) {
            return $this->forbidden('You do not have permission to access this resource');
        }

        return $handler->handle($request);
    }

    private function unauthorized(string $message)
    {
        $response = new Response();
        $response->getBody()->write(json_encode(['error' => $message]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }

    private function forbidden(string $message)
    {
        $response = new Response();
        $response->getBody()->write(json_encode(['error' => $message]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
    }
}
