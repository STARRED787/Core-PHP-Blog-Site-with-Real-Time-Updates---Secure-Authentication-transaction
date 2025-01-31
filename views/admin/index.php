<?php
session_start();
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/JWT.php';
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';

// Create database connection
$database = new Database();
$pdo = $database->getConnection();

// Initialize and check admin authentication
$authMiddleware = new AuthMiddleware($pdo);
$authMiddleware->redirectIfNotAdmin();

// Get user data
$user = $authMiddleware->getUser();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Admin Dashboard</a>
            <div class="navbar-nav ms-auto">
                <span class="nav-item nav-link text-light">Welcome,
                    <?php echo htmlspecialchars($user['username']); ?></span>
                <a class="nav-link" href="/KD Enterprise/blog-site/auth/logout.php">Logout</a>
            </div>

        </div>
    </nav>

    <div class="container mt-4">
        <h1>Welcome to Admin Dashboard</h1>

        <!-- Button to trigger Add Blog Modal -->
        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addBlogModal">
            Add New Blog
        </button>

        <!-- Table to display blogs -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Content</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Blog entries should be dynamically inserted here -->
            </tbody>
        </table>

        <!-- Add Blog Modal -->
        <div class="modal fade" id="addBlogModal" tabindex="-1" aria-labelledby="addBlogModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addBlogModalLabel">Add New Blog</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="../routes/blog.php" method="POST">
                            <div class="mb-3">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            <div class="mb-3">
                                <label for="content" class="form-label">Content</label>
                                <textarea class="form-control" id="content" name="content" rows="3" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Add Blog</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function checkTokenExpiration() {
            fetch('/KD Enterprise/blog-site/api/check-token.php')
                .then(response => response.json())
                .then(data => {
                    if (data.expired) {
                        window.location.href = '/KD Enterprise/blog-site/public/index.php';
                    }
                });
        }

        // Check every minute
        setInterval(checkTokenExpiration, 60000);
    </script>
</body>

</html>