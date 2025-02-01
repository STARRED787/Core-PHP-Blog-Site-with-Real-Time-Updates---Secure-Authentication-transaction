<?php
/**
 * Admin Dashboard View
 * Provides blog management interface for administrators
 * Features:
 * - Create, edit, and delete blog posts
 * - Real-time updates via WebSocket
 * - Tabular view of all blog posts
 * - Modal forms for CRUD operations
 */

// Load authentication and blog model
require_once __DIR__ . '/../auth/admin_auth.php';
require_once __DIR__ . '/../models/Blog.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

    <!-- Bootstrap 4 or 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>

<body class="bg-light">
    <!-- Header section with navigation and welcome message -->
    <header>
        <!-- Navigation bar with logout option -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">Blog Management System</a>
                <!-- Responsive navigation toggle -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <!-- Right-aligned navigation items -->
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="../../blog-site/handler/logout_handler.php">Logout</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main content area -->
    <main class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="m-0">Blog Posts</h2>
            <!-- Create post button - triggers modal -->
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPostModal">
                Create New Post
            </button>
        </div>

        <!-- Table responsive wrapper -->
        <div class="table-responsive">
            <!-- Blog posts table - Initially hidden -->
            <table class="table table-bordered" style="display: none;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Content</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="blogTable">
                    <!-- Content will be loaded by WebSocket -->
                </tbody>
            </table>
        </div>
    </main>

    <!-- Modal for creating new blog posts -->
    <div class="modal fade" id="createPostModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Post</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Blog creation form -->
                    <form id="blogForm" method="POST" action="../handler/blog_store_handler.php">
                        <div class="mb-3">
                            <label for="postTitle" class="form-label">Title</label>
                            <input type="text" class="form-control" id="postTitle" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="postContent" class="form-label">Content</label>
                            <textarea class="form-control" id="postContent" name="content" rows="3" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Create Post</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for editing existing posts -->
    <div class="modal fade" id="editPostModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Post</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Blog edit form -->
                    <form id="editBlogForm">
                        <input type="hidden" id="editPostId" name="id">
                        <div class="mb-3">
                            <label for="editPostTitle" class="form-label">Title</label>
                            <input type="text" class="form-control" id="editPostTitle" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="editPostContent" class="form-label">Content</label>
                            <textarea class="form-control" id="editPostContent" name="content" rows="3" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Post</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for delete confirmation -->
    <div class="modal fade" id="deletePostModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Post</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this post?</p>
                    <input type="hidden" id="deletePostId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="confirmDelete()">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 4 or 5 JS, Popper.js, and jQuery (for Bootstrap 4) -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

  <script src="../helper/adminAlert.js"></script>
  <script src="../websocket/adminView.js"></script>

</body>

</html>