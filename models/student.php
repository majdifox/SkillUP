<?php

require_once 'users.php';

class student extends users {

    public function __construct($db, $userData) {
        parent::__construct($db);
        $this->role = 'student';
        if ($userData) {
            $this->id = $userData['user_id'];
            $this->username = $userData['username'];
            $this->email = $userData['email'];
        }
    }

    public function getData() {

        $query = "SELECT * FROM course JOIN enroll where enroll.student_username= :student_id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':student_id' , $this->id);
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