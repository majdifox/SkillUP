<?php
require_once "userInterface.php";
require_once "interfaceCRUD.php";


abstract class users implements userInterface, interfaceCRUD  {
    
    protected $db;
    protected $id;
    protected $username;
    protected $email;
    protected $role;


// this is the users constructor 
    public function __construct($db, $userData = null) {
        $this->db = $db;
        if (userData){
            $this->id = userData['id'];
            $this->	username = userData['username'];
            $this->email = userData['email'];

        }
    }

    // I'm going to implement the CRUD here using the crud interface that I've created before 

    // create
    
    public function create($data){

        $query = "INSERT INTO users (username, email, password, role ) VALUES (:username , :email, :password, :role)";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam('username', data['username']);
        $stmt->bindParam('email', data['email']);
        $stmt->bindParam('password', password_hash(data['password'], PASSWORD_BCRYPT)); 
        $stmt->bindParam('role',data['role']);

        return $stmt->execute();
    }
    



}

?>