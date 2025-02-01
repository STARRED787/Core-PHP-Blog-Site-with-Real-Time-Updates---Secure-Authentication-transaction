// WebSocket Connection
const ws = new WebSocket('ws://localhost:8080');

// WebSocket event handlers
ws.onopen = function() {
    console.log('Connected to WebSocket server');
    // Request initial posts when connection opens
    ws.send(JSON.stringify({ type: 'request_posts' }));
};

ws.onmessage = function(event) {
    console.log('Received message:', event.data); // Debug log
    const data = JSON.parse(event.data);
    if (data.type === 'update_posts') {
        updateBlogTable(data.posts);
    }
};

ws.onerror = function(error) {
    console.error('WebSocket Error:', error);
};

ws.onclose = function() {
    console.log('WebSocket Connection Closed');
    // Optional: Implement reconnection logic here
};

// Function to update the blog table
function updateBlogTable(posts) {
    console.log('Updating table with posts:', posts);
    const blogTable = document.querySelector('.table');
    const tableContainer = document.querySelector('.table-responsive');
    const noPostsMessage = document.getElementById('noPostsMessage') || document.createElement('div');
    noPostsMessage.id = 'noPostsMessage';
    
    if (!posts || posts.length === 0) {
        blogTable.style.display = 'none';
        noPostsMessage.className = 'alert alert-info text-center';
        noPostsMessage.innerHTML = `
            <h4 class="alert-heading">No Blog Posts Yet!</h4>
            <p class="mb-0">Click the "Create New Post" button to add your first blog post.</p>
        `;
        tableContainer.appendChild(noPostsMessage);
        return;
    }

    // Remove no posts message if it exists
    if (document.getElementById('noPostsMessage')) {
        document.getElementById('noPostsMessage').remove();
    }

    blogTable.style.display = 'table';
    const tbody = document.getElementById('blogTable');
    
    tbody.innerHTML = posts.map(post => `
        <tr>
            <td>${post.id}</td>
            <td>${escapeHtml(post.title)}</td>
            <td>${escapeHtml(post.content.substring(0, 100))}${post.content.length > 100 ? '...' : ''}</td>
            <td>${new Date(post.created_at).toLocaleString()}</td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="editPost(${post.id}, '${escapeHtml(post.title)}', '${escapeHtml(post.content)}')">Edit</button>
                <button class="btn btn-sm btn-danger" onclick="deletePost(${post.id})">Delete</button>
            </td>
        </tr>
    `).join('');
}

// Helper function to escape HTML
function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Function to notify server about new post
function notifyNewPost() {
    ws.send(JSON.stringify({ type: 'new_post' }));
}

// Edit functionality
function editPost(id, title, content) {
    document.getElementById('editPostId').value = id;
    document.getElementById('editPostTitle').value = title;
    document.getElementById('editPostContent').value = content;
    new bootstrap.Modal(document.getElementById('editPostModal')).show();
}

// Handle edit form submission
document.getElementById('editBlogForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const id = document.getElementById('editPostId').value;
    const title = document.getElementById('editPostTitle').value;
    const content = document.getElementById('editPostContent').value;

    try {
        const response = await fetch('../handler/blog_update_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                id: id,
                title: title,
                content: content
            })
        });

        const data = await response.json();
        showAlert(data.message, data.success ? 'success' : 'danger');
        
        if (data.success) {
            // Close modal
            document.getElementById('editPostModal').querySelector('.btn-close').click();
            // Broadcast update to all clients
            ws.send(JSON.stringify({ type: 'new_post' })); // Using new_post type as it's working
        }
    } catch (error) {
        showAlert('Error: ' + error, 'danger');
    }
});

// Delete functionality
function deletePost(id) {
    document.getElementById('deletePostId').value = id;
    new bootstrap.Modal(document.getElementById('deletePostModal')).show();
}

function confirmDelete() {
    const id = document.getElementById('deletePostId').value;
    
    fetch('../handler/blog_delete_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id: id })
    })
    .then(response => response.json())
    .then(data => {
        showAlert(data.message, data.success ? 'success' : 'danger');
        if (data.success) {
            // Close modal
            document.getElementById('deletePostModal').querySelector('.btn-close').click();
            // Broadcast delete to all clients
            ws.send(JSON.stringify({ type: 'new_post' })); // Using new_post type as it's working
        }
    })
    .catch(error => showAlert('Error: ' + error, 'danger'));
} 