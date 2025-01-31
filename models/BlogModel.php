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
                INSERT INTO blogs (title, content, created_at) 
                VALUES (?, ?, NOW())
            ");
            
            error_log("Inserting - Title: " . $title . ", Content: " . $content);
            
            if ($stmt->execute([$title, $content])) {
                $id = $this->pdo->lastInsertId();
                error_log("Insert successful, new ID: " . $id);
                
                // Fetch and return the new blog
                $blog = $this->getBlogById($id);
                error_log("Retrieved blog: " . print_r($blog, true));
                return $blog;
            }
            
            $error = $stmt->errorInfo();
            error_log("Insert failed: " . print_r($error, true));
            return false;
            
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }

    public function updateBlog($id, $title, $content)
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE {$this->table} 
                SET title = ?, content = ? 
                WHERE id = ?
            ");
            
            if ($stmt->execute([$title, $content, $id])) {
                return $this->getBlogById($id);
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error updating blog: " . $e->getMessage());
            return false;
        }
    }

    public function deleteBlog($id)
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error deleting blog: " . $e->getMessage());
            return false;
        }
    }

    public function getAllBlogs()
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, title, content, created_at 
                FROM {$this->table} 
                ORDER BY created_at DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting all blogs: " . $e->getMessage());
            return [];
        }
    }

    public function getBlogById($id)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, title, content, created_at 
                FROM {$this->table} 
                WHERE id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getBlogById: " . $e->getMessage());
            return false;
        }
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
