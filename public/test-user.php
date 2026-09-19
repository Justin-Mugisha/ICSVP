<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Config\Database;
use App\Models\User;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Try creating a test user
$id = User::create('testuser@example.com', password_hash('testpassword123', PASSWORD_DEFAULT), 'volunteer');
echo "Created user with ID: $id\n";

// Try finding that user by email
$found = User::findByEmail('testuser@example.com');
echo "Found user: ";
print_r($found);