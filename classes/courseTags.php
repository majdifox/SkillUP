<?php


class courseTags{
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function create($id_course,$id_tag) {
        $query = "INSERT INTO public.course_tag(course_id,tag_id)
	VALUES ( :course_id,:tag_id);";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':course_id', $id_course);
        $stmt->bindParam(':tag_id', $id_tag);
        
        return $stmt->execute();
    }
    
  
}

