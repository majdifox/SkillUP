<?php

require_once 'users.php';

class instructor extends users{

    public function __construct($db, $userData = null){

        parent::__construct($db, $userData);
        $this->role = 'instructor';
        if ($userData) {
            $this->id = $userData['id_user'];
            $this->username = $userData['username'];
            $this->email = $userData['email'];
        }

    }

    public function getData() {

        $query = "SELECT * FROM course JOIN enroll where enroll.instructor_username= :instructor_id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':instructor_id' , $this->id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}





?>