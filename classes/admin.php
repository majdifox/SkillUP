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
}

?>