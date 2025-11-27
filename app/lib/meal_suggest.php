<?php
function sustainability_score($detected_items) {
    // Example: heuristics (less meat = higher score, vegetarian + local = highest)
    $score = 50;
    foreach ($detected_items as $item) {
        if (stripos($item['name'], 'beef') !== false || stripos($item['name'], 'pork') !== false) $score -= 20;
        if (stripos($item['name'], 'chicken') !== false) $score -= 10;
        if (stripos($item['name'], 'broccoli') !== false || stripos($item['name'], 'beans') !== false) $score += 10;
        if ($item['vegetarian'] ?? false) $score += 8;
    }
    return max(0, min(100, $score));
}
?>
