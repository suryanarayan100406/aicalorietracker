<?php
require_once __DIR__ . '/../../app/lib/auth.php';
require_once __DIR__ . '/../../app/models/Meal.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$meals = Meal::byUser($user_id, 31);
$days = [];
foreach ($meals as $meal) {
    $date = substr($meal['created_at'], 0, 10);
    if (!isset($days[$date])) $days[$date] = [];
    $macros = json_decode($meal['macros'] ?? '{}', true);
    $days[$date][] = [
        'id' => (int)$meal['id'],
        'image_path' => $meal['image_path'],
        'calories' => (float)$meal['calories'],
        'protein' => (float)($macros['protein'] ?? 0),
        'carb' => (float)($macros['carb'] ?? 0),
        'fat' => (float)($macros['fat'] ?? 0),
        'created_at' => $meal['created_at'],
        'suggestion' => $meal['suggestion'] ?? ''
    ];
}

echo json_encode([
    'success' => true,
    'days' => $days
]);
