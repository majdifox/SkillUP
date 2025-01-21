<?php
require_once 'course.php';

class documentCourse extends course {
    private $documentUrl;

    public function __construct($db, $courseData = null) {
        parent::__construct($db);
        if ($courseData) {
                // $this->id = $courseData['id'] ;
            $this->title = $courseData['title'];
            $this->description = $courseData['description'];
            $this->teacher_id = $courseData['teacher_id'];
            $this->category_id = $courseData['category_id'];
            $this->documentUrl = $courseData['document_url'];
        }
    }

    public function display() {
        echo "Affichage du cours document : {$this->title}<br>";
        echo "URL du document : {$this->documentUrl}<br>";
    }

    public function setDocumentUrl($url) {
        $this->documentUrl = $url;
    }

    public function getDocumentUrl() {
        return $this->documentUrl;
    }
}

