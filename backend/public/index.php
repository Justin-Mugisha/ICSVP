<?php

use Slim\Factory\AppFactory;
use Dotenv\Dotenv;
use App\Controllers\AuthController;
use App\Controllers\VolunteerController;
use App\Controllers\RequesterController;
use App\Controllers\NotificationController;
use App\Controllers\AdminController;
use App\Middleware\AuthMiddleware;
use App\Models\User;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

session_start();

$app = AppFactory::create();

$app->setBasePath('/icsvp/backend/public');

$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', $_ENV['FRONTEND_URL'])
        ->withHeader('Access-Control-Allow-Credentials', 'true')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});

$app->addBodyParsingMiddleware();
$app->addErrorMiddleware(true, true, true);
$app->options('/{routes:.+}', function ($request, $response) {
    return $response;
});

$app->get('/api/health', function ($request, $response) {
    $data = ['status' => 'ok', 'message' => 'ICSVP PHP backend is running'];
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->post('/api/auth/register', [AuthController::class, 'register']);
$app->post('/api/auth/login', [AuthController::class, 'login']);
$app->post('/api/auth/logout', [AuthController::class, 'logout']);

$app->get('/api/me', function ($request, $response) {
    $user = User::findById($_SESSION['user_id']);
    $response->getBody()->write(json_encode(['user' => $user]));
    return $response->withHeader('Content-Type', 'application/json');
})->add(new AuthMiddleware());

$app->get('/api/volunteers/profile', [VolunteerController::class, 'getProfile'])
    ->add(new AuthMiddleware(['volunteer']));

$app->put('/api/volunteers/profile', [VolunteerController::class, 'updateProfile'])
    ->add(new AuthMiddleware(['volunteer']));

$app->get('/api/volunteers/skills', [VolunteerController::class, 'getSkills'])
    ->add(new AuthMiddleware(['volunteer']));

$app->post('/api/volunteers/skills', [VolunteerController::class, 'addSkill'])
    ->add(new AuthMiddleware(['volunteer']));

$app->delete('/api/volunteers/skills/{skillId}', [VolunteerController::class, 'removeSkill'])
    ->add(new AuthMiddleware(['volunteer']));

$app->get('/api/requests', [VolunteerController::class, 'listOpenRequests'])
    ->add(new AuthMiddleware());
    $app->get('/api/skills', function ($request, $response) {
    $skills = \App\Models\Skill::all();
    $response->getBody()->write(json_encode(['skills' => $skills]));
    return $response->withHeader('Content-Type', 'application/json');
})->add(new AuthMiddleware());

$app->get('/api/requests/{id}', [VolunteerController::class, 'getRequestDetail'])
    ->add(new AuthMiddleware());

$app->post('/api/requests/{id}/apply', [VolunteerController::class, 'applyToRequest'])
    ->add(new AuthMiddleware(['volunteer']));

$app->get('/api/volunteers/applications', [VolunteerController::class, 'getMyApplications'])
    ->add(new AuthMiddleware(['volunteer']));

$app->get('/api/requesters/profile', [RequesterController::class, 'getProfile'])
    ->add(new AuthMiddleware(['individual', 'organization']));

$app->put('/api/requesters/profile', [RequesterController::class, 'updateProfile'])
    ->add(new AuthMiddleware(['individual', 'organization']));

$app->post('/api/requesters/requests', [RequesterController::class, 'createRequest'])
    ->add(new AuthMiddleware(['individual', 'organization']));

$app->get('/api/requesters/requests', [RequesterController::class, 'getMyRequests'])
    ->add(new AuthMiddleware(['individual', 'organization']));

$app->put('/api/requesters/requests/{id}', [RequesterController::class, 'updateRequest'])
    ->add(new AuthMiddleware(['individual', 'organization']));

$app->delete('/api/requesters/requests/{id}', [RequesterController::class, 'deleteRequest'])
    ->add(new AuthMiddleware(['individual', 'organization']));

$app->get('/api/requesters/requests/{id}/applications', [RequesterController::class, 'getApplicants'])
    ->add(new AuthMiddleware(['individual', 'organization']));

$app->get('/api/requesters/requests/{id}/matches', [RequesterController::class, 'getMatches'])
    ->add(new AuthMiddleware(['individual', 'organization']));

$app->put('/api/requesters/applications/{id}/status', [RequesterController::class, 'updateApplicationStatus'])
    ->add(new AuthMiddleware(['individual', 'organization']));

$app->get('/api/notifications', [NotificationController::class, 'getMyNotifications'])
    ->add(new AuthMiddleware());

$app->put('/api/notifications/{id}/read', [NotificationController::class, 'markAsRead'])
    ->add(new AuthMiddleware());

$app->get('/api/admin/dashboard', [AdminController::class, 'getDashboard'])
    ->add(new AuthMiddleware(['admin']));

$app->get('/api/admin/users', [AdminController::class, 'getUsers'])
    ->add(new AuthMiddleware(['admin']));

$app->get('/api/admin/requests', [AdminController::class, 'getRequests'])
    ->add(new AuthMiddleware(['admin']));

$app->get('/api/admin/applications', [AdminController::class, 'getApplications'])
    ->add(new AuthMiddleware(['admin']));

$app->get('/api/admin/skills', [AdminController::class, 'getSkills'])
    ->add(new AuthMiddleware(['admin']));

$app->post('/api/admin/skills', [AdminController::class, 'createSkill'])
    ->add(new AuthMiddleware(['admin']));

$app->run();