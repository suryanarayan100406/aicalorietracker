<?php
function ab_get_group($user_id) {
    // Simple modulo based AB assignment for repeatability
    return ($user_id % 2 === 0) ? 'A' : 'B';
}
?>
