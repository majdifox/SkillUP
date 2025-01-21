<?php
require_once 'config/Database.php';
require_once 'classes/Users.php';
require_once 'classes/Student.php';

session_start();

// Security check for student role
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$student = new Student($db, ['user_id' => $_SESSION['user_id']]);

// Handle course enrollment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll_course'])) {
    $courseId = $_POST['course_id'];
    if ($student->enrollCourse($courseId)) {
        $_SESSION['message'] = "Successfully enrolled in the course!";
    } else {
        $_SESSION['error'] = "Failed to enroll in the course.";
    }
    header('Location: studentdash.php');
    exit();
}

// Get search term
$search = $_GET['search'] ?? '';

// Get courses
$enrolledCourses = $student->getData();
$availableCourses = $student->getAvailableCourses($search);

// Count total enrollments
$totalEnrolled = count($enrolledCourses);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard - SkillUp</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-100">
    <!-- Top Navigation -->
    <nav class="bg-blue-600 text-white px-6 py-4">
        <div class="flex justify-between items-center">
            <h1 class="text-xl font-bold">SkillUp Student</h1>
            <div class="flex items-center gap-4">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="logout.php" class="bg-blue-700 px-4 py-2 rounded hover:bg-blue-800">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php 
                echo htmlspecialchars($_SESSION['message']);
                unset($_SESSION['message']);
                ?>
            </div>
        <?php endif; ?>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-gray-500 mb-2">Enrolled Courses</h3>
                <p class="text-2xl font-bold"><?php echo $totalEnrolled; ?></p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-gray-500 mb-2">Available Courses</h3>
                <p class="text-2xl font-bold"><?php echo count($availableCourses); ?></p>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="mb-8">
            <form action="" method="GET" class="flex gap-4">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Search courses..." 
                       class="flex-1 p-2 border rounded">
                <button type="submit" 
                        class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
                    <i class="fas fa-search mr-2"></i>Search
                </button>
            </form>
        </div>

           <!-- Available Courses -->
           <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold mb-4">Available Courses</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($availableCourses as $course): ?>
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden border">
                        <?php if ($course['thumbnail_url']): ?>
                            <img src="<?php echo htmlspecialchars($course['thumbnail_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($course['title']); ?>" 
                                 class="w-full h-48 object-cover">
                        <?php endif; ?>
                        
                        <div class="p-6">
                            <h2 class="text-xl font-bold mb-2"><?php echo htmlspecialchars($course['title']); ?></h2>
                            <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($course['description']); ?></p>
                            
                            <div class="flex flex-wrap gap-2 mb-4">
                                
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-sm">
                                    <?php echo htmlspecialchars($course['difficulty_level']); ?>
                                </span>
                                <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded text-sm">
                                    <?php echo htmlspecialchars($course['content_type']); ?>
                                </span>
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-sm">
                                    By <?php echo htmlspecialchars($course['instructor_name']); ?>
                                </span>
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-sm">
                                <i class="fas fa-clock mr-1"></i>
                                <?php echo htmlspecialchars($course['duration_value'] . ' ' . $course['duration_type']); ?>
                            </span>
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-sm">
                                <?php echo htmlspecialchars($course['category_name']); ?>
                            </span>
                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-sm">
                                <i class="fas fa-clock mr-1"></i>
                                <?php echo date('l, F j, Y - h:i A', strtotime($course['created_at'])); ?>
                            </span>
                                
                            </div>

                            <?php if (!empty($course['tags'])): ?>
                                <div class="flex flex-wrap gap-2 mb-4">
                                    <?php foreach (explode(',', $course['tags']) as $tag): ?>
                                        <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs">
                                            #<?php echo htmlspecialchars(trim($tag)); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST" class="mt-4">
                                <input type="hidden" name="course_id" value="<?php echo $course['course_id']; ?>">
                                <button type="submit" name="enroll_course" 
                                        class="w-full bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                                    Enroll Now
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    

        <!-- Enrolled Courses -->
         <br>
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h2 class="text-xl font-bold mb-4">My Enrolled Courses</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($enrolledCourses as $course): ?>
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden border">
                        <?php if ($course['thumbnail_url']): ?>
                            <img src="<?php echo htmlspecialchars($course['thumbnail_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($course['title']); ?>" 
                                 class="w-full h-48 object-cover">
                        <?php endif; ?>
                        
                        <div class="p-6">
                            <h2 class="text-xl font-bold mb-2"><?php echo htmlspecialchars($course['title']); ?></h2>
                            <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($course['description']); ?></p>
                            
                            <div class="flex flex-wrap gap-2 mb-4">
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-sm">
                                    <?php echo htmlspecialchars($course['difficulty_level']); ?>
                                </span>
                                <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded text-sm">
                                    <?php echo htmlspecialchars($course['content_type']); ?>
                                </span>
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-sm">
                                <i class="fas fa-clock mr-1"></i>
                                <?php echo htmlspecialchars($course['duration_value'] . ' ' . $course['duration_type']); ?>
                            </span>
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-sm">
                                <?php echo htmlspecialchars($course['category_name']); ?>
                            </span>
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-sm">
                                    By <?php echo htmlspecialchars($course['instructor_name']); ?>
                                </span>
                            </div>

                            <?php if (!empty($course['tags'])): ?>
                                <div class="flex flex-wrap gap-2 mb-4">
                                    <?php foreach (explode(',', $course['tags']) as $tag): ?>
                                        <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs">
                                            #<?php echo htmlspecialchars(trim($tag)); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <a href="student_courses.php?id=<?php echo $course['course_id']; ?>" 
                               class="block w-full text-center bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                View Course Content
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        </div>

     

    <script>
        // Add fade out effect for alert messages
        // document.addEventListener('DOMContentLoaded', function() {
        //     setTimeout(function() {
        //         const alerts = document.querySelectorAll('.bg-green-100');
        //         alerts.forEach(alert => {
        //             alert.style.transition = 'opacity 0.5s ease-in-out';
        //             alert.style.opacity = '0';
        //             setTimeout(() => alert.remove(), 500);
        //         });
        //     }, 3000);
        // });
    </script>
</body>
</html>