<?php
require_once __DIR__ . '/../../app/lib/auth.php';
require_once __DIR__ . '/../../app/models/User.php';
require_once __DIR__ . '/../../app/lib/db.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$weight = isset($data['weight']) ? floatval($data['weight']) : null;
$goal_calories = isset($data['goal_calories']) ? intval($data['goal_calories']) : null;

DB::conn()->prepare('UPDATE users SET weight=?, goal_calories=? WHERE id=?')
    ->execute([$weight, $goal_calories, $_SESSION['user_id']]);

echo json_encode(['success' => true]);
