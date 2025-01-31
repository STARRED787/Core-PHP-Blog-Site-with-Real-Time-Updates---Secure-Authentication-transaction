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
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO blogs (title, content, status, created_at) 
                VALUES (?, ?, 'draft', NOW())
            ");
            $stmt->execute([$title, $content]);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error creating blog: " . $e->getMessage());
            return false;
        }
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
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM blogs ORDER BY created_at DESC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting all blogs: " . $e->getMessage());
            return [];
        }
    }

    public function getBlogById($id)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getPublishedBlogs()
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM blogs WHERE status = 'published' ORDER BY created_at DESC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting published blogs: " . $e->getMessage());
            return [];
        }
    }

    public function updateBlogStatus($id, $status)
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE blogs SET status = ? WHERE id = ?");
            return $stmt->execute([$status, $id]);
        } catch (PDOException $e) {
            error_log("Error updating blog status: " . $e->getMessage());
            return false;
        }
    }
}
