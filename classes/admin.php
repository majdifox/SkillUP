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

    public function validateInstructor($instructorId) {
        $sql = "UPDATE users SET is_active = TRUE WHERE id = :id AND role = 'teacher'";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $teacherId);
        return $stmt->execute();
    }
}

?>