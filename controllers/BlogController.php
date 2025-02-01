<?php

require_once __DIR__ . '/../config/database.php';
use Illuminate\Database\Capsule\Manager as DB;

class BlogController {
    public function createBlog($title, $content) {
        try {
            // Using Query Builder
            $result = DB::table('blogs')->insert([
                'title' => $title,
                'content' => $content
            ]);

            if ($result) {
                // Return JSON response
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Blog post created successfully!']);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Error creating blog post.']);
            }
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
}
