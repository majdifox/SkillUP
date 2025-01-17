<?php
require_once 'interfaceCRUD.php';
require_once 'displayInterface.php';

abstract class Course implements CrudInterface, DisplayableInterface {
    protected $db;
    protected $course_id;
    protected $title;
    protected $description;
    protected $instructor_username;
    protected $category_id;

    public function __construct($db) {
        $this->db = $db;
    }

    public function create($data) {
        $query = "INSERT INTO courses (title, description, instructor_username, category_id) VALUES (:title, :description, :instructor_username, :category_id)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':instructor_username', $data['instructor_username']);
        $stmt->bindParam(':category_id', $data['category_id']);
        return $stmt->execute();
    }
    public function read($course_id) {
        $query = "SELECT * FROM courses WHERE course_id = :course_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':course_id', $course_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function update($course_id, $data) {
        $query = "UPDATE courses SET title = :title, description = :description, category_id = :category_id WHERE course_id = :course_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':category_id', $data['category_id']);
        $stmt->bindParam(':course_id', $course_id);
        return $stmt->execute();
    }
    public function delete($course_id) {
        $query = "DELETE FROM courses WHERE course_id = :course_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':course_id', $course_id);
        return $stmt->execute();
    }

    
    public function getAll($page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        $query = "SELECT * FROM courses LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
?>