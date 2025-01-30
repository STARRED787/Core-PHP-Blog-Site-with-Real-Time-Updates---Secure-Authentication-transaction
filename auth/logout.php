<?php
session_start();

// Clear the token cookie
setcookie('token', '', time() - 3600, '/');

// Clear session
session_destroy();

// Redirect to login page
header('Location: /KD Enterprise/blog-site/public/index.php');
exit(); 