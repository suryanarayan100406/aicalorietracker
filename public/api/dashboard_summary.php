<?php
require_once __DIR__ . '/../../app/lib/auth.php';
require_once __DIR__ . '/../../app/lib/db.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$userId = $_SESSION['user_id'];
$pdo = DB::conn();

// today totals
$sqlToday = "
 SELECT 
   COALESCE(SUM(calories),0) AS total_cal,
   COALESCE(SUM(JSON_EXTRACT(macros, '$.protein')),0) AS total_protein,
   COALESCE(SUM(JSON_EXTRACT(macros, '$.fat')),0) AS total_fat
 FROM meals
 WHERE user_id = :uid
   AND DATE(created_at) = CURDATE()
";
$stmt = $pdo->prepare($sqlToday);
$stmt->execute([':uid' => $userId]);
$todayRow = $stmt->fetch() ?: ['total_cal' => 0, 'total_protein' => 0, 'total_fat' => 0];

// weekly calories
$sqlWeek = "
 SELECT COALESCE(SUM(calories),0) AS week_cal
 FROM meals
 WHERE user_id = :uid
   AND YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)
";
$stmt = $pdo->prepare($sqlWeek);
$stmt->execute([':uid' => $userId]);
$weekCalories = (float)$stmt->fetchColumn();

// goal
$sqlGoal = "SELECT goal_calories, weight, username, email FROM users WHERE id = :uid LIMIT 1";
$stmt = $pdo->prepare($sqlGoal);
$stmt->execute([':uid' => $userId]);
$goalRow = $stmt->fetch();

// recent meals
$sqlRecent = "
 SELECT id, image_path, calories, macros, created_at, suggestion
 FROM meals
 WHERE user_id = :uid
 ORDER BY created_at DESC
 LIMIT 10
";
$stmt = $pdo->prepare($sqlRecent);
$stmt->execute([':uid' => $userId]);
$recent = [];
while ($row = $stmt->fetch()) {
    $macros = json_decode($row['macros'] ?? '{}', true);
    $recent[] = [
        'id' => (int)$row['id'],
        'image_path' => $row['image_path'],
        'calories' => (float)$row['calories'],
        'protein' => (float)($macros['protein'] ?? 0),
        'carb' => (float)($macros['carb'] ?? 0),
        'fat' => (float)($macros['fat'] ?? 0),
        'created_at' => $row['created_at'],
        'suggestion' => $row['suggestion'] ?? ''
    ];
}

echo json_encode([
    'success' => true,
    'today' => [
        'calories' => (float)$todayRow['total_cal'],
        'protein'  => (float)$todayRow['total_protein'],
        'fat'      => (float)$todayRow['total_fat']
    ],
    'weekCalories' => $weekCalories,
    'goalCalories' => isset($goalRow['goal_calories']) ? (int)$goalRow['goal_calories'] : null,
    'user' => [
        'username' => $goalRow['username'] ?? '',
        'email' => $goalRow['email'] ?? '',
        'weight' => isset($goalRow['weight']) ? (float)$goalRow['weight'] : null
    ],
    'recentMeals' => $recent
]);
