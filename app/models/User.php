<?php
require_once __DIR__ . '/../lib/db.php';

class User {
    public static function find($id) {
        $q = DB::conn()->prepare('SELECT * FROM users WHERE id=?');
        $q->execute([$id]);
        return $q->fetch();
    }
    public static function byUsername($username) {
        $q = DB::conn()->prepare('SELECT * FROM users WHERE username=?');
        $q->execute([$username]);
        return $q->fetch();
    }
    public static function create($username, $email, $password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $q = DB::conn()->prepare('INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())');
        $q->execute([$username, $email, $hash]);
        return DB::conn()->lastInsertId();
    }
}
?>
