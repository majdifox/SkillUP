<?php
require_once 'CrudInterface.php';

class Category implements CrudInterface {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function create($data) {
        $query = "INSERT INTO category (category_name) VALUES (:category_name)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':category_name', $data['category_name']);
        return $stmt->execute();
    }

    public function read($category_id) {
        $query = "SELECT * FROM category WHERE category_id = :category_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($category_id, $data) {
        $query = "UPDATE category SET category_name = :category_name WHERE category_id = :category_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':category_name', $data['category_name']);
        $stmt->bindParam(':category_id', $category_id);
        return $stmt->execute();
    }

    public function delete($category_id) {
        $query = "DELETE FROM category WHERE category_id = :category_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':category_id', $category_id);
        return $stmt->execute();
    }

    public function getAll() {
        $query = "SELECT * FROM category";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

