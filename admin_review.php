<?php
// review_course.php
session_start();
require_once 'config/Database.php';
require_once 'classes/Admin.php';
require_once 'classes/Course.php';

// Security check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$admin = new Admin($db, ['user_id' => $_SESSION['user_id']]);

// Get course ID from URL
$course_id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : 0;

if (!$course_id) {
    header('Location: admindash.php');
    exit();
}

// Handle course approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $status = ($_POST['action'] === 'approve') ? 'accepted' : 'refused';
    if ($admin->updateCourseStatus($course_id, $status)) {
        $_SESSION['message'] = "Course successfully " . ($_POST['action'] === 'approve' ? 'approved' : 'rejected');
        header('Location: admindash.php');
        exit();
    }
}

// Get course details
$query = "SELECT c.*, u.username as instructor_name, u.email as instructor_email,
          cat.name as category_name, COUNT(e.enrollment_id) as enrolled_count
          FROM courses c
          LEFT JOIN users u ON c.teacher_id = u.user_id
          LEFT JOIN categories cat ON c.category_id = cat.category_id
          LEFT JOIN enrollments e ON c.course_id = e.course_id
          WHERE c.course_id = :course_id
          GROUP BY c.course_id";

$stmt = $db->prepare($query);
$stmt->bindParam(':course_id', $course_id);
$stmt->execute();
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    header('Location: admindash.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Course Review - <?php echo htmlspecialchars($course['title']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-100">
    <nav class="bg-blue-600 text-white px-6 py-4">
        <div class="flex justify-between items-center">
            <h1 class="text-xl font-bold">Course Review</h1>
            <a href="admindash.php" class="bg-blue-700 px-4 py-2 rounded hover:bg-blue-800">
                <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
            </a>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-3 gap-8">
            <!-- Course Preview Section -->
            <div class="col-span-2">
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <!-- Course Thumbnail -->
                    <img src="<?php echo htmlspecialchars($course['thumbnail_url']); ?>" 
                         alt="<?php echo htmlspecialchars($course['title']); ?>"
                         class="w-full h-64 object-cover">
                    
                    <!-- Course Content -->
                    <div class="p-6">
                        <h1 class="text-3xl font-bold mb-4"><?php echo htmlspecialchars($course['title']); ?></h1>
                        
                        <div class="mb-6">
                            <p class="text-gray-600"><?php echo nl2br(htmlspecialchars($course['description'])); ?></p>
                        </div>

                        <!-- Content Preview -->
                        <div class="border rounded-lg p-4 mb-6">
                            <h3 class="text-xl font-semibold mb-4">Content Preview</h3>
                            <?php if ($course['content_type'] === 'video'): ?>
                                <div class="aspect-w-16 aspect-h-9">
                                    <video controls class="w-full">
                                        <source src="<?php echo htmlspecialchars($course['video_url']); ?>" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                </div>
                            <?php else: ?>
                                <iframe src="<?php echo htmlspecialchars($course['document_url']); ?>" 
                                        class="w-full h-96 border-0"></iframe>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Course Details Sidebar -->
            <div class="col-span-1">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-bold mb-4">Course Details</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-sm text-gray-500">Instructor</h3>
                            <p class="font-medium"><?php echo htmlspecialchars($course['instructor_name']); ?></p>
                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($course['instructor_email']); ?></p>
                        </div>

                        <div>
                            <h3 class="text-sm text-gray-500">Category</h3>
                            <p class="font-medium"><?php echo htmlspecialchars($course['category_name']); ?></p>
                        </div>

                        <div>
                            <h3 class="text-sm text-gray-500">Content Type</h3>
                            <p class="font-medium capitalize"><?php echo htmlspecialchars($course['content_type']); ?></p>
                            <?php if ($course['content_type'] === 'video'): ?>
                                <p class="text-sm text-gray-600"><?php echo $course['video_length']; ?> minutes</p>
                            <?php else: ?>
                                <p class="text-sm text-gray-600"><?php echo $course['document_pages']; ?> pages</p>
                            <?php endif; ?>
                        </div>

                        <div>
                            <h3 class="text-sm text-gray-500">Difficulty Level</h3>
                            <p class="font-medium capitalize"><?php echo htmlspecialchars($course['difficulty_level']); ?></p>
                        </div>

                        <div>
                            <h3 class="text-sm text-gray-500">Duration</h3>
                            <p class="font-medium">
                                <?php echo $course['duration_value'] . ' ' . $course['duration_type']; ?>
                            </p>
                        </div>

                        <div>
                            <h3 class="text-sm text-gray-500">Submission Date</h3>
                            <p class="font-medium">
                            <?php echo date('l, F j, Y - h:i A', strtotime($course['created_at'])); ?>

                            </p>
                        </div>
                    </div>

                    <!-- Approval Actions -->
                    <?php if ($course['status'] === 'in_progress'): ?>
                        <div class="mt-8 space-y-4">
                            <form method="POST" class="grid grid-cols-2 gap-4">
                                <button type="submit" name="action" value="approve" 
                                        class="w-full py-2 bg-green-500 text-white rounded hover:bg-green-600">
                                    <i class="fas fa-check mr-2"></i>Approve
                                </button>
                                <button type="submit" name="action" value="reject"
                                        class="w-full py-2 bg-red-500 text-white rounded hover:bg-red-600">
                                    <i class="fas fa-times mr-2"></i>Reject
                                </button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="mt-8">
                            <p class="text-center font-medium">
                                Status: <span class="capitalize"><?php echo $course['status']; ?></span>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>