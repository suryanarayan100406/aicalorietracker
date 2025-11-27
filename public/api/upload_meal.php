<?php
require_once __DIR__ . '/../../app/lib/auth.php';
require_once __DIR__ . '/../../app/lib/db.php';
require_once __DIR__ . '/../../app/models/Meal.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

if (!isset($_FILES['photo'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No image uploaded']);
    exit;
}

// TODO: reuse your existing upload logic from MealController:
// - move_uploaded_file()
// - call your AI / food DB logic
// - create a row in meals table via Meal::create(...)

// For demo, assume you already do something like this in your web flow:
// $meal = Meal::createFromUpload($_SESSION['user_id'], $_FILES['photo']);

$meal = [
    'id' => 1,
    'image_path' => '/uploads/example.jpg',
    'calories' => 550,
    'macros' => json_encode([
        'protein' => 25,
        'carb' => 60,
        'fat' => 18,
    ]),
    'created_at' => date('Y-m-d H:i:s'),
    'suggestion' => 'Nice balanced meal, maybe add more veggies next time.',
];

$macros = json_decode($meal['macros'], true);

echo json_encode([
    'success' => true,
    'meal' => [
        'id' => (int)$meal['id'],
        'image_path' => $meal['image_path'],
        'calories' => (float)$meal['calories'],
        'protein' => (float)($macros['protein'] ?? 0),
        'carb' => (float)($macros['carb'] ?? 0),
        'fat' => (float)($macros['fat'] ?? 0),
        'created_at' => $meal['created_at'],
        'suggestion' => $meal['suggestion'] ?? '',
    ],
]);

