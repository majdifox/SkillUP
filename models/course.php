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
        $query = "INSERT INTO course (title, description, instructor_username, category_id) VALUES (:title, :description, :instructor_username, :category_id)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':instructor_username', $data['instructor_username']);
        $stmt->bindParam(':category_id', $data['category_id']);
        return $stmt->execute();
    }
    public function read($course_id) {
        $query = "SELECT * FROM course WHERE course_id = :course_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':course_id', $course_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function update($course_id, $data) {
        $query = "UPDATE course SET title = :title, description = :description, category_id = :category_id WHERE course_id = :course_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':category_id', $data['category_id']);
        $stmt->bindParam(':course_id', $course_id);
        return $stmt->execute();
    }
    public function delete($course_id) {
        $query = "DELETE FROM course WHERE course_id = :course_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':course_id', $course_id);
        return $stmt->execute();
    }

    
    public function getAll($page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        $query = "SELECT * FROM course LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function search($keyword) {
        $quer = "SELECT * FROM course WHERE title LIKE :keyword OR description LIKE :keyword";
        $stmt = $this->db->prepare($query);
        $keyword = "%$keyword%";
        $stmt->bindParam(':keyword', $keyword);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
// this is an abstract method 
    abstract public function display();

    public function enrollement($student_username) {
        $query = "INSERT INTO enroll (student_username, course_id) VALUES (:student_username, :course_id)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':student_username', $student_username);
        $stmt->bindParam(':course_id', $this->id);
        return $stmt->execute();
    }
// getters

public function getEnrollments() {
    $query = "SELECT users.* FROM users JOIN enroll ON users.user_id = enroll.student_username WHERE enroll.course_id = :course_id";
    $stmt = $this->db->prepare($query);
    $stmt->bindParam(':course_id', $this->id);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
public function getTitle(){
    return $this->title;
}
public function getDescription(){
    return $this->description;
}
public function getId(){
    return $this->id;
}
}
?>