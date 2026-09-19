<?php

$orgCookie = __DIR__ . '/org_cookies.txt';
$volCookie = __DIR__ . '/vol_cookies.txt';
$base = 'http://localhost/icsvp/backend/public';

function callApi($url, $method = 'GET', $data = null, $cookieFile = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    }
    if ($cookieFile) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    }
    $result = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$status, $result];
}

// 1. Login as volunteer, add skill_id 1 (First Aid) - they'll have 1 of 2 required skills
[$status, $result] = callApi("$base/api/auth/login", 'POST', [
    'email' => 'volunteer1@example.com',
    'password' => 'securepass123',
], $volCookie);
echo "1. VOLUNTEER LOGIN - Status: $status - $result\n\n";

[$status, $result] = callApi("$base/api/volunteers/skills", 'GET', null, $volCookie);
echo "2. VOLUNTEER CURRENT SKILLS - Status: $status - $result\n\n";

// 2. Login as org, check matches for request 3 (Tree Planting Initiative, requires skills 1 and 13)
[$status, $result] = callApi("$base/api/auth/login", 'POST', [
    'email' => 'greenearth@example.com',
    'password' => 'orgpass123',
], $orgCookie);
echo "3. ORG LOGIN - Status: $status - $result\n\n";

[$status, $result] = callApi("$base/api/requesters/requests/3/matches", 'GET', null, $orgCookie);
echo "4. MATCHES FOR REQUEST 3 - Status: $status - $result\n\n";

unlink($orgCookie);
unlink($volCookie);
