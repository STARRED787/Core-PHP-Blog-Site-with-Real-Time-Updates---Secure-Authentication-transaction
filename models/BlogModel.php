<?php
class BlogModel
{
    private $db;

    public function __construct($database)
    {
        $this->db = $database;
    }

    public function createBlog($title, $content)
    {
        $stmt = $this->db->prepare("INSERT INTO blogs (title, content, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$title, $content]);
        return $this->db->lastInsertId();
    }

    public function getAllBlogs()
    {
        $stmt = $this->db->query("SELECT * FROM blogs ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
