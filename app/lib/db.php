<?php
require_once __DIR__ . '/../../config/config.php';

class DB {
    public static function conn() {
        static $pdo;
        if ($pdo === null) {
            // Correct DSN - quote properly and concatenate strings
            $dsn = "mysql:host=sql100.infinityfree.com;dbname=if0_40061703_ai;charset=utf8mb4";
            // Put username and password as strings
            $username = 'if0_40061703';
            $password = 'RC7ihZAkxMTVle';

            // PDO constructor needs variables, not bare strings or constants
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        }
        return $pdo;
    }
}
?>
