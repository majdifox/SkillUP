<?php
// view_enrollments.php
session_start();
require_once 'config/Database.php';
require_once 'classes/Users.php';
require_once 'classes/Instructor.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'instructor') {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$instructor = new Instructor($db, ['user_id' => $_SESSION['user_id']]);

$course_id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : 0;

if (!$course_id) {
    header('Location: instructordash.php');
    exit();
}

// Verify course belongs to instructor
$query = "SELECT * FROM courses WHERE course_id = :course_id AND teacher_id = :teacher_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':course_id', $course_id);
$stmt->bindParam(':teacher_id', $_SESSION['user_id']);
$stmt->execute();

if (!$stmt->fetch()) {
    header('Location: instructordash.php');
    exit();
}

// Get enrolled students
$query = "SELECT u.*, e.enrolled_at, 
COALESCE(e.last_accessed, e.enrolled_at) as last_accessed
FROM users u
JOIN enrollments e ON u.user_id = e.student_id
WHERE e.course_id = :course_id
ORDER BY e.enrolled_at DESC";


$stmt = $db->prepare($query);
$stmt->bindParam(':course_id', $course_id);
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Course Enrollments</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-100">
    <nav class="bg-indigo-600 text-white px-6 py-4">
        <div class="flex justify-between items-center">
            <h1 class="text-xl font-bold">Course Enrollments</h1>
            <a href="instructordash.php" class="bg-indigo-700 px-4 py-2 rounded hover:bg-indigo-800">
                <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
            </a>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold mb-6">Enrolled Students</h2>
            
            <?php if (empty($students)): ?>
                <p class="text-gray-500 text-center py-8">No students enrolled in this course yet.</p>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($students as $student): ?>
                        <div class="border rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-indigo-500"></i>
                                </div>
                                <div>
                                    <h3 class="font-medium"><?php echo htmlspecialchars($student['username']); ?></h3>
                                    <p class="text-sm text-gray-500"><?php echo htmlspecialchars($student['email']); ?></p>
                                </div>
                            </div>
                            <div class="mt-4 text-sm text-gray-600">
                                <p>
                                    <i class="fas fa-calendar-alt mr-2"></i>
                                    Enrolled: <?php echo date('M d, Y', strtotime($student['enrolled_at'])); ?>
                                </p>
                                <?php if ($student['last_accessed']): ?>
                                    <p>
                                        <i class="fas fa-clock mr-2"></i>
                                        Last Active: <?php echo date('M d, Y', strtotime($student['last_accessed'])); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>