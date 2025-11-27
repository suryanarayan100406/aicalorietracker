<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../app/controllers/AdminController.php';

try {
    $stmt = DB::conn()->query('SELECT * FROM api_keys ORDER BY name');
    $keys = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <title>Admin - API Keys</title>
    <link rel="stylesheet" href="../assets/style.css"/>
    <style>
        table { width:100%; border-collapse: collapse; margin-top:1rem; }
        th, td { padding:0.5rem; border:1px solid #ccc; text-align:center; }
        th { background:#4caf50; color:#fff; }
        button { padding:0.3rem 0.6rem; margin:0 0.1rem; border:none; border-radius:4px; background:#4caf50; color:#fff; cursor:pointer; }
        button:hover { background:#45a049; }
        form { display:inline; }
    </style>
</head>
<body>
<main>
    <h2>Manage LLM API Keys</h2>
    <table>
        <tr>
            <th>Name</th>
            <th>Endpoint</th>
            <th>Model</th>
            <th>Enabled</th>
            <th>Group</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($keys as $k): ?>
        <tr>
            <td><?= htmlspecialchars($k['name']) ?></td>
            <td><?= htmlspecialchars($k['endpoint']) ?></td>
            <td><?= htmlspecialchars($k['model']) ?></td>
            <td><?= $k['enabled'] ? 'Yes' : 'No' ?></td>
            <td><?= htmlspecialchars($k['ab_group']) ?></td>
            <td>
                <form method="POST">
                    <input type="hidden" name="key_id" value="<?= $k['id'] ?>"/>
                    <button name="action" value="toggle_enabled"><?= $k['enabled'] ? "Disable" : "Enable" ?></button>
                </form>
                <form method="POST">
                    <input type="hidden" name="key_id" value="<?= $k['id'] ?>"/>
                    <button name="action" value="toggle_abgroup">Toggle Group</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</main>
</body>
</html>
