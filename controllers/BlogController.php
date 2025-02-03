<?php

require_once __DIR__ . '/../config/database.php';
use Illuminate\Database\Capsule\Manager as DB;

class BlogController {
    public function createBlog($title, $content) {
        try {
            // Start transaction
            DB::beginTransaction();

            // Using Query Builder
            $result = DB::table('blogs')->insert([
                'title' => $title,
                'content' => $content,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            if (!$result) {
                throw new Exception('Failed to create blog post');
            }

            // If we get here, commit the transaction
            DB::commit();

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Blog post created successfully!']);
            
        } catch (Exception $e) {
            // Something went wrong, rollback the transaction
            DB::rollBack();
            
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
}
