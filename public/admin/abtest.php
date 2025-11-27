<?php
require_once __DIR__ . '/../../app/controllers/AdminController.php';
$stats = DB::conn()->query("SELECT ab_group, rating, COUNT(*) as n FROM meal_feedback GROUP BY ab_group, rating")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin - AB Test</title>
    <meta charset="utf-8"/>
    <link rel="stylesheet" href="../assets/style.css"/>
</head>
<body>
<main>
    <h2>A/B Test Results</h2>
    <table>
        <tr><th>A/B Group</th><th>Rating</th><th>Number</th></tr>
        <?php foreach ($stats as $s): ?>
        <tr>
            <td><?= htmlspecialchars($s['ab_group']) ?></td>
            <td><?= htmlspecialchars($s['rating']) ?></td>
            <td><?= $s['n'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</main>
</body>
</html>
