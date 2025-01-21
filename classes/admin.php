<?php

require_once "Users.php";

class Admin extends Users {

    public function __construct($db, $userData = null) {
        parent::__construct($db, $userData);
        $this->role = 'admin';
    }

    public function getData() {
        $sql = "SELECT 
                (SELECT COUNT(*) FROM courses),
                (SELECT COUNT(*) FROM users WHERE role = 'student'),
                (SELECT COUNT(*) FROM users WHERE role = 'teacher')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function validateInstructor($instructorId) {
        $sql = "UPDATE users SET is_active = TRUE WHERE id = :id AND role = 'teacher'";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $teacherId);
        return $stmt->execute();
    }
}

?>