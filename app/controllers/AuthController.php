<?php 
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Admin.php';
require_once __DIR__ . '/../lib/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? 'user';

    if ($type === 'user') {
        $user = User::byUsername($_POST['username']);
        if (!$user) {
            $error = "Username not found (User)";
        } elseif (!password_verify($_POST['password'], $user['password'])) {
            $error = "Incorrect password (User)";
        } else {
            regenerate_sess();
            $_SESSION['user_id'] = $user['id'];
            header("Location: /public/dashboard.php");
            exit();
        }
    } elseif ($type === 'admin') {
        $admin = Admin::byUsername($_POST['username']);
        if (!$admin) {
            $error = "Username not found (Admin)";
        } elseif (!password_verify($_POST['password'], $admin['password'])) {
            $error = "Incorrect password (Admin)";
        } else {
            regenerate_sess();
            $_SESSION['admin_id'] = $admin['id'];
            header("Location: /public/admin/index.php");
            exit();
        }
    } else {
        $error = "Invalid login type";
    }
}
?>
