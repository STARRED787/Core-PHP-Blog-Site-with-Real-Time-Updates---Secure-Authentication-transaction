<?php

require_once __DIR__ . '/../config/database.php';
use Illuminate\Database\Capsule\Manager as DB;

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

try {
    $result = DB::table('blogs')
        ->where('id', $data['id'])
        ->delete();

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Blog post deleted successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Blog post not found.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error deleting blog post: ' . $e->getMessage()]);
} 