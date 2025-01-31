<?php
class BlogModel
{
    private $pdo;
    private $table = 'blogs';

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function createBlog($title, $content)
    {
        $query = "INSERT INTO " . $this->table . " (title, content, created_at) VALUES (:title, :content, NOW())";
        $stmt = $this->pdo->prepare($query);
        
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':content', $content);
        
        if ($stmt->execute()) {
            $id = $this->pdo->lastInsertId();
            return $this->getBlogById($id);
        }
        return false;
    }

    public function updateBlog($id, $title, $content)
    {
        $query = "UPDATE " . $this->table . " SET title = :title, content = :content WHERE id = :id";
        $stmt = $this->pdo->prepare($query);
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':content', $content);
        
        if ($stmt->execute()) {
            return $this->getBlogById($id);
        }
        return false;
    }

    public function deleteBlog($id)
    {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function getAllBlogs()
    {
        $query = "SELECT * FROM " . $this->table . " ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBlogById($id)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
