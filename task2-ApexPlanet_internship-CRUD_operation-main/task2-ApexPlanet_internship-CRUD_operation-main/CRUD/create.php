<?php
session_start();
require_once '../config/database.php';
require_once '../models/Post.php';
require_once '../includes/auth_check.php';

checkAuth();

$database = new Database();
$db = $database->getConnection();
$post = new Post($db);

if ($_POST) {
    $post->title = sanitizeInput($_POST['title']);
    $post->content = sanitizeInput($_POST['content']);
    $post->user_id = $_SESSION['user_id'];
    
    if (empty($post->title) || empty($post->content)) {
        $_SESSION['error'] = "Title and content are required!";
    } else {
        if ($post->create()) {
            $_SESSION['success'] = "Post created successfully!";
            header("Location: ../index.php");
            exit;
        } else {
            $_SESSION['error'] = "Failed to create post!";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Post - CRUD App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Create New Post</h4>
                    </div>
                    <div class="card-body">
                        <?php if(isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                        <?php endif; ?>
                        
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" class="form-control" required
                                       value="<?= isset($_POST['title']) ? htmlspecialchars($_POST['title']) : '' ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Content</label>
                                <textarea name="content" class="form-control" rows="10" required><?= isset($_POST['content']) ? htmlspecialchars($_POST['content']) : '' ?></textarea>
                            </div>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="../index.php" class="btn btn-secondary me-md-2">Cancel</a>
                                <button type="submit" class="btn btn-primary">Create Post</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>