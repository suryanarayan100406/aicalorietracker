<?php
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../models/User.php';

require_login();
$user = User::find($_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $weight = floatval($_POST['weight']);
    $goal_calories = intval($_POST['goal_calories']);
    DB::conn()->prepare('UPDATE users SET weight=?, goal_calories=? WHERE id=?')
        ->execute([$weight, $goal_calories, $_SESSION['user_id']]);
    redirect('profile.php');
}
?>
