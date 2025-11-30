<?php
require_once __DIR__ . '/db.php';

// Gemini API key + endpoint (only one)
if (!defined('LLM_KEY')) {
    define('LLM_KEY', 'enter gemini key');
}
if (!defined('LLM_ENDPOINT')) {
    define('LLM_ENDPOINT', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent');
}
if (!defined('LLM_MODEL')) {
    define('LLM_MODEL', 'gemini-2.5-flash');
}

/**
 * Strip markdown/code blocks and parse JSON safely
 */
function parse_llm_json($text) {
    $text = trim($text);
    // Remove code block markers
    $text = preg_replace('/^```json\s*/i', '', $text);
    $text = preg_replace('/^```/i', '', $text);
    $text = preg_replace('/\s*```$/i', '', $text);
    $decoded = json_decode($text, true);
    return (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
}

/**
 * Estimate calories & macros for a food image using Gemini
 */
function llm_estimate_calories($image_path, $user_id, $is_leftover = false) {
    if (!file_exists($image_path) || !is_readable($image_path)) {
        return ['success' => false, 'error' => 'Image file missing or unreadable.'];
    }

    $image_data = base64_encode(file_get_contents($image_path));

    // Request body with strict JSON instruction
    $data = [
        "contents" => [[
            "parts" => [
                [
                    "text" => "Estimate calories and macros for this food image. " .
                              "Return ONLY valid JSON, no extra text or code blocks, " .
                              "with EXACT structure: " .
                              '{"items":[{"name","grams"}],"calories","macros":{"protein","carb","fat"},"confidence","suggestion","waste_score","recipe"}'
                ],
                ["inline_data" => [
                    "mime_type" => "image/jpeg",
                    "data" => $image_data
                ]]
            ]
        ]]
    ];

    $url = LLM_ENDPOINT . '?key=' . LLM_KEY;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 100);
    $res = curl_exec($ch);

    if ($res === false) {
        $curlErr = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE) ?: 0;
        curl_close($ch);
        error_log("Gemini CURL error: $curlErr");
        return ['success' => false, 'error' => 'LLM request failed: ' . $curlErr];
    }

    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200) {
        return ['success' => false, 'error' => 'API error: ' . $http_code . ' Response: ' . substr($res, 0, 1000)];
    }

    $result = json_decode($res, true);
    if ($result === null && json_last_error() !== JSON_ERROR_NONE) {
        return ['success' => false, 'error' => 'Invalid JSON from LLM', 'raw' => $res];
    }

    // Parse response from candidates
    $parsed = null;
    if (!empty($result['candidates'][0]['content']['parts'])) {
        foreach ($result['candidates'][0]['content']['parts'] as $part) {
            if (isset($part['text'])) {
                $decoded = parse_llm_json($part['text']);
                if (is_array($decoded)) {
                    $parsed = $decoded;
                    break;
                }
            }
        }
    }

    if ($parsed === null) {
        return ['success' => false, 'error' => 'No valid JSON payload in LLM response', 'raw' => $result];
    }

    return ['success' => true, 'data' => $parsed, 'raw' => $result];
}
?>
