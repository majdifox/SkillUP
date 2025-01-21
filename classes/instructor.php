<?php

class Instructor extends Users {
    public function __construct($db, $userData = null) {
        parent::__construct($db, $userData);
        $this->role = 'instructor';
    }

    public function getData() {
        $query = "SELECT 
            c.*, 
            cat.name AS category_name,
            GROUP_CONCAT(t.name) as tags,
            COUNT(e.enrollment_id) AS student_count,
            CONCAT(c.duration_value, ' ', c.duration_type) as duration
        FROM courses c 
        LEFT JOIN categories cat ON c.category_id = cat.category_id 
        LEFT JOIN enrollments e ON c.course_id = e.course_id 
        LEFT JOIN course_tags ct ON c.course_id = ct.course_id
        LEFT JOIN tags t ON ct.tag_id = t.tag_id
        WHERE c.teacher_id = :teacher_id 
        GROUP BY c.course_id, cat.name";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':teacher_id', $this->id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addCourse($data) {
        $query = "INSERT INTO courses (
            title, description, thumbnail_url, difficulty_level, duration_type, duration_value, content_type, teacher_id, category_id, document_pages, video_length, video_url, document_url, status) 
            VALUES (:title, :description, :thumbnail_url, :difficulty_level, :duration_type, :duration_value, :content_type, :teacher_id, :category_id, :document_pages, :video_length, :video_url, :document_url, 'in_progress')";
    
        try {
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(':title', $data['title']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':thumbnail_url', $data['thumbnail_url']);
            $stmt->bindParam(':difficulty_level', $data['difficulty_level']);
            $stmt->bindParam(':duration_type', $data['duration_type']);
            $stmt->bindParam(':duration_value', $data['duration_value']);
            $stmt->bindParam(':content_type', $data['content_type']);
            $stmt->bindParam(':teacher_id', $data['teacher_id']);
            $stmt->bindParam(':category_id', $data['category_id']);
            
            // Handle nullable fields
            $document_pages = isset($data['document_pages']) ? $data['document_pages'] : null;
            $video_length = isset($data['video_length']) ? $data['video_length'] : null;
            $video_url = isset($data['video_url']) ? $data['video_url'] : null;
            $document_url = isset($data['document_url']) ? $data['document_url'] : null;
            
            $stmt->bindParam(':document_pages', $document_pages);
            $stmt->bindParam(':video_length', $video_length);
            $stmt->bindParam(':video_url', $video_url);
            $stmt->bindParam(':document_url', $document_url);
    
            if ($stmt->execute()) {
                return true;
            }
            return false;
        } catch (PDOException $e) {
            // Log error or handle it appropriately
            error_log("Error while adding the course: " . $e->getMessage());
            return false;
        }
    }

    public function deleteCourse($courseId) {
        try {
            $this->db->beginTransaction();
            
            error_log("Starting course deletion for course ID: " . $courseId);
            $this->db->beginTransaction();
            
            // Delete from course_tags
            $query = "DELETE FROM course_tags WHERE course_id = :course_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':course_id', $courseId);
            $success = $stmt->execute();
            error_log("Course tags deletion: " . ($success ? "success" : "failed"));
            
            // Delete from enrollments
            $query = "DELETE FROM enrollments WHERE course_id = :course_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':course_id', $courseId);
            $success = $stmt->execute();
            error_log("Enrollments deletion: " . ($success ? "success" : "failed"));
            
            // Delete the course
            $query = "DELETE FROM courses WHERE course_id = :course_id AND teacher_id = :teacher_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':course_id', $courseId);
            $stmt->bindParam(':teacher_id', $this->id);
            $success = $stmt->execute();
            error_log("Course deletion: " . ($success ? "success" : "failed"));
            
            $this->db->commit();
            error_log("Course deletion completed successfully");
            return true;
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error in deleteCourse method: " . $e->getMessage());
            return false;
        }
    }


}





?>