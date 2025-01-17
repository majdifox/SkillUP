<?php
require_once 'config/Database.php';
require_once 'models/UserFactory.php';
require_once 'models/UserFactory.php';
require_once 'models/Course_Tags.php';
require_once 'admin/AdminManager.php';
require_once 'admin/CategoriesManager.php';
require_once 'admin/TagsManager.php';

session_start();

$db = Database::getInstance()->getConnection();
$userFactory = new UserFactory($db);
$courseFactory = new CourseFactory($db);
$adminManager = new AdminManager($db);
$categoryManager =  new CategoriesManager($db);
$tagManager =  new TagsManager($db);
$action = $_GET['action'] ?? 'home';
$course_tag = new TagsCourse($db);

switch ($action) {
    case 'home':
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $courses = $courseFactory->getAllCourses($page);
        require 'views/home.php';
        break;
    case 'search':
        $keyword = $_GET['keyword'] ?? '';
        $results = $courseFactory->searchCourses($keyword);
        require 'views/search_results.php';
        break;
    case 'register':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            try {
                $user = $userFactory->createUser($_POST['role']);
                
                $user->create($_POST);
                echo "<pre>";
                var_dump($_POST);
                echo "</pre>";

                header('Location: index.php?action=login');
            } catch (Exception $e) {
                echo "error";
                $error = $e->getMessage();
                require 'views/register.php';
            }
        } else {
            require 'views/register.php';
        }
        break;


    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            $user = $userFactory->authenticate($_POST['username'], $_POST['password']);
            echo "authenticate";
            if ($user) {
                echo "there is a user";
                $_SESSION['user'] = [
                    'id_user' => $user->getId(),
                    'username' => $user->getUsername(),
                    'email' => $user->getEmail(),
                    'role' => $user->getRole(),
                    'status' => $user->getStatus()
                ];
                echo "session created";
                header('Location: index.php');
            } else {
                $error = "Invalid credentials";
                echo "else";
                require 'views/login.php';
            }
        } else {
            require 'views/login.php';
        }
        break;
    case 'logout':
        session_destroy();
        header('Location: index.php');
        break;
    case 'course':
        $courseId = $_GET['id'] ?? null;
        if ($courseId) {
            $course = $courseFactory->getCourse($courseId);
            if ($course) {
                require 'views/course_details.php';
            } else {
                header('Location: index.php');
            }
        } else {
            header('Location: index.php');
        }
        break;
    case 'enroll':
        if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'student') {
            $student = $userFactory->createUser('student', $_SESSION['user']);
            $courseId = $_GET['id'] ?? null;
            if ($courseId) {
                $course = $courseFactory->getCourse($courseId);
                if ($course){
                    $course->enroll($student->getId());
                    header('Location: index.php?action=course&id=' . $courseId);
                } else {
                    header('Location: index.php');
                }
            } else {
                header('Location: index.php');
            }
        } else {
            header('Location: index.php?action=login');
        }
        break;
    case 'my_courses':

        if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'student') {
            $student = $userFactory->createUser('student', $_SESSION['user']);
            $enrolledCourses = $student->getSpecificData();
            require 'views/student/my_courses.php';
        } else {
            header('Location: index.php?action=login');
        }
        break;
    case 'add_course':
        if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'teacher') {
            $teacher = $userFactory->createUser('teacher', $_SESSION['user']);
            $categories = $categoryManager->listcategory();
            $tags = $tagManager->listTags();
            var_dump($_SESSION['user']);
            echo  $_SESSION['user']['status'];
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                var_dump($_POST);
                $course = $courseFactory->createCourse($_POST['content_type'],$_POST);
               $id_course = $course->create($_POST);
               $tags = $_POST["selected_tags"];
               $tags = explode(',', $tags); 
               var_dump($id_course);
               foreach ($tags as $id_tag ) {
                    $course_tag->create($id_course["id"],$id_tag);
                }
                header('Location: index.php?action=teacher_dashboard');
            } else {
                    require 'views/teacher/add_course.php';
            }
        } else {
            header('Location: index.php?action=login');
        }
        break;
    case 'teacher_courses':
        echo "hhh";
        if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'teacher') {
            $teacher = $userFactory->createUser('teacher', $_SESSION['user']);
           $data =  $teacher->getSpecificData();
            require_once('views/teacher/tacher_courses.php');
        } else {
            header('Location: index.php?action=login');
        }
        break;
    case 'add_category':
        if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin') {
            $teacher = $userFactory->createUser('teacher', $_SESSION['user']);
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $name = $_POST['name'];
                $categoryManager->addCategory($name);
                header('Location: index.php?action=teacher_dashboard');
            } else {
                require 'views/teacher/add_categorie.php';
            }
        } else {
            header('Location: index.php?action=login');
        }
        break;
    case 'add_tag':
        if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin') {
            $teacher = $userFactory->createUser('teacher', $_SESSION['user']);
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $name = $_POST['name'];
                $tagManager->addTag($name);
                header('Location: index.php?action=teacher_dashboard');
            } else {
                require 'views/add_tag.php';
            }
        } else {
            header('Location: index.php?action=login');
        }
        break;
    case 'teacher_dashboard':
        if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'teacher') {
        
            $teacher = $userFactory->createUser('teacher', $_SESSION['user']);
            $teacherCourses = $teacher->getSpecificData();
            require 'views/teacher/teacher_dashboard.php';
            
        } else {
            header('Location: index.php?action=login');
        }
        break;
    case 'admin_dashboard':
        if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin') {
            $admin = $userFactory->createUser('admin', $_SESSION['user']);
            $statistics = $admin->getSpecificData();
            $categories = $categoryManager->listcategory();
            $tags = $tagManager->listTags();
            $listUsers = $admin->getAll();
            $globalStatistics = $adminManager->getGlobalStatistics();
            $listTeachers = $userFactory->getAllTeachers();
            require 'views/admin/admin_dashboard.php';
        } else {
            header('Location: index.php?action=login');
        }
        break;
    case 'courses':
        if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin') {
            $admin = $userFactory->createUser('admin', $_SESSION['user']);
           
            require 'views/courses.php';
        } else {
            header('Location: index.php?action=login');
        }
        break;
    case 'listUsers':
        if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin') {
            $admin = $userFactory->createUser('admin', $_SESSION['user']);
            $listUsers = $admin->getAll();
            require 'views/admin/listUsers.php';
        } else {
            header('Location: index.php?action=login');
        }
        break;
    case 'delete' || 'activate' || 'suspend':
        if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin') {
            $userId = $_GET['id_user'];
            $adminManager->manageUser($userId,$action);
            echo "hdvhjej";
        } else {
        }
        break;
    default:
        require 'views/404.php';
        break;
}

