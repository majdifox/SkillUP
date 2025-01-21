<?php
require_once 'CrudInterface.php';

class Category implements CrudInterface {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function create($data) {
        $query = "INSERT INTO categories (name) VALUES (:name)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':name', $data['name']);
        return $stmt->execute();
    }

    public function read($id) {
        $query = "SELECT * FROM categories WHERE category_id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, $data) {
        $query = "UPDATE categories SET name = :name WHERE category_id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function delete($id) {
        try {
            $this->db->beginTransaction();
            
            // First update all courses with this category to NULL
            $query = "UPDATE courses SET category_id = NULL WHERE category_id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            // Then delete the category
            $query = "DELETE FROM categories WHERE category_id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function getAll() {
        $query = "SELECT * FROM categories ORDER BY name ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCourseCount($category_id) {
        $query = "SELECT COUNT(*) as course_count FROM courses WHERE category_id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $category_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['course_count'];
    }
}

