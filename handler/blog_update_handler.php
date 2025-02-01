<?php

require_once __DIR__ . '/../config/database.php';
use Illuminate\Database\Capsule\Manager as DB;

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

try {
    $result = DB::table('blogs')
        ->where('id', $data['id'])
        ->update([
            'title' => $data['title'],
            'content' => $data['content']
        ]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Blog post updated successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes made to the blog post.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error updating blog post: ' . $e->getMessage()]);
} 