<?php
require_once 'config/Database.php';

$database = new Database();
$db = $database->getConnection();

// Get search parameters
$search = $_GET['search'] ?? '';

// Basic query to get available courses that are accepted
$query = "SELECT c.*, u.username as instructor_name, 
          cat.name as category_name,
          GROUP_CONCAT(t.name) as tags
          FROM courses c 
          LEFT JOIN categories cat ON c.category_id = cat.category_id
          LEFT JOIN users u ON c.teacher_id = u.user_id
          LEFT JOIN course_tags ct ON c.course_id = ct.course_id
          LEFT JOIN tags t ON ct.tag_id = t.tag_id
          WHERE c.status = 'accepted'";

if (!empty($search)) {
    $query .= " AND (c.description LIKE :search 
               OR c.title LIKE :search 
               OR u.username LIKE :search 
               OR t.name LIKE :search
               OR cat.name LIKE :search)";
}

$query .= " GROUP BY c.course_id";

$stmt = $db->prepare($query);

if (!empty($search)) {
    $searchTerm = "%$search%";
    $stmt->bindParam(':search', $searchTerm);
}

$stmt->execute();
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome to SkillUp</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-100">
    <!-- Top Navigation -->
    <nav class="bg-blue-600 text-white px-6 py-4">
        <div class="flex justify-between items-center">
            <h1 class="text-xl font-bold">SkillUp</h1>
            <div class="flex items-center gap-4">
                <a href="login.php" class="bg-blue-700 px-4 py-2 rounded hover:bg-blue-800">Login</a>
                <a href="register.php" class="bg-green-600 px-4 py-2 rounded hover:bg-green-700">Register</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <!-- Hero Section -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-800 mb-4">Discover Your Next Skill</h1>
            <p class="text-xl text-gray-600">Browse our collection of courses and start learning today</p>
        </div>

        <!-- Search Bar -->
        <div class="mb-8">
            <form action="" method="GET" class="flex gap-4">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Search by course name, instructor, category, or tags..." 
                       class="flex-1 p-2 border rounded">
                <button type="submit" 
                        class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
                    <i class="fas fa-search mr-2"></i>Search
                </button>
            </form>
        </div>

        <!-- Available Courses -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($courses as $course): ?>
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

                        <a href="login.php" 
                           class="block w-full text-center bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                            Enroll Now
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-12 py-8">
        <div class="container mx-auto px-4 text-center">
            <p>© 2024 SkillUp. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>