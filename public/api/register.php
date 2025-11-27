<?php
require_once __DIR__ . '/../../app/lib/auth.php';
require_once __DIR__ . '/../../app/models/User.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$username = trim($data['username'] ?? '');
$email    = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

if (!$username || !$email || !$password) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email']);
    exit;
}

if (strlen($password) < 8) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Password too short']);
    exit;
}

// simple duplicate check
if (method_exists('User', 'byUsername') && User::byUsername($username)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Username already exists']);
    exit;
}

User::create($username, $email, $password);

echo json_encode(['success' => true, 'message' => 'Registered']);
