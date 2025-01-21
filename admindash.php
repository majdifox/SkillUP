<?php
// admindash.php
session_start();
require_once 'config/Database.php';
require_once 'classes/Admin.php';

// Enhanced security check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$admin = new Admin($db, ['user_id' => $_SESSION['user_id']]);

// Handle instructor approval/rejection
// if (isset($_POST['action']) && isset($_POST['instructor_id'])) {
//     $instructorId = filter_var($_POST['instructor_id'], FILTER_VALIDATE_INT);
//     $action = $_POST['action'];
    
//     if ($instructorId && in_array($action, ['approve', 'reject'])) {
//         $status = ($action === 'approve') ? 'accepted' : 'refused';
//         $isActive = ($action === 'approve') ? 1 : 0;
        
//         $query = "UPDATE users SET status = :status, is_active = :is_active 
//                   WHERE user_id = :id AND role = 'instructor'";
//         $stmt = $db->prepare($query);
//         $stmt->bindParam(':status', $status);
//         $stmt->bindParam(':is_active', $isActive);
//         $stmt->bindParam(':id', $instructorId);
        
//         if ($stmt->execute()) {
//             $_SESSION['message'] = "Instructor successfully " . ($action === 'approve' ? 'approved' : 'rejected');
//         } else {
//             $_SESSION['error'] = "Failed to process instructor " . $action;
//         }
//     }
// }

// Get stats
$stats = $admin->getData();

// Get pending instructors
// $query = "SELECT * FROM users WHERE role = 'instructor' AND status = 'in_progress'";
// $stmt = $db->query($query);
// $pendingInstructors = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent courses
// $query = "SELECT c.*, u.username as instructor_name 
//           FROM courses c 
//           JOIN users u ON c.teacher_id = u.user_id 
//           WHERE c.status = 'in_progress' 
//           ORDER BY c.created_at DESC LIMIT 5";
// $stmt = $db->query($query);
// $pendingCourses = $stmt->fetchAll(PDO::FETCH_ASSOC);




// Get all courses with detailed information
$query = "SELECT c.*, u.username as instructor_name, cat.name as category_name,
          COUNT(DISTINCT e.enrollment_id) as student_count,
          GROUP_CONCAT(t.name) as tags
          FROM courses c
          LEFT JOIN users u ON c.teacher_id = u.user_id
          LEFT JOIN categories cat ON c.category_id = cat.category_id
          LEFT JOIN enrollments e ON c.course_id = e.course_id
          LEFT JOIN course_tags ct ON c.course_id = ct.course_id
          LEFT JOIN tags t ON ct.tag_id = t.tag_id
          GROUP BY c.course_id
          ORDER BY c.created_at DESC";

$stmt = $db->query($query);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle course approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $courseId = filter_var($_POST['course_id'], FILTER_VALIDATE_INT);
    $status = ($_POST['action'] === 'approve') ? 'accepted' : 'refused';
    
    if ($courseId && $admin->updateCourseStatus($courseId, $status)) {
        $_SESSION['message'] = "Course successfully " . ($_POST['action'] === 'approve' ? 'approved' : 'rejected');
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - SkillUp</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-100">
    <!-- Top Navigation -->
    <nav class="bg-blue-600 text-white px-6 py-4">
        <div class="flex justify-between items-center">
            <h1 class="text-xl font-bold">SkillUp Admin</h1>
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

        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php 
                echo htmlspecialchars($_SESSION['error']);
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-gray-500 mb-2">Total Users</h3>
                <p class="text-2xl font-bold"><?php echo $stats['users']['total_users']; ?></p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-gray-500 mb-2">Active Students</h3>
                <p class="text-2xl font-bold"><?php echo $stats['users']['student_count']; ?></p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-gray-500 mb-2">Active Instructors</h3>
                <p class="text-2xl font-bold"><?php echo $stats['users']['instructor_count']; ?></p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-gray-500 mb-2">Total Courses</h3>
                <p class="text-2xl font-bold"><?php echo $stats['courses']['total_courses']; ?></p>
            </div>
        </div>


        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold mb-4">Course Management</h2>
                <div class="space-y-4">
                    <a href="admin_courses.php" class="flex items-center justify-center bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        <i class="fas fa-book-open mr-2"></i> Manage Courses
                    </a>
                    <a href="admin_categories.php" class="flex items-center justify-center bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600">
                        <i class="fas fa-tags mr-2"></i> Manage Categories
                    </a>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold mb-4">User and Tags Management</h2>
                <div class="space-y-4">
                    <a href="admin_tags.php" class="flex items-center justify-center bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                        <i class="fas fa-user-graduate mr-2"></i> Manage Tags
                    </a>
                    <a href="admin_users.php" class="flex items-center justify-center bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">
                        <i class="fas fa-chalkboard-teacher mr-2"></i> Manage Users
                    </a>
                </div>
            </div>
        </div>

        <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">Course Review Dashboard</h1>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php 
                echo htmlspecialchars($_SESSION['message']);
                unset($_SESSION['message']);
                ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($courses as $course): ?>
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <?php if ($course['thumbnail_url']): ?>
                        <img src="<?php echo htmlspecialchars($course['thumbnail_url']); ?>" 
                             alt="<?php echo htmlspecialchars($course['title']); ?>" 
                             class="w-full h-48 object-cover">
                    <?php endif; ?>
                    
                    <div class="p-6">
                        <h2 class="text-xl font-bold mb-2"><?php echo htmlspecialchars($course['title']); ?></h2>
                        <p class="text-gray-600 mb-4"><?php echo htmlspecialchars(substr($course['description'], 0, 150)) . '...'; ?></p>
                        
                        <div class="flex flex-wrap gap-2 mb-4">
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-sm">
                                <?php echo htmlspecialchars($course['difficulty_level']); ?>
                            </span>
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-sm">
                                <?php echo htmlspecialchars($course['category_name']); ?>
                            </span>
                            <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded text-sm">
                                <?php echo htmlspecialchars($course['content_type']); ?>
                            </span>
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-sm">
                                <i class="fas fa-clock mr-1"></i>
                                <?php echo htmlspecialchars($course['duration_value'] . ' ' . $course['duration_type']); ?>
                            </span>
                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-sm">
                                <i class="fas fa-clock mr-1"></i>
                                <?php echo date('l, F j, Y - h:i A', strtotime($course['created_at'])); ?>
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

                        <div class="flex items-center justify-between mb-4">
                            <span class="text-sm text-gray-500">
                                <i class="fas fa-users mr-2"></i><?php echo $course['student_count']; ?> students
                            </span>
                            <span class="px-2 py-1 rounded text-sm <?php 
                                echo $course['status'] === 'accepted' ? 'bg-green-100 text-green-800' : 
                                    ($course['status'] === 'refused' ? 'bg-red-100 text-red-800' : 
                                    'bg-yellow-100 text-yellow-800'); 
                                ?>">
                                <?php echo ucfirst($course['status']); ?>
                            </span>
                        </div>

                        <div class="flex gap-2 mt-4">
                            <a href="admin_review.php?id=<?php echo $course['course_id']; ?>" 
                               class="flex-1 bg-blue-500 text-white text-center py-2 rounded hover:bg-blue-600">
                                <i class="fas fa-eye mr-2"></i>Review
                            </a>
                            <?php if ($course['status'] === 'in_progress'): ?>
                                <form method="POST" class="flex-1 flex gap-2">
                                    <input type="hidden" name="course_id" value="<?php echo $course['course_id']; ?>">
                                    <button type="submit" name="action" value="approve" 
                                            class="flex-1 bg-green-500 text-white py-2 rounded hover:bg-green-600">
                                        <i class="fas fa-check mr-2"></i>Approve
                                    </button>
                                    <button type="submit" name="action" value="reject"
                                            class="flex-1 bg-red-500 text-white py-2 rounded hover:bg-red-600">
                                        <i class="fas fa-times mr-2"></i>Reject
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
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
        //         const alerts = document.querySelectorAll('.bg-green-100, .bg-red-100');
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