<?php

require_once "Users.php";

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


}

?>