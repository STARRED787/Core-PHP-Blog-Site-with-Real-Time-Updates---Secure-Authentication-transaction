<?php

require_once '../controllers/BlogController.php';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the title and content from the POST request
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';

    // Create an instance of BlogController
    $blogController = new BlogController();

    // Call the createBlog method
    $blogController->createBlog($title, $content);
}
