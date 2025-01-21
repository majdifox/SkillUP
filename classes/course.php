<?php
abstract class Course implements CrudInterface {
    protected $db;
    protected $course_id;
    protected $title;
    protected $description;
    protected $thumbnail_url;
    protected $difficulty_level;
    protected $duration_type;
    protected $duration_value;
    protected $content_type;
    protected $teacher_id;
    protected $category_id;
    protected $status;
    
    public function __construct($db) {
        $this->db = $db;
    }

    public function create($data) {
        $query = "INSERT INTO courses (title, description, thumbnail_url, difficulty_level, 
                 duration_type, duration_value, content_type, teacher_id, category_id, 
                 document_pages, video_length, status) 
                 VALUES (:title, :description, :thumbnail_url, :difficulty_level, 
                 :duration_type, :duration_value, :content_type, :teacher_id, :category_id, 
                 :document_pages, :video_length, 'in_progress')";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute($data);
    }

    public function read($course_id) {
        $query = "SELECT c.*, u.username as teacher_name, cat.name as category_name 
                 FROM courses c 
                 LEFT JOIN users u ON c.teacher_id = u.user_id 
                 LEFT JOIN categories cat ON c.category_id = cat.category_id 
                 WHERE c.course_id = :course_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':course_id', $course_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($course_id, $data) {
        $query = "UPDATE courses SET 
                 title = :title, 
                 description = :description,
                 thumbnail_url = :thumbnail_url,
                 difficulty_level = :difficulty_level,
                 duration_type = :duration_type,
                 duration_value = :duration_value,
                 category_id = :category_id,
                 status = :status 
                 WHERE course_id = :course_id";
        
        $stmt = $this->db->prepare($query);
        $data['course_id'] = $course_id;
        return $stmt->execute($data);
    }

    public function delete($id) {
        try {
            // Start transaction
            $this->db->beginTransaction();
            
            // Delete course tags first
            $query = "DELETE FROM course_tags WHERE course_id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            // Delete course
            $query = "DELETE FROM courses WHERE course_id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $result = $stmt->execute();
            
            // Commit transaction
            $this->db->commit();
            
            return $result;
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->db->rollBack();
            throw $e;
        }
    }

    
    public function getAll($page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        $query = "SELECT c.*, u.username as teacher_name, cat.name as category_name,
                 COUNT(e.enrollment_id) as enrolled_count 
                 FROM courses c 
                 LEFT JOIN users u ON c.teacher_id = u.user_id 
                 LEFT JOIN categories cat ON c.category_id = cat.category_id 
                 LEFT JOIN enrollments e ON c.course_id = e.course_id 
                 GROUP BY c.course_id 
                 LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getEnrollments() {
        $query = "SELECT u.*, e.enrolled_at 
                 FROM users u 
                 JOIN enrollments e ON u.user_id = e.student_id 
                 WHERE e.course_id = :course_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':course_id', $this->course_id);
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
    return $this->course_id;
}
}
?>