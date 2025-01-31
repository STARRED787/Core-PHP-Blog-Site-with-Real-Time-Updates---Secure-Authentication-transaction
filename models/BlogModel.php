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
            $this->pdo->beginTransaction();
            
            $stmt = $this->pdo->prepare("
                INSERT INTO blogs (title, content, created_at) 
                VALUES (?, ?, NOW())
            ");
            
            if (!$stmt->execute([$title, $content])) {
                throw new PDOException("Failed to execute blog creation");
            }
            
            $id = $this->pdo->lastInsertId();
            $blog = $this->getBlogById($id);
            
            if (!$blog) {
                throw new PDOException("Failed to retrieve created blog");
            }
            
            $this->pdo->commit();
            return $blog;
            
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Database error in createBlog: " . $e->getMessage());
            return false;
        }
    }

    public function updateBlog($id, $title, $content)
    {
        try {
            $this->pdo->beginTransaction();
            
            $stmt = $this->pdo->prepare("
                UPDATE {$this->table} 
                SET title = ?, content = ? 
                WHERE id = ?
            ");
            
            if (!$stmt->execute([$title, $content, $id])) {
                throw new PDOException("Failed to update blog");
            }
            
            $blog = $this->getBlogById($id);
            if (!$blog) {
                throw new PDOException("Failed to retrieve updated blog");
            }
            
            $this->pdo->commit();
            return $blog;
            
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Error updating blog: " . $e->getMessage());
            return false;
        }
    }

    public function deleteBlog($id)
    {
        try {
            $this->pdo->beginTransaction();
            
            $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = ?");
            if (!$stmt->execute([$id])) {
                throw new PDOException("Failed to delete blog");
            }
            
            $this->pdo->commit();
            return true;
            
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
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
