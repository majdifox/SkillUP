<?php
require_once 'course.php';

class videoCourse extends course {
    private $videoUrl;

    public function __construct($db, $courseData = null) {
        parent::__construct($db);
        if ($courseData) {
            $this->title = $courseData['title'];
            $this->description = $courseData['description'];
            $this->teacher_id = $courseData['teacher_id'];
            $this->category_id = $courseData['category_id'];
            $this->videoUrl = $courseData['document_url'];
        }
    }

    public function display() {
        echo "Affichage du cours vidéo : {$this->title}<br>";
        echo "URL de la vidéo : {$this->videoUrl}<br>";
    }

    public function setVideoUrl($url) {
        $this->videoUrl = $url;
    }

    public function getVideoUrl() {
        return $this->videoUrl;
    }
}

