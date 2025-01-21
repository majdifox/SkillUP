<?php

require_once "users.php";

class admin extends users{

    public function __construct($db, $userData = null) {
        parent::__construct($db);
        $this->role = 'admin';
        if ($userData) {
            $this->id = $userData['id'];
            $this->username = $userData['username'];
            $this->email = $userData['email'];
        }
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