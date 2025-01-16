<?php
require_once "userInterface.php";
require_once "interfaceCRUD.php";


abstract class users implements userInterface, interfaceCRUD  {
    
    protected $db;
    protected $id;
    protected $user_serial;
    protected $email;
    protected $role;



    public function __construct($db, $userData = null) {
        $this->db = $db;
        if (userData){
            $this->id = userData['id'];
            $this->user_serial = userData['user_serial'];
            $this->email = userData['email'];

        }
    }

    public function register() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (user_serial, first_name, last_name, age, email, password, role,  status) 
                  VALUES (:user_serial, :first_name, :last_name, :age, :email, :password, :role, :status)";

        $stmt = $this->conn->prepare($query);

        $this->user_serial = $this->sanitizeInput($this->user_serial);
        $this->first_name = $this->sanitizeInput($this->first_name);
        $this->last_name = $this->sanitizeInput($this->last_name);
        $this->age = $this->sanitizeInput($this->age);
        $this->email = $this->sanitizeInput($this->email);
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        $this->role = $this->sanitizeInput($this->role);
        $this->status = ($this->role === 'student') ? 'accept' : 'in progress';

        $stmt->bindParam(":user_serial", $this->user_serial);
        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":age", $this->age);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":status", $this->status);

        try {
            if($stmt->execute()) {
                return true;
            }
            $this->error_message = "Error while executing query.";
            error_log("Sign UP ERROR: " . $this->error_message);
            return false;
        } catch (PDOException $e) {
            $this->error_message = "PDO ERROR : " . $e->getMessage();
            error_log("Sign UP ERROR : " . $this->error_message);
            return false;
        }
    }




}

?>