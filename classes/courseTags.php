<?php
require_once 'CRUDinterface.php';

class CourseTags implements CrudInterface {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function create($data) {
        $query = "INSERT INTO course_tags (course_id, tag_id) VALUES (:course_id, :tag_id)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':course_id', $data['course_id']);
        $stmt->bindParam(':tag_id', $data['tag_id']);
        return $stmt->execute();
    }

    public function read($id) {
        // For course_tags, we'll get all tags for a course
        $query = "SELECT t.* FROM tags t 
                 JOIN course_tags ct ON t.tag_id = ct.tag_id 
                 WHERE ct.course_id = :course_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':course_id', $id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($id, $data) {
        // In a many-to-many relationship, update means removing old tags and adding new ones
        $this->deleteAllTags($id);
        foreach ($data['tags'] as $tag_id) {
            $this->create(['course_id' => $id, 'tag_id' => $tag_id]);
        }
        return true;
    }

    public function delete($id) {
        // Delete a specific course-tag relationship
        $query = "DELETE FROM course_tags WHERE course_id = :course_id AND tag_id = :tag_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':course_id', $id);
        $stmt->bindParam(':tag_id', $id);
        return $stmt->execute();
    }

    public function getAll() {
        // Get all course-tag relationships
        $query = "SELECT c.title, t.name as tag_name 
                 FROM course_tags ct
                 JOIN courses c ON ct.course_id = c.course_id
                 JOIN tags t ON ct.tag_id = t.tag_id
                 ORDER BY c.title";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function deleteAllTags($course_id) {
        $query = "DELETE FROM course_tags WHERE course_id = :course_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':course_id', $course_id);
        return $stmt->execute();
    }
  
    public function getTagsByCourse($course_id) {
        $query = "SELECT t.* FROM tags t 
                 JOIN course_tags ct ON t.tag_id = ct.tag_id 
                 WHERE ct.course_id = :course_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':course_id', $course_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCoursesByTag($tag_id) {
        $query = "SELECT c.* FROM courses c 
                 JOIN course_tags ct ON c.course_id = ct.course_id 
                 WHERE ct.tag_id = :tag_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':tag_id', $tag_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

