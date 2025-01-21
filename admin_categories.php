<?php
session_start();
require_once 'config/Database.php';
require_once 'classes/Admin.php';
require_once 'classes/Category.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$admin = new Admin($db, ['user_id' => $_SESSION['user_id']]);
$category = new Category($db);

// Handle category actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'create':
                if ($category->create(['name' => $_POST['name']])) {
                    $_SESSION['message'] = "Category created successfully";

                }
                break;
                
            case 'update':
                if ($category->update($_POST['category_id'], ['name' => $_POST['name']])) {
                    $_SESSION['message'] = "Category updated successfully";
                }
                break;
                
            case 'delete':
                if ($category->delete($_POST['category_id'])) {
                    $_SESSION['message'] = "Category deleted successfully";

                }
                break;
        }
    }
}

// Get all categories
$categories = $category->getAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Category Management - SkillUp</title>
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
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold">Category Management</h2>
                <button onclick="document.getElementById('addCategoryModal').classList.remove('hidden')"
                        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    <i class="fas fa-plus mr-2"></i> Add Category
                </button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full table-auto">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left">Name</th>
                            <th class="px-6 py-3 text-left">Courses Count</th>
                            <th class="px-6 py-3 text-left">Created At</th>
                            <th class="px-6 py-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): ?>
                            <tr class="border-b">
                                <td class="px-6 py-4"><?php echo htmlspecialchars($cat['name']); ?></td>
                                <td class="px-6 py-4">
                                    <?php echo $category->getCourseCount($cat['category_id']); ?>
                                </td>
                                <td class="px-6 py-4">
                                <?php echo date('l, F j, Y - h:i A', strtotime($cat['created_at'])); ?>
                                </td>
                                <td class="px-6 py-4">
                                    <button onclick="editCategory('<?php echo $cat['category_id']; ?>', '<?php echo htmlspecialchars($cat['name']); ?>')"
                                            class="bg-yellow-500 text-white px-3 py-1 rounded mr-2 hover:bg-yellow-600">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="category_id" value="<?php echo $cat['category_id']; ?>">
                                        <button type="submit" 
                                                onclick="return confirm('Are you sure? This will remove the category from all associated courses.')"
                                                class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">
                                            <i class="fas fa-trash"></i>
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

    <!-- Add Category Modal -->
    <div id="addCategoryModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Add New Category</h3>
                <form method="POST" class="mt-4">
                    <input type="hidden" name="action" value="create">
                    <input type="text" name="name" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                           placeholder="Category Name">
                    <div class="mt-4 flex justify-end">
                        <button type="button" 
                                onclick="document.getElementById('addCategoryModal').classList.add('hidden')"
                                class="mr-2 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-500 rounded-md hover:bg-blue-600">
                            Add Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div id="editCategoryModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Edit Category</h3>
                <form method="POST" class="mt-4">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="category_id" id="edit_category_id">
                    <input type="text" name="name" id="edit_category_name" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <div class="mt-4 flex justify-end">
                        <button type="button" 
                                onclick="document.getElementById('editCategoryModal').classList.add('hidden')"
                                class="mr-2 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-500 rounded-md hover:bg-blue-600">
                            Update Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function editCategory(id, name) {
            document.getElementById('edit_category_id').value = id;
            document.getElementById('edit_category_name').value = name;
            document.getElementById('editCategoryModal').classList.remove('hidden');
        }
    </script>
</body>
</html>