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




}

?>