<?php
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../models/Meal.php';
require_once __DIR__ . '/../lib/llm.php';
require_once __DIR__ . '/../lib/meal_suggest.php';

require_login();

/**
 * Simple redirect helper
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo'])) {
    // Basic upload checks
    if (!isset($_FILES['photo']['tmp_name']) || !is_uploaded_file($_FILES['photo']['tmp_name'])) {
        $error = 'No valid uploaded file found.';
    } else {
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $fname = uniqid('meal_', true) . ($ext ? '.' . $ext : '');
        $dest = defined('UPLOAD_DIR') ? rtrim(UPLOAD_DIR, '/\\') . DIRECTORY_SEPARATOR . $fname : __DIR__ . '/../../uploads/' . $fname;

        // Ensure upload directory exists
        $uploadDir = dirname($dest);
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                $error = 'Server error: cannot create upload directory.';
            }
        }

        if (empty($error)) {
            if (!move_uploaded_file($_FILES['photo']['tmp_name'], $dest)) {
                $error = 'Failed to save uploaded file.';
            } else {
                $is_leftover = !empty($_POST['is_leftover']);
                $llm_result = llm_estimate_calories($dest, $_SESSION['user_id'], $is_leftover);

                if (empty($llm_result) || !isset($llm_result['success']) || !$llm_result['success']) {
                    $error_msg = $llm_result['error'] ?? 'Failed to estimate calories. Please try again.';
                    $raw = isset($llm_result['raw']) ? json_encode($llm_result['raw']) : '';
                    $error = $error_msg . ($raw ? ' Raw: ' . substr($raw, 0, 1000) : '');
                } else {
                    $d = $llm_result['data'] ?? null;

                    if (!is_array($d) || !isset($d['calories'])) {
                        $error = 'Failed to parse LLM response. Please try again.';
                    } else {
                        $items = is_array($d['items']) ? $d['items'] : [];

                        $waste_score = 0;
                        try {
                            $waste_score = function_exists('sustainability_score') ? sustainability_score($items) : 0;
                        } catch (Throwable $e) {
                            $waste_score = 0;
                        }

                        $macros = is_array($d['macros']) ? $d['macros'] : ['protein' => 0, 'carb' => 0, 'fat' => 0];
                        $portion = isset($d['portion']) ? $d['portion'] : null;
                        $suggestion = $d['suggestion'] ?? '';
                        $calories_val = is_numeric($d['calories']) ? (float)$d['calories'] : 0.0;

                        try {
                            $meal_id = Meal::create(
                                $_SESSION['user_id'],
                                'assets/' . $fname,
                                $items,
                                $calories_val,
                                $macros,
                                isset($d['confidence']) ? (float)$d['confidence'] : null,
                                $portion,
                                $suggestion,
                                $waste_score,
                                $is_leftover
                            );

                            redirect("result.php?mid=" . urlencode($meal_id));
                        } catch (Exception $e) {
                            $error = 'Server error saving meal: ' . $e->getMessage();
                        }
                    }
                }
            }
        }
    }
}

// Optional: display $error in your form view if needed
?>
