<?php

class statistics {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getTotalCourses() {
        $sql = "SELECT COUNT(*) as total FROM courses";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function getCoursesByCategory() {
        $sql = "SELECT categories.name_categorie, COUNT(courses.id) as count 
                FROM categories 
                LEFT JOIN courses ON categories.id_categorie = courses.category_id 
                GROUP BY categories.id_categorie";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMostPopularCourse() {
        $sql = "SELECT courses.*, COUNT(enrollments.id) as student_count 
                FROM courses 
                LEFT JOIN enrollments ON courses.id = enrollments.course_id 
                GROUP BY courses.id 
                ORDER BY student_count DESC 
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getTopTeachers() {
        $sql = "SELECT users.*, COUNT(courses.id) as course_count 
                FROM users 
                JOIN courses ON users.id_user = courses.teacher_id 
                WHERE users.role = 'teacher' 
                GROUP BY users.id_user
                ORDER BY course_count DESC 
                LIMIT 3";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

