<?php

interface crudInterface{

    public function create($data);
    public function read($id);
    public function update($id);
    public function delete($id);
    public function getAll();

}

?>