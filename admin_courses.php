<?php
session_start();
require_once 'config/Database.php';
require_once 'classes/Admin.php';
require_once 'classes/Course.php';
require_once 'classes/Category.php';
require_once 'classes/Tags.php';
require_once 'classes/CourseTags.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$admin = new Admin($db, ['user_id' => $_SESSION['user_id']]);

// Handle course actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $courseId = filter_var($_POST['course_id'], FILTER_VALIDATE_INT);
    $action = $_POST['action'];
    
    if ($courseId) {
        switch($action) {
            case 'approve':
                $admin->updateCourseStatus($courseId, 'accepted');
                $_SESSION['message'] = "Course approved successfully";
                break;
            case 'reject':
                $admin->updateCourseStatus($courseId, 'refused');
                $_SESSION['message'] = "Course rejected successfully";
                break;
                case 'delete':
                    try {
                        $admin->deleteCourse($courseId);
                        $_SESSION['message'] = "Course deleted successfully";
                    } catch (PDOException $e) {
                        $_SESSION['message'] = "Error deleting course: " . $e->getMessage();
                    }
                    break;
        }
    }
}

// Get all courses with instructor and category info
$query = "SELECT c.*, u.username as instructor_name, cat.name as category_name 
          FROM courses c 
          LEFT JOIN users u ON c.teacher_id = u.user_id 
          LEFT JOIN categories cat ON c.category_id = cat.category_id 
          ORDER BY c.created_at DESC";
$stmt = $db->query($query);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories for filtering
$category = new Category($db);
$categories = $category->getAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Course Management - SkillUp</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'includes/admin_nav.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php 
                echo htmlspecialchars($_SESSION['message']);
                unset($_SESSION['message']);
                ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold mb-4">Course Management</h2>
            
            <!-- Filter Controls -->
            <div class="mb-6 flex gap-4">
                <select id="categoryFilter" class="border rounded px-3 py-2">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['category_id']; ?>">
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <select id="statusFilter" class="border rounded px-3 py-2">
                    <option value="">All Statuses</option>
                    <option value="in_progress">Pending</option>
                    <option value="accepted">Approved</option>
                    <option value="refused">Refused</option>
                </select>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full table-auto">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left">Title</th>
                            <th class="px-6 py-3 text-left">Instructor</th>
                            <th class="px-6 py-3 text-left">Category</th>
                            <th class="px-6 py-3 text-left">Type</th>
                            <th class="px-6 py-3 text-left">Status</th>
                            <th class="px-6 py-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $course): ?>
                            <tr class="border-b">
                                <td class="px-6 py-4"><?php echo htmlspecialchars($course['title']); ?></td>
                                <td class="px-6 py-4"><?php echo htmlspecialchars($course['instructor_name']); ?></td>
                                <td class="px-6 py-4"><?php echo htmlspecialchars($course['category_name']); ?></td>
                                <td class="px-6 py-4 capitalize"><?php echo htmlspecialchars($course['content_type']); ?></td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded text-sm <?php 
                                        echo $course['status'] === 'accepted' ? 'bg-green-100 text-green-800' : 
                                             ($course['status'] === 'refused' ? 'bg-red-100 text-red-800' : 
                                              'available'); 
                                    ?>">
                                        <?php echo ucfirst($course['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="course_id" value="<?php echo $course['course_id']; ?>">
                                        <?php if ($course['status'] === 'in_progress'): ?>
                                            <button type="submit" name="action" value="approve" 
                                                    class="bg-green-500 text-white px-3 py-1 rounded mr-2 hover:bg-green-600">
                                                <i class="fas fa-check mr-1"></i> Approve
                                            </button>
                                            <button type="submit" name="action" value="refused" 
                                                    class="bg-yellow-500 text-white px-3 py-1 rounded mr-2 hover:bg-yellow-600">
                                                <i class="fas fa-times mr-1"></i> Reject
                                            </button>
                                        <?php endif; ?>
                                        <button type="submit" name="action" value="delete" 
                                                class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600"
                                                onclick="return confirm('Are you sure you want to delete this course?')">
                                            <i class="fas fa-trash mr-1"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Add filtering functionality
        // document.getElementById('categoryFilter').addEventListener('change', filterCourses);
        // document.getElementById('statusFilter').addEventListener('change', filterCourses);

        // function filterCourses() {
        //     const categoryId = document.getElementById('categoryFilter').value;
        //     const status = document.getElementById('statusFilter').value;
        //     const rows = document.querySelectorAll('tbody tr');

        //     rows.forEach(row => {
        //         const categoryMatch = !categoryId || row.cells[2].textContent === categoryId;
        //         const statusMatch = !status || row.cells[4].textContent.toLowerCase().includes(status);
        //         row.style.display = categoryMatch && statusMatch ? '' : 'none';
        //     });
        // }
    </script>
</body>
</html>