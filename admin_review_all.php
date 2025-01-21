<?php
// review_course.php
session_start();
require_once 'config/Database.php';
require_once 'classes/Admin.php';
require_once 'classes/Course.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$admin = new Admin($db, ['user_id' => $_SESSION['user_id']]);

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
    <title>Course Review - SkillUp</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'includes/admin_nav.php'; ?>
    
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
                            <a href="review_course_detail.php?id=<?php echo $course['course_id']; ?>" 
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

    <script>
        // Add fade out effect for alert messages
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const alerts = document.querySelectorAll('.bg-green-100, .bg-red-100');
                alerts.forEach(alert => {
                    alert.style.transition = 'opacity 0.5s ease-in-out';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                });
            }, 3000);
        });
    </script>
</body>
</html>