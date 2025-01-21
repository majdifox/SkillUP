<?php
session_start();
require_once 'config/Database.php';
require_once 'classes/Admin.php';
require_once 'classes/UserFactory.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$admin = new Admin($db, ['user_id' => $_SESSION['user_id']]);
$userFactory = new UserFactory($db);

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = filter_var($_POST['user_id'], FILTER_VALIDATE_INT);
    $action = $_POST['action'];
    
    if ($userId) {
        switch($action) {
            case 'activate':
                $admin->update($userId, ['is_active' => 1, 'status' => 'accepted']);
                $_SESSION['message'] = "User activated successfully";
                break;
            case 'deactivate':
                $admin->update($userId, ['is_active' => 0, 'status' => 'refused']);
                $_SESSION['message'] = "User deactivated successfully";
                break;
            case 'delete':
                $admin->delete($userId);
                $_SESSION['message'] = "User deleted successfully";
                break;
        }
    }
}

// Get all users
$query = "SELECT * FROM users WHERE role != 'admin' ORDER BY created_at DESC";
$stmt = $db->query($query);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Management - SkillUp</title>
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
            <h2 class="text-xl font-bold mb-4">User Management</h2>
            
            <div class="overflow-x-auto">
                <table class="w-full table-auto">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left">Username</th>
                            <th class="px-6 py-3 text-left">Email</th>
                            <th class="px-6 py-3 text-left">Role</th>
                            <th class="px-6 py-3 text-left">Status</th>
                            <th class="px-6 py-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr class="border-b">
                                <td class="px-6 py-4"><?php echo htmlspecialchars($user['username']); ?></td>
                                <td class="px-6 py-4"><?php echo htmlspecialchars($user['email']); ?></td>
                                <td class="px-6 py-4 capitalize"><?php echo htmlspecialchars($user['role']); ?></td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded text-sm <?php 
                                        echo $user['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; 
                                    ?>">
                                        <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                        <?php if (!$user['is_active']): ?>
                                            <button type="submit" name="action" value="activate" 
                                                    class="bg-green-500 text-white px-3 py-1 rounded mr-2 hover:bg-green-600">
                                                <i class="fas fa-check mr-1"></i> Activate
                                            </button>
                                        <?php else: ?>
                                            <button type="submit" name="action" value="deactivate" 
                                                    class="bg-yellow-500 text-white px-3 py-1 rounded mr-2 hover:bg-yellow-600">
                                                <i class="fas fa-ban mr-1"></i> Deactivate
                                            </button>
                                        <?php endif; ?>
                                        <button type="submit" name="action" value="delete" 
                                                class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600"
                                                onclick="return confirm('Are you sure you want to delete this user?')">
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
</body>
</html>