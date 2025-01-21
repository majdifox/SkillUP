<?php

require_once 'interfaceCRUD.php';

class tags implements CrudInterface {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function create($data) {
        $query = "INSERT INTO tags (tag_name) VALUES (:tag_name)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':tag_name', $data['tag_name']);
        return $stmt->execute();
    }

    public function read($tag_id) {
        $query = "SELECT * FROM tags WHERE tag_id = :tag_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':tag_id', $tag_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($tag_id, $data) {
        $query = "UPDATE tags SET tag_name = :tag_name WHERE tag_id = :tag_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':tag_name', $data['tag_name']);
        $stmt->bindParam(':tag_id', $tag_id);
        return $stmt->execute();
    }

    public function delete($tag_id) {
        $query = "DELETE FROM tags WHERE tag_id = :tag_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':tag_id', $tag_id);
        return $stmt->execute();
    }

    public function getAll() {
        $query = "SELECT * FROM tags";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}











?>