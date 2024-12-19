<?php
session_start();
require_once 'database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];

// Handle delete user
if (isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    try {
        $stmt = $conn->prepare("DELETE FROM users WHERE id_user = :id");
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
    } catch(PDOException $e) {
        $error_message = "Error deleting user: " . $e->getMessage();
    }
}

// Handle delete project
if (isset($_POST['delete_project'])) {
    $project_id = $_POST['project_id'];
    try {
        $stmt = $conn->prepare("DELETE FROM Projects WHERE project_id = :id");
        $stmt->bindParam(':id', $project_id);
        $stmt->execute();
    } catch(PDOException $e) {
        $error_message = "Error deleting project: " . $e->getMessage();
    }
}

// Handle delete freelancer
if (isset($_POST['delete_freelancer'])) {
    $freelancer_id = $_POST['freelancer_id'];
    try {
        $stmt = $conn->prepare("DELETE FROM freelancers WHERE id = :id");
        $stmt->bindParam(':id', $freelancer_id);
        $stmt->execute();
    } catch(PDOException $e) {
        $error_message = "Error deleting freelancer: " . $e->getMessage();
    }
}

// Get all users
$users_stmt = $conn->query("SELECT * FROM users WHERE role != 'admin'");
$users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all projects
$projects_stmt = $conn->prepare("
    SELECT p.*, u.nom_user, c.category_name 
    FROM Projects p 
    LEFT JOIN users u ON p.id_user = u.id_user 
    LEFT JOIN Categories c ON p.category_id = c.category_id
");
$projects_stmt->execute();
$projects = $projects_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all freelancers
$freelancers_stmt = $conn->query("SELECT * FROM freelancers");
$freelancers = $freelancers_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-[#1e2936]">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="w-64 bg-[#1e2936] border-r border-gray-700">
            <div class="flex items-center p-4">
                <span class="text-2xl font-bold text-green-500">IT</span>
                <span class="text-2xl font-bold text-white">THINK</span>
            </div>
            <div class="px-4 py-2">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full bg-red-500 flex items-center justify-center text-white font-bold">
                        A
                    </div>
                    <div>
                        <p class="text-white font-medium">Admin Panel</p>
                        <p class="text-gray-400 text-sm"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                </div>
            </div>
            <nav class="mt-8">
                <a href="#users" class="flex items-center px-6 py-3 text-white bg-gray-800">
                    <i class="fas fa-users mr-3"></i>
                    <span>Users</span>
                </a>
                <a href="#projects" class="flex items-center px-6 py-3 text-gray-400 hover:bg-gray-800">
                    <i class="fas fa-project-diagram mr-3"></i>
                    <span>Projects</span>
                </a>
                <a href="#freelancers" class="flex items-center px-6 py-3 text-gray-400 hover:bg-gray-800">
                    <i class="fas fa-laptop-code mr-3"></i>
                    <span>Freelancers</span>
                </a>
                <a href="logout.php" class="flex items-center px-6 py-3 text-red-400 hover:bg-gray-800">
                    <i class="fas fa-sign-out-alt mr-3"></i>
                    <span>Logout</span>
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1">
            <!-- Top Navigation -->
            <div class="bg-[#1e2936] border-b border-gray-700 p-4">
                <div class="flex justify-between items-center">
                    <h1 class="text-xl font-bold text-white">Admin Dashboard</h1>
                </div>
            </div>

            <!-- Dashboard Content -->
            <div class="p-6">
                <!-- Users Section -->
                <div id="users" class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-bold">Users Management</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($users as $u): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 font-medium text-gray-900">
                                        <?php echo htmlspecialchars($u['nom_user']); ?>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500">
                                        <?php echo htmlspecialchars($u['email']); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                            <input type="hidden" name="user_id" value="<?php echo $u['id_user']; ?>">
                                            <button type="submit" name="delete_user" class="text-red-600 hover:text-red-900">
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

                <!-- Projects Section -->
                <div id="projects" class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-bold">Projects Management</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Project</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($projects as $project): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">
                                            <?php echo htmlspecialchars($project['title']); ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?php echo htmlspecialchars($project['description']); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500">
                                        <?php echo htmlspecialchars($project['nom_user']); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">
                                            <?php echo htmlspecialchars($project['category_name']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this project?');">
                                            <input type="hidden" name="project_id" value="<?php echo $project['project_id']; ?>">
                                            <button type="submit" name="delete_project" class="text-red-600 hover:text-red-900">
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

                <!-- Freelancers Section -->
                <div id="freelancers" class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-bold">Freelancers Management</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Skills</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($freelancers as $freelancer): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">
                                       <?php echo htmlspecialchars($freelancer['name']); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php 
                                        $skills = explode(',', $freelancer['skills']);
                                        foreach ($skills as $skill): ?>
                                            <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full mr-1">
                                                <?php echo htmlspecialchars(trim($skill)); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this freelancer?');">
                                            <input type="hidden" name="freelancer_id" value="<?php echo $freelancer['id']; ?>">
                                            <button type="submit" name="delete_freelancer" class="text-red-600 hover:text-red-900">
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
        </div>
    </div>
</body>
</html>