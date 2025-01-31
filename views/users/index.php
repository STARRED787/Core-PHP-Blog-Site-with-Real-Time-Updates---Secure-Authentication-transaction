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
        let ws;
        let reconnectAttempts = 0;
        const maxReconnectAttempts = 5;

        function connectWebSocket() {
            ws = new WebSocket('ws://localhost:8080');
            
            ws.onopen = function() {
                console.log('WebSocket connected');
                reconnectAttempts = 0;
            };
            
            ws.onmessage = function(event) {
                try {
                    const data = JSON.parse(event.data);
                    console.log('Received message:', data);
                    
                    switch(data.type) {
                        case 'create':
                            addBlogPost(data.blog);
                            break;
                        case 'update':
                            updateBlogPost(data.blog);
                            break;
                        case 'delete':
                            removeBlogPost(data.blogId);
                            break;
                    }
                } catch (error) {
                    console.error('Error processing WebSocket message:', error);
                }
            };
            
            ws.onerror = function(error) {
                console.error('WebSocket error:', error);
            };
            
            ws.onclose = function() {
                console.log('WebSocket disconnected.');
                if (reconnectAttempts < maxReconnectAttempts) {
                    reconnectAttempts++;
                    console.log(`Attempting to reconnect (${reconnectAttempts}/${maxReconnectAttempts})...`);
                    setTimeout(connectWebSocket, 5000);
                } else {
                    console.log('Max reconnection attempts reached.');
                }
            };
        }

        function addBlogPost(blog) {
            const blogsContainer = document.getElementById('blogs');
            const blogHtml = `
                <div class="blog-post" data-blog-id="${blog.id}">
                    <h3>${escapeHtml(blog.title)}</h3>
                    <p>${escapeHtml(blog.content)}</p>
                    <small>Posted on: ${blog.created_at}</small>
                    <hr>
                </div>
            `;
            blogsContainer.insertAdjacentHTML('afterbegin', blogHtml);
        }

        function updateBlogPost(blog) {
            const existingPost = document.querySelector(`.blog-post[data-blog-id="${blog.id}"]`);
            if (existingPost) {
                existingPost.innerHTML = `
                    <h3>${escapeHtml(blog.title)}</h3>
                    <p>${escapeHtml(blog.content)}</p>
                    <small>Posted on: ${blog.created_at}</small>
                    <hr>
                `;
            }
        }

        function removeBlogPost(blogId) {
            const post = document.querySelector(`.blog-post[data-blog-id="${blogId}"]`);
            if (post) {
                post.remove();
            }
        }

        function escapeHtml(unsafe) {
            return unsafe
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // Start WebSocket connection
        connectWebSocket();
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

    <div class="container mt-4">
        <h1>Latest Blogs</h1>
        <div id="blogs">
            <?php
            require_once __DIR__ . '/../../models/BlogModel.php';

            // Create database connection
            $database = new Database();
            $pdo = $database->getConnection();

            // Initialize BlogModel with database connection
            $blogModel = new BlogModel($pdo);
            $blogs = $blogModel->getAllBlogs();

            // Display blogs
            foreach ($blogs as $blog): ?>
                <div class="blog-post" data-blog-id="<?php echo $blog['id']; ?>">
                    <h3><?php echo htmlspecialchars($blog['title']); ?></h3>
                    <p><?php echo htmlspecialchars($blog['content']); ?></p>
                    <small>Posted on: <?php echo htmlspecialchars($blog['created_at']); ?></small>
                    <hr>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>