<?php
session_start();
require_once 'config/database.php';
require_once 'models/Post.php';

$database = new Database();
$db = $database->getConnection();
$post = new Post($db);

// Get all posts
$stmt = $post->readAll();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Add pagination to index.php after the Post class initialization
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 5;
$from_record_num = ($records_per_page * $page) - $records_per_page;

$total_query = "SELECT COUNT(*) as total FROM posts";
$total_stmt = $db->prepare($total_query);
$total_stmt->execute();
$total_row = $total_stmt->fetch(PDO::FETCH_ASSOC);
$total_pages = ceil($total_row['total'] / $records_per_page);

// Check if search term is present
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build base query
$base_query = "FROM posts p 
               LEFT JOIN users u ON p.user_id = u.id 
               WHERE 1";

// Add search condition if needed
if ($search !== '') {
    $base_query .= " AND (p.title LIKE :search OR p.content LIKE :search)";
}

// Count total for pagination
$total_query = "SELECT COUNT(*) as total " . $base_query;
$total_stmt = $db->prepare($total_query);
if ($search !== '') {
    $search_param = "%{$search}%";
    $total_stmt->bindParam(':search', $search_param);
}
$total_stmt->execute();
$total_row = $total_stmt->fetch(PDO::FETCH_ASSOC);
$total_pages = ceil($total_row['total'] / $records_per_page);

// Get paginated posts
$query = "SELECT p.*, u.username " . $base_query . " 
          ORDER BY p.created_at DESC 
          LIMIT :limit OFFSET :offset";
$stmt = $db->prepare($query);
if ($search !== '') {
    $stmt->bindParam(':search', $search_param);
}
$stmt->bindParam(':limit', $records_per_page, PDO::PARAM_INT);
$stmt->bindParam(':offset', $from_record_num, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<nav>
    <ul class="pagination">
        <?php for($i=1; $i<=$total_pages; $i++): ?>
            <li class="page-item <?= $page == $i ? 'active' : '' ?>">
                <a class="page-link" href="index.php?page=<?= $i ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
    </ul>
</nav>


<!DOCTYPE html>
<html>
<head>
    <title>Blog CRUD App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Blog CRUD</a>
            
            <div class="navbar-nav ms-auto">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <span class="navbar-text me-3">Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span>
                    <a href="crud/create.php" class="btn btn-success me-2">Create Post</a>
                    <a href="auth/logout.php" class="btn btn-outline-light">Logout</a>
                <?php else: ?>
                    <a href="auth/login.php" class="btn btn-outline-light me-2">Login</a>
                    <a href="auth/register.php" class="btn btn-primary">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
<!-- Search Form -->
<div class="container mt-4">
    <form class="d-flex mb-4" method="GET" action="index.php">
        <input class="form-control me-2" 
               type="search" 
               name="search" 
               placeholder="Search posts..." 
               value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
        <button class="btn btn-outline-success" type="submit">Search</button>
    </form>
</div>

    <div class="container mt-4">
        <!-- Messages -->
        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <!-- Posts List -->
        <div class="row">
            <?php if(empty($posts)): ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <h4>No posts found</h4>
                        <p>Be the first to create a post!</p>
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <a href="crud/create.php" class="btn btn-primary">Create First Post</a>
                        <?php else: ?>
                            <a href="auth/register.php" class="btn btn-primary">Register to Create Posts</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach($posts as $post): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($post['title']) ?></h5>
                                <p class="card-text"><?= nl2br(htmlspecialchars(substr($post['content'], 0, 150))) ?>...</p>
                                <small class="text-muted">
                                    By: <?= htmlspecialchars($post['username']) ?> | 
                                    <?= date('M j, Y', strtotime($post['created_at'])) ?>
                                </small>
                            </div>
                            <div class="card-footer">
                                <a href="crud/read.php?id=<?= $post['id'] ?>" class="btn btn-primary btn-sm">Read More</a>
                                <?php if(isset($_SESSION['user_id']) && ($_SESSION['user_id'] == $post['user_id'] || $_SESSION['role'] == 'admin')): ?>
                                    <a href="crud/update.php?id=<?= $post['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <a href="crud/delete.php?id=<?= $post['id'] ?>" class="btn btn-danger btn-sm" 
                                       onclick="return confirm('Are you sure?')">Delete</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>