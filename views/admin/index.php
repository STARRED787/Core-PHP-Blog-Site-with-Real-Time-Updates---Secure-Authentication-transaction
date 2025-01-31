<?php
session_start();
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/JWT.php';
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../models/BlogModel.php';

// Create database connection
$database = new Database();
$pdo = $database->getConnection();

// Initialize and check admin authentication
$authMiddleware = new AuthMiddleware($pdo);
$authMiddleware->redirectIfNotAdmin();

// Get user data
$user = $authMiddleware->getUser();

// Get blog model
$blogModel = new BlogModel($pdo);
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
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="blogTableBody">
                <?php
                $blogs = $blogModel->getAllBlogs();
                foreach ($blogs as $blog): ?>
                    <tr data-blog-id="<?php echo $blog['id']; ?>">
                        <td><?php echo htmlspecialchars($blog['id']); ?></td>
                        <td><?php echo htmlspecialchars($blog['title']); ?></td>
                        <td><?php echo htmlspecialchars($blog['content']); ?></td>
                        <td><?php echo htmlspecialchars($blog['created_at']); ?></td>
                        <td>
                            <button class="btn btn-sm btn-primary edit-blog" data-bs-toggle="modal" data-bs-target="#editBlogModal" 
                                    data-blog-id="<?php echo $blog['id']; ?>"
                                    data-blog-title="<?php echo htmlspecialchars($blog['title']); ?>"
                                    data-blog-content="<?php echo htmlspecialchars($blog['content']); ?>">
                                Edit
                            </button>
                            <button class="btn btn-sm btn-danger delete-blog" 
                                    data-blog-id="<?php echo $blog['id']; ?>">
                                Delete
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
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
                        <form id="addBlogForm">
                            <div class="mb-3">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            <div class="mb-3">
                                <label for="content" class="form-label">Content</label>
                                <textarea class="form-control" id="content" name="content" rows="3" required></textarea>
                            </div>
                            <input type="hidden" name="action" value="create">
                            <button type="submit" class="btn btn-primary">Add Blog</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Blog Modal -->
        <div class="modal fade" id="editBlogModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Blog</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editBlogForm">
                            <input type="hidden" id="editBlogId" name="id">
                            <input type="hidden" name="action" value="update">
                            <div class="mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" class="form-control" id="editBlogTitle" name="title" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Content</label>
                                <textarea class="form-control" id="editBlogContent" name="content" rows="3" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Update Blog</button>
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
                            addBlogToTable(data.blog);
                            break;
                        case 'update':
                            updateBlogInTable(data.blog);
                            break;
                        case 'delete':
                            removeBlogFromTable(data.blogId);
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
        
        // Helper functions for table updates
        function addBlogToTable(blog) {
            const tbody = document.getElementById('blogTableBody');
            const newRow = createBlogRow(blog);
            tbody.insertAdjacentHTML('afterbegin', newRow);
            attachEventListeners();
        }
        
        function updateBlogInTable(blog) {
            const existingRow = document.querySelector(`tr[data-blog-id="${blog.id}"]`);
            if (existingRow) {
                existingRow.outerHTML = createBlogRow(blog);
                attachEventListeners();
            }
        }
        
        function removeBlogFromTable(blogId) {
            const row = document.querySelector(`tr[data-blog-id="${blogId}"]`);
            if (row) {
                row.remove();
            }
        }
        
        function createBlogRow(blog) {
            return `
                <tr data-blog-id="${blog.id}">
                    <td>${blog.id}</td>
                    <td>${escapeHtml(blog.title)}</td>
                    <td>${escapeHtml(blog.content)}</td>
                    <td>${blog.created_at}</td>
                    <td>
                        <button class="btn btn-sm btn-primary edit-blog" 
                                data-bs-toggle="modal" 
                                data-bs-target="#editBlogModal"
                                data-blog-id="${blog.id}"
                                data-blog-title="${escapeHtml(blog.title)}"
                                data-blog-content="${escapeHtml(blog.content)}">
                            Edit
                        </button>
                        <button class="btn btn-sm btn-danger delete-blog" 
                                data-blog-id="${blog.id}">
                            Delete
                        </button>
                    </td>
                </tr>
            `;
        }

        function attachEventListeners() {
            // Reattach edit button listeners
            document.querySelectorAll('.edit-blog').forEach(button => {
                button.addEventListener('click', function() {
                    const blogId = this.dataset.blogId;
                    const blogTitle = this.dataset.blogTitle;
                    const blogContent = this.dataset.blogContent;
                    
                    document.getElementById('editBlogId').value = blogId;
                    document.getElementById('editBlogTitle').value = blogTitle;
                    document.getElementById('editBlogContent').value = blogContent;
                });
            });

            // Reattach delete button listeners
            document.querySelectorAll('.delete-blog').forEach(button => {
                button.addEventListener('click', handleDeleteBlog);
            });
        }

        function handleDeleteBlog() {
            if (!confirm('Are you sure you want to delete this blog?')) return;
            
            const blogId = this.dataset.blogId;
            deleteBlog(blogId);
        }

        async function deleteBlog(blogId) {
            try {
                const response = await fetch('/KD Enterprise/blog-site/routes/blog.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'delete',
                        id: blogId
                    })
                });
                
                if (!response.ok) throw new Error('Failed to delete blog');
                
                const result = await response.json();
                if (result.success) {
                    document.querySelector(`tr[data-blog-id="${blogId}"]`).remove();
                    
                    // Notify other clients through WebSocket
                    if (ws && ws.readyState === WebSocket.OPEN) {
                        ws.send(JSON.stringify({
                            type: 'delete',
                            blogId: blogId
                        }));
                    }
                }
            } catch (error) {
                alert('Error: ' + error.message);
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

        // Handle blog form submission
        document.getElementById('addBlogForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch('/KD Enterprise/blog-site/routes/blog.php', {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.error || 'Failed to create blog');
                }
                
                const result = await response.json();
                if (result.success) {
                    // Send update through WebSocket
                    if (ws && ws.readyState === WebSocket.OPEN) {
                        ws.send(JSON.stringify({
                            type: 'create',
                            blog: result.blog
                        }));
                    }
                    
                    // Add to local table
                    addBlogToTable(result.blog);
                    
                    // Reset form and close modal
                    this.reset();
                    bootstrap.Modal.getInstance(document.getElementById('addBlogModal')).hide();
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        });
    </script>
</body>

</html>