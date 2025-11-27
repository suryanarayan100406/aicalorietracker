<?php
require_once __DIR__ . '/../lib/db.php';

class Admin {
    public static function byUsername($username) {
        $db = DB::conn();   // ✅ use conn()
        $stmt = $db->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    public static function create($username, $email, $password) {
        $db = DB::conn();   // ✅ use conn()
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO admins (username, email, password) VALUES (?, ?, ?)");
        return $stmt->execute([$username, $email, $hash]);
    }
}
