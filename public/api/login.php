<?php
require_once __DIR__ . '/../../app/models/User.php';
require_once __DIR__ . '/../../app/models/Admin.php';
require_once __DIR__ . '/../../app/lib/auth.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$username = $data['username'] ?? '';
$password = $data['password'] ?? '';
$type     = $data['type'] ?? 'user'; // 'user' or 'admin'

if (!$username || !$password) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing credentials']);
    exit;
}

if ($type === 'user') {
    $user = User::byUsername($username);
    if (!$user || !password_verify($password, $user['password'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
        exit;
    }
    regenerate_sess();
    $_SESSION['user_id'] = $user['id'];
    echo json_encode([
        'success' => true,
        'user' => [
            'id' => (int)$user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'weight' => (float)($user['weight'] ?? 0),
            'goal_calories' => (int)($user['goal_calories'] ?? 0),
            'role' => 'user'
        ]
    ]);
    exit;
}

if ($type === 'admin') {
    $admin = Admin::byUsername($username);
    if (!$admin || !password_verify($password, $admin['password'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid admin credentials']);
        exit;
    }
    regenerate_sess();
    $_SESSION['admin_id'] = $admin['id'];
    echo json_encode([
        'success' => true,
        'user' => [
            'id' => (int)$admin['id'],
            'username' => $admin['username'],
            'email' => $admin['email'],
            'role' => 'admin'
        ]
    ]);
    exit;
}

http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Invalid type']);
