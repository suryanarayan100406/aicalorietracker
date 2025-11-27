<?php
function json_safe($val) {
    return htmlspecialchars(json_encode($val, JSON_PRETTY_PRINT), ENT_QUOTES);
}
function redirect($page) {
    header("Location: $page");
    exit;
}
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, "UTF-8");
}
?>
