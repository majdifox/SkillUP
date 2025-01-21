<?php
session_start();
require_once 'config/Database.php';
// require_once 'classes/CrudInterface.php';
require_once 'classes/Admin.php';
require_once 'classes/Tags.php';
require_once 'classes/CourseTags.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$admin = new Admin($db, ['user_id' => $_SESSION['user_id']]);
$tags = new Tags($db);
$courseTags = new CourseTags($db);

// Handle tag actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'add':
                if (!empty($_POST['tag_name'])) {
                    if ($tags->create(['name' => trim($_POST['tag_name'])])) {
                        $_SESSION['message'] = "Tag added successfully";
                    } else {
                        $_SESSION['error'] = "Failed to add tag";
                    }
                }
                break;
                
            case 'edit':
                if (!empty($_POST['tag_id']) && !empty($_POST['tag_name'])) {
                    if ($tags->update($_POST['tag_id'], ['name' => trim($_POST['tag_name'])])) {
                        $_SESSION['message'] = "Tag updated successfully";
                    } else {
                        $_SESSION['error'] = "Failed to update tag";
                    }
                }
                break;
                
            case 'delete':
                if (!empty($_POST['tag_id'])) {
                    // First check if tag is being used
                    $taggedCourses = $tags->getTaggedCourses($_POST['tag_id']);
                    if (empty($taggedCourses)) {
                        if ($tags->delete($_POST['tag_id'])) {
                            $_SESSION['message'] = "Tag deleted successfully";
                        } else {
                            $_SESSION['error'] = "Failed to delete tag";
                        }
                    } else {
                        $_SESSION['error'] = "Cannot delete tag as it is being used by courses";
                    }
                }
                break;
        }
    }
}

// Get all tags with course count
$query = "SELECT t.*, COUNT(ct.course_id) as course_count 
          FROM tags t 
          LEFT JOIN course_tags ct ON t.tag_id = ct.tag_id 
          GROUP BY t.tag_id 
          ORDER BY t.name ASC";
$stmt = $db->query($query);
$allTags = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tag Management - SkillUp</title>
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

        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php 
                echo htmlspecialchars($_SESSION['error']);
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold">Tag Management</h2>
                <button onclick="openAddModal()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    <i class="fas fa-plus mr-2"></i> Add New Tag
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full table-auto">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left">Tag Name</th>
                            <th class="px-6 py-3 text-left">Courses Using Tag</th>
                            <th class="px-6 py-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allTags as $tag): ?>
                            <tr class="border-b">
                                <td class="px-6 py-4"><?php echo htmlspecialchars($tag['name']); ?></td>
                                <td class="px-6 py-4"><?php echo $tag['course_count']; ?></td>
                                <td class="px-6 py-4">
                                    <button onclick="openEditModal(<?php echo $tag['tag_id']; ?>, '<?php echo htmlspecialchars($tag['name']); ?>')" 
                                            class="bg-yellow-500 text-white px-3 py-1 rounded mr-2 hover:bg-yellow-600">
                                        <i class="fas fa-edit mr-1"></i> Edit
                                    </button>
                                    <?php if ($tag['course_count'] == 0): ?>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="tag_id" value="<?php echo $tag['tag_id']; ?>">
                                            <button type="submit" name="action" value="delete" 
                                                    class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600"
                                                    onclick="return confirm('Are you sure you want to delete this tag?')">
                                                <i class="fas fa-trash mr-1"></i> Delete
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Tag Modal -->
    <div id="addModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
                <h3 class="text-lg font-bold mb-4">Add New Tag</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Tag Name</label>
                        <input type="text" name="tag_name" required
                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="closeModal('addModal')"
                                class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Cancel</button>
                        <button type="submit"
                                class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Add Tag</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Tag Modal -->
    <div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
                <h3 class="text-lg font-bold mb-4">Edit Tag</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="tag_id" id="editTagId">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Tag Name</label>
                        <input type="text" name="tag_name" id="editTagName" required
                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="closeModal('editModal')"
                                class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Cancel</button>
                        <button type="submit"
                                class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Update Tag</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('addModal').classList.remove('hidden');
        }

        function openEditModal(tagId, tagName) {
            document.getElementById('editTagId').value = tagId;
            document.getElementById('editTagName').value = tagName;
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('fixed')) {
                event.target.classList.add('hidden');
            }
        }

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