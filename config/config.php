<?php
// Core config
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'calorievision');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('UPLOAD_DIR', __DIR__ . '/../public/assets/');
define('BASE_URL', '/calorievision/public/');
define('DEFAULT_AVATAR', 'assets/default-avatar.png');

// Gemini LLM API
define('LLM_ENDPOINT_A', getenv('LLM_ENDPOINT_A') ?: 'https://api.gemini-a.local/vision');
define('LLM_ENDPOINT_B', getenv('LLM_ENDPOINT_B') ?: 'https://api.gemini-b.local/vision');
define('LLM_KEY_A', getenv('LLM_KEY_A') ?: '');
define('LLM_KEY_B', getenv('LLM_KEY_B') ?: '');

// LLM default model names
define('LLM_MODEL_A', 'gemini-1.5-pro');
define('LLM_MODEL_B', 'gemini-1.5-pro-abtest');
?>
