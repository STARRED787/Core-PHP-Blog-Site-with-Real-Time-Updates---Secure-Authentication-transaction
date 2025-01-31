<?php
ini_set('session.use_cookies', '0');
ini_set('session.use_only_cookies', '0');
ini_set('session.use_trans_sid', '0');
ini_set('session.cache_limiter', null);

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/JWT.php';
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../models/BlogModel.php';
require_once __DIR__ . '/../../models/User.php';

// Create database connection
$database = new Database();
$pdo = $database->getConnection();

// Initialize User model
$userModel = new User($pdo);

// Initialize AuthMiddleware
$authMiddleware = new AuthMiddleware($pdo, $userModel);

// Verify admin status
if (!$authMiddleware->isAdmin()) {
    header('Location: /KD Enterprise/blog-site/public/index.php?error=' . urlencode('Admin access required'));
    exit();
}

// Get user data
$user = $authMiddleware->getUser();
if (!$user) {
    header('Location: /KD Enterprise/blog-site/public/index.php');
    exit();
}

// Initialize BlogModel
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
        <?php 
        $blogs = $blogModel->getAllBlogs();
        if (empty($blogs)): ?>
            <div class="alert alert-info">No blogs available. Click "Add New Blog" to create one.</div>
        <?php else: ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Content</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="blogTableBody">
                    <?php foreach ($blogs as $blog): ?>
                        <tr data-blog-id="<?php echo $blog['id']; ?>">
                            <td><?php echo htmlspecialchars($blog['id']); ?></td>
                            <td><?php echo htmlspecialchars($blog['title']); ?></td>
                            <td><?php echo htmlspecialchars($blog['content']); ?></td>
                            <td>
                                <select class="form-select blog-status" data-blog-id="<?php echo $blog['id']; ?>">
                                    <option value="draft" <?php echo $blog['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                    <option value="published" <?php echo $blog['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
                                </select>
                            </td>
                            <td><?php echo $blog['created_at']; ?></td>
                            <td>
                                <button class="btn btn-sm btn-primary edit-blog" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editBlogModal"
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
        <?php endif; ?>

        <!-- Add Blog Modal -->
        <div class="modal fade" id="addBlogModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Blog</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addBlogForm">
                            <input type="hidden" name="action" value="create">
                            <div class="mb-3">
                                <label for="blogTitle" class="form-label">Title</label>
                                <input type="text" class="form-control" id="blogTitle" name="title" required minlength="1">
                            </div>
                            <div class="mb-3">
                                <label for="blogContent" class="form-label">Content</label>
                                <textarea class="form-control" id="blogContent" name="content" rows="3" required minlength="1"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Create Blog</button>
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
                                <label for="editBlogTitle" class="form-label">Title</label>
                                <input type="text" class="form-control" id="editBlogTitle" name="title" required minlength="1">
                            </div>
                            <div class="mb-3">
                                <label for="editBlogContent" class="form-label">Content</label>
                                <textarea class="form-control" id="editBlogContent" name="content" rows="3" required minlength="1"></textarea>
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
                console.log('WebSocket disconnected');
                if (reconnectAttempts < maxReconnectAttempts) {
                    reconnectAttempts++;
                    setTimeout(connectWebSocket, 5000);
                }
            };
        }

        // Table update functions
        function addBlogToTable(blog) {
            const container = document.querySelector('.container.mt-4');
            const alertDiv = container.querySelector('.alert');
            
            // If this is the first blog, replace the alert with a table
            if (alertDiv) {
                const tableHTML = `
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Content</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="blogTableBody"></tbody>
                    </table>
                `;
                alertDiv.outerHTML = tableHTML;
            }

            const tbody = document.getElementById('blogTableBody');
            const row = createBlogRow(blog);
            tbody.insertAdjacentHTML('afterbegin', row);
            attachEventListeners();
        }

        function updateBlogInTable(blog) {
            const row = document.querySelector(`tr[data-blog-id="${blog.id}"]`);
            if (row) {
                row.outerHTML = createBlogRow(blog);
                attachEventListeners();
            }
        }

        function removeBlogFromTable(blogId) {
            const tbody = document.getElementById('blogTableBody');
            const row = document.querySelector(`tr[data-blog-id="${blogId}"]`);
            if (row) {
                row.remove();
                
                // If no more blogs, show the alert
                if (tbody.children.length === 0) {
                    const table = tbody.closest('table');
                    const alertHTML = '<div class="alert alert-info">No blogs available. Click "Add New Blog" to create one.</div>';
                    table.outerHTML = alertHTML;
                }
            }
        }

        function createBlogRow(blog) {
            return `
                <tr data-blog-id="${blog.id}">
                    <td>${blog.id}</td>
                    <td>${escapeHtml(blog.title)}</td>
                    <td>${escapeHtml(blog.content)}</td>
                    <td>
                        <select class="form-select blog-status" data-blog-id="${blog.id}">
                            <option value="draft" ${blog.status === 'draft' ? 'selected' : ''}>Draft</option>
                            <option value="published" ${blog.status === 'published' ? 'selected' : ''}>Published</option>
                        </select>
                    </td>
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

        function handleDelete(blogId) {
            return async function() {
                if (!confirm('Are you sure you want to delete this blog?')) {
                    return;
                }
                
                try {
                    const response = await fetch('/KD Enterprise/blog-site/routes/blog.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            action: 'delete',
                            id: blogId
                        })
                    });
                    
                    const result = await response.json();
                    
                    if (!response.ok) {
                        throw new Error(result.error || 'Failed to delete blog');
                    }
                    
                    if (result.success) {
                        // Send update through WebSocket
                        if (ws && ws.readyState === WebSocket.OPEN) {
                            ws.send(JSON.stringify({
                                type: 'delete',
                                blogId: blogId
                            }));
                        }
                        
                        // Remove from local table
                        removeBlogFromTable(blogId);
                    }
                } catch (error) {
                    alert('Error: ' + error.message);
                }
            };
        }

        function attachEventListeners() {
            // Remove existing event listeners
            document.querySelectorAll('.edit-blog').forEach(button => {
                const newButton = button.cloneNode(true);
                button.parentNode.replaceChild(newButton, button);
            });

            document.querySelectorAll('.delete-blog').forEach(button => {
                const newButton = button.cloneNode(true);
                button.parentNode.replaceChild(newButton, button);
            });

            // Add new event listeners
            document.querySelectorAll('.edit-blog').forEach(button => {
                button.addEventListener('click', function() {
                    document.getElementById('editBlogId').value = this.dataset.blogId;
                    document.getElementById('editBlogTitle').value = this.dataset.blogTitle;
                    document.getElementById('editBlogContent').value = this.dataset.blogContent;
                });
            });

            document.querySelectorAll('.delete-blog').forEach(button => {
                button.addEventListener('click', handleDelete(button.dataset.blogId));
            });
        }

        // Form submit handlers
        document.getElementById('editBlogForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const blogData = {
                action: 'update',
                id: document.getElementById('editBlogId').value,
                title: document.getElementById('editBlogTitle').value,
                content: document.getElementById('editBlogContent').value
            };
            
            try {
                const response = await fetch('/KD Enterprise/blog-site/routes/blog.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(blogData)
                });
                
                const result = await response.json();
                
                if (!response.ok) {
                    throw new Error(result.error || 'Failed to update blog');
                }
                
                if (result.success) {
                    if (ws && ws.readyState === WebSocket.OPEN) {
                        ws.send(JSON.stringify({
                            type: 'update',
                            blog: result.blog
                        }));
                    }
                    
                    updateBlogInTable(result.blog);
                    
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editBlogModal'));
                    if (modal) {
                        modal.hide();
                    }
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        });

        document.getElementById('addBlogForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch('/KD Enterprise/blog-site/routes/blog.php', {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) {
                    const result = await response.json();
                    throw new Error(result.error || 'Failed to create blog');
                }
                
                const result = await response.json();
                if (result.success) {
                    if (ws && ws.readyState === WebSocket.OPEN) {
                        ws.send(JSON.stringify({
                            type: 'create',
                            blog: result.blog
                        }));
                    }
                    
                    addBlogToTable(result.blog);
                    
                    this.reset();
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addBlogModal'));
                    if (modal) {
                        modal.hide();
                    }
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        });

        function escapeHtml(unsafe) {
            return unsafe
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            connectWebSocket();
            attachEventListeners();
        });

        // Add this to your existing JavaScript
        document.querySelectorAll('.blog-status').forEach(select => {
            select.addEventListener('change', async function() {
                const blogId = this.dataset.blogId;
                const newStatus = this.value;
                
                try {
                    const response = await fetch('/KD Enterprise/blog-site/routes/blog.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            action: 'updateStatus',
                            id: blogId,
                            status: newStatus
                        })
                    });
                    
                    const result = await response.json();
                    
                    if (!response.ok) {
                        throw new Error(result.error || 'Failed to update blog status');
                    }
                    
                    if (result.success) {
                        if (ws && ws.readyState === WebSocket.OPEN) {
                            ws.send(JSON.stringify({
                                type: 'statusUpdate',
                                blog: result.blog
                            }));
                        }
                    }
                } catch (error) {
                    alert('Error: ' + error.message);
                    // Reset select to previous value
                    this.value = this.dataset.originalValue;
                }
            });
        });
    </script>
</body>

</html>