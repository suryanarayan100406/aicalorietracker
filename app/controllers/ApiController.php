<?php
require_once __DIR__ . '/../lib/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Provide endpoints for AJAX requests (user feedback, etc).
    // E.g., saving meal feedback for A/B test
    if ($_POST['action'] == 'rate_meal' && isset($_POST['meal_id'], $_POST['rating'], $_POST['ab_group'])) {
        $user_id = $_SESSION['user_id'];
        $meal_id = intval($_POST['meal_id']);
        $ab_group = $_POST['ab_group'];
        $rating = $_POST['rating']; // 'accurate' or 'inaccurate'
        DB::conn()->prepare('INSERT INTO meal_feedback (meal_id, user_id, ab_group, rating) VALUES (?, ?, ?, ?)')
            ->execute([$meal_id, $user_id, $ab_group, $rating]);
        echo json_encode(['success' => true]);
        exit;
    }
}
?>
