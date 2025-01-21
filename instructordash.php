<?php
// instructordash.php
require_once 'config/Database.php';
require_once 'classes/Users.php';
require_once 'classes/Instructor.php';
require_once 'classes/CourseTags.php';
// Create upload directories if they don't exist
$uploadDirs = [
    'uploads/thumbnails',
    'uploads/videos',
    'uploads/documents'
];

foreach ($uploadDirs as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
}

session_start();

// Security check for instructor role
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'instructor') {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$instructor = new Instructor($db, ['user_id' => $_SESSION['user_id']]);

// Fetch categories for the form
$query = "SELECT * FROM categories";
$stmt = $db->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$query = "SELECT * FROM tags";
$stmt = $db->prepare($query);
$stmt->execute();
$tags = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle file uploads and course creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_course') {
    $uploadDir = 'uploads/';
    $courseData = [
        'title' => $_POST['title'],
        'description' => $_POST['description'],
        'difficulty_level' => $_POST['difficulty_level'],
        'duration_type' => $_POST['duration_type'],
        'duration_value' => $_POST['duration_value'],
        'content_type' => $_POST['content_type'],
        'category_id' => $_POST['category_id'],
        'tag_id' => $_POST['tag_id'],
        'teacher_id' => $_SESSION['user_id'],
        'status' => 'in_progress'
    ];

    // Handle thumbnail upload
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === 0) {
        $thumbnail = $_FILES['thumbnail'];
        $thumbnailName = uniqid() . '_' . basename($thumbnail['name']);
        $thumbnailPath = $uploadDir . 'thumbnails/' . $thumbnailName;
        
        if (move_uploaded_file($thumbnail['tmp_name'], $thumbnailPath)) {
            $courseData['thumbnail_url'] = $thumbnailPath;
        }
    }

    // Handle course deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_course'])) {
    $course_id = $_POST['course_id'];
    $query = "DELETE FROM courses WHERE course_id = :course_id AND teacher_id = :teacher_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':course_id', $course_id);
    $stmt->bindParam(':teacher_id', $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Course deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete course.";
    }
    
    header('Location: instructordash.php');
    exit();
}


// Handle content upload based on type
if ($_POST['content_type'] === 'video') {
    if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] === 0) {
        $video = $_FILES['video_file'];
        $videoName = uniqid() . '_' . basename($video['name']);
        $videoPath = $uploadDir . 'videos/' . $videoName;
        
        if (move_uploaded_file($video['tmp_name'], $videoPath)) {
            $courseData['video_url'] = $videoPath;
            $courseData['video_length'] = $_POST['video_length'];
            $courseData['document_pages'] = null;
        } else {
            $_SESSION['error'] = "Failed to upload video file.";
            header('Location: instructordash.php');
            exit();
        }
    }
} else {
    if (isset($_FILES['document_file']) && $_FILES['document_file']['error'] === 0) {
        $document = $_FILES['document_file'];
        $documentName = uniqid() . '_' . basename($document['name']);
        $documentPath = $uploadDir . 'documents/' . $documentName;
        
        if (move_uploaded_file($document['tmp_name'], $documentPath)) {
            $courseData['document_url'] = $documentPath;
            $courseData['document_pages'] = $_POST['document_pages'];
            $courseData['video_length'] = null;
            $courseData['video_url'] = null;
        } else {
            $_SESSION['error'] = "Failed to upload document file.";
            header('Location: instructordash.php');
            exit();
        }
    }
}

    if ($instructor->addCourse($courseData)) {
        $course_id = $db->lastInsertId();

        if (isset($_POST['tag_id']) && is_array($_POST['tag_id'])) {
            $courseTags = new CourseTags($db);
            foreach ($_POST['tag_id'] as $tag_id) {
                $tagData = [
                    'course_id' => $course_id,
                    'tag_id' => $tag_id
                ];
                $courseTags->create($tagData);
            }
        }
        $_SESSION['message'] = "Course created successfully!";
    } else {
        $_SESSION['error'] = "Failed to create course.";
    }
    
    header('Location: instructordash.php');
    exit();
}

// Get instructor's courses
$courses = $instructor->getData();

// Calculate statistics
$totalStudents = 0;
$totalCourses = count($courses);
$pendingCourses = 0;
$acceptedCourses = 0;

foreach ($courses as $course) {
    $totalStudents += $course['student_count'];
    if ($course['status'] === 'in_progress') {
        $pendingCourses++;
    } elseif ($course['status'] === 'accepted') {
        $acceptedCourses++;
    }
}

$courses = $instructor->getData();



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Instructor Dashboard - SkillUp</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-100">
    <!-- Top Navigation -->
    <nav class="bg-indigo-600 text-white px-6 py-4">
        <div class="flex justify-between items-center">
            <h1 class="text-xl font-bold">SkillUp Instructor</h1>
            <div class="flex items-center gap-4">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="logout.php" class="bg-indigo-700 px-4 py-2 rounded hover:bg-indigo-800">Logout</a>
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
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-gray-500 mb-2">Total Students</h3>
                <p class="text-2xl font-bold"><?php echo $totalStudents; ?></p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-gray-500 mb-2">Total Courses</h3>
                <p class="text-2xl font-bold"><?php echo $totalCourses; ?></p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-gray-500 mb-2">Pending Courses</h3>
                <p class="text-2xl font-bold"><?php echo $pendingCourses; ?></p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-gray-500 mb-2">Active Courses</h3>
                <p class="text-2xl font-bold"><?php echo $acceptedCourses; ?></p>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mb-8">
            <button onclick="document.getElementById('addCourseModal').classList.remove('hidden')" 
                    class="bg-indigo-500 text-white px-6 py-2 rounded-lg hover:bg-indigo-600">
                <i class="fas fa-plus mr-2"></i>Add New Course
            </button>
        </div>

        <!-- Course List -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold mb-4">My Courses</h2>
            <div class="container mx-auto px-4 py-8">
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
                        <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($course['description']); ?></p>
                        
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
                            <?php if (!empty($course['tags'])): ?>
                            <div class="flex flex-wrap gap-2 mb-4">
                                <?php foreach (explode(',', $course['tags']) as $tag): ?>
                                    <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs">
                                        #<?php echo htmlspecialchars(trim($tag)); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        </div>

                        

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

                        <div class="flex justify-between items-center">
                            <div class="space-x-2">
                                <a href="instructor_edit.php?id=<?php echo $course['course_id']; ?>" 
                                   class="inline-block px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="instructordash.php" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this course?');">
                                <input type="hidden" name="delete_course" value="1">
                                <input type="hidden" name="course_id" value="<?php echo $course['course_id']; ?>">
                                <button type="submit" class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            </div>
                            <a href="instructor_enrollments.php?id=<?php echo $course['course_id']; ?>" 
                               class="inline-block px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600">
                                <i class="fas fa-users mr-1"></i>View Students
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Add Course Modal -->
    <div id="addCourseModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-2/3 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Add New Course</h3>
                <form action="instructordash.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="create_course">
                    
                    <!-- Basic Course Information -->
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Title</label>
                            <input type="text" name="title" required 
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Category</label>
                            <select name="category_id" required class="shadow border rounded w-full py-2 px-3 text-gray-700">
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['category_id']; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
    <label class="block text-gray-700 text-sm font-bold mb-2">Tags (Select up to 10)</label>
    <div id="tagContainer">
        <?php foreach ($tags as $tag): ?>
            <label class="block">
                <input type="checkbox" name="tag_id[]" value="<?php echo $tag['tag_id']; ?>" class="tag-checkbox">
                <?php echo htmlspecialchars($tag['name']); ?>
            </label>
        <?php endforeach; ?>        
    </div>
</div>

                    </div>

                    <!-- Course Description -->
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Description</label>
                        <textarea name="description" required rows="4"
                                  class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700"></textarea>
                    </div>

                    <!-- Course Settings -->
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Difficulty Level</label>
                            <select name="difficulty_level" required class="shadow border rounded w-full py-2 px-3 text-gray-700">
                                <option value="beginner">Beginner</option>
                                <option value="intermediate">Intermediate</option>
                                <option value="experienced">Experienced</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Content Type</label>
                            <select name="content_type" required onchange="toggleContentFields(this.value)"
                                    class="shadow border rounded w-full py-2 px-3 text-gray-700">
                                <option value="document">Document</option>
                                <option value="video">Video</option>
                            </select>
                        </div>
                    </div>

                    <!-- Course Duration -->
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Duration Type</label>
                            <select name="duration_type" required class="shadow border rounded w-full py-2 px-3 text-gray-700">
                                <option value="hours">Hours</option>
                                <option value="days">Days</option>
                                <option value="weeks">Weeks</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Duration Value</label>
                            <input type="number" name="duration_value" required min="1"
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                        </div>
                    </div>

                    <!-- Thumbnail Upload -->
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Course Thumbnail</label>
                        <input type="file" name="thumbnail" accept="image/*" required
                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                        <p class="text-sm text-gray-500 mt-1">Recommended size: 1280x720 pixels</p>
                    </div>

                    <!-- Content Type Specific Fields -->
                    <div id="videoFields" class="hidden">
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Video File</label>
                            <input type="file" name="video_file" accept="video/*"
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                            <p class="text-sm text-gray-500 mt-1">Maximum file size: 500MB</p>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Video Length (minutes)</label>
                            <input type="number" name="video_length" min="1"
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                        </div>
                    </div>

                    <div id="documentFields">
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Document File</label>
                            <input type="file" name="document_file" accept=".pdf,.doc,.docx"
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                            <p class="text-sm text-gray-500 mt-1">Accepted formats: PDF, DOC, DOCX</p>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Number of Pages</label>
                            <input type="number" name="document_pages" min="1"
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex justify-end gap-4 mt-6">
                        <button type="button" onclick="document.getElementById('addCourseModal').classList.add('hidden')"
                                class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-indigo-500 text-white rounded hover:bg-indigo-600">
                            Create Course
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleContentFields(contentType) {
            const videoFields = document.getElementById('videoFields');
            const documentFields = document.getElementById('documentFields');
            
            if (contentType === 'video') {
                videoFields.classList.remove('hidden');
                documentFields.classList.add('hidden');
            } else {
                videoFields.classList.add('hidden');
                documentFields.classList.remove('hidden');
            }
        }

        // Add fade out effect for alert messages
        document.addEventListener('DOMContentLoaded', function() {
            // setTimeout(function() {
            //     const alerts = document.querySelectorAll('.bg-green-100, .bg-red-100');
            //     alerts.forEach(alert => {
            //         alert.style.transition = 'opacity 0.5s ease-in-out';
            //         alert.style.opacity = '0';
            //         setTimeout(() => alert.remove(), 500);
            //     });
            // }, 3000);

            // Initialize content type fields
            toggleContentFields(document.querySelector('[name="content_type"]').value);
        });
    </script>
</body>
</html>