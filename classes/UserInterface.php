<?php

interface userInterface{

    public function getRole();
    public function getId();
    public function getUsername();
    public function getEmail();
    public function getStatus(); 
    public function isActive(); 
}


?>