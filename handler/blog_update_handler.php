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
        ->update([
            'title' => $data['title'],
            'content' => $data['content'],
            'updated_at' => date('Y-m-d H:i:s')
        ]);

    if ($result === false) {
        throw new Exception('Failed to update blog post');
    }

    // If we get here, commit the transaction
    DB::commit();

    echo json_encode(['success' => true, 'message' => 'Blog post updated successfully!']);
} catch (Exception $e) {
    // Something went wrong, rollback the transaction
    DB::rollBack();
    
    echo json_encode(['success' => false, 'message' => 'Error updating blog post: ' . $e->getMessage()]);
} 