<?php
require_once 'CrudInterface.php';
require_once 'UserInterface.php';


abstract class Users implements UserInterface, CrudInterface {
    protected $db;
    protected $id;
    protected $username;
    protected $email;
    protected $role;
    protected $is_active;
    protected $status;

    public function __construct($db, $userData = null) {
        $this->db = $db;
        if ($userData && isset($userData['user_id'])) {
            $this->id = $userData['user_id'];
            $this->username = $userData['username'] ?? null;
            $this->email = $userData['email'] ?? null;
            $this->is_active = $userData['is_active'] ?? 0;
            $this->status = $userData['status'] ?? 'pending';
    }
    }

    // I'm going to implement the CRUD here using the crud interface that I've created before 

    // create
    
    public function create($data){

        $query = "INSERT INTO users (username, email, password, role ) VALUES (:username , :email, :password, :role)";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':username', data['username']);
        $stmt->bindParam(':email', data['email']);
        $stmt->bindParam(':password', password_hash($data['password'], PASSWORD_BCRYPT)); 
        $stmt->bindParam(':role',data['role']);

        return $stmt->execute();
    }
    
    // display
    public function read($id){

        $query = "SELECT * FROM users WHERE user_id = :id";

        $stmt = $this-db->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC); 

    }

    // update 
    public function update($id, $data){
        $query = "UPDATE users SET username = :username , email = :email, password = :password where user_id = :id";

        $stmt = $this->prepare($query);
        $stmt->bindParam(":username",$data['username']);
        $stmt->bindParam(":email", $data['email']);
        $stmt->bindParam(":password",password_hash($data['password'],PASSWORD_BCRYPT));
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

// here to display all users from users table
    public function getAll(){

        $query = "SELECT * FROM users WHERE role <> 'admin'";

        $stmt = $this->prepare($query);
        $stmt->bindParam(":admin",$this->role);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // now I'm implementing the userInterface 

    public function getRole(){
        return $this->role;
    }

    public function getId(){
        return $this->id;
    }

    public function getUsername(){
        return $this->username;
    }

    public function getEmail(){
        return $this->email;
    }

    // abstract method to get student Instructor and courses

    abstract public function getData();
    
}


?>