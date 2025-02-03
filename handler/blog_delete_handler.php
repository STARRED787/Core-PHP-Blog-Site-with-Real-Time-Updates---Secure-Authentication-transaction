<?php

require_once __DIR__ . '/../config/database.php';
use Illuminate\Database\Capsule\Manager as DB;

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

try {
    // Start transaction
    DB::beginTransaction();

    $result = DB::table('blogs')
        ->where('id', $data['id'])
        ->delete();

    if ($result === false) {
        throw new Exception('Failed to delete blog post');
    }

    // If we get here, commit the transaction
    DB::commit();

    echo json_encode(['success' => true, 'message' => 'Blog post deleted successfully!']);
} catch (Exception $e) {
    // Something went wrong, rollback the transaction
    DB::rollBack();
    
    echo json_encode(['success' => false, 'message' => 'Error deleting blog post: ' . $e->getMessage()]);
} 