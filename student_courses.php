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

// Check if course ID is provided
if (!isset($_GET['id'])) {
    header('Location: studentdash.php');
    exit();
}

$courseId = $_GET['id'];
$database = new Database();
$db = $database->getConnection();
$student = new Student($db, ['user_id' => $_SESSION['user_id']]);

// Check if student is enrolled in this course
if (!$student->isEnrolled($courseId)) {
    header('Location: studentdash.php');
    exit();
}

// Get course content
$course = $student->getCourseContent($courseId);

// If course not found
if (!$course) {
    header('Location: studentdash.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($course['title']); ?> - SkillUp</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-100">
    <!-- Top Navigation -->
    <nav class="bg-blue-600 text-white px-6 py-4">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="studentdash.php" class="hover:text-gray-200">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
                <h1 class="text-xl font-bold">Course Viewer</h1>
            </div>
            <div class="flex items-center gap-4">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="logout.php" class="bg-blue-700 px-4 py-2 rounded hover:bg-blue-800">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <!-- Course Header -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-8">
            <?php if ($course['thumbnail_url']): ?>
                <img src="<?php echo htmlspecialchars($course['thumbnail_url']); ?>" 
                     alt="<?php echo htmlspecialchars($course['title']); ?>" 
                     class="w-full h-64 object-cover">
            <?php endif; ?>
            
            <div class="p-6">
                <h1 class="text-3xl font-bold mb-4"><?php echo htmlspecialchars($course['title']); ?></h1>
                <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($course['description']); ?></p>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-sm text-gray-500">Difficulty Level</h3>
                        <p class="font-semibold"><?php echo ucfirst($course['difficulty_level']); ?></p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-sm text-gray-500">Duration</h3>
                        <p class="font-semibold"><?php echo $course['duration_value'] . ' ' . $course['duration_type']; ?></p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-sm text-gray-500">Content Type</h3>
                        <p class="font-semibold"><?php echo ucfirst($course['content_type']); ?></p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-sm text-gray-500">Status</h3>
                        <p class="font-semibold"><?php echo ucfirst($course['status']); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Course Content -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold mb-6">Course Content</h2>
            
            <?php if ($course['content_type'] === 'video'): ?>
                <?php if ($course['video_url']): ?>
                    <div class="aspect-w-16 aspect-h-9 mb-4">
                        <video controls class="w-full rounded-lg">
                            <source src="<?php echo htmlspecialchars($course['video_url']); ?>" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    </div>
                    <p class="text-gray-600">Video Length: <?php echo $course['video_length']; ?> minutes</p>
                <?php else: ?>
                    <p class="text-gray-600">Video content is not available at the moment.</p>
                <?php endif; ?>
            <?php else: ?>
                <?php if ($course['document_url']): ?>
                    <div class="mb-4">
                        <a href="<?php echo htmlspecialchars($course['document_url']); ?>" 
                           target="_blank"
                           class="inline-flex items-center gap-2 bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600">
                            <i class="fas fa-file-pdf"></i>
                            View Document
                        </a>
                    </div>
                    <p class="text-gray-600">Document Pages: <?php echo $course['document_pages']; ?></p>
                <?php else: ?>
                    <p class="text-gray-600">Document content is not available at the moment.</p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>