<?php

$cookieFile = __DIR__ . '/cookies.txt';

$loginData = json_encode([
    'email' => 'volunteer1@example.com',
    'password' => 'securepass123',
]);

$ch = curl_init('http://localhost/icsvp/backend/public/api/auth/login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $loginData);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
$loginResult = curl_exec($ch);
$loginStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "LOGIN - HTTP Status: $loginStatus\n";
echo "LOGIN - Response: $loginResult\n\n";

$ch = curl_init('http://localhost/icsvp/backend/public/api/me');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$meResult = curl_exec($ch);
$meStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "ME - HTTP Status: $meStatus\n";
echo "ME - Response: $meResult\n";

unlink($cookieFile);
