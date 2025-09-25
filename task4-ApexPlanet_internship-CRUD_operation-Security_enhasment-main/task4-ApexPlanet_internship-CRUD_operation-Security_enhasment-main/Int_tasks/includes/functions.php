<?php
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = "Please login to access this page!";
        header("Location: auth/login.php");
        exit;
    }
}

function checkPostOwnership($post_user_id, $current_user_id, $current_user_role) {
    return ($post_user_id == $current_user_id || $current_user_role == 'admin');
}
?>