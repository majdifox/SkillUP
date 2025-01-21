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

    public function addCourse($courseData){

        $query = "INSERT INTO course (title, description, instructor_username, category) 
        VALUES (:title, :description, :instructor_username, :category)";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':title', $courseData['title']);
        $stmt->bindParam(':description', $courseData['description']);
        $stmt->bindParam(':instructor_username', $this->id);
        $stmt->bindParam(':category', $courseData['category']);
        return $stmt->execute();



    }

}





?>