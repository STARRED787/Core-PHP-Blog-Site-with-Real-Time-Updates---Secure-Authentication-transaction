<?php
require_once '../middleware/auth.php';

// Define allowed roles for this page
checkAccess(['admin', 'user']); // Adjust roles as needed

// ... rest of your page code ... 