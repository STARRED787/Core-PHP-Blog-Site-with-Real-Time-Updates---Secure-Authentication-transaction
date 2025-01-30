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
                <span class="nav-item nav-link text-light">Welcome, <?php echo htmlspecialchars($user['username']); ?></span>
                <a class="nav-link" href="/KD Enterprise/blog-site/auth/logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1>Welcome to Admin Dashboard</h1>
        <!-- Add your admin dashboard content here -->
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