<?php
require_once __DIR__ . '/../lib/db.php';

class FoodDB {
    public static function all() {
        $q = DB::conn()->prepare('SELECT * FROM food_database ORDER BY name');
        $q->execute();
        return $q->fetchAll();
    }

    public static function find($id) {
        $q = DB::conn()->prepare('SELECT * FROM food_database WHERE id=?');
        $q->execute([$id]);
        return $q->fetch();
    }

    public static function create($name, $grams, $cal, $protein, $carb, $fat) {
        $q = DB::conn()->prepare('INSERT INTO food_database (name, typical_portion_grams, calories_per_100g, protein_per_100g, carb_per_100g, fat_per_100g, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
        $q->execute([$name, $grams, $cal, $protein, $carb, $fat]);
        return DB::conn()->lastInsertId();
    }

    public static function update($id, $fields) {
        $sets = [];
        $params = [];
        foreach ($fields as $k=>$v) {
            $sets[] = "$k=?";
            $params[] = $v;
        }
        $params[] = $id;
        $sql = "UPDATE food_database SET " . join(', ', $sets) . " WHERE id=?";
        DB::conn()->prepare($sql)->execute($params);
    }

    public static function delete($id) {
        DB::conn()->prepare('DELETE FROM food_database WHERE id=?')->execute([$id]);
    }
}
?>
