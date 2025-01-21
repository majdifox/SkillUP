<?php
require_once 'config/Database.php';
require_once 'classes/Users.php';
require_once 'classes/Instructor.php';
require_once 'classes/CourseTags.php';

session_start();

// Security check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'instructor') {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$instructor = new Instructor($db, ['user_id' => $_SESSION['user_id']]);

// Fetch categories
$query = "SELECT * FROM categories";
$stmt = $db->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch tags
$query = "SELECT * FROM tags";
$stmt = $db->prepare($query);
$stmt->execute();
$tags = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch course data and its tags
if (isset($_GET['id'])) {
    $course_id = $_GET['id'];
    $query = "SELECT c.*, cat.name as category_name,
              GROUP_CONCAT(ct.tag_id) as selected_tags 
              FROM courses c 
              LEFT JOIN categories cat ON c.category_id = cat.category_id 
              LEFT JOIN course_tags ct ON c.course_id = ct.course_id
              WHERE c.course_id = :course_id AND c.teacher_id = :teacher_id
              GROUP BY c.course_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':course_id', $course_id);
    $stmt->bindParam(':teacher_id', $_SESSION['user_id']);
    $stmt->execute();
    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$course) {
        header('Location: instructordash.php');
        exit();
    }

    // Convert selected_tags string to array
    $selected_tags = $course['selected_tags'] ? explode(',', $course['selected_tags']) : [];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uploadDir = 'uploads/';
    $courseData = [
        'title' => $_POST['title'],
        'description' => $_POST['description'],
        'difficulty_level' => $_POST['difficulty_level'],
        'duration_type' => $_POST['duration_type'],
        'duration_value' => $_POST['duration_value'],
        'category_id' => $_POST['category_id'],
        'content_type' => $_POST['content_type']
    ];

    // Handle thumbnail upload if new file is provided
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === 0) {
        $thumbnail = $_FILES['thumbnail'];
        $thumbnailName = uniqid() . '_' . basename($thumbnail['name']);
        $thumbnailPath = $uploadDir . 'thumbnails/' . $thumbnailName;
        
        if (move_uploaded_file($thumbnail['tmp_name'], $thumbnailPath)) {
            $courseData['thumbnail_url'] = $thumbnailPath;
        }
    }

    // Handle content file upload based on type
    if ($_POST['content_type'] === 'video') {
        if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] === 0) {
            $video = $_FILES['video_file'];
            $videoName = uniqid() . '_' . basename($video['name']);
            $videoPath = $uploadDir . 'videos/' . $videoName;
            
            if (move_uploaded_file($video['tmp_name'], $videoPath)) {
                $courseData['video_url'] = $videoPath;
                $courseData['video_length'] = $_POST['video_length'];
                $courseData['document_url'] = null;
                $courseData['document_pages'] = null;
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
                $courseData['video_url'] = null;
                $courseData['video_length'] = null;
            }
        }
    }

    // Build update query
    $query = "UPDATE courses SET 
              title = :title,
              description = :description,
              difficulty_level = :difficulty_level,
              duration_type = :duration_type,
              duration_value = :duration_value,
              category_id = :category_id,
              content_type = :content_type";
    
    if (isset($courseData['thumbnail_url'])) {
        $query .= ", thumbnail_url = :thumbnail_url";
    }
    if (isset($courseData['video_url'])) {
        $query .= ", video_url = :video_url, video_length = :video_length, document_url = NULL, document_pages = NULL";
    }
    if (isset($courseData['document_url'])) {
        $query .= ", document_url = :document_url, document_pages = :document_pages, video_url = NULL, video_length = NULL";
    }
    
    $query .= " WHERE course_id = :course_id AND teacher_id = :teacher_id";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':course_id', $course_id);
    $stmt->bindParam(':teacher_id', $_SESSION['user_id']);
    
    foreach ($courseData as $key => $value) {
        if ($value !== null) {
            $stmt->bindValue(":$key", $value);
        }
    }

    if ($stmt->execute()) {
        // Update tags
        if (isset($_POST['tag_id'])) {
            // First, delete existing tags
            $delete_query = "DELETE FROM course_tags WHERE course_id = :course_id";
            $delete_stmt = $db->prepare($delete_query);
            $delete_stmt->bindParam(':course_id', $course_id);
            $delete_stmt->execute();

            // Then insert new tags
            $courseTags = new CourseTags($db);
            foreach ($_POST['tag_id'] as $tag_id) {
                $tagData = [
                    'course_id' => $course_id,
                    'tag_id' => $tag_id
                ];
                $courseTags->create($tagData);
            }
        }

        $_SESSION['message'] = "Course updated successfully!";
        header('Location: instructordash.php');
        exit();
    } else {
        $_SESSION['error'] = "Failed to update course.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Course - SkillUp</title>
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
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-2xl font-bold mb-6">Edit Course</h1>
            
            <form method="POST" enctype="multipart/form-data">
                <!-- Basic Course Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Title</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($course['title']); ?>" 
                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Category</label>
                        <select name="category_id" class="shadow border rounded w-full py-2 px-3 text-gray-700" required>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['category_id']; ?>" 
                                    <?php echo $category['category_id'] == $course['category_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Tags (Select up to 10)</label>
                        <div class="max-h-40 overflow-y-auto border rounded p-2">
                            <?php foreach ($tags as $tag): ?>
                                <label class="block">
                                    <input type="checkbox" name="tag_id[]" 
                                           value="<?php echo $tag['tag_id']; ?>" 
                                           <?php echo in_array($tag['tag_id'], $selected_tags) ? 'checked' : ''; ?>>
                                    <?php echo htmlspecialchars($tag['name']); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Course Description -->
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Description</label>
                    <textarea name="description" rows="4" 
                              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" 
                              required><?php echo htmlspecialchars($course['description']); ?></textarea>
                </div>

                <!-- Course Settings -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Difficulty Level</label>
                        <select name="difficulty_level" class="shadow border rounded w-full py-2 px-3 text-gray-700" required>
                            <option value="beginner" <?php echo $course['difficulty_level'] == 'beginner' ? 'selected' : ''; ?>>Beginner</option>
                            <option value="intermediate" <?php echo $course['difficulty_level'] == 'intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                            <option value="advanced" <?php echo $course['difficulty_level'] == 'advanced' ? 'selected' : ''; ?>>Advanced</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Content Type</label>
                        <select name="content_type" class="shadow border rounded w-full py-2 px-3 text-gray-700" 
                                required onchange="toggleContentFields(this.value)">
                            <option value="document" <?php echo $course['content_type'] == 'document' ? 'selected' : ''; ?>>Document</option>
                            <option value="video" <?php echo $course['content_type'] == 'video' ? 'selected' : ''; ?>>Video</option>
                        </select>
                    </div>
                </div>

                <!-- Course Duration -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Duration Type</label>
                        <select name="duration_type" class="shadow border rounded w-full py-2 px-3 text-gray-700" required>
                            <option value="hours" <?php echo $course['duration_type'] == 'hours' ? 'selected' : ''; ?>>Hours</option>
                            <option value="days" <?php echo $course['duration_type'] == 'days' ? 'selected' : ''; ?>>Days</option>
                            <option value="weeks" <?php echo $course['duration_type'] == 'weeks' ? 'selected' : ''; ?>>Weeks</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Duration Value</label>
                        <input type="number" name="duration_value" value="<?php echo htmlspecialchars($course['duration_value']); ?>" 
                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required min="1">
                    </div>
                </div>

                <!-- Thumbnail Upload -->
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Course Thumbnail (optional)</label>
                    <input type="file" name="thumbnail" accept="image/*" 
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                    <?php if ($course['thumbnail_url']): ?>
                        <div class="mt-2">
                            <p class="text-sm text-gray-600">Current thumbnail:</p>
                            <img src="<?php echo htmlspecialchars($course['thumbnail_url']); ?>" 
                                 alt="Current thumbnail" class="mt-1 h-20">
                        </div>
                    <?php endif; ?>
                    <p class="text-sm text-gray-500 mt-1">Recommended size: 1280x720 pixels</p>
                </div>

                <!-- Video Content Fields -->
                <div id="videoFields" class="<?php echo $course['content_type'] != 'video' ? 'hidden' : ''; ?> mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Video File (optional)</label>
                            <input type="file" name="video_file" accept="video/*" 
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                            <?php if ($course['video_url']): ?>
                                <p class="mt-2 text-sm text-gray-600">Current video: <?php echo basename($course['video_url']); ?></p>
                            <?php endif; ?>
                            <p class="text-sm text-gray-500 mt-1">Maximum file size: 500MB</p>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Video Length (minutes)</label>
                            <input type="number" name="video_length" 
                                   value="<?php echo htmlspecialchars($course['video_length'] ?? ''); ?>" 
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" 
                                   min="1">
                        </div>
                    </div>
                </div>

                <!-- Document Content Fields -->
                <div id="documentFields" class="<?php echo $course['content_type'] != 'document' ? 'hidden' : ''; ?> mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Document File (optional)</label>
                            <input type="file" name="document_file" accept=".pdf,.doc,.docx" 
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                            <?php if ($course['document_url']): ?>
                                <p class="mt-2 text-sm text-gray-600">Current document: <?php echo basename($course['document_url']); ?></p>
                            <?php endif; ?>
                            <p class="text-sm text-gray-500 mt-1">Accepted formats: PDF, DOC, DOCX</p>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Number of Pages</label>
                            <input type="number" name="document_pages" 
                                   value="<?php echo htmlspecialchars($course['document_pages'] ?? ''); ?>" 
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" 
                                   min="1">
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex justify-end gap-4 mt-6">
                    <a href="instructordash.php" 
                       class="px-6 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 inline-block">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                        Update Course
                    </button>
                </div>
            </form>
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

        // Limit tag selection to 10
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('input[type="checkbox"][name="tag_id[]"]');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const checked = document.querySelectorAll('input[type="checkbox"][name="tag_id[]"]:checked');
                    if (checked.length > 10) {
                        this.checked = false;
                        alert('You can only select up to 10 tags');
                    }
                });
            });
        });

        // Initialize content fields based on current selection
        document.addEventListener('DOMContentLoaded', function() {
            toggleContentFields(document.querySelector('[name="content_type"]').value);
        });
    </script>
</body>
</html>