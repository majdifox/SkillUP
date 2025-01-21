<?php
require_once 'CrudInterface.php';

class Tags implements CrudInterface {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function create($data) {
        $query = "INSERT INTO tags (name) VALUES (:name)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':name', $data['name']);
        return $stmt->execute();
    }
    
    public function read($id) {
        $query = "SELECT * FROM tags WHERE tag_id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function update($id, $data) {
        $query = "UPDATE tags SET name = :name WHERE tag_id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
    
    public function delete($id) {
        $query = "DELETE FROM tags WHERE tag_id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
    
    public function getAll() {
        $query = "SELECT * FROM tags ORDER BY name ASC";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Additional useful method
    public function getTaggedCourses($tag_id) {
        $query = "SELECT c.* FROM courses c 
                 JOIN course_tags ct ON c.course_id = ct.course_id 
                 WHERE ct.tag_id = :tag_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':tag_id', $tag_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}