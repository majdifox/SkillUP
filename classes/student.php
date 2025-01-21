<?php

class Student extends Users {
    public function __construct($db, $userData = null) {
        parent::__construct($db, $userData);
        $this->role = 'student';
    }
// getting here enrolled courses
    public function getData() {
        $query = "SELECT c.*, e.enrolled_at, u.username as instructor_name, cat.name as category_name, GROUP_CONCAT(t.name) as tags FROM courses c 
                  LEFT JOIN categories cat ON c.category_id = cat.category_id 
                  JOIN enrollments e ON c.course_id = e.course_id 
                  LEFT JOIN users u ON c.teacher_id = u.user_id
                  LEFT JOIN course_tags ct ON c.course_id = ct.course_id
                  LEFT JOIN tags t ON ct.tag_id = t.tag_id
                  WHERE e.student_id = :student_id
                  GROUP BY c.course_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':student_id', $this->id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function enrollCourse($courseId) {
        $query = "INSERT INTO enroll (student_username, course_title) VALUES (:student_username, :course_title)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':student_username', $this->id);
        $stmt->bindParam(':course_title', $courseId);
        return $stmt->execute();
    }





    }




?>