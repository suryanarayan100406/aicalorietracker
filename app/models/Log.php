<?php
require_once __DIR__ . '/../lib/db.php';

class Log {
    public static function recent($limit = 100) {
        $q = DB::conn()->prepare('SELECT * FROM logs ORDER BY created_at DESC LIMIT ?');
        $q->execute([$limit]);
        return $q->fetchAll();
    }
}
?>
