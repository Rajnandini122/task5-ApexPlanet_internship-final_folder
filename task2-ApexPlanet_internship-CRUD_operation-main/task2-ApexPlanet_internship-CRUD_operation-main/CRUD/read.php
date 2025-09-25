<?php
session_start();
require_once '../config/database.php';
require_once '../models/Post.php';

$database = new Database();
$db = $database->getConnection();
$post = new Post($db);

// Check if post ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Post ID not specified!";
    header("Location: ../index.php");
    exit;
}

// Get post data
$post->id = $_GET['id'];
$post_data = $post->readOne();

// Check if post exists
if (!$post_data) {
    $_SESSION['error'] = "Post not found!";
    header("Location: ../index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post_data['title']) ?> - Blog CRUD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .post-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
        }
        .post-content {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #333;
        }
        .post-meta {
            background-color: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 1rem;
            border-radius: 0 5px 5px 0;
        }
        .action-buttons .btn {
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }
        .back-button {
            transition: all 0.3s ease;
        }
        .back-button:hover {
            transform: translateX(-5px);
        }
        .post-image {
            max-height: 400px;
            object-fit: cover;
            width: 100%;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        .comment-section {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-top: 3rem;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-blog me-2"></i>Blog CRUD
            </a>
            
            <div class="navbar-nav ms-auto">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <span class="navbar-text me-3">
                        <i class="fas fa-user me-1"></i>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>
                    </span>
                    <a href="../crud/create.php" class="btn btn-success me-2">
                        <i class="fas fa-plus me-1"></i>Create Post
                    </a>
                    <a href="../auth/logout.php" class="btn btn-outline-light">
                        <i class="fas fa-sign-out-alt me-1"></i>Logout
                    </a>
                <?php else: ?>
                    <a href="../auth/login.php" class="btn btn-outline-light me-2">
                        <i class="fas fa-sign-in-alt me-1"></i>Login
                    </a>
                    <a href="../auth/register.php" class="btn btn-primary">
                        <i class="fas fa-user-plus me-1"></i>Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Back Button -->
    <div class="container mt-3">
        <a href="../index.php" class="btn btn-outline-secondary back-button">
            <i class="fas fa-arrow-left me-1"></i>Back to All Posts
        </a>
    </div>

    <!-- Post Header -->
    <div class="post-header">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb" style="background: transparent; color: rgba(255,255,255,0.8);">
                            <li class="breadcrumb-item"><a href="../index.php" style="color: rgba(255,255,255,0.8);">Home</a></li>
                            <li class="breadcrumb-item active" style="color: white;">Post</li>
                        </ol>
                    </nav>
                    
                    <h1 class="display-4 fw-bold"><?= htmlspecialchars($post_data['title']) ?></h1>
                    
                    <div class="d-flex align-items-center mt-3">
                        <div class="d-flex align-items-center me-4">
                            <i class="fas fa-user me-2"></i>
                            <span><?= htmlspecialchars($post_data['username']) ?></span>
                        </div>
                        <div class="d-flex align-items-center me-4">
                            <i class="fas fa-calendar me-2"></i>
                            <span><?= date('F j, Y', strtotime($post_data['created_at'])) ?></span>
                        </div>
                        <?php if($post_data['updated_at'] != $post_data['created_at']): ?>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-edit me-2"></i>
                            <span>Updated: <?= date('F j, Y', strtotime($post_data['updated_at'])) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Messages -->
                <?php if(isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if(isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Post Content -->
                <article class="post-content mb-5">
                    <div class="post-text">
                        <?= nl2br(htmlspecialchars($post_data['content'])) ?>
                    </div>
                </article>

                <!-- Action Buttons -->
                <?php if(isset($_SESSION['user_id']) && ($_SESSION['user_id'] == $post_data['user_id'] || $_SESSION['role'] == 'admin')): ?>
                <div class="action-buttons mb-4 p-3 bg-light rounded">
                    <h5><i class="fas fa-cog me-2"></i>Post Actions</h5>
                    <div class="btn-group">
                        <a href="update.php?id=<?= $post_data['id'] ?>" class="btn btn-warning">
                            <i class="fas fa-edit me-1"></i>Edit Post
                        </a>
                        <a href="delete.php?id=<?= $post_data['id'] ?>" class="btn btn-danger" 
                           onclick="return confirm('Are you sure you want to delete this post? This action cannot be undone.')">
                            <i class="fas fa-trash me-1"></i>Delete Post
                        </a>
                        <a href="../index.php" class="btn btn-secondary">
                            <i class="fas fa-list me-1"></i>All Posts
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Post Meta Information -->
                <div class="post-meta mb-5">
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-info-circle me-2"></i>Post Information</h6>
                            <ul class="list-unstyled">
                                <li><strong>Author:</strong> <?= htmlspecialchars($post_data['username']) ?></li>
                                <li><strong>Created:</strong> <?= date('F j, Y g:i A', strtotime($post_data['created_at'])) ?></li>
                                <li><strong>Last Updated:</strong> <?= date('F j, Y g:i A', strtotime($post_data['updated_at'])) ?></li>
                                <li><strong>Post ID:</strong> #<?= $post_data['id'] ?></li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-share-alt me-2"></i>Share This Post</h6>
                            <div class="btn-group">
                                <button class="btn btn-outline-primary btn-sm" onclick="sharePost('twitter')">
                                    <i class="fab fa-twitter"></i>
                                </button>
                                <button class="btn btn-outline-primary btn-sm" onclick="sharePost('facebook')">
                                    <i class="fab fa-facebook"></i>
                                </button>
                                <button class="btn btn-outline-primary btn-sm" onclick="sharePost('linkedin')">
                                    <i class="fab fa-linkedin"></i>
                                </button>
                                <button class="btn btn-outline-primary btn-sm" onclick="copyPostLink()">
                                    <i class="fas fa-link"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Between Posts -->
                <div class="post-navigation d-flex justify-content-between mb-5">
                    <?php
                    // Get previous post
                    $prev_query = "SELECT id, title FROM posts WHERE id < ? ORDER BY id DESC LIMIT 1";
                    $prev_stmt = $db->prepare($prev_query);
                    $prev_stmt->bindParam(1, $post_data['id']);
                    $prev_stmt->execute();
                    $prev_post = $prev_stmt->fetch(PDO::FETCH_ASSOC);

                    // Get next post
                    $next_query = "SELECT id, title FROM posts WHERE id > ? ORDER BY id ASC LIMIT 1";
                    $next_stmt = $db->prepare($next_query);
                    $next_stmt->bindParam(1, $post_data['id']);
                    $next_stmt->execute();
                    $next_post = $next_stmt->fetch(PDO::FETCH_ASSOC);
                    ?>

                    <?php if($prev_post): ?>
                    <a href="read.php?id=<?= $prev_post['id'] ?>" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-1"></i>Previous Post<br>
                        <small><?= htmlspecialchars(substr($prev_post['title'], 0, 30)) ?>...</small>
                    </a>
                    <?php else: ?>
                    <span class="btn btn-outline-secondary disabled">
                        <i class="fas fa-arrow-left me-1"></i>Previous Post
                    </span>
                    <?php endif; ?>

                    <?php if($next_post): ?>
                    <a href="read.php?id=<?= $next_post['id'] ?>" class="btn btn-outline-primary">
                        Next Post<i class="fas fa-arrow-right ms-1"></i><br>
                        <small><?= htmlspecialchars(substr($next_post['title'], 0, 30)) ?>...</small>
                    </a>
                    <?php else: ?>
                    <span class="btn btn-outline-secondary disabled">
                        Next Post<i class="fas fa-arrow-right ms-1"></i>
                    </span>
                    <?php endif; ?>
                </div>

                <!-- Related Posts (Basic Implementation) -->
                <div class="related-posts mb-5">
                    <h4 class="mb-3"><i class="fas fa-th-list me-2"></i>Other Posts</h4>
                    <div class="row">
                        <?php
                        // Get 3 random posts (excluding current one)
                        $related_query = "SELECT id, title, created_at FROM posts WHERE id != ? ORDER BY RAND() LIMIT 3";
                        $related_stmt = $db->prepare($related_query);
                        $related_stmt->bindParam(1, $post_data['id']);
                        $related_stmt->execute();
                        $related_posts = $related_stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        if($related_posts):
                            foreach($related_posts as $related_post):
                        ?>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="card-title"><?= htmlspecialchars(substr($related_post['title'], 0, 50)) ?>...</h6>
                                    <small class="text-muted"><?= date('M j, Y', strtotime($related_post['created_at'])) ?></small>
                                </div>
                                <div class="card-footer bg-transparent">
                                    <a href="read.php?id=<?= $related_post['id'] ?>" class="btn btn-sm btn-outline-primary w-100">Read More</a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; else: ?>
                        <div class="col-12">
                            <div class="alert alert-info">No other posts available.</div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-blog me-2"></i>Blog CRUD</h5>
                    <p class="mb-0">A simple PHP CRUD application for managing blog posts.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">&copy; <?= date('Y') ?> Blog CRUD. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Share functionality
        function sharePost(platform) {
            const title = "<?= addslashes($post_data['title']) ?>";
            const url = window.location.href;
            
            let shareUrl = '';
            switch(platform) {
                case 'twitter':
                    shareUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(title)}&url=${encodeURIComponent(url)}`;
                    break;
                case 'facebook':
                    shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
                    break;
                case 'linkedin':
                    shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}`;
                    break;
            }
            
            if(shareUrl) {
                window.open(shareUrl, '_blank', 'width=600,height=400');
            }
        }

        // Copy link to clipboard
        function copyPostLink() {
            const url = window.location.href;
            navigator.clipboard.writeText(url).then(function() {
                alert('Post link copied to clipboard!');
            }, function(err) {
                console.error('Could not copy text: ', err);
            });
        }

        // Add reading time calculation
        document.addEventListener('DOMContentLoaded', function() {
            const content = document.querySelector('.post-text').textContent;
            const words = content.split(/\s+/).length;
            const readingTime = Math.ceil(words / 200); // Average reading speed: 200 words per minute
            
            // Create reading time element
            const readingTimeElement = document.createElement('div');
            readingTimeElement.className = 'd-flex align-items-center mt-2';
            readingTimeElement.innerHTML = `
                <i class="fas fa-clock me-2"></i>
                <span>Reading time: ${readingTime} min</span>
            `;
            
            // Add to post meta
            const postMeta = document.querySelector('.post-header .container .row .col-lg-8');
            const existingMeta = postMeta.querySelector('.d-flex.align-items-center.mt-3');
            existingMeta.appendChild(readingTimeElement);
        });
    </script>
</body>
</html>