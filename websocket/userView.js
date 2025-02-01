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
        blogContainer.innerHTML = `
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <h4 class="alert-heading">No Blog Posts Yet!</h4>
                    <p class="mb-0">Check back later for new content.</p>
                </div>
            </div>`;
        return;
    }

    blogContainer.innerHTML = posts.map(post => `
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="card-title h5">${escapeHtml(post.title)}</h2>
                    <p class="card-text">${escapeHtml(post.content)}</p>
                    <div class="text-muted small">Posted on: ${new Date(post.created_at).toLocaleString()}</div>
                </div>
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
