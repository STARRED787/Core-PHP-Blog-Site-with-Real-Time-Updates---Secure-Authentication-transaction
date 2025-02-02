<?php
/**
 * User Dashboard View
 * Displays blog posts and handles real-time updates for regular users
 * Features:
 * - Real-time blog post updates via WebSocket
 * - Responsive grid layout for blog posts
 * - Secure authentication check
 */

// Load authentication and blog model
require_once __DIR__ . '/../auth/user_auth.php';
require_once __DIR__ . '/../models/Blog.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <!-- Bootstrap CSS for responsive design and styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Header with user information and logout -->
    <header class="bg-light py-3 border-bottom mb-4">
        <div class="container d-flex justify-content-between align-items-center">
            <!-- Display authenticated username dynamically -->
            <h1 class="h3" id="username">Welcome, <?php echo $username; ?>!</h1>
            <p class="text-muted mb-0">Role: User</p>
            <!-- Logout navigation -->
            <nav>
                <form action="../handler/logout_handler.php" method="post">
                    <button type="submit" class="btn btn-danger" id="logoutBtn">Logout</button>
                </form>
               
            </nav>
        </div>
    </header>

    <!-- Main content area for blog posts -->
    <main class="container">
        <h2 class="mb-4">User Dashboard</h2>

        <!-- Responsive grid for blog posts -->
        <div class="row row-cols-1 row-cols-md-3 g-4" id="blogPosts">
            <!-- Blog posts will be dynamically inserted here -->
        </div>
    </main>

    <!-- Required JavaScript libraries -->
    <!-- Bootstrap JS for UI components -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JavaScript -->

    <!-- WebSocket handler for real-time updates -->
    <script src="../websocket/userView.js"></script>
</body>
</html>
