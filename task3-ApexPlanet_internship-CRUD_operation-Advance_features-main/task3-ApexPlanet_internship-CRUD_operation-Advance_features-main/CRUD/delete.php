<?php
session_start();
require_once '../config/database.php';
require_once '../models/Post.php';
require_once '../includes/auth_check.php';

checkAuth();

if (isset($_GET['id'])) {
    $database = new Database();
    $db = $database->getConnection();
    $post = new Post($db);
    
    $post->id = $_GET['id'];
    $post_data = $post->readOne();
    
    if ($post_data) {
        // Check ownership
        if (checkPostOwnership($post_data['user_id'], $_SESSION['user_id'], $_SESSION['role'])) {
            if ($post->delete()) {
                $_SESSION['success'] = "Post deleted successfully!";
            } else {
                $_SESSION['error'] = "Failed to delete post!";
            }
        } else {
            $_SESSION['error'] = "You don't have permission to delete this post!";
        }
    } else {
        $_SESSION['error'] = "Post not found!";
    }
} else {
    $_SESSION['error'] = "Post ID not specified!";
}

header("Location: ../index.php");
exit;
?>