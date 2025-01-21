<?php
require_once 'Users.php';

class Admin extends Users {
   
    public function __construct($db, $userData = null) {
        parent::__construct($db, $userData);
        $this->role = 'admin';
    }
    public function getData() {
        $data = [
            'courses' => $this->getCourseStats(),
            'users' => $this->getUserStats(),
        ];
        return $data;
    }

    public function getCourseStats() {
        $query = "SELECT 
                    COUNT(*) as total_courses,
                    COUNT(CASE WHEN status = 'in_progress' THEN 1 END) as pending_courses,
                    COUNT(CASE WHEN status = 'accepted' THEN 1 END) as accepted_courses
                  FROM courses";
        $stmt = $this->db->query($query);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserStats() {
        $query = "SELECT 
                    COUNT(*) as total_users,
                    COUNT(CASE WHEN role = 'student' THEN 1 END) as student_count,
                    COUNT(CASE WHEN role = 'instructor' THEN 1 END) as instructor_count
                  FROM users
                  WHERE role != 'admin'";
        $stmt = $this->db->query($query);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateCourseStatus($courseId, $status) {
        $query = "UPDATE courses SET status = :status WHERE course_id = :course_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':course_id', $courseId);
        return $stmt->execute();
    }

    public function updateInstructorStatus($instructorId, $status) {
        $query = "UPDATE users SET status = :status WHERE user_id = :user_id AND role = 'instructor'";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':user_id', $instructorId);
        return $stmt->execute();
    }

    public function deleteCourse($courseId) {
        try {
            $this->db->beginTransaction();
            
            // First, delete related records from course_tags
            $query = "DELETE FROM course_tags WHERE course_id = :course_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':course_id', $courseId);
            $stmt->execute();
            
            // Then delete any related records from enrollments (if you have this table)
            $query = "DELETE FROM enrollments WHERE course_id = :course_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':course_id', $courseId);
            $stmt->execute();
            
            // Finally delete the course
            $query = "DELETE FROM courses WHERE course_id = :course_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':course_id', $courseId);
            $stmt->execute();
            
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}

?>  