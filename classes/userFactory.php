<?php

require_once 'Users.php';
require_once 'Admin.php';
require_once 'Instructor.php';
require_once 'Student.php';

class UserFactory {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }


    public function createUser($role, $userData = null) {
        switch ($role) {
            case 'student':
                return new student($this->db, $userData);
            case 'instructor':
                return new instructor($this->db, $userData);
            case 'admin':
                return new admin($this->db, $userData);
            default:
                throw new Exception("Invalid user role");
        }
    }

    public function login($email, $password) {
        $query = "SELECT * FROM users WHERE email = :email";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($userData && password_verify($password, $userData['password'])) {
            return $this->createUser($userData['role'], $userData);
        }
        return null;
    }
    public function getAllTeachers() {
        $query = "SELECT * FROM users WHERE role = 'instructor'";
        $stmt = $this->db->query($query);
        $instructor = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $list = [];
        $i = 0;
        foreach ($instructor as $instructor) {

         $list[$i] = $this->createUser($instructor['role'],$instructor);
         $i++;
        } 
        return $list;
    }
}

