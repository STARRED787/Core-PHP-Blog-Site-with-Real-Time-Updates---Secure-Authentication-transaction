<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/database.php';
use Illuminate\Database\Capsule\Manager as DB;

/**
 * BlogController demonstrates database transaction handling:
 * 1. DB::beginTransaction() - Starts a new transaction
 * 2. DB::commit() - Saves all changes if successful
 * 3. DB::rollBack() - Reverts all changes if error occurs
 * 
 * Transaction ensures all database operations succeed or none do
 */
class BlogController {
    private $logger;

    public function __construct() {
        $this->logger = function($message) {
            error_log("[BlogController] " . $message);
        };
    }

    public function createBlog($title, $content) {
        try {
            // Start transaction - all following DB operations will be part of this transaction
            DB::beginTransaction();
            ($this->logger)("Transaction started - CreateBlog");

            // Test transaction rollback with validation
            if (strlen($title) < 3) {
                throw new Exception('Title too short!');
            }

            // This insert will be rolled back if any error occurs
            $result = DB::table('blogs')->insert([
                'title' => $title,
                'content' => $content,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            if (!$result) {
                throw new Exception('Failed to create blog post');
            }

            // Commit transaction - makes all changes permanent
            DB::commit();
            ($this->logger)("Transaction committed - Blog created successfully");

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Blog post created successfully!']);
            
        } catch (Exception $e) {
            // Rollback transaction - reverts all changes made in this transaction
            DB::rollBack();
            ($this->logger)("Transaction rolled back - Blog creation failed: " . $e->getMessage());
            
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
}
