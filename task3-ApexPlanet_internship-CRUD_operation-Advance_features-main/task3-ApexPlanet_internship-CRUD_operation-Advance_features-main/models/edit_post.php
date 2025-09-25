<?php
session_start();
require_once 'config/database.php';
require_once 'models/Post.php';
require_once 'includes/auth_check.php';

// Check authentication
checkAuth();

$database = new Database();
$db = $database->getConnection();
$post = new Post($db);

// Get post ID from URL
if (isset($_GET['id'])) {
    $post->id = $_GET['id'];
    $post->readOne();
} else {
    $_SESSION['error'] = "Post ID not specified!";
    header("Location: index.php");
    exit;
}

// Check if user owns the post or is admin
if ($_SESSION['user_id'] != $post->user_id && $_SESSION['role'] != 'admin') {
    $_SESSION['error'] = "You don't have permission to edit this post!";
    header("Location: index.php");
    exit;
}

// Handle form submission
if ($_POST && isset($_POST['update'])) {
    $post->title = htmlspecialchars(strip_tags($_POST['title']));
    $post->content = htmlspecialchars(strip_tags($_POST['content']));
    
    // Validation
    if (empty($post->title) || empty($post->content)) {
        $_SESSION['error'] = "Title and content are required!";
    } else {
        if ($post->update()) {
            $_SESSION['success'] = "Post updated successfully!";
            header("Location: index.php");
            exit;
        } else {
            $_SESSION['error'] = "Failed to update post!";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Post - Blog Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            max-width: 800px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Blog Admin</a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span>
                <a href="auth/logout.php" class="btn btn-outline-light">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Edit Post</h2>
            <a href="index.php" class="btn btn-secondary">Back to Posts</a>
        </div>

        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="post">
                    <div class="mb-3">
                        <label for="title" class="form-label">Post Title</label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?= htmlspecialchars($post->title) ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="content" class="form-label">Post Content</label>
                        <textarea class="form-control" id="content" name="content" 
                                  rows="10" required><?= htmlspecialchars($post->content) ?></textarea>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="index.php" class="btn btn-secondary me-md-2">Cancel</a>
                        <button type="submit" name="update" class="btn btn-primary">Update Post</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Client-side validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const content = document.getElementById('content').value.trim();
            
            if (!title) {
                e.preventDefault();
                alert('Please enter a title');
                document.getElementById('title').focus();
                return false;
            }
            
            if (!content) {
                e.preventDefault();
                alert('Please enter content');
                document.getElementById('content').focus();
                return false;
            }
        });
    </script>
</body>
</html>