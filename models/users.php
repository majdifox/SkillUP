<?php
class users {
    private $conn;
    private $table_name = "users";

    public $user_id;
    public $user_serial;
    public $first_name;
    public $last_name;
    public $age;
    public $password;
    public $role;
    public $email;
    public $status;
    public $error_message;

    public function __construct($db) {
        $this->conn = $db;
        $this->error_message = '';
    }

    



}

?>