// Add this function at the start of your script
    function showAlert(message, type = 'success') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
        alertDiv.role = 'alert';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        document.body.appendChild(alertDiv);
        
        // Remove the alert after 3 seconds
        setTimeout(() => {
            alertDiv.remove();
        }, 3000);
    }

    document.getElementById('blogForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        fetch('../handler/blog_store_handler.php', {
            method: 'POST',
            body: new FormData(this)
        })
        .then(response => response.json())
        .then(data => {
            showAlert(data.message, data.success ? 'success' : 'danger');
            if (data.success) {
                // Notify WebSocket about new post
                notifyNewPost();
                // Close modal and reset form
                document.getElementById('createPostModal').querySelector('.btn-close').click();
                this.reset();
            }
        })
        .catch(error => showAlert('Error: ' + error, 'danger'));
    });