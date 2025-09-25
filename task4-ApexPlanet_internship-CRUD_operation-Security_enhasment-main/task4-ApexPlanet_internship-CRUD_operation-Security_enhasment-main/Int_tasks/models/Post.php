<?php
class Post {
    private $conn;
    private $table = "posts";

    public $id;
    public $title;
    public $content;
    public $user_id;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // CREATE - Add new post
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                 SET title=:title, content=:content, user_id=:user_id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":content", $this->content);
        $stmt->bindParam(":user_id", $this->user_id);
        
        return $stmt->execute();
    }

    // READ - Get all posts
    public function readAll() {
        $query = "SELECT p.*, u.username 
                 FROM " . $this->table . " p 
                 LEFT JOIN users u ON p.user_id = u.id 
                 ORDER BY p.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    // READ - Get single post
    public function readOne() {
        $query = "SELECT p.*, u.username 
                 FROM " . $this->table . " p 
                 LEFT JOIN users u ON p.user_id = u.id 
                 WHERE p.id = ? 
                 LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->title = $row['title'];
            $this->content = $row['content'];
            $this->user_id = $row['user_id'];
            $this->username = $row['username'];
            $this->created_at = $row['created_at'];
        }
        
        return $row;
    }

    // UPDATE - Update post
    public function update() {
        $query = "UPDATE " . $this->table . " 
                 SET title=:title, content=:content 
                 WHERE id=:id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":content", $this->content);
        $stmt->bindParam(":id", $this->id);
        
        return $stmt->execute();
    }

    // DELETE - Delete post
    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        
        return $stmt->execute();
    }

    // Check if user owns the post
    public function isOwner($user_id) {
        $query = "SELECT user_id FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['user_id'] == $user_id;
        }
        return false;
    }
}
?>