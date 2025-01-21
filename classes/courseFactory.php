<?php
require_once 'videoCourse.php';
require_once 'documentCourse.php';

class CourseFactory {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function createCourse($type, $courseData) {
        switch ($type) {
            case 'video':
                return new videoCourse($this->db, $courseData);
            case 'document':
                return new documentCourse($this->db, $courseData);
            default:
                throw new Exception("Invalid course type");
        }
    }

    public function getCourse($id) {
        $sql = "SELECT * FROM courses WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $courseData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($courseData) {
            $type = $courseData['type']; 
            return $this->createCourse($type, $courseData);
        }
        return null;
    }

    public function getAllCourses($page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT * FROM courses LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $coursesData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $courses = [];
        foreach ($coursesData as $courseData) {
            $courses[] = $this->createCourse($courseData['type'], $courseData);
        }
        return $courses;
    }

    public function searchCourses($keyword) {
        $sql = "SELECT * FROM courses WHERE title LIKE :keyword OR description LIKE :keyword";
        $stmt = $this->db->prepare($sql);
        $keyword = "%$keyword%";
        $stmt->bindParam(':keyword', $keyword);
        $stmt->execute();
        $coursesData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $courses = [];
        foreach ($coursesData as $courseData) {
            $courses[] = $this->createCourse($courseData['type'], $courseData);
        }
        return $courses;
    }
}

