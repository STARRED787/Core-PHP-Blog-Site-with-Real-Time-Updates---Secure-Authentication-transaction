<?php
session_start();
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/JWT.php';
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';

// Create database connection
$database = new Database();
$pdo = $database->getConnection();

// Initialize and check user authentication
$authMiddleware = new AuthMiddleware($pdo);
$authMiddleware->redirectIfNotAuthenticated();


// Get user data
$user = $authMiddleware->getUser();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <script>
        var ws = new WebSocket("ws://localhost:8080");

        ws.onmessage = function (event) {
            var blog = JSON.parse(event.data);
            var blogContainer = document.getElementById("blogs");

            var blogElement = document.createElement("div");
            blogElement.innerHTML = `<h3>${blog.title}</h3><p>${blog.content}</p><hr>`;
            blogContainer.prepend(blogElement);
        };
    </script>


</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">User Dashboard</a>
            <div class="navbar-nav ms-auto">
                <span class="nav-item nav-link text-light">Welcome,
                    <?php echo htmlspecialchars($user['username']); ?></span>
                <a class="nav-link" href="/KD Enterprise/blog-site/auth/logout.php">Logout</a>
            </div>


        </div>
    </nav>

    <h1>Latest Blogs</h1>
    <div id="blogs">
        <?php
        require 'BlogModel.php';
        $db = new PDO('mysql:host=localhost;dbname=blogdb', 'root', '');
        $blogModel = new BlogModel($db);
        $blogs = $blogModel->getAllBlogs();

        foreach ($blogs as $blog) {
            echo "<div><h3>{$blog['title']}</h3><p>{$blog['content']}</p><hr></div>";
        }
        ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>