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

    public function getData(){

        


    }


}





?>