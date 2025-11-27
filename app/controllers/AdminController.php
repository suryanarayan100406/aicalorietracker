<?php
require_once __DIR__ . '/../models/Admin.php';
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../models/FoodDB.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Log.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'ban_user':
            DB::conn()->prepare('UPDATE users SET is_banned=1 WHERE id=?')->execute([$_POST['user_id']]);
            break;
        case 'unban_user':
            DB::conn()->prepare('UPDATE users SET is_banned=0 WHERE id=?')->execute([$_POST['user_id']]);
            break;
        case 'edit_food':
            FoodDB::update($_POST['food_id'], [
                'name' => $_POST['name'], 
                'typical_portion_grams' => $_POST['grams'],
                'calories_per_100g' => $_POST['cal'], 
                'protein_per_100g' => $_POST['protein'],
                'carb_per_100g' => $_POST['carb'],
                'fat_per_100g' => $_POST['fat'],
            ]);
            break;
        case 'remove_food':
            FoodDB::delete($_POST['food_id']);
            break;
        // Add other admin actions (toggle keys, A/B test, etc)
    }
}
?>
