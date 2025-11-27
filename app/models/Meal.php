<?php
require_once __DIR__ . '/../lib/db.php';

class Meal {
    public static function create($user_id, $image_path, $detected_items, $calories, $macros, $confidence, $portion_grams, $suggestion, $waste_score, $is_leftover) {
        $detected_items_json = json_encode(is_array($detected_items) ? $detected_items : []);
        $macros_json = json_encode(is_array($macros) ? $macros : ['protein' => 0, 'carb' => 0, 'fat' => 0]);
        $calories_val = is_numeric($calories) ? $calories : 0;
        $confidence_val = is_numeric($confidence) ? $confidence : null;
        $portion_val = is_numeric($portion_grams) ? $portion_grams : null;
        $suggestion_val = $suggestion ?? '';
        $waste_score_val = is_numeric($waste_score) ? $waste_score : 0;
        $is_leftover_val = $is_leftover ? 1 : 0;

        $q = DB::conn()->prepare(
            'INSERT INTO meals (user_id, image_path, detected_items, calories, macros, confidence, portion_grams, suggestion, waste_score, is_leftover, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())'
        );

        $q->execute([
            $user_id,
            $image_path,
            $detected_items_json,
            $calories_val,
            $macros_json,
            $confidence_val,
            $portion_val,
            $suggestion_val,
            $waste_score_val,
            $is_leftover_val
        ]);

        return DB::conn()->lastInsertId();
    }

    public static function byUser($user_id, $limit = 100) {
        $limit = (int)$limit;
        $sql = "SELECT * FROM meals WHERE user_id = ? ORDER BY created_at DESC LIMIT $limit";
        $q = DB::conn()->prepare($sql);
        $q->execute([$user_id]);
        return $q->fetchAll();
    }

    public static function find($meal_id) {
        $q = DB::conn()->prepare('SELECT * FROM meals WHERE id = ?');
        $q->execute([$meal_id]);
        return $q->fetch();
    }
}
?>
