<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="../index.php">Blog CRUD</a>
        
        <div class="navbar-nav ms-auto">
            <?php if(isset($_SESSION['user_id'])): ?>
                <span class="navbar-text me-3">Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span>
                <a href="../crud/create.php" class="btn btn-success me-2">Create Post</a>
                <a href="../auth/logout.php" class="btn btn-outline-light">Logout</a>
            <?php else: ?>
                <a href="../auth/login.php" class="btn btn-outline-light me-2">Login</a>
                <a href="../auth/register.php" class="btn btn-primary">Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>