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

    public function getAvailableCourses($search = '') {
        $query = "SELECT c.*, u.username as instructor_name, 
                  cat.name as category_name,
                  GROUP_CONCAT(t.name) as tags
                  FROM courses c 
                  LEFT JOIN categories cat ON c.category_id = cat.category_id
                  LEFT JOIN users u ON c.teacher_id = u.user_id
                  LEFT JOIN course_tags ct ON c.course_id = ct.course_id
                  LEFT JOIN tags t ON ct.tag_id = t.tag_id
                  WHERE c.status = 'accepted' 
                  AND c.course_id NOT IN (
                    SELECT course_id FROM enrollments WHERE student_id = :student_id
                  )";
        
        if (!empty($search)) {
            $query .= " AND (c.description LIKE :search 
                       OR c.title LIKE :search 
                       OR u.username LIKE :search 
                       OR t.name LIKE :search
                       OR cat.name LIKE :search)";
        }
        
        $query .= " GROUP BY c.course_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':student_id', $this->id);
        
        if (!empty($search)) {
            $searchTerm = "%$search%";
            $stmt->bindParam(':search', $searchTerm);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

     // Enroll in a course
     public function enrollCourse($courseId) {
        $query = "INSERT INTO enrollments (student_id, course_id) VALUES (:student_id, :course_id)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':student_id', $this->id);
        $stmt->bindParam(':course_id', $courseId);
        return $stmt->execute();
    }

    public function getCourseContent($courseId) {
        $query = "SELECT c.* FROM courses c
                  JOIN enrollments e ON c.course_id = e.course_id
                  WHERE e.student_id = :student_id AND c.course_id = :course_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':student_id', $this->id);
        $stmt->bindParam(':course_id', $courseId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    }




?>