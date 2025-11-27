<?php
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../models/Meal.php';

require_login();

$user_id = $_SESSION['user_id'];
$meals = Meal::byUser($user_id, 31);
$days = [];
foreach ($meals as $meal) {
    $date = substr($meal['created_at'], 0, 10);
    if (!isset($days[$date])) $days[$date] = [];
    $days[$date][] = $meal;
}
?>
