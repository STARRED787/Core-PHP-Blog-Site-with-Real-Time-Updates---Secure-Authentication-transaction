// WebSocket Connection
const ws = new WebSocket('ws://localhost:8080');

// WebSocket event handlers
ws.onopen = function() {
    console.log('Connected to WebSocket server');
    // Request initial posts when connection opens
    ws.send(JSON.stringify({ type: 'request_posts' }));
};

ws.onmessage = function(event) {
    console.log('Received message:', event.data);
    const data = JSON.parse(event.data);
    if (data.type === 'update_posts') {
        updateBlogPosts(data.posts);
    }
};

ws.onerror = function(error) {
    console.error('WebSocket Error:', error);
};

// Function to update the blog posts display
function updateBlogPosts(posts) {
    const blogContainer = document.getElementById('blogPosts');
    
    if (!posts || posts.length === 0) {
        blogContainer.innerHTML = '<div class="alert alert-info">No blog posts available.</div>';
        return;
    }

    blogContainer.innerHTML = posts.map(post => `
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="card-title">${escapeHtml(post.title)}</h2>
                <p class="card-text">${escapeHtml(post.content)}</p>
                <div class="text-muted">Posted on: ${new Date(post.created_at).toLocaleString()}</div>
            </div>
        </div>
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
