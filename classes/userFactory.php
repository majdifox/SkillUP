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
                return new Student($this->db, $userData);
            case 'instructor':
                return new Instructor($this->db, $userData);
            case 'admin':
                return new Admin($this->db, $userData);
            default:
                throw new Exception("Invalid user role: {$role}");
        }
    }
    
    public function login($email, $password) {
        $query = "SELECT * FROM users WHERE email = :email AND is_active = 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($userData && password_verify($password, $userData['password'])) {
            return $this->createUser($userData['role'], $userData);
        }
        return null;
    }
    
    public function register($userData) {
        try {
            $query = "INSERT INTO users (username, email, password, role, is_active, status) 
                     VALUES (:username, :email, :password, :role, :is_active, :status)";
                     
            $stmt = $this->db->prepare($query);
            
            // Store the original password before hashing
            $originalPassword = $userData['password'];
            $userData['password'] = password_hash($userData['password'], PASSWORD_BCRYPT);
            
            if ($stmt->execute($userData)) {
                // Use the original password for login, not the hashed one
                return $this->login($userData['email'], $originalPassword);
            } else {
                return null;
            }
        } catch (PDOException $e) {
            echo "PDO Exception: " . $e->getMessage();
            return null;
        }
    }
    
    public function getInstructors($status = null) {
        $query = "SELECT * FROM users WHERE role = 'instructor'";
        if ($status) {
            $query .= " AND status = :status";
        }
        
        $stmt = $this->db->prepare($query);
        if ($status) {
            $stmt->bindParam(':status', $status);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}